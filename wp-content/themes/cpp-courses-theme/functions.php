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
require_once __DIR__ . '/inc/nav-walker.php';

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
 * Get current archive page from query var or ?page.
 *
 * @return int
 */
function cpp_courses_get_current_archive_page() {
    $paged = (int) get_query_var('paged');
    $page_query = isset($_GET['page']) ? (int) $_GET['page'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return max(1, $paged, $page_query);
}

/**
 * Build compact archive pagination links using ?page=N.
 *
 * @param int $current_page Current page.
 * @param int $max_pages Total pages.
 * @return array<int, array<string, mixed>>
 */
function cpp_courses_get_compact_archive_pagination($current_page, $max_pages) {
    $current_page = max(1, (int) $current_page);
    $max_pages = max(1, (int) $max_pages);
    if ($max_pages <= 1) {
        return array();
    }

    $links = array();

    if ($current_page > 1) {
        $links[] = array(
            'type' => 'prev',
            'page' => $current_page - 1,
        );
    }

    $pages = array();
    if ($max_pages <= 5) {
        $pages = range(1, $max_pages);
    } elseif ($current_page <= 3) {
        $pages = array(1, 2, 3, 'dots', $max_pages);
    } elseif ($current_page >= $max_pages - 2) {
        $pages = array(1, 'dots', $max_pages - 2, $max_pages - 1, $max_pages);
    } else {
        $pages = array(1, 'dots', $current_page, 'dots', $max_pages);
    }

    foreach ($pages as $page_number) {
        if ($page_number === 'dots') {
            $links[] = array('type' => 'dots');
            continue;
        }
        $links[] = array(
            'type' => (int) $page_number === $current_page ? 'current' : 'page',
            'page' => (int) $page_number,
        );
    }

    if ($current_page < $max_pages) {
        $links[] = array(
            'type' => 'next',
            'page' => $current_page + 1,
        );
    }

    return $links;
}

/**
 * Build archive pagination URL via ?page=N.
 *
 * @param int $page_number
 * @return string
 */
function cpp_courses_get_archive_page_url($page_number) {
    $page_number = max(1, (int) $page_number);
    $base_url = get_post_type_archive_link(get_post_type());
    if (!$base_url) {
        $base_url = home_url('/');
    }
    if ($page_number <= 1) {
        return (string) $base_url;
    }
    return (string) add_query_arg('page', $page_number, $base_url);
}

/**
 * Render reusable archive pagination markup.
 *
 * @param int $current_page
 * @param int $max_pages
 * @return string
 */
function cpp_courses_render_archive_pagination($current_page, $max_pages) {
    $items = cpp_courses_get_compact_archive_pagination($current_page, $max_pages);
    if (empty($items)) {
        return '';
    }

    $html = '<ul class="pagination_list">';
    foreach ($items as $item) {
        $html .= '<li class="pagination_list_item">';
        switch ($item['type']) {
            case 'dots':
                $html .= '<span class="pagination_list_link dots">…</span>';
                break;
            case 'current':
                $html .= '<span class="pagination_list_link pagination_list_current" aria-current="page">' . esc_html((string) $item['page']) . '</span>';
                break;
            case 'prev':
                $html .= '<a class="pagination_list_link pagination_list_link--prev" href="' . esc_url(cpp_courses_get_archive_page_url((int) $item['page'])) . '"><span class="pagination_list_icon pagination_list_icon--prev"></span></a>';
                break;
            case 'next':
                $html .= '<a class="pagination_list_link pagination_list_link--next" href="' . esc_url(cpp_courses_get_archive_page_url((int) $item['page'])) . '"><span class="pagination_list_icon pagination_list_icon--next"></span></a>';
                break;
            default:
                $html .= '<a class="pagination_list_link" href="' . esc_url(cpp_courses_get_archive_page_url((int) $item['page'])) . '">' . esc_html((string) $item['page']) . '</a>';
                break;
        }
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

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
    $GLOBALS['post'] = $post;
    setup_postdata($post);
    ob_start();
    get_template_part('template-parts/post-card', 'article', array('post' => $post));
    $html = (string) ob_get_clean();
    wp_reset_postdata();
    return $html;
}

/**
 * AJAX: load more articles cards.
 *
 * @return void
 */
function cpp_courses_ajax_load_more_articles() {
    check_ajax_referer('cpp_articles_nonce', 'nonce');

    $per_page = (int) get_option('posts_per_page', 10);
    $per_page = $per_page > 0 ? $per_page : 10;

    $post_type = isset($_POST['post_type']) ? sanitize_key((string) $_POST['post_type']) : 'articles';
    if (!$post_type) {
        $post_type = 'articles';
    }

    $exclude_ids = array();
    if (isset($_POST['exclude_ids'])) {
        $raw = (string) wp_unslash($_POST['exclude_ids']);
        $exclude_ids = array_filter(array_map('absint', preg_split('/\s*,\s*/', $raw)));
    }

    // Prefer offset-based pagination to avoid duplicates on paged archives.
    $offset = isset($_POST['offset']) ? max(0, (int) $_POST['offset']) : null;
    $paged = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;

    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $per_page,
    );
    if (!empty($exclude_ids)) {
        $args['post__not_in'] = $exclude_ids;
    }

    if ($offset !== null) {
        $args['offset'] = $offset;
    } else {
        $args['paged'] = $paged;
    }

    $query = new WP_Query($args);

    $html = '';
    if ($query->have_posts()) {
        foreach ($query->posts as $article_post) {
            $html .= cpp_courses_render_article_card($article_post);
        }
    }

    $returned = (int) $query->post_count;
    $total_found = (int) $query->found_posts;
    $next_offset = ($offset !== null ? $offset : (($paged - 1) * $per_page)) + $returned;

    wp_send_json_success(
        array(
            'html' => $html,
            'has_more' => $next_offset < $total_found,
            'next_offset' => $next_offset,
            'next_page' => $paged + 1,
        )
    );
}
add_action('wp_ajax_cpp_load_more_articles', 'cpp_courses_ajax_load_more_articles');
add_action('wp_ajax_nopriv_cpp_load_more_articles', 'cpp_courses_ajax_load_more_articles');

/**
 * Map ?page=N to WP paged for post type archives.
 *
 * @param WP_Query $query Query object.
 * @return void
 */
function cpp_courses_archive_query_page_to_paged($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive()) {
        return;
    }
    $page_query = isset($_GET['page']) ? (int) $_GET['page'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page_query > 1) {
        $query->set('paged', $page_query);
    }
}
add_action('pre_get_posts', 'cpp_courses_archive_query_page_to_paged');

/**
 * Disable WordPress canonical redirects for CPT archives when using ?page=N pagination.
 *
 * WordPress core tends to canonicalize paged archives to /page/N/, which creates
 * redirects like /articles/?page=2 -> /articles/page/2/?page=2.
 *
 * @param string|false $redirect_url
 * @return string|false
 */
function cpp_courses_disable_canonical_for_query_page_archives($redirect_url) {
    if (is_admin() || !is_post_type_archive()) {
        return $redirect_url;
    }
    // When explicitly using ?page=, keep it canonical.
    if (isset($_GET['page']) && (int) $_GET['page'] > 0) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'cpp_courses_disable_canonical_for_query_page_archives', 10, 1);

/**
 * Canonicalize CPT archive pagination to ?page=N (no /page/N/; no duplicates).
 *
 * @return void
 */
function cpp_courses_canonicalize_archive_pagination() {
    if (is_admin() || !is_post_type_archive()) {
        return;
    }

    $paged = (int) get_query_var('paged');
    $page_query = isset($_GET['page']) ? (int) $_GET['page'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    // If query arg is present, it is canonical; just normalize /page/N/?page=N -> ?page=N.
    if ($page_query > 0) {
        if (strpos($request_uri, '/page/') !== false) {
            $target = cpp_courses_get_archive_page_url($page_query);
            wp_safe_redirect($target, 301);
            exit;
        }
        // Normalize ?page=1 to canonical page 1 URL.
        if ($page_query === 1) {
            $target = cpp_courses_get_archive_page_url(1);
            wp_safe_redirect($target, 301);
            exit;
        }
        return;
    }

    // Redirect only when the request is actually /page/N/ style.
    if ($paged > 1 && strpos($request_uri, '/page/') !== false) {
        $target = cpp_courses_get_archive_page_url($paged);
        wp_safe_redirect($target, 301);
        exit;
    }
}
add_action('template_redirect', 'cpp_courses_canonicalize_archive_pagination', 1);

/**
 * Use ?page=N for CPT archives instead of /page/N/.
 *
 * @param string $url
 * @param int    $page
 * @return string
 */
function cpp_courses_archive_pagenum_link($url, $page) {
    if (is_admin() || !is_post_type_archive()) {
        return $url;
    }
    $post_type = get_query_var('post_type');
    if (is_array($post_type)) {
        $post_type = reset($post_type);
    }
    if (!$post_type) {
        $post_type = get_post_type();
    }
    $archive_url = $post_type ? get_post_type_archive_link($post_type) : '';
    if (!$archive_url) {
        return $url;
    }
    if ((int) $page <= 1) {
        return $archive_url;
    }
    return add_query_arg('page', (int) $page, $archive_url);
}
add_filter('get_pagenum_link', 'cpp_courses_archive_pagenum_link', 10, 2);

/**
 * Remove Yoast paged crumb on articles archive.
 *
 * @param array<int, array<string, mixed>> $crumbs
 * @return array<int, array<string, mixed>>
 */
function cpp_courses_filter_yoast_breadcrumb_links($crumbs) {
    if (!is_post_type_archive('articles') || empty($crumbs) || !is_array($crumbs)) {
        return $crumbs;
    }

    $last_index = array_key_last($crumbs);
    if ($last_index !== null && isset($crumbs[$last_index]['text']) && is_string($crumbs[$last_index]['text'])) {
        if (preg_match('/^Страница\s+\d+$/u', trim($crumbs[$last_index]['text']))) {
            unset($crumbs[$last_index]);
            $crumbs = array_values($crumbs);
        }
    }

    return $crumbs;
}
add_filter('wpseo_breadcrumb_links', 'cpp_courses_filter_yoast_breadcrumb_links');

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
