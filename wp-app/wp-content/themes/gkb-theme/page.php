<?php
get_header();
the_content();

/* Частые вопросы страницы (размечаются FAQPage) и блок «О компании» на главной */
$gkbPage = get_queried_object();
if ($gkbPage instanceof WP_Post) {
    $gkbFaq = \seo\Content::faqRows($gkbPage);
    if ($gkbFaq) {
        echo '<section class="gkb-page-faq"><div class="container">';
        \seo\Content::renderFaq($gkbFaq);
        echo '</div></section>';
    }
}
if (is_front_page()) {
    \seo\Content::renderAbout();
}

get_footer();
