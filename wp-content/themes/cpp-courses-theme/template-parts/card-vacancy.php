<?php
/**
 * Vacancy card for home section.
 *
 * @package CppCoursesTheme
 *
 * @var array $args { @type WP_Post $post }
 */

if (!defined('ABSPATH')) {
    exit;
}

$post = isset($args['post']) && $args['post'] instanceof WP_Post ? $args['post'] : null;
if (!$post instanceof WP_Post) {
    return;
}

$icon = function_exists('get_field') ? get_field('vacancy_icon', $post->ID) : null;
$icon_url = cpp_courses_image_field_url($icon);
if ($icon_url === '') {
    $icon_url = get_template_directory_uri() . '/assets/img/vacancy-card-icon-1.svg';
}

$pay = function_exists('get_field') ? trim((string) get_field('vacancy_pay_line', $post->ID)) : '';
$bullets = function_exists('get_field') ? get_field('vacancy_bullets', $post->ID) : null;
$apply = function_exists('get_field') ? trim((string) get_field('vacancy_apply_url', $post->ID)) : '';
if ($apply === '') {
    $apply = home_url('/contacts/#contacts');
}

$desc = get_the_excerpt($post);
if ($desc === '') {
    $desc = wp_trim_words(wp_strip_all_tags((string) $post->post_content), 24);
}
?>
<div class="vacancies_card">
    <div class="vacancies_card_icon">
        <img src="<?php echo esc_url($icon_url); ?>" alt="" width="48" height="48" loading="lazy" />
    </div>
    <div class="vacancies_card_head">
        <h3 class="vacancies_card_title"><?php echo esc_html(get_the_title($post)); ?></h3>
        <?php if ($desc !== '') : ?>
            <p class="vacancies_card_desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>
    </div>
    <div class="vacancies_card_body">
        <?php if ($pay !== '' || (is_array($bullets) && !empty($bullets))) : ?>
            <ul class="vacancies_card_list">
                <?php if ($pay !== '') : ?>
                    <li class="vacancies_card_list_item"><?php echo esc_html($pay); ?></li>
                <?php endif; ?>
                <?php
                if (is_array($bullets)) {
                    foreach ($bullets as $row) {
                        $t = isset($row['text']) ? trim((string) $row['text']) : '';
                        if ($t === '') {
                            continue;
                        }
                        echo '<li class="vacancies_card_list_item">' . esc_html($t) . '</li>';
                    }
                }
                ?>
            </ul>
        <?php endif; ?>
        <a class="button button--outline button--sm" href="<?php echo esc_url($apply); ?>">
            <span class="button_text"><?php esc_html_e('Откликнуться', 'cpp-courses-theme'); ?></span>
        </a>
    </div>
</div>
