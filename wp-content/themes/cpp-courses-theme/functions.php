<?php
/**
 * Theme bootstrap for CPP Courses.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function cpp_courses_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'script', 'style'));
    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'cpp-courses-theme'),
        )
    );
}
add_action('after_setup_theme', 'cpp_courses_theme_setup');

function cpp_courses_enqueue_assets() {
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    $css_files = glob($theme_dir . '/assets/css/*.css');
    if (!empty($css_files)) {
        $css_file = basename($css_files[0]);
        wp_enqueue_style(
            'cpp-courses-app',
            $theme_uri . '/assets/css/' . $css_file,
            array(),
            filemtime($css_files[0])
        );
    }

    $js_files = glob($theme_dir . '/assets/js/*.js');
    if (!empty($js_files)) {
        $js_file = basename($js_files[0]);
        wp_enqueue_script(
            'cpp-courses-app',
            $theme_uri . '/assets/js/' . $js_file,
            array(),
            filemtime($js_files[0]),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cpp_courses_enqueue_assets');

/**
 * Render static build HTML content inside WordPress template.
 *
 * @param string $file_name Static HTML filename located in theme root.
 * @return string
 */
function cpp_courses_render_static_page($file_name) {
    $file_path = trailingslashit(get_template_directory()) . ltrim($file_name, '/');
    if (!file_exists($file_path)) {
        return '<main class="page"><section class="section"><div class="container"><h1>Страница не найдена</h1></div></section></main>';
    }

    $html = file_get_contents($file_path);
    if ($html === false) {
        return '<main class="page"><section class="section"><div class="container"><h1>Ошибка чтения шаблона</h1></div></section></main>';
    }

    if (!preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $matches)) {
        return $html;
    }

    $content = $matches[1];
    $theme_uri = trailingslashit(get_template_directory_uri());
    $asset_uri = $theme_uri . 'assets/';

    $replacements = array(
        'src="assets/' => 'src="' . $asset_uri,
        "src='assets/" => "src='" . $asset_uri,
        'href="assets/' => 'href="' . $asset_uri,
        "href='assets/" => "href='" . $asset_uri,
        'href="index.html"' => 'href="' . esc_url(home_url('/')) . '"',
        'href="services.html"' => 'href="' . esc_url(home_url('/services/')) . '"',
        'href="service.html"' => 'href="' . esc_url(home_url('/service/')) . '"',
        'href="course.html"' => 'href="' . esc_url(home_url('/course/')) . '"',
        'href="articles.html"' => 'href="' . esc_url(home_url('/articles/')) . '"',
        'href="article.html"' => 'href="' . esc_url(home_url('/article/')) . '"',
        'href="contacts.html"' => 'href="' . esc_url(home_url('/contacts/')) . '"',
        'href="edu-info.html"' => 'href="' . esc_url(home_url('/edu-info/')) . '"',
        'href="test-intro.html"' => 'href="' . esc_url(home_url('/test-intro/')) . '"',
        'href="test-quiz.html"' => 'href="' . esc_url(home_url('/test-quiz/')) . '"',
        'href="links.html"' => 'href="' . esc_url(home_url('/links/')) . '"',
    );

    $content = strtr($content, $replacements);
    $content = preg_replace('/<script[^>]+src="\/assets\/js\/[^"]+"[^>]*><\/script>/i', '', $content);
    $content = preg_replace('/<script[^>]+type="module"[^>]*><\/script>/i', '', $content);

    return $content;
}
