<?php
/**
 * Education archive card: PDF download.
 *
 * @package CppCoursesTheme
 *
 * @var array $args {
 *     @type WP_Post $post Post object.
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$post = isset($args['post']) && $args['post'] instanceof WP_Post ? $args['post'] : get_post();
if (!$post instanceof WP_Post) {
    return;
}

$pdf = function_exists('get_field') ? get_field('cpp_edu_pdf', $post->ID) : null;
$url = '';
if (is_array($pdf) && !empty($pdf['url'])) {
    $url = (string) $pdf['url'];
}
$btn_label = function_exists('get_field') ? (string) get_field('cpp_edu_download_label', $post->ID) : 'Скачать';
if ($btn_label === '') {
    $btn_label = 'Скачать';
}

$title = get_the_title($post);
$desc = get_the_excerpt($post);
if ($desc === '') {
    $desc = wp_trim_words(wp_strip_all_tags((string) $post->post_content), 28);
}
?>
<div class="orders_item">
    <div class="orders_item_content">
        <?php if ($url !== '') : ?>
            <a class="orders_item-title" href="<?php echo esc_url($url); ?>" download><?php echo esc_html($title); ?></a>
        <?php else : ?>
            <span class="orders_item-title"><?php echo esc_html($title); ?></span>
        <?php endif; ?>
        <?php if ($desc !== '') : ?>
            <p class="orders_item-desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>
    </div>
    <?php if ($url !== '') : ?>
        <a class="button button--icon button--sm" href="<?php echo esc_url($url); ?>" download>
            <div class="button_icon button_icon--download" aria-hidden="true"></div>
            <span class="button_text"><?php echo esc_html($btn_label); ?></span>
        </a>
    <?php endif; ?>
</div>
