<?php get_header();
$image = get_field('image_404', 'option')
?>
    <section class="404">
        <div class="container">
            <?php if (!empty($image)) : ?>
                <img src="<?= $image['url'] ?>" alt="<?= $image['title'] ?>">
            <?php endif; ?>
        </div>
    </section>
<?php get_footer(); ?>