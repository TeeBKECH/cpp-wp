<?php
/**
 * Register ACF/SCF blocks and load field group JSON.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_acf_register_gallery_slider_block() {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type(
        array(
            'name' => 'cpp-gallery-slider',
            'title' => __('Галерея (слайдер)', 'cpp-courses-theme'),
            'description' => __('Как на странице услуги: слайдер + полноэкранный просмотр.', 'cpp-courses-theme'),
            'category' => 'media',
            'icon' => 'images-alt2',
            'keywords' => array('gallery', 'slider', 'swiper'),
            'mode' => 'preview',
            'supports' => array(
                'align' => false,
                'anchor' => true,
                'jsx' => true,
            ),
            'render_template' => get_template_directory() . '/blocks/gallery-slider/gallery-slider.php',
        )
    );
}
add_action('acf/init', 'cpp_courses_acf_register_gallery_slider_block');

/**
 * @return void
 */
function cpp_courses_acf_load_gallery_block_field_group() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    $path = trailingslashit(get_template_directory()) . 'acf-json/group_cpp_block_gallery_slider.json';
    if (!is_readable($path)) {
        return;
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return;
    }
    $group = json_decode($raw, true);
    if (!is_array($group) || empty($group['key']) || empty($group['fields'])) {
        return;
    }
    acf_add_local_field_group($group);
}
add_action('acf/init', 'cpp_courses_acf_load_gallery_block_field_group', 5);
