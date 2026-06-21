<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Kaikon2\Kaikondb\Models\Journal;
use Kaikon2\Kaikondb\Models\Literature;
use Kaikon2\Kaikondb\Models\LiteratureHistory;
use Kaikon2\Kaikondb\Models\Order;
use Kaikon2\Kaikondb\Models\User;

class LiteratureIoController extends Controller
{
  private const IMPORT_COLUMNS = [
    'id',
    'delete_flg',
    'author',
    'author_en',
    'year',
    'title',
    'title_en',
    'language_id',
    'journal_code',
    'publisher',
    'vol_no',
    'page',
    'order_ids',
    'link',
    'comment',
    'memo1',
  ];

  public function index()
  {
    return view('kaikon::admin.literatures-io');
  }

  public function export()
  {
    $user = $this->currentUser();
    $literatures = Literature::query()->accessibleBy($user)->get();

    $stream = fopen('php://temp', 'w');
    $csvheader = '"id","code","author","author_en","year","title","title_en","vol_no","journal_id","publisher","page","language_id","memo1","memo2","memo3","memo4","memo5","memo6","memo7","memo8","memo9","memo10","inventory","random_id","link","comment","created_at","updated_at","deleted_at","user_id"' . "\n";
    fwrite($stream, $csvheader);

    foreach ($literatures as $literature) {
      $csvdata = [
        $literature->id,
        $literature->code,
        $literature->author,
        $literature->author_en,
        $literature->year,
        $literature->title,
        $literature->title_en,
        $literature->vol_no,
        $literature->journal_id,
        $literature->publisher,
        $literature->page,
        $literature->language_id,
        $literature->memo1,
        $literature->memo2,
        $literature->memo3,
        $literature->memo4,
        $literature->memo5,
        $literature->memo6,
        $literature->memo7,
        $literature->memo8,
        $literature->memo9,
        $literature->memo10,
        $literature->inventory,
        $literature->random_id,
        $literature->link,
        $literature->comment,
        $literature->created_at,
        $literature->updated_at,
        $literature->deleted_at,
        $literature->user_id,
      ];
      fwrite($stream, '"' . implode('","', $csvdata) . "\"\n");
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    $csv = mb_convert_encoding($csv, 'UTF-8');
    fclose($stream);

    return Response::make($csv, 200, [
      'Content-Type' => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename=literatures.csv',
    ]);
  }

  public function importFormat()
  {
    $header = implode(',', self::IMPORT_COLUMNS);

    return Response::make($header . "\n", 200, [
      'Content-Type' => 'text/csv; charset=UTF-8',
      'Content-Disposition' => 'attachment; filename=literatures_import_format.csv',
    ]);
  }

  public function import(Request $request): JsonResponse
  {
    $request->validate([
      'csv_file' => ['required', 'file', 'mimes:csv,txt'],
    ]);

    $rows = $this->parseCsvRows($request->file('csv_file')->getRealPath());
    $user = $this->currentUser();

    $createdCount = 0;
    $updatedCount = 0;
    $deletedCount = 0;
    $skippedCount = 0;

    DB::transaction(function () use ($rows, $user, &$createdCount, &$updatedCount, &$deletedCount, &$skippedCount) {
      foreach ($rows as $row) {
        $lineNo = $row['_line_no'];
        $deleteFlg = ($row['delete_flg'] ?? '') === '1' ? 1 : 0;
        $id = $row['id'] ?? '';

        if ($deleteFlg === 1) {
          $this->processDeleteRow($row, $lineNo, $user, $deletedCount, $skippedCount);
          continue;
        }

        if ($id !== '') {
          if ($this->processUpdateRow($row, $lineNo, $user, $skippedCount)) {
            $updatedCount++;
          }
          continue;
        }

        $this->processAddRow($row, $lineNo);
        $createdCount++;
      }
    });

    return response()->json([
      'message' => 'CSV取込が完了しました。',
      'createdCount' => $createdCount,
      'updatedCount' => $updatedCount,
      'deletedCount' => $deletedCount,
      'skippedCount' => $skippedCount,
    ]);
  }

  public function check(Request $request): JsonResponse
  {
    $request->validate([
      'checks' => ['required', 'array', 'min:1'],
      'checks.*' => ['in:duplicate'],
    ], [
      'checks.required' => 'チェック項目を1つ以上選択してください。',
      'checks.min' => 'チェック項目を1つ以上選択してください。',
    ]);

    $checks = $request->input('checks', []);
    $rows = [];
    $messages = [];

    if (in_array('duplicate', $checks, true)) {
      $duplicateRows = $this->buildDuplicateCheckRows($this->currentUser());
      if ($duplicateRows !== []) {
        $messages[] = 'エラー：重複レコードがあります';
        $rows = array_merge($rows, $duplicateRows);
      }
    }

    if ($rows === []) {
      return response()->json([
        'hasIssues' => false,
        'message' => 'エラーはありませんでした',
      ]);
    }

    return response()->json([
      'hasIssues' => true,
      'messages' => $messages,
      'csv' => base64_encode($this->buildCheckResultCsv($rows)),
      'filename' => 'literatures_check_result.csv',
    ]);
  }

  private function buildCheckResultCsv(array $rows): string
  {
    $stream = fopen('php://temp', 'w');
    $csvheader = '"check_type","group_id","duplicate_count","id","author","author_en","year","title","title_en","journal_id","vol_no","page","random_id"' . "\n";
    fwrite($stream, $csvheader);

    foreach ($rows as $row) {
      fwrite($stream, '"' . implode('","', $row) . "\"\n");
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return mb_convert_encoding($csv, 'UTF-8');
  }

  private function buildDuplicateCheckRows(User $user): array
  {
    $literatures = Literature::query()
      ->accessibleBy($user)
      ->orderBy('id')
      ->get(['id', 'author', 'author_en', 'year', 'title', 'title_en', 'journal_id', 'vol_no', 'page', 'random_id']);

    $groups = [];

    foreach ($literatures as $literature) {
      $key = $this->duplicateGroupKey($literature);
      $groups[$key][] = $literature;
    }

    $rows = [];
    $groupId = 1;

    foreach ($groups as $group) {
      if (count($group) < 2) {
        continue;
      }

      $duplicateCount = count($group);

      foreach ($group as $literature) {
        $rows[] = [
          '文献重複チェック',
          $groupId,
          $duplicateCount,
          $literature->id,
          $literature->author,
          $literature->author_en,
          $literature->year,
          $literature->title,
          $literature->title_en,
          $literature->journal_id,
          $literature->vol_no,
          $literature->page,
          $literature->random_id,
        ];
      }

      $groupId++;
    }

    return $rows;
  }

  private function duplicateGroupKey(Literature $literature): string
  {
    $author = mb_strtolower(trim((string) $literature->author));
    $title = mb_strtolower(trim((string) $literature->title));

    return $author . '|' . $literature->year . '|' . $title . '|' . $literature->journal_id;
  }

  private function parseCsvRows(string $path): array
  {
    $this->validateCsvFormat($path);

    $handle = fopen($path, 'r');

    if ($handle === false) {
      throw ValidationException::withMessages([
        'csv_file' => 'CSVファイルを開けませんでした。',
      ]);
    }

    $header = fgetcsv($handle, 0, ',', '"', '\\');

    if ($header === false) {
      fclose($handle);

      throw ValidationException::withMessages([
        'csv_file' => 'CSVヘッダが読み取れません。',
      ]);
    }

    $normalizedHeader = $this->normalizeHeader($header);
    $sortedHeader = $normalizedHeader;
    $sortedExpected = self::IMPORT_COLUMNS;

    sort($sortedHeader);
    sort($sortedExpected);

    if ($sortedHeader !== $sortedExpected) {
      fclose($handle);

      throw ValidationException::withMessages([
        'csv_file' => 'CSVヘッダが不正です。' . implode(', ', self::IMPORT_COLUMNS) . ' の16項目を使用してください。列順は任意です。 actual=' . implode(',', $normalizedHeader),
      ]);
    }

    $rows = [];
    $lineNo = 1;

    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
      $lineNo++;

      if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
        continue;
      }

      $row = array_pad($row, count($normalizedHeader), '');
      $assoc = array_combine($normalizedHeader, $row);
      $assoc = array_map(fn ($v) => trim((string) $v), $assoc);
      $assoc['_line_no'] = $lineNo;

      $rows[] = $assoc;
    }

    fclose($handle);

    if ($rows === []) {
      throw ValidationException::withMessages([
        'csv_file' => '取込対象のデータ行がありません。',
      ]);
    }

    return $rows;
  }

  private function validateCsvFormat(string $path): void
  {
    $content = file_get_contents($path);

    if ($content === false) {
      throw ValidationException::withMessages([
        'csv_file' => 'CSVファイルを読み取れませんでした。',
      ]);
    }

    if (str_starts_with($content, "\xEF\xBB\xBF")) {
      $content = substr($content, 3);
    }

    $lines = preg_split('/\r\n|\r|\n/', $content);
    $lineNo = 0;

    foreach ($lines as $line) {
      $lineNo++;

      if (trim($line) === '') {
        continue;
      }

      $this->assertCsvLineUsesCommaDelimiter($line, $lineNo);
      $this->assertCsvLineUsesDoubleQuoteEnclosure($line, $lineNo);
    }
  }

  private function assertCsvLineUsesCommaDelimiter(string $line, int $lineNo): void
  {
    $commaFields = str_getcsv($line, ',', '"', '\\');

    foreach (["\t" => 'タブ', ';' => 'セミコロン'] as $delimiter => $label) {
      if (!str_contains($line, $delimiter)) {
        continue;
      }

      $altFields = str_getcsv($line, $delimiter, '"', '\\');

      if (count($altFields) > count($commaFields)) {
        throw ValidationException::withMessages([
          'csv_file' => "{$lineNo}行目: 区切り文字はカンマ(,)である必要があります（{$label}区切りは使用できません）。",
        ]);
      }
    }
  }

  private function assertCsvLineUsesDoubleQuoteEnclosure(string $line, int $lineNo): void
  {
    $strictFields = $this->parseCsvLineStrict($line);

    if ($strictFields === null) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: 囲い文字は二重引用符(\")である必要があります。",
      ]);
    }

    $parsedFields = str_getcsv($line, ',', '"', '\\');

    if ($strictFields !== $parsedFields) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: 囲い文字は二重引用符(\")である必要があります。",
      ]);
    }
  }

  /**
   * @return array<int, string>|null
   */
  private function parseCsvLineStrict(string $line): ?array
  {
    $fields = [];
    $currentField = '';
    $inQuotes = false;
    $length = strlen($line);

    for ($i = 0; $i < $length; $i++) {
      $char = $line[$i];

      if ($inQuotes) {
        if ($char === '"') {
          if ($i + 1 < $length && $line[$i + 1] === '"') {
            $currentField .= '"';
            $i++;
            continue;
          }

          $inQuotes = false;
          continue;
        }

        $currentField .= $char;
        continue;
      }

      if ($char === '"') {
        $inQuotes = true;
        continue;
      }

      if ($char === ',') {
        $fields[] = $currentField;
        $currentField = '';
        continue;
      }

      $currentField .= $char;
    }

    if ($inQuotes) {
      return null;
    }

    $fields[] = $currentField;

    return $fields;
  }

  private function normalizeHeader(array $header): array
  {
    return array_map(function ($value) {
      $value = (string) $value;
      $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
      $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $value);

      return mb_strtolower(trim($value));
    }, $header);
  }

  private function processDeleteRow(array $row, int $lineNo, User $user, int &$deletedCount, int &$skippedCount): void
  {
    $id = $row['id'] ?? '';

    if ($id === '' || !ctype_digit($id)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: 削除には id（数値）が必須です。",
      ]);
    }

    $literature = Literature::find((int) $id);

    if ($literature === null) {
      $trashed = Literature::onlyTrashed()->find((int) $id);
      if ($trashed !== null) {
        $skippedCount++;

        return;
      }

      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: id={$id} の文献が見つかりません。",
      ]);
    }

    $this->assertCanAccessLiterature($user, $literature, $lineNo);

    $this->recordLiteratureHistory($literature, 'delete', Auth::id());
    $literature->delete();
    $deletedCount++;
  }

  private function processAddRow(array $row, int $lineNo): void
  {
    $validated = $this->validateImportDataRow($row, $lineNo);

    $literature = Literature::create([
      'code' => 0,
      'author' => $validated['author'],
      'author_en' => $validated['authorEn'],
      'year' => $validated['yearInt'],
      'title' => $validated['title'],
      'title_en' => $validated['titleEn'],
      'vol_no' => $validated['volNo'],
      'journal_id' => $validated['journal']->id,
      'publisher' => $validated['publisher'],
      'page' => $validated['page'],
      'language_id' => $validated['languageId'],
      'memo1' => $validated['memo1'],
      'memo2' => '',
      'memo3' => '',
      'memo4' => '',
      'memo5' => '',
      'memo6' => '',
      'memo7' => '',
      'memo8' => '',
      'memo9' => '',
      'memo10' => '',
      'inventory' => 0,
      'random_id' => hash('sha256', uniqid('', true)),
      'link' => $validated['link'],
      'comment' => $validated['comment'],
      'user_id' => Auth::id(),
    ]);

    $literature->orders()->attach($validated['orderIds']);

    $this->recordLiteratureHistory($literature->fresh(), 'create', Auth::id());
  }

  private function processUpdateRow(array $row, int $lineNo, User $user, int &$skippedCount): bool
  {
    $id = $row['id'] ?? '';

    if ($id === '' || !ctype_digit($id)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: 更新には id（数値）が必須です。",
      ]);
    }

    $literature = Literature::find((int) $id);

    if ($literature === null) {
      $trashed = Literature::onlyTrashed()->find((int) $id);
      if ($trashed !== null) {
        $skippedCount++;

        return false;
      }

      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: id={$id} の文献が見つかりません。",
      ]);
    }

    $this->assertCanAccessLiterature($user, $literature, $lineNo);

    $validated = $this->validateImportDataRow($row, $lineNo);

    $literature->update([
      'author' => $validated['author'],
      'author_en' => $validated['authorEn'],
      'year' => $validated['yearInt'],
      'title' => $validated['title'],
      'title_en' => $validated['titleEn'],
      'vol_no' => $validated['volNo'],
      'journal_id' => $validated['journal']->id,
      'publisher' => $validated['publisher'],
      'page' => $validated['page'],
      'language_id' => $validated['languageId'],
      'memo1' => $validated['memo1'],
      'link' => $validated['link'],
      'comment' => $validated['comment'],
    ]);

    $literature->orders()->sync($validated['orderIds']);

    $this->recordLiteratureHistory($literature->fresh(), 'edit', Auth::id());

    return true;
  }

  /**
   * @return array{
   *   author: string,
   *   authorEn: string,
   *   yearInt: int,
   *   title: string,
   *   titleEn: string,
   *   languageId: int,
   *   journal: Journal,
   *   publisher: ?string,
   *   volNo: string,
   *   page: string,
   *   orderIds: array<int, int>,
   *   link: ?string,
   *   comment: ?string,
   *   memo1: string
   * }
   */
  private function validateImportDataRow(array $row, int $lineNo): array
  {
    $author = $row['author'] ?? '';
    $authorEn = $row['author_en'] ?? '';
    $year = $row['year'] ?? '';
    $title = $row['title'] ?? '';
    $titleEn = $row['title_en'] ?? '';
    $languageId = $row['language_id'] ?? '';
    $journalCode = $row['journal_code'] ?? '';
    $publisher = $row['publisher'] ?? '';
    $volNo = $row['vol_no'] ?? '';
    $page = $row['page'] ?? '';
    $orderIdsRaw = $row['order_ids'] ?? '';
    $link = $row['link'] ?? '';
    $comment = $row['comment'] ?? '';
    $memo1 = $row['memo1'] ?? '';

    if ($author === '') {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: author は必須です。",
      ]);
    }
    if (mb_strlen($author) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: author は255文字以内にしてください。",
      ]);
    }
    if ($authorEn !== '' && mb_strlen($authorEn) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: author_en は255文字以内にしてください。",
      ]);
    }
    if ($year === '' || !ctype_digit($year)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: year は1000〜2050の整数で指定してください。",
      ]);
    }
    $yearInt = (int) $year;
    if ($yearInt < 1000 || $yearInt > 2050) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: year は1000〜2050の整数で指定してください。",
      ]);
    }
    if ($title === '') {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: title は必須です。",
      ]);
    }
    if (mb_strlen($title) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: title は255文字以内にしてください。",
      ]);
    }
    if ($titleEn !== '' && mb_strlen($titleEn) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: title_en は255文字以内にしてください。",
      ]);
    }
    if (!in_array($languageId, ['1', '2', '9'], true)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: language_id は 1（日本語）、2（English）、9（その他）のいずれかを指定してください。",
      ]);
    }
    if ($journalCode === '' || !ctype_digit($journalCode)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: journal_code は必須です。",
      ]);
    }
    $journal = Journal::where('journal_code', (int) $journalCode)->first();
    if ($journal === null) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: journal_code={$journalCode} の雑誌が見つかりません。",
      ]);
    }
    if ($publisher !== '' && mb_strlen($publisher) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: publisher は255文字以内にしてください。",
      ]);
    }
    if ($link !== '' && mb_strlen($link) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: link は255文字以内にしてください。",
      ]);
    }
    if ($comment !== '' && mb_strlen($comment) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: comment は255文字以内にしてください。",
      ]);
    }
    if ($memo1 !== '' && mb_strlen($memo1) > 255) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: memo1 は255文字以内にしてください。",
      ]);
    }

    $orderIds = $this->parseOrderIds($orderIdsRaw);
    if ($orderIds === []) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: order_ids は1件以上指定してください（セミコロン区切り）。",
      ]);
    }

    $validOrderCount = Order::query()
      ->whereIn('id', $orderIds)
      ->where('status', true)
      ->count();

    if ($validOrderCount !== count($orderIds)) {
      throw ValidationException::withMessages([
        'csv_file' => "{$lineNo}行目: order_ids に無効な目が含まれています。",
      ]);
    }

    return [
      'author' => $author,
      'authorEn' => $authorEn,
      'yearInt' => $yearInt,
      'title' => $title,
      'titleEn' => $titleEn,
      'languageId' => (int) $languageId,
      'journal' => $journal,
      'publisher' => $publisher !== '' ? $publisher : null,
      'volNo' => $volNo,
      'page' => $page,
      'orderIds' => $orderIds,
      'link' => $link !== '' ? $link : null,
      'comment' => $comment !== '' ? $comment : null,
      'memo1' => $memo1,
    ];
  }

  private function parseOrderIds(string $value): array
  {
    $value = trim($value);
    if ($value === '') {
      return [];
    }

    $parts = preg_split('/[;,]/', $value);
    $ids = [];

    foreach ($parts as $part) {
      $part = trim($part);
      if ($part === '' || !ctype_digit($part)) {
        continue;
      }
      $ids[] = (int) $part;
    }

    return array_values(array_unique($ids));
  }

  private function currentUser(): User
  {
    $user = User::fromAppUser(Auth::user());
    $user->load(['roles', 'tags']);

    return $user;
  }

  private function assertCanAccessLiterature(User $user, Literature $literature, int $lineNo): void
  {
    if ($user->sharesTagsWithLiterature($literature)) {
      return;
    }

    throw ValidationException::withMessages([
      'csv_file' => "{$lineNo}行目: id={$literature->id} の文献を操作する権限がありません。",
    ]);
  }

  private function recordLiteratureHistory(Literature $literature, string $action, ?int $savedByUserId = null): void
  {
    LiteratureHistory::create([
      'literature_id' => $literature->id,
      'action' => $action,
      'saved_by_user_id' => $savedByUserId,
      'code' => $literature->code,
      'author' => $literature->author,
      'author_en' => $literature->author_en,
      'year' => $literature->year,
      'title' => $literature->title,
      'title_en' => $literature->title_en,
      'vol_no' => $literature->vol_no,
      'journal_id' => $literature->journal_id,
      'publisher' => $literature->publisher,
      'page' => $literature->page,
      'language_id' => $literature->language_id,
      'memo1' => $literature->memo1,
      'memo2' => $literature->memo2,
      'memo3' => $literature->memo3,
      'memo4' => $literature->memo4,
      'memo5' => $literature->memo5,
      'memo6' => $literature->memo6,
      'memo7' => $literature->memo7,
      'memo8' => $literature->memo8,
      'memo9' => $literature->memo9,
      'memo10' => $literature->memo10,
      'inventory' => $literature->inventory,
      'random_id' => $literature->random_id,
      'link' => $literature->link,
      'comment' => $literature->comment,
      'created_at' => $literature->created_at,
      'updated_at' => $literature->updated_at,
      'deleted_at' => $literature->deleted_at,
      'tag_id' => $literature->getPrimaryTagId(),
      'user_id' => $literature->user_id,
      'recorded_at' => now(),
    ]);
  }
}
