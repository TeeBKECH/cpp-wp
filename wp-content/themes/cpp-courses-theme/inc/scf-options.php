<?php
/**
 * Secure Custom Fields (ACF-compatible) options pages.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page(
        array(
            'page_title' => 'Настройки сайта',
            'menu_title' => 'Настройки сайта',
            'menu_slug'  => 'cpp-site-settings',
            'capability' => 'manage_options',
            'redirect'   => true,
            'position'   => 60,
            'icon_url'   => 'dashicons-admin-generic',
        )
    );

    acf_add_options_sub_page(
        array(
            'page_title'  => 'Контакты',
            'menu_title'  => 'Контакты',
            'parent_slug' => 'cpp-site-settings',
            'menu_slug'   => 'cpp-site-contacts',
        )
    );

    acf_add_options_sub_page(
        array(
            'page_title'  => 'Брендинг',
            'menu_title'  => 'Брендинг',
            'parent_slug' => 'cpp-site-settings',
            'menu_slug'   => 'cpp-site-branding',
        )
    );

    acf_add_options_sub_page(
        array(
            'page_title'  => 'Архивы и разделы',
            'menu_title'  => 'Архивы',
            'parent_slug' => 'cpp-site-settings',
            'menu_slug'   => 'cpp-site-archives',
        )
    );
});

