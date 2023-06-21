<?php

/*
Title: Модуль дилеров
Mode: preview
*/

use helpers\Helpers;

$fields = (new Helpers)->get_field_multi_lang();
?>
<?php if (!is_admin()) : ?>
    <section class="dealers">
        <div class="container">
            <?php if (!empty($fields['title'])) : ?>
                <h2 class="dealers__title">
                    <?= $fields['title'] ?>
                </h2>
            <?php endif; ?>
            <?php if (!empty($fields['regions'])) : ?>
                <div class="dealers__regions">
                    <?php foreach ($fields['regions'] as $key => $region) : ?>
                        <?php if (!empty($region)) : ?>
                            <div class="dealers__region <?= $key === 0 ? 'active' : '' ?>">
                                <?php if (!empty($region['region'])) : ?>
                                    <div class="dealers__region-title">
                                        <?php if (!empty($fields['icon_region'])) : ?>
                                            <img src="<?= $fields['icon_region']['url'] ?>"
                                                 alt="<?= $fields['icon_region']['title'] ?>">
                                        <?php endif; ?>
                                        <p>
                                            <?= $region['region'] ?>
                                        </p>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24">
                                            <rect x="0" fill="none" width="24" height="24"></rect>
                                            <g>
                                                <path d="M7 10l5 5 5-5"></path>
                                            </g>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($region['cyties'])) : ?>
                                    <div class="dealers__cities">
                                        <?php foreach ($region['cyties'] as $city) : ?>
                                            <div class="dealers__city">
                                                <?php if (!empty($city['city'])) : ?>
                                                    <p class="dealers__city-title">
                                                        <?= $city['city'] ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($city['address'])) : ?>
                                                    <div class="dealers__address">
                                                        <?= $city['address'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($fields['description'])) : ?>
                <div class="dealers__description">
                    <?= $fields['description'] ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <h2 style="font-family: 'Mark', sans-serif;">Модуль дилеров</h2>
<?php endif; ?>