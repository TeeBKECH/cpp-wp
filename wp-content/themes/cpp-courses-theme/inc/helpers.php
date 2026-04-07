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

