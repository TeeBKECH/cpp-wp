<?php
/**
 * Theme helpers.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get an SCF/ACF option field value with a fallback.
 *
 * @param string $field_name
 * @param mixed  $default
 * @return mixed
 */
function cpp_courses_get_option($field_name, $default = '') {
    if (function_exists('get_field')) {
        $value = get_field($field_name, 'option');
        if ($value !== null && $value !== false && $value !== '') {
            return $value;
        }
    }
    return $default;
}

/**
 * Convert an attachment image field (id/array/url) to a URL.
 *
 * @param mixed $field_value
 * @return string
 */
function cpp_courses_image_field_url($field_value) {
    if (is_array($field_value) && !empty($field_value['url'])) {
        return (string) $field_value['url'];
    }
    if (is_numeric($field_value)) {
        $url = wp_get_attachment_image_url((int) $field_value, 'full');
        return $url ? (string) $url : '';
    }
    if (is_string($field_value)) {
        return $field_value;
    }
    return '';
}

/**
 * Render bottom navigation items from options (SCF repeater).
 *
 * Expected option field name: cpp_bottom_nav_items
 * Row shape:
 * - icon (image)
 * - link (link)
 * - label (text)
 *
 * @return void
 */
function cpp_courses_render_bottom_nav() {
    if (!function_exists('have_rows') || !have_rows('cpp_bottom_nav_items', 'option')) {
        return;
    }

    echo '<nav class="bottom-nav">';
    while (have_rows('cpp_bottom_nav_items', 'option')) {
        the_row();
        $icon = get_sub_field('icon');
        $link = get_sub_field('link');
        $label = get_sub_field('label');

        $url = is_array($link) && !empty($link['url']) ? $link['url'] : '#';
        $title = is_array($link) && !empty($link['title']) ? $link['title'] : (string) $label;
        $target = is_array($link) && !empty($link['target']) ? $link['target'] : '_self';
        $icon_url = cpp_courses_image_field_url($icon);

        echo '<a class="bottom-nav_item" href="' . esc_url($url) . '" target="' . esc_attr($target) . '">';
        if ($icon_url) {
            echo '<div class="bottom-nav_item-icon"><img src="' . esc_url($icon_url) . '" alt="' . esc_attr($title) . '" /></div>';
        } else {
            echo '<div class="bottom-nav_item-icon"></div>';
        }
        echo '<span class="bottom-nav_item-label">' . esc_html($label) . '</span>';
        echo '</a>';
    }
    echo '</nav>';
}

