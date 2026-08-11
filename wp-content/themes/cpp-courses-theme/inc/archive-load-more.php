<?php
/**
 * Archive "load more" AJAX and card rendering.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Map render_part POST value to template part (slug, name).
 *
 * @return array{slug:string,name:string}|null
 */
function cpp_courses_load_more_template_map() {
    return array(
        'article' => array('slug' => 'post-card', 'name' => 'article'),
        'service' => array('slug' => 'post-card', 'name' => 'service'),
    );
}

/**
 * Render a post card for archive load-more (by template part key).
 *
 * @param WP_Post $post
 * @param string  $part_key 'article'|'service'
 * @return string
 */
function cpp_courses_render_archive_card($post, $part_key) {
    if (!$post instanceof WP_Post) {
        return '';
    }
    $map = cpp_courses_load_more_template_map();
    if (!isset($map[$part_key])) {
        $part_key = 'article';
    }
    $slug = $map[$part_key]['slug'];
    $name = $map[$part_key]['name'];

    $GLOBALS['post'] = $post;
    setup_postdata($post);
    ob_start();
    get_template_part('template-parts/' . $slug, $name, array('post' => $post));
    $html = (string) ob_get_clean();
    wp_reset_postdata();
    return $html;
}

/**
 * AJAX: load more archive cards (articles, services, …).
 *
 * @return void
 */
function cpp_courses_ajax_load_more_posts() {
    check_ajax_referer('cpp_archive_load_more_nonce', 'nonce');

    $per_page = (int) get_option('posts_per_page', 10);
    $per_page = $per_page > 0 ? $per_page : 10;

    $post_type = isset($_POST['post_type']) ? sanitize_key((string) $_POST['post_type']) : 'articles';
    if (!$post_type) {
        $post_type = 'articles';
    }

    $part_key = isset($_POST['render_part']) ? sanitize_key((string) $_POST['render_part']) : 'article';
    $map = cpp_courses_load_more_template_map();
    if (!isset($map[$part_key])) {
        $part_key = 'article';
    }

    $exclude_ids = array();
    if (isset($_POST['exclude_ids'])) {
        $raw = (string) wp_unslash($_POST['exclude_ids']);
        $exclude_ids = array_filter(array_map('absint', preg_split('/\s*,\s*/', $raw)));
    }

    $offset = isset($_POST['offset']) ? max(0, (int) $_POST['offset']) : null;
    $paged = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;

    $requested_offset = $offset !== null ? $offset : (($paged - 1) * $per_page);
    $scan_offset = $requested_offset;
    $scan_limit = $per_page;
    $max_scan_attempts = 5;
    $furthest_scanned_end = $requested_offset;

    $kept_posts = array();
    $seen_ids = array();

    for ($attempt = 0; $attempt < $max_scan_attempts; $attempt++) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $scan_limit,
            'offset' => $scan_offset,
            'no_found_rows' => false,
        );
        $query = new WP_Query($args);
        $window_end = $scan_offset + (int) $query->post_count;
        $furthest_scanned_end = max($furthest_scanned_end, $window_end);

        if (empty($query->posts)) {
            break;
        }

        foreach ($query->posts as $p) {
            $pid = (int) $p->ID;
            if ($pid <= 0) {
                continue;
            }
            if (in_array($pid, $exclude_ids, true)) {
                continue;
            }
            if (isset($seen_ids[$pid])) {
                continue;
            }
            $seen_ids[$pid] = true;
            $kept_posts[] = $p;
            if (count($kept_posts) >= $per_page) {
                break 2;
            }
        }

        $scan_offset = $window_end;
        $scan_limit = $per_page;
    }

    $html = '';
    foreach ($kept_posts as $p) {
        $html .= cpp_courses_render_archive_card($p, $part_key);
    }

    $window_size = isset($_POST['window_size']) ? max(1, (int) $_POST['window_size']) : $per_page;
    $next_offset = max($furthest_scanned_end, $requested_offset + $window_size);
    $has_more = true;
    if (isset($query) && $query instanceof WP_Query) {
        $has_more = $next_offset < (int) $query->found_posts;
    }

    wp_send_json_success(
        array(
            'html' => $html,
            'has_more' => $has_more,
            'next_offset' => $next_offset,
            'appended_exclude_ids' => implode(',', array_unique(array_filter(array_merge($exclude_ids, array_map(static function ($p) { return (int) $p->ID; }, $kept_posts))))),
            'next_page' => $paged + 1,
        )
    );
}
add_action('wp_ajax_cpp_load_more_posts', 'cpp_courses_ajax_load_more_posts');
add_action('wp_ajax_nopriv_cpp_load_more_posts', 'cpp_courses_ajax_load_more_posts');
