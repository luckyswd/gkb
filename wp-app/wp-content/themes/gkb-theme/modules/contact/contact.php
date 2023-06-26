<?php

/*
Title: Модуль контактов
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang();
?>
<?php if (!is_admin()) : ?>
    <section class="contact">
        <div class="container">
            <?php if (!empty($fields['title'])) : ?>
                <h1 class="contact__title">
                    <?= $fields['title'] ?>
                </h1>
            <?php endif; ?>
            <?php if (!empty($fields['contact_section'])) : ?>
                <div class="contact__sections">
                    <?php foreach ($fields['contact_section'] as $section) : ?>
                        <?php if (!empty($section)) : ?>
                            <div class="contact__section">
                                <div class="contact__section-header">
                                    <?php if (!empty($section['caption'])) : ?>
                                        <p class="contact__caption">
                                            <?= $section['caption'] ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($section['title'])) : ?>
                                        <p class="contact__section-title">
                                            <?= $section['title'] ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($section['description'])) : ?>
                                        <div class="contact__description">
                                            <?= $section['description'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="contact__section-footer">
                                    <?php if (!empty($section['link'])) : ?>
                                        <a href="<?= $section['link']['url'] ?>" class="contact__link">
                                            <?php if (!empty($fields['icon_map_image'])) : ?>
                                                <img src="<?= $fields['icon_map_image']['url'] ?>"
                                                     alt="<?= $fields['icon_map_image']['title'] ?>">
                                            <?php endif; ?>
                                            <p>
                                                <?= $section['link']['title'] ?>
                                            </p>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль контактов</h2>
<?php endif; ?>