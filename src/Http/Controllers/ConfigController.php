<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public function index(): View
    {
        $raw = config('kaikon', []);

        $dbDriver = '';
        try {
            $dbDriver = DB::connection()->getDriverName();
        } catch (\Throwable) {
        }

        $sectionGroups = [
            '環境' => [
                'ベースシステム' => [
                    ['key' => 'PHP',      'label' => 'PHP',      'value' => PHP_VERSION],
                    ['key' => 'Laravel',  'label' => 'Laravel',  'value' => \Illuminate\Foundation\Application::VERSION],
                    ['key' => 'DB Driver','label' => 'DBドライバー', 'value' => $dbDriver],
                ],
            ],
            '設定値' => [
                'サイト基本情報' => [
                    ['key' => 'ProjectTitle',      'label' => 'プロジェクト名',   'value' => $this->formatLocale($raw['ProjectTitle'] ?? null)],
                    ['key' => 'SubTitle',          'label' => 'サブタイトル',     'value' => $this->formatLocale($raw['SubTitle'] ?? null)],
                    ['key' => 'FirstMessage',      'label' => 'メインメッセージ', 'value' => $this->formatLocale($raw['FirstMessage'] ?? null)],
                    ['key' => 'OrganizationName',  'label' => '組織名',           'value' => $this->formatLocale($raw['OrganizationName'] ?? null)],
                    ['key' => 'ExpandedArea',      'label' => '拡張エリア名',     'value' => $this->formatLocale($raw['ExpandedArea'] ?? null)],
                    ['key' => 'Administrator',     'label' => '管理者名',         'value' => $raw['Administrator'] ?? ''],
                    ['key' => 'Email',             'label' => '連絡先メール',     'value' => $raw['Email'] ?? ''],
                    ['key' => 'StartingYear',      'label' => '開始年',           'value' => $raw['StartingYear'] ?? ''],
                ],
                '外部リンク' => [
                    ['key' => 'SupportFormUrl',    'label' => '情報提供フォームURL', 'value' => $raw['SupportFormUrl'] ?? ''],
                ],
                '表示設定' => [
                    ['key' => 'LITERATURES', 'label' => '文献 LITERATURES',   'value' => $this->formatToggle($raw['LITERATURES'] ?? 0)],
                    ['key' => 'SPECIMENS',   'label' => '標本 SPECIMENS',     'value' => $this->formatToggle($raw['SPECIMENS'] ?? 0)],
                    ['key' => 'INVENTORY',   'label' => '分布 INVENTORY',     'value' => $this->formatToggle($raw['INVENTORY'] ?? 0)],
                    ['key' => 'PHOTOS',      'label' => '写真 PHOTOS',        'value' => $this->formatToggle($raw['PHOTOS'] ?? 0)],
                ],
                'システム' => [
                    ['key' => 'APP_URL',                      'label' => 'APP_URL（フルURL）',     'value' => (string) config('app.url')],
                    ['key' => 'SESSION_IDLE_TIMEOUT_SECONDS', 'label' => 'アイドルタイムアウト(秒)', 'value' => $raw['SESSION_IDLE_TIMEOUT_SECONDS'] ?? 0],
                ],
                'ジョブ' => $this->formatFeatureGroup($raw['FEATURES']['jobs'] ?? []),
                'リスナー' => $this->formatFeatureGroup($raw['FEATURES']['listeners'] ?? []),
            ],
        ];

        return view('kaikon::admin.config', compact('sectionGroups'));
    }

    private function formatLocale($value): string
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $lang => $text) {
                $parts[] = "[{$lang}] {$text}";
            }
            return implode("\n", $parts);
        }
        return (string) ($value ?? '');
    }

    private function formatToggle($value): string
    {
        return (int) $value === 1 ? '有効 (1)' : '無効 (0)';
    }

    private function formatFeatureGroup(array $features): array
    {
        $rows = [];
        foreach ($features as $key => $val) {
            $rows[] = [
                'key'   => $key,
                'label' => $key,
                'value' => $this->formatToggle($val),
            ];
        }
        return $rows;
    }
}
