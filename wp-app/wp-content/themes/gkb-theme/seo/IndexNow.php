<?php

namespace seo;

/**
 * IndexNow: адреса изменённых записей копятся в очереди и уходят одним
 * запросом через 30 секунд. Сохранение записи сеть не ждёт; ошибки — в журнал.
 */
class IndexNow
{
    private const QUEUE = 'gkb_indexnow_queue';
    private const LOG = 'gkb_indexnow_log';
    private const EVENT = 'gkb_indexnow_flush';
    private const ENDPOINT = 'https://yandex.com/indexnow';

    public static function register(): void
    {
        add_action('transition_post_status', [self::class, 'onTransition'], 20, 3);
        add_action('acf/save_post', [self::class, 'onAcfSave'], 30);
        add_action('edited_product-category', [self::class, 'onTerm'], 20);
        add_action('before_delete_post', [self::class, 'onDelete'], 10);
        add_action(self::EVENT, [self::class, 'flush']);
    }

    public static function key(): string
    {
        $key = (string) get_option('gkb_indexnow_key');
        if (!preg_match('/^[a-f0-9]{32}$/', $key)) {
            $key = bin2hex(random_bytes(16));
            update_option('gkb_indexnow_key', $key, true);
        }
        return $key;
    }

    private static function tracked(\WP_Post $post): bool
    {
        return in_array($post->post_type, ['products', 'page', 'articles'], true);
    }

    public static function onTransition($new, $old, $post): void
    {
        if (!$post instanceof \WP_Post || !self::tracked($post) || wp_is_post_revision($post) || wp_is_post_autosave($post)) {
            return;
        }
        if ($new === 'publish' || $old === 'publish') {
            if (!Context::objectNoindex($post) || $old === 'publish') {
                self::queue([get_permalink($post)]);
            }
            if ($post->post_type === 'products') {
                foreach ((array) get_the_terms($post->ID, 'product-category') as $t) {
                    if ($t instanceof \WP_Term) {
                        self::queue([get_term_link($t)]);
                    }
                }
            }
        }
    }

    public static function onAcfSave($postId): void
    {
        if (is_numeric($postId)) {
            $post = get_post((int) $postId);
            if ($post instanceof \WP_Post && $post->post_status === 'publish' && self::tracked($post)) {
                self::queue([get_permalink($post)]);
            }
        } elseif (is_string($postId) && str_starts_with($postId, 'term_')) {
            $term = get_term((int) substr($postId, 5));
            if ($term instanceof \WP_Term && $term->taxonomy === 'product-category') {
                self::queue([get_term_link($term)]);
            }
        }
    }

    public static function onTerm($termId): void
    {
        $link = get_term_link((int) $termId, 'product-category');
        if (!is_wp_error($link)) {
            self::queue([$link]);
        }
    }

    public static function onDelete($postId): void
    {
        $post = get_post($postId);
        if ($post instanceof \WP_Post && $post->post_status === 'publish' && self::tracked($post)) {
            self::queue([get_permalink($post)]);
        }
    }

    public static function queue(array $urls): void
    {
        if (!Context::isProduction()) {
            return;
        }
        $urls = array_filter(array_map(static fn ($u) => is_string($u) ? trailingslashit(strtok($u, '?#')) : null, $urls));
        if (!$urls) {
            return;
        }
        $q = (array) get_option(self::QUEUE, []);
        update_option(self::QUEUE, array_values(array_unique(array_merge($q, $urls))), false);
        if (!wp_next_scheduled(self::EVENT)) {
            wp_schedule_single_event(time() + 30, self::EVENT);
        }
    }

    public static function flush(): void
    {
        $urls = (array) get_option(self::QUEUE, []);
        delete_option(self::QUEUE);
        if (!$urls) {
            return;
        }
        $host = (string) parse_url(home_url(), PHP_URL_HOST);
        $res = wp_remote_post(self::ENDPOINT, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'body' => wp_json_encode([
                'host' => $host,
                'key' => self::key(),
                'keyLocation' => home_url('/' . self::key() . '.txt'),
                'urlList' => array_values(array_slice($urls, 0, 10000)),
            ]),
        ]);
        $code = is_wp_error($res) ? 0 : (int) wp_remote_retrieve_response_code($res);
        $entry = [
            'time' => wp_date('Y-m-d H:i:s'),
            'code' => $code,
            'error' => is_wp_error($res) ? $res->get_error_message() : (in_array($code, [200, 202], true) ? '' : mb_substr((string) wp_remote_retrieve_body($res), 0, 300)),
            'urls' => $urls,
        ];
        $log = (array) get_option(self::LOG, []);
        array_unshift($log, $entry);
        update_option(self::LOG, array_slice($log, 0, 30), false);
        if ($entry['error'] !== '') {
            error_log('gkb indexnow: ' . $code . ' ' . $entry['error']);
        }
    }

    public static function log(): array
    {
        return (array) get_option(self::LOG, []);
    }
}
