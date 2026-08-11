<?php
/**
 * Education card (related block on single, same grid as services).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$icon_url = '';
if (has_post_thumbnail()) {
    $icon_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: '';
}
if ($icon_url === '') {
    $icon_url = get_template_directory_uri() . '/assets/img/course-icon-1.svg';
}

$card_text = '';
if (function_exists('get_field')) {
    $card_text = trim((string) get_field('cpp_edu_intro_text'));
}
if ($card_text === '') {
    $card_text = get_the_excerpt();
}
?>
<article <?php post_class('courses_card'); ?>>
    <div class="courses_card_head">
        <div class="courses_card_icon">
            <img src="<?php echo esc_url($icon_url); ?>" alt="" width="48" height="48" loading="lazy" />
        </div>
        <h3 class="courses_card_title"><?php the_title(); ?></h3>
    </div>
    <div class="courses_card_content">
        <p class="courses_card_text"><?php echo esc_html(wp_trim_words($card_text, 40)); ?></p>
    </div>
    <div class="courses_card_actions">
        <a class="button button--outline button--sm" href="<?php the_permalink(); ?>">
            <span class="button_text">Подробнее</span>
        </a>
    </div>
</article>
