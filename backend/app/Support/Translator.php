<?php

namespace App\Support;

class Translator
{
    public static function lang(): string
    {
        $lang = session('lang', 'sw');

        return in_array($lang, ['sw', 'en'], true) ? $lang : 'sw';
    }

    public static function get(string $section, string $key, ?string $lang = null): string
    {
        $lang ??= self::lang();
        $group = config("translations.{$lang}.{$section}", []);

        return is_array($group) ? ($group[$key] ?? $key) : $key;
    }

    /** @return array<string, mixed> */
    public static function section(string $section, ?string $lang = null): array
    {
        $lang ??= self::lang();

        return config("translations.{$lang}.{$section}", []);
    }
}
