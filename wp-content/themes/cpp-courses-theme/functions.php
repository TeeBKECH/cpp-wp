<?php
/**
 * Theme bootstrap for CPP Courses.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/inc/scf-options.php';
require_once __DIR__ . '/inc/helpers.php';

add_filter('acf/settings/save_json', function ($path) {
    return __DIR__ . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = __DIR__ . '/acf-json';
    return $paths;
});

function cpp_courses_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'script', 'style'));
    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'cpp-courses-theme'),
            'mobile_primary' => __('Mobile Primary Menu', 'cpp-courses-theme'),
            'footer' => __('Footer Menu', 'cpp-courses-theme'),
        )
    );
}
add_action('after_setup_theme', 'cpp_courses_theme_setup');

/**
 * Get static HTML file mapping by page slug.
 *
 * @return array<string, string>
 */
function cpp_courses_static_page_map() {
    return array(
        'services' => 'services.html',
        'service' => 'service.html',
        'course' => 'course.html',
        'articles' => 'articles.html',
        'article' => 'article.html',
        'contacts' => 'contacts.html',
        'edu-info' => 'edu-info.html',
        'test-intro' => 'test-intro.html',
        'test-quiz' => 'test-quiz.html',
        'links' => 'links.html',
    );
}

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
    // theme-overrides.css was a temporary workaround; WP-specific build now produces correct URLs.

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
 * Increase view counter for single articles posts.
 *
 * @return void
 */
function cpp_courses_track_article_views() {
    if (!is_singular('articles') || is_admin()) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }

    $meta_key = 'cpp_article_views';
    $views = (int) get_post_meta($post_id, $meta_key, true);
    update_post_meta($post_id, $meta_key, $views + 1);
}
add_action('template_redirect', 'cpp_courses_track_article_views');

/**
 * Render article card template.
 *
 * @param WP_Post $post
 * @return string
 */
function cpp_courses_render_article_card($post) {
    if (!$post instanceof WP_Post) {
        return '';
    }
    ob_start();
    get_template_part('template-parts/article-card', null, array('post' => $post));
    return (string) ob_get_clean();
}

/**
 * AJAX: load more articles cards.
 *
 * @return void
 */
function cpp_courses_ajax_load_more_articles() {
    check_ajax_referer('cpp_articles_nonce', 'nonce');

    $paged = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, (int) $_POST['per_page']) : 9;

    $query = new WP_Query(
        array(
            'post_type' => 'articles',
            'post_status' => 'publish',
            'paged' => $paged,
            'posts_per_page' => $per_page,
        )
    );

    $html = '';
    if ($query->have_posts()) {
        foreach ($query->posts as $article_post) {
            $html .= cpp_courses_render_article_card($article_post);
        }
    }

    wp_send_json_success(
        array(
            'html' => $html,
            'has_more' => $paged < (int) $query->max_num_pages,
            'next_page' => $paged + 1,
        )
    );
}
add_action('wp_ajax_cpp_load_more_articles', 'cpp_courses_ajax_load_more_articles');
add_action('wp_ajax_nopriv_cpp_load_more_articles', 'cpp_courses_ajax_load_more_articles');

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

    $content = strtr(
        $content,
        array(
            'src="assets/' => 'src="' . $asset_uri,
            "src='assets/" => "src='" . $asset_uri,
            'href="assets/' => 'href="' . $asset_uri,
            "href='assets/" => "href='" . $asset_uri,
            'href="/assets/' => 'href="' . $asset_uri,
            "href='/assets/" => "href='" . $asset_uri,
            'src="/assets/' => 'src="' . $asset_uri,
            "src='/assets/" => "src='" . $asset_uri,
            'href="index.html"' => 'href="' . esc_url(home_url('/')) . '"',
            "href='index.html'" => "href='" . esc_url(home_url('/')) . "'",
            'href="index.html#' => 'href="' . esc_url(home_url('/')) . '#',
            "href='index.html#" => "href='" . esc_url(home_url('/')) . '#',
        )
    );

    $map = cpp_courses_static_page_map();
    foreach ($map as $slug => $html_page) {
        $target_url = esc_url(home_url('/' . $slug . '/'));
        $content = str_replace(
            array(
                'href="' . $html_page . '"',
                "href='" . $html_page . "'",
            ),
            array(
                'href="' . $target_url . '"',
                "href='" . $target_url . "'",
            ),
            $content
        );
        $content = str_replace(
            array(
                'href="' . $html_page . '#',
                "href='" . $html_page . '#',
            ),
            array(
                'href="' . $target_url . '#',
                "href='" . $target_url . '#',
            ),
            $content
        );
    }

    $content = preg_replace('/<script[^>]+src="\/assets\/js\/[^"]+"[^>]*><\/script>/i', '', $content);
    $content = preg_replace('/<script[^>]+type="module"[^>]*><\/script>/i', '', $content);

    return $content;
}

/**
 * Render only <main>...</main> from a static template.
 *
 * @param string $file_name
 * @return string
 */
function cpp_courses_render_static_main($file_name) {
    $html = cpp_courses_render_static_page($file_name);
    if (preg_match('/<main\\b[^>]*>[\\s\\S]*?<\\/main>/i', $html, $m)) {
        return $m[0];
    }
    return $html;
}
