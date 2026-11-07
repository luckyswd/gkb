<?php

namespace seo;

use WP_Post;

/**
 * Маршруты, которые отдаёт тема: sitemap.xml и вложенные карты, llms.txt,
 * файл ключа IndexNow, региональные страницы дилеров. robots.txt — через
 * фильтр ядра. Физических файлов на диске нет: FTP-выкладка их бы затёрла.
 */
class Endpoints
{
    /** Меняется при изменении правил — правила перезаписываются один раз. */
    public const VERSION = '1';

    public const SITEMAPS = ['pages', 'products', 'categories', 'articles', 'regions'];

    public static function register(): void
    {
        add_action('init', [self::class, 'rules']);
        add_filter('query_vars', static function ($vars) {
            return array_merge($vars, ['gkb_sitemap', 'gkb_llms', 'gkb_indexnow', 'gkb_region']);
        });
        add_action('init', [self::class, 'maybeFlush'], 99);
        add_action('parse_request', [self::class, 'serve'], 1);
        add_filter('robots_txt', [self::class, 'robots'], 999, 2);
        add_filter('wp_sitemaps_enabled', '__return_false');
        add_filter('redirect_canonical', static function ($redirect, $requested) {
            // sitemap.xml и llms.txt без завершающего слеша
            if (preg_match('#/(sitemap(-[a-z]+)?\.xml|llms\.txt|[a-f0-9]{32}\.txt)$#', (string) parse_url($requested, PHP_URL_PATH))) {
                return false;
            }
            return $redirect;
        }, 10, 2);
        add_action('template_redirect', [self::class, 'regionGuard'], 2);
        add_filter('template_include', [self::class, 'regionTemplate'], 99);
    }

    public static function rules(): void
    {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?gkb_sitemap=index', 'top');
        add_rewrite_rule('^sitemap-([a-z]+)\.xml$', 'index.php?gkb_sitemap=$matches[1]', 'top');
        add_rewrite_rule('^llms\.txt$', 'index.php?gkb_llms=1', 'top');
        add_rewrite_rule('^([a-f0-9]{32})\.txt$', 'index.php?gkb_indexnow=$matches[1]', 'top');
        add_rewrite_rule('^where-to-buy/([a-z-]+)/?$', 'index.php?gkb_region=$matches[1]', 'top');
    }

    public static function maybeFlush(): void
    {
        if (get_option('gkb_seo_rewrite_version') !== self::VERSION) {
            update_option('gkb_seo_rewrite_version', self::VERSION, true);
            flush_rewrite_rules(false);
        }
    }

    /** sitemap, llms.txt и ключ отдаются до запроса записей. */
    public static function serve(\WP $wp): void
    {
        $qv = $wp->query_vars;

        if (!empty($qv['gkb_sitemap'])) {
            $xml = Sitemap::render((string) $qv['gkb_sitemap']);
            if ($xml === null) {
                return; // неизвестная карта — обычный 404
            }
            self::send('application/xml; charset=UTF-8', $xml);
        }

        if (!empty($qv['gkb_llms'])) {
            self::send('text/plain; charset=UTF-8', Llms::render(), false);
        }

        if (!empty($qv['gkb_indexnow'])) {
            if (hash_equals(IndexNow::key(), (string) $qv['gkb_indexnow'])) {
                self::send('text/plain; charset=UTF-8', IndexNow::key());
            }
        }
    }

    private static function send(string $type, string $body, bool $noindex = true): void
    {
        status_header(200);
        header('Content-Type: ' . $type);
        header('Cache-Control: public, max-age=3600');
        if ($noindex) {
            header('X-Robots-Tag: noindex');
        }
        echo $body;
        exit;
    }

    /** Неизвестный регион или регион без дилеров — 404. */
    public static function regionGuard(): void
    {
        $slug = (string) get_query_var('gkb_region');
        if ($slug === '') {
            return;
        }
        if (!Regions::get($slug)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            set_query_var('gkb_region', '');
            Context::reset();
            return;
        }
        global $wp_query;
        $wp_query->is_404 = false;
        $wp_query->is_home = false;
        status_header(200);
    }

    public static function regionTemplate(string $template): string
    {
        if (get_query_var('gkb_region') && Regions::get((string) get_query_var('gkb_region'))) {
            return get_theme_file_path('where-to-buy-region.php');
        }
        return $template;
    }

    public static function robots(string $output, $public): string
    {
        if (!$public) {
            return "User-agent: *\nDisallow: /\n";
        }
        $base = home_url('/');
        $disallow = [
            '/wp-admin/',
            '/wp-login.php',
            '/?s=',
            '/*?s=',
            '/*?search=',
            '/login/',
            '/registration/',
            '/feed/',
            '/*/feed/',
            '/wp-content/uploads/wpo-plugins-tables-list.json',
        ];
        $rules = "Disallow: " . implode("\nDisallow: ", $disallow) . "\nAllow: /wp-admin/admin-ajax.php\n";

        $ai = ['OAI-SearchBot', 'ChatGPT-User', 'GPTBot', 'PerplexityBot', 'Perplexity-User', 'ClaudeBot', 'Claude-User', 'Claude-SearchBot', 'Google-Extended', 'Applebot-Extended', 'YandexAdditional', 'YandexGPT'];

        $txt = "# robots.txt — gazkotelbel.com\n\n";
        $txt .= "User-agent: *\n" . $rules . "\n";
        $txt .= "User-agent: Yandex\n" . $rules . "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&gclid&fbclid&_openstat&from\n\n";
        $txt .= "# ИИ-ассистенты и ИИ-поиск: разрешены с теми же ограничениями\n";
        foreach ($ai as $bot) {
            $txt .= "User-agent: $bot\n";
        }
        $txt .= $rules . "\n";
        $txt .= "Sitemap: " . $base . "sitemap.xml\n\n";
        $txt .= "# Описание сайта для ИИ-ассистентов (llmstxt.org)\n# " . $base . "llms.txt\n";
        return $txt;
    }
}

/**
 * Карта сайта: индекс + вложенные карты только с индексируемыми русскими адресами.
 */
class Sitemap
{
    public static function render(string $name): ?string
    {
        if ($name === 'index') {
            return self::index();
        }
        if (!in_array($name, Endpoints::SITEMAPS, true)) {
            return null;
        }
        $urls = self::urls($name);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . esc_url($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . esc_html($u['lastmod']) . "</lastmod>\n";
            }
            foreach ($u['images'] ?? [] as $img) {
                $xml .= "    <image:image><image:loc>" . esc_url($img) . "</image:loc></image:image>\n";
            }
            $xml .= "  </url>\n";
        }
        return $xml . "</urlset>\n";
    }

    private static function index(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (Endpoints::SITEMAPS as $name) {
            $urls = self::urls($name);
            if (!$urls) {
                continue;
            }
            $last = max(array_map(static fn ($u) => $u['lastmod'] ?? '', $urls));
            $xml .= "  <sitemap>\n    <loc>" . esc_url(home_url("/sitemap-$name.xml")) . "</loc>\n";
            if ($last) {
                $xml .= "    <lastmod>$last</lastmod>\n";
            }
            $xml .= "  </sitemap>\n";
        }
        return $xml . "</sitemapindex>\n";
    }

    private static function lastmod(WP_Post $p): string
    {
        return get_post_modified_time('c', true, $p);
    }

    /** Список адресов карты: [loc, lastmod, images]. */
    public static function urls(string $name): array
    {
        $out = [];
        switch ($name) {
            case 'pages':
                $front = (int) get_option('page_on_front');
                foreach (get_posts(['post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']) as $p) {
                    if (Context::objectNoindex($p) || post_password_required($p)) {
                        continue;
                    }
                    $out[] = [
                        'loc' => $p->ID === $front ? home_url('/') : trailingslashit(get_permalink($p)),
                        'lastmod' => self::lastmod($p),
                    ];
                }
                usort($out, static fn ($a, $b) => strlen($a['loc']) <=> strlen($b['loc']));
                break;

            case 'products':
                $helper = new \helpers\Helpers();
                foreach (get_posts(['post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => -1]) as $p) {
                    if (Context::objectNoindex($p)) {
                        continue;
                    }
                    $images = [];
                    foreach (array_slice($helper->getProductImageIds($p), 0, 5) as $id) {
                        $src = wp_get_attachment_image_url($id, 'full');
                        if ($src) {
                            $images[] = $src;
                        }
                    }
                    $out[] = ['loc' => trailingslashit(get_permalink($p)), 'lastmod' => self::lastmod($p), 'images' => $images];
                }
                break;

            case 'categories':
                $terms = get_terms(['taxonomy' => 'product-category', 'hide_empty' => true]);
                foreach (is_wp_error($terms) ? [] : $terms as $t) {
                    if (Context::objectNoindex($t)) {
                        continue;
                    }
                    $posts = get_posts(['post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'DESC',
                        'tax_query' => [['taxonomy' => 'product-category', 'field' => 'term_id', 'terms' => $t->term_id]]]);
                    if (!$posts) {
                        continue;
                    }
                    $out[] = ['loc' => trailingslashit(get_term_link($t)), 'lastmod' => self::lastmod($posts[0])];
                }
                break;

            case 'articles':
                if (!post_type_exists('articles')) {
                    break;
                }
                $posts = get_posts(['post_type' => 'articles', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC']);
                if ($posts) {
                    $out[] = ['loc' => trailingslashit(get_post_type_archive_link('articles')), 'lastmod' => self::lastmod($posts[0])];
                }
                foreach ($posts as $p) {
                    if (Context::objectNoindex($p)) {
                        continue;
                    }
                    $img = get_the_post_thumbnail_url($p, 'full');
                    $out[] = ['loc' => trailingslashit(get_permalink($p)), 'lastmod' => self::lastmod($p), 'images' => $img ? [$img] : []];
                }
                break;

            case 'regions':
                $page = Regions::page();
                foreach (Regions::all() as $slug => $region) {
                    $out[] = ['loc' => Regions::url($slug), 'lastmod' => $page ? self::lastmod($page) : ''];
                }
                break;
        }
        return $out;
    }

    /** Все адреса всех карт (для llms.txt и проверок). */
    public static function all(): array
    {
        $all = [];
        foreach (Endpoints::SITEMAPS as $name) {
            foreach (self::urls($name) as $u) {
                $all[] = $u['loc'];
            }
        }
        return $all;
    }
}
