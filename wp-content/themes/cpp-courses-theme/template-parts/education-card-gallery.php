<?php
/**
 * Education archive card: single image (Fancybox gallery per section).
 *
 * @package CppCoursesTheme
 *
 * @var array $args {
 *     @type WP_Post $post Post object.
 *     @type string  $fancybox_group Gallery group name (e.g. term slug).
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$post = isset($args['post']) && $args['post'] instanceof WP_Post ? $args['post'] : get_post();
if (!$post instanceof WP_Post) {
    return;
}

$group = isset($args['fancybox_group']) && is_string($args['fancybox_group']) ? $args['fancybox_group'] : 'education';
$group = sanitize_key($group);
if ($group === '') {
    $group = 'education';
}

$thumb_id = (int) get_post_thumbnail_id($post);
if ($thumb_id < 1) {
    return;
}

$full = wp_get_attachment_image_url($thumb_id, 'full');
if (!$full) {
    return;
}

$alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
$alt = is_string($alt) && $alt !== '' ? $alt : get_the_title($post);
?>
<a
    class="orders_item orders_item--photo"
    href="<?php echo esc_url($full); ?>"
    data-fancybox="<?php echo esc_attr('edu-' . $group); ?>"
    data-caption="<?php echo esc_attr(get_the_title($post)); ?>"
>
    <div class="orders_item-img">
        <?php echo wp_get_attachment_image($thumb_id, 'medium', false, array('loading' => 'lazy', 'alt' => $alt)); ?>
    </div>
</a>
