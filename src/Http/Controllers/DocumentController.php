<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Models\Literature;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Document;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentController extends Controller
{

    protected static $pdf_path = 'documents/';
    
    //
    public function show( Request $request ){
        if(!Auth::check()){
            abort(403, 'Unauthorized action.');
        }

        $user = User::fromAppUser(Auth::user());

        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        if ($user->isAdmin()) {
            $documents = Document::where('literature_id', $request->literature_id)->get();
        } elseif ($user->isModerator()) {
            $tags = $user->tags->pluck('id');
            $documents = Document::where('literature_id', $request->literature_id)
                                ->whereIn('tag_id', $tags)->get();
        } else {
            abort(403, 'Unauthorized action.');
        }

        return $documents;
    }

    //
    public function upload(string $id, Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        $literature = Literature::query()
            ->where('random_id', $id)
            ->firstOrFail();

        $literatureId = (int) $literature->getKey();
        if ($literatureId <= 0) {
            return $this->uploadResponse($request, false, '文献IDを取得できませんでした。');
        }

        if ($request->filled('literature_id') && (int) $request->input('literature_id') !== $literatureId) {
            return $this->uploadResponse($request, false, '文献IDが一致しません。');
        }

        $user = User::fromAppUser(Auth::user());

        if (!$user->isAdmin() && !$user->hasTag($literature->tag_id)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'document_file' => 'required|file|mimes:pdf|max:51200',
            'literature_id' => 'nullable|integer',
        ]);

        $uploadedFile = $request->file('document_file');
        if (!$uploadedFile) {
            return $this->uploadResponse($request, false, 'ファイルが選択されていません。');
        }

        $save_file_name = now()->format('YmdHisu').'.pdf';
        $storedPath = null;
        $document = null;

        try {
            DB::transaction(function () use ($literature, $literatureId, $user, $uploadedFile, $save_file_name, &$storedPath, &$document) {
                $document = $literature->documents()->create([
                    'literature_id' => $literatureId,
                    'file_name' => $save_file_name,
                    'display_title' => '本文',
                    'user_id' => $user->id,
                    'tag_id' => $literature->tag_id,
                ]);

                if (!$document->exists || !$document->getKey()) {
                    throw new \RuntimeException('documentsテーブルへの登録に失敗しました。');
                }

                $storedPath = $uploadedFile->storeAs('documents', $save_file_name);
                if ($storedPath === false || !Storage::exists($storedPath)) {
                    throw new \RuntimeException('ファイルの保存に失敗しました。');
                }
            });
        } catch (\Throwable $e) {
            if ($storedPath !== null && $storedPath !== false) {
                Storage::delete($storedPath);
            }

            report($e);

            $message = '文献の登録に失敗しました。';
            if (config('app.debug')) {
                $message .= ' ('.$e->getMessage().')';
            }

            return $this->uploadResponse($request, false, $message);
        }

        $persisted = Document::query()
            ->whereKey($document->getKey())
            ->where('literature_id', $literatureId)
            ->exists();

        if (!$persisted) {
            if ($storedPath !== null && $storedPath !== false) {
                Storage::delete($storedPath);
            }

            return $this->uploadResponse($request, false, '文献の登録を確認できませんでした。');
        }

        return $this->uploadResponse($request, true, 'アップロードが完了しました。', [
            'document_id' => $document->getKey(),
            'file_name' => $save_file_name,
            'literature_id' => $literatureId,
        ]);
    }

    private function uploadResponse(Request $request, bool $result, string $message, array $extra = [])
    {
        $payload = array_merge([
            'result' => $result,
            'message' => $message,
        ], $extra);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, $result ? 200 : 422);
        }

        return response($message, $result ? 200 : 422);
    }

    public function edit(Request $request)
    {
        if (!$request->isJson()) {
            return ['result' => false, 'message' => 'Invalid request format'];
        }
        $validatedData = $request->validate([
            'document_id' => 'required|string|max:5',
            'document_name' => 'required|string|max:10',
        ]);
        $document_id = $validatedData['document_id'];
        $document_name = $validatedData['document_name'];

        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where( 'id', $document_id )->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        try {
            $document = Document::findOrFail($document_id);
            $document->update([
                'display_title' => $document_name
            ]);
            return ['result' => true, 'message' => 'Document updated successfully'];
        } catch (ModelNotFoundException $e) {
            return ['result' => false, 'message' => 'Document not found'];
        } catch (\Exception $e) {
            return ['result' => false, 'message' => 'An error occurred'];
        }
    }
    
    //
    public function delete( string $file_name, Request $request ){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where('file_name', $file_name)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }
        $target_file = Document::where('file_name', '=', $file_name)->firstOrFail();
        $target_path = public_path('uploads/');
        $file = storage_path('app/private/documents/').$target_file->file_name;
        //ファイル削除成功の判定
        if( !unlink($file) ){
            return 'ファイル削除に失敗しました';
        }

        //データベース処理の判定
        if( !$target_file->delete()){
            return 'ファイル削除には成功しましたが、データベース処理に失敗しました。';
            //ログに書き出す、ジョブに残すなど別の仕組みが必要。
        }

        return 'ファイル削除に成功しました';
            
    }

    //
    public function open( string $file_name ){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Document::where('file_name', $file_name)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        $file_path = self::$pdf_path . $file_name;
        abort_if(!Storage::exists($file_path), 404);
    
        return response()->make(Storage::get($file_path), 200, [
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="' . $file_name . '"'
        ]);
    }
    

}
