<?php
/**
 * Helpers for front page (queries by relationship IDs or latest posts).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string               $post_type
 * @param array<int, int>|null $ids
 * @param int                  $limit
 * @return WP_Post[]
 */
function cpp_home_get_posts_for_section($post_type, $ids, $limit) {
    $post_type = sanitize_key((string) $post_type);
    $limit = max(1, (int) $limit);
    $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : array();

    if (!empty($ids)) {
        $q = new WP_Query(
            array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'post__in' => $ids,
                'orderby' => 'post__in',
                'posts_per_page' => count($ids),
                'no_found_rows' => true,
            )
        );
        return $q->posts;
    }

    $q = new WP_Query(
        array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
        )
    );
    return $q->posts;
}

/**
 * @param int $page_id Front page ID.
 * @return int
 */
function cpp_home_front_page_id($page_id = 0) {
    $page_id = (int) $page_id;
    if ($page_id > 0) {
        return $page_id;
    }
    return (int) get_option('page_on_front');
}
