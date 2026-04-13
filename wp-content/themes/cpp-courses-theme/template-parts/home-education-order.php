<?php
/**
 * One education row for home orders_list (download / link / gallery as link to post).
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

$type = function_exists('get_field') ? (string) get_field('cpp_edu_display_type', $post->ID) : 'link';
if ($type === 'download') {
    get_template_part('template-parts/education-card', 'download', array('post' => $post));
    return;
}
if ($type === 'gallery') {
    get_template_part(
        'template-parts/education-card',
        'gallery',
        array(
            'post' => $post,
            'fancybox_group' => 'home-education',
        )
    );
    return;
}
get_template_part('template-parts/education-card', 'link', array('post' => $post));
