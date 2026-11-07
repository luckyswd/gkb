<?php

namespace seo;

/**
 * Данные организации из «Глобальных настроек» с запасными значениями из
 * реквизитов прайс-листа, чтобы разметка была верной до заполнения полей.
 */
class Org
{
    public const NAME = 'ГазКотелБел';

    private static ?array $data = null;

    public static function get(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $f = static function (string $name, $default = '') {
            if (!function_exists('get_field')) {
                return $default;
            }
            $v = get_field($name, 'option');
            return ($v === null || $v === '' || $v === false || $v === []) ? $default : $v;
        };

        $phones = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', (string) $f('org_phones', "+375 29 151 68 90\n+375 33 684 83 95")))));
        $logo = $f('org_logo', null) ?: $f('logo', null);
        $ogImage = $f('og_default_image', null);

        $socials = [];
        foreach ((array) $f('socials', []) as $social) {
            if (!empty($social['url'])) {
                $socials[] = $social['url'];
            }
        }

        return self::$data = [
            'name' => (string) $f('org_name', self::NAME),
            'legal_name' => (string) $f('org_legal_name', 'ООО «ГазКотелБел»'),
            'street' => (string) $f('org_street', 'ул. Центральная, 1А-2'),
            'locality' => (string) $f('org_locality', 'г. п. Мачулищи'),
            'region' => (string) $f('org_region', 'Минская область, Минский район'),
            'postcode' => (string) $f('org_postcode', '223012'),
            'phones' => $phones,
            'email' => (string) $f('org_email', ''),
            'unp' => (string) $f('org_unp', '693156545'),
            'hours' => (string) $f('org_hours', ''),
            'hours_schema' => (string) $f('org_hours_schema', ''),
            'logo' => is_array($logo) ? ($logo['url'] ?? '') : '',
            'og_image' => is_array($ogImage) ? $ogImage : null,
            'socials' => $socials,
            'prices_valid_from' => (string) $f('prices_valid_from', '2025-06-09'),
            'prices_review_date' => (string) $f('prices_review_date', ''),
            'google_verification' => (string) $f('google_verification', ''),
            'yandex_verification' => (string) $f('yandex_verification', '56c82ebe91ca39d9'),
            'origin_trial' => (string) $f('origin_trial_token', ''),
            'region_text' => (string) $f('region_delivery_text', ''),
        ];
    }

    public static function addressLine(): string
    {
        $o = self::get();
        return trim(implode(', ', array_filter([$o['postcode'], $o['region'], $o['locality'], $o['street']])));
    }

    /** Дата начала действия цен в формате «09.06.2025». */
    public static function pricesDateHuman(): string
    {
        $ts = strtotime(self::get()['prices_valid_from']);
        return $ts ? date('d.m.Y', $ts) : '';
    }

    /** priceValidUntil: дата пересмотра прайса, если она в будущем, иначе конец следующего года. */
    public static function priceValidUntil(): string
    {
        $review = strtotime(self::get()['prices_review_date']);
        if ($review && $review > time()) {
            return date('Y-m-d', $review);
        }
        return ((int) date('Y') + 1) . '-12-31';
    }
}
