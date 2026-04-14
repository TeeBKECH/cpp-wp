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
 * CF7 wrapper classes matching static layout (.form .form--dark / .form .form--light).
 *
 * @param string $variant 'dark' (CTA, modals, quiz) or 'light' (contacts page).
 * @return string Space-separated classes for shortcode html_class=.
 */
function cpp_courses_cf7_form_html_class($variant = 'dark') {
    $variant = ($variant === 'light') ? 'light' : 'dark';
    $default = $variant === 'light' ? 'form form--light' : 'form form--dark';
    $class = apply_filters('cpp_courses_cf7_form_class', $default, $variant);
    $class = trim(preg_replace('/\s+/', ' ', (string) $class));
    return $class;
}

/**
 * Render Contact Form 7 by post ID with layout html_class (CF7 shortcode attribute).
 *
 * @param int|string $form_post_id CF7 post ID from options / SCF.
 * @param string     $variant      'dark' or 'light' (see contacts.html vs CTA sections).
 * @return string
 */
function cpp_courses_render_cf7_form($form_post_id, $variant = 'dark') {
    $id = absint($form_post_id);
    if ($id < 1) {
        return '';
    }
    $html_class = cpp_courses_cf7_form_html_class($variant);
    if ($html_class === '') {
        return do_shortcode('[contact-form-7 id="' . $id . '"]');
    }
    return do_shortcode('[contact-form-7 id="' . $id . '" html_class="' . esc_attr($html_class) . '"]');
}

/**
 * CF7 post ID for dark CTA blocks (section--cta), modals, quiz — not the contacts page form.
 *
 * @return int
 */
function cpp_courses_get_cta_dark_cf7_form_id() {
    $dark = cpp_courses_get_option('cpp_cf7_cta_dark_form_post', null);
    $dark_id = is_numeric($dark) ? (int) $dark : 0;
    if ($dark_id > 0) {
        return $dark_id;
    }
    $fallback = cpp_courses_get_option('cpp_cf7_form_post', null);
    return is_numeric($fallback) ? (int) $fallback : 0;
}

/**
 * CF7 post ID for global lead modal (data-modal="lead-form-modal").
 *
 * @return int
 */
function cpp_courses_get_modal_cf7_form_id() {
    $id = cpp_courses_get_option('cpp_cf7_modal_form_post', null);
    return is_numeric($id) ? (int) $id : 0;
}

/**
 * Normalize phone for tel: links.
 *
 * @param string $phone
 * @return string
 */
function cpp_courses_phone_to_tel($phone) {
    $phone = (string) $phone;
    // Keep only digits and plus sign.
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return $phone ? $phone : '';
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
    // Backward compat:
    // - new: group_cpp_bottom_nav.json uses repeater field "cpp_bottom_nav"
    // - old: group_cpp_bottom_nav_items used "cpp_bottom_nav_items"
    $field_name = 'cpp_bottom_nav';
    if (function_exists('have_rows') && have_rows('cpp_bottom_nav', 'option')) {
        $field_name = 'cpp_bottom_nav';
    } elseif (function_exists('have_rows') && have_rows('cpp_bottom_nav_items', 'option')) {
        $field_name = 'cpp_bottom_nav_items';
    } else {
        return;
    }

    echo '<nav class="bottom-nav">';
    while (have_rows($field_name, 'option')) {
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

