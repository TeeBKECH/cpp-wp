<?php
/**
 * Teacher card for home / grids.
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

$role = function_exists('get_field') ? (string) get_field('teacher_role', $post->ID) : '';
$extra = function_exists('get_field') ? (string) get_field('teacher_extra', $post->ID) : '';
$alt = get_the_title($post);
?>
<article class="teachers_card">
    <div class="teachers_card_img-wrap">
        <?php if (has_post_thumbnail($post)) : ?>
            <?php echo get_the_post_thumbnail($post, 'medium_large', array('class' => 'teachers_card_img', 'loading' => 'lazy', 'alt' => esc_attr($alt))); ?>
        <?php else : ?>
            <img
                class="teachers_card_img"
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/teacher-1.png'); ?>"
                alt="<?php echo esc_attr($alt); ?>"
                width="260"
                height="290"
                loading="lazy"
            />
        <?php endif; ?>
    </div>
    <div class="teachers_card_content">
        <h3 class="teachers_card_name"><?php echo esc_html($alt); ?></h3>
        <?php if ($role !== '') : ?>
            <p class="teachers_card_role"><?php echo esc_html($role); ?></p>
        <?php endif; ?>
        <?php if ($extra !== '') : ?>
            <p class="teachers_card_role"><?php echo esc_html($extra); ?></p>
        <?php endif; ?>
    </div>
</article>
