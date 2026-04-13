<?php
/**
 * Education archive card: link to single post.
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

$permalink = get_permalink($post);
$btn_label = function_exists('get_field') ? (string) get_field('cpp_edu_link_label', $post->ID) : 'Перейти';
if ($btn_label === '') {
    $btn_label = 'Перейти';
}

$title = get_the_title($post);
$desc = get_the_excerpt($post);
if ($desc === '') {
    $desc = wp_trim_words(wp_strip_all_tags((string) $post->post_content), 28);
}
?>
<div class="orders_item">
    <div class="orders_item_content">
        <a class="orders_item-title" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
        <?php if ($desc !== '') : ?>
            <p class="orders_item-desc"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>
    </div>
    <a class="button button--outline button--sm" href="<?php echo esc_url($permalink); ?>">
        <span class="button_text"><?php echo esc_html($btn_label); ?></span>
    </a>
</div>
