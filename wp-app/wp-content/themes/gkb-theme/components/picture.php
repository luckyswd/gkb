<?php
/**
 * Изображение из поля ACF (массив вложения) с srcset/sizes и явными
 * размерами. Переменные из Helpers::getPictureImage(): $image, $width,
 * $height, $sizes, $priority, $alt.
 */

$imageId = (int) ($image['ID'] ?? $image['id'] ?? 0);
$altText = $alt ?? (trim((string) ($image['alt'] ?? '')) ?: (string) ($image['title'] ?? ''));

echo '<picture>';
if ($imageId) {
    $attr = [];
    if (!empty($width)) {
        $attr['width'] = (int) $width;
    }
    if (!empty($height)) {
        $attr['height'] = (int) $height;
    }
    echo \seo\Perf::img($imageId, $altText, $sizes ?: '100vw', !empty($priority), $attr);
} elseif (!empty($image['url'])) { ?>
    <img src="<?= esc_url($image['url']) ?>" alt="<?= esc_attr($altText) ?>"
         <?= !empty($priority) ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async"
        <?php if (!empty($width)) : ?> width="<?= (int) $width ?>"<?php endif; ?>
        <?php if (!empty($height)) : ?> height="<?= (int) $height ?>"<?php endif; ?>>
<?php }
echo '</picture>';
