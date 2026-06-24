<?php

namespace Kaikon2\Kaikondb\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Kaikon2\Kaikondb\Models\SectionMaintenance;
use Kaikon2\Kaikondb\Models\User;

class SectionMaintenanceGate
{
    public const CACHE_KEY = 'kaikon.section_maintenance';

    /** @var list<string> */
    public const SECTIONS = ['literatures', 'specimens', 'inventory', 'photos'];

    /** @var array<string, string> */
    public const CONFIG_KEYS = [
        'literatures' => 'LITERATURES',
        'specimens' => 'SPECIMENS',
        'inventory' => 'INVENTORY',
        'photos' => 'PHOTOS',
    ];

    public static function isEnabled(string $section): bool
    {
        if (! in_array($section, self::SECTIONS, true)) {
            return false;
        }

        try {
            if (! Schema::hasTable('section_maintenances')) {
                return false;
            }

            return (bool) (self::allStates()[$section]['enabled'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function isAccessible(string $section, ?User $user = null): bool
    {
        if (! self::isFeatureEnabled($section)) {
            return false;
        }

        if (! self::isEnabled($section)) {
            return true;
        }

        return self::canBypass($user);
    }

    public static function isFeatureEnabled(string $section): bool
    {
        $configKey = self::CONFIG_KEYS[$section] ?? null;
        if ($configKey === null) {
            return false;
        }

        return (int) config("kaikon.{$configKey}", 0) === 1;
    }

    public static function canBypass(?User $user = null): bool
    {
        if ($user === null && Auth::check()) {
            $user = User::fromAppUser(Auth::user());
        }

        if ($user === null) {
            return false;
        }

        return $user->isAdmin() || $user->isDeveloper();
    }

    public static function message(string $section): ?string
    {
        $locale = app()->getLocale();
        $key = str_starts_with($locale, 'en') ? 'message_en' : 'message_ja';
        $message = trim((string) (self::allStates()[$section][$key] ?? ''));

        return $message !== '' ? $message : null;
    }

    public static function label(string $section): string
    {
        return match ($section) {
            'literatures' => __('messages.Literatures'),
            'specimens' => __('messages.Specimens'),
            'inventory' => __('messages.Inventory'),
            'photos' => __('messages.Photos'),
            default => $section,
        };
    }

    /**
     * @return array<string, array{enabled: bool, message_ja: ?string, message_en: ?string}>
     */
    public static function allStates(): array
    {
        try {
            if (! Schema::hasTable('section_maintenances')) {
                return self::defaultStates();
            }
        } catch (\Throwable) {
            return self::defaultStates();
        }

        return Cache::remember(self::CACHE_KEY, 60, function (): array {
            $rows = SectionMaintenance::query()
                ->get(['section', 'enabled', 'message_ja', 'message_en']);

            $states = self::defaultStates();
            foreach ($rows as $row) {
                if (! array_key_exists($row->section, $states)) {
                    continue;
                }
                $states[$row->section] = [
                    'enabled' => (bool) $row->enabled,
                    'message_ja' => $row->message_ja,
                    'message_en' => $row->message_en,
                ];
            }

            return $states;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array{enabled: bool, message_ja: ?string, message_en: ?string}>
     */
    private static function defaultStates(): array
    {
        $states = [];
        foreach (self::SECTIONS as $section) {
            $states[$section] = [
                'enabled' => false,
                'message_ja' => null,
                'message_en' => null,
            ];
        }

        return $states;
    }
}
