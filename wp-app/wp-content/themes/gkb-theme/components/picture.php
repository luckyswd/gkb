<picture>
    <img loading="lazy"
         src="<?= $image['url'] ?? '' ?>"
         alt="<?= $image['title'] ?? '' ?>"
        <?php if ($width) : ?>
            width="<?= $width ?>"
        <?php endif; ?>
        <?php if ($height) : ?>
            height="<?= $height ?>"
        <?php endif; ?>
    >
</picture>