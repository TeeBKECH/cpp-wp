<?php
/**
 * Education CPT archive: grouping, sorting, filter (?filter=term-slug).
 *
 * Post type and taxonomy are registered in SCF, not in this theme.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Active education_cats slug from query string.
 *
 * @return string
 */
function cpp_courses_education_get_filter_slug() {
    if (!isset($_GET['filter']) || !is_string($_GET['filter'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return '';
    }
    $slug = sanitize_title(wp_unslash($_GET['filter'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return $slug;
}

/**
 * Sort rank for archive cards (download → link → gallery).
 *
 * @param int $post_id
 * @return int
 */
function cpp_courses_education_display_rank($post_id) {
    $type = '';
    if (function_exists('get_field')) {
        $raw = get_field('cpp_edu_display_type', $post_id);
        $type = is_string($raw) ? $raw : '';
    }
    $map = array(
        'download' => 1,
        'link' => 2,
        'gallery' => 3,
    );
    return isset($map[$type]) ? $map[$type] : 99;
}

/**
 * Sort education posts for one category bucket.
 *
 * @param array<int, WP_Post> $posts
 * @return array<int, WP_Post>
 */
function cpp_courses_education_sort_posts_for_archive(array $posts) {
    usort(
        $posts,
        static function (WP_Post $a, WP_Post $b) {
            $ra = cpp_courses_education_display_rank((int) $a->ID);
            $rb = cpp_courses_education_display_rank((int) $b->ID);
            if ($ra !== $rb) {
                return $ra <=> $rb;
            }
            $oa = (int) $a->menu_order;
            $ob = (int) $b->menu_order;
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }
            return strtotime((string) $b->post_date) <=> strtotime((string) $a->post_date);
        }
    );
    return $posts;
}

/**
 * Fetch all published education posts (optionally filtered by one category slug).
 *
 * @param string $filter_slug education_cats slug or ''.
 * @return array<int, WP_Post>
 */
function cpp_courses_education_get_archive_posts($filter_slug) {
    $args = array(
        'post_type' => 'education',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array(
            'menu_order' => 'ASC',
            'date' => 'DESC',
        ),
        'no_found_rows' => true,
        'suppress_filters' => false,
    );
    $filter_slug = is_string($filter_slug) ? $filter_slug : '';
    if ($filter_slug !== '') {
        $args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            array(
                'taxonomy' => 'education_cats',
                'field' => 'slug',
                'terms' => $filter_slug,
            ),
        );
    }
    $q = new WP_Query($args);
    return $q->posts;
}

/**
 * Group posts by education_cats term (posts in several terms appear in each list).
 *
 * @param array<int, WP_Post> $posts
 * @return array<int, array{term: WP_Term|null, posts: array<int, WP_Post>}>
 */
function cpp_courses_education_group_posts_by_category(array $posts) {
    $terms = get_terms(
        array(
            'taxonomy' => 'education_cats',
            'hide_empty' => false,
        )
    );
    if (is_wp_error($terms) || empty($terms)) {
        $buckets = array();
    } else {
        $buckets = array();
        foreach ($terms as $term) {
            if ($term instanceof WP_Term) {
                $buckets[(int) $term->term_id] = array(
                    'term' => $term,
                    'posts' => array(),
                    'seen' => array(),
                );
            }
        }
    }

    $uncat = array();

    foreach ($posts as $post) {
        if (!$post instanceof WP_Post) {
            continue;
        }
        $post_terms = wp_get_post_terms((int) $post->ID, 'education_cats');
        if (is_wp_error($post_terms) || empty($post_terms)) {
            $uncat[] = $post;
            continue;
        }
        foreach ($post_terms as $t) {
            if (!$t instanceof WP_Term) {
                continue;
            }
            $tid = (int) $t->term_id;
            if (!isset($buckets[$tid])) {
                $buckets[$tid] = array(
                    'term' => $t,
                    'posts' => array(),
                    'seen' => array(),
                );
            }
            $pid = (int) $post->ID;
            if (!isset($buckets[ $tid ]['seen'][ $pid ])) {
                $buckets[ $tid ]['seen'][ $pid ] = true;
                $buckets[ $tid ]['posts'][] = $post;
            }
        }
    }

    foreach ($buckets as $tid => $data) {
        unset($buckets[ $tid ]['seen']);
        $buckets[ $tid ]['posts'] = cpp_courses_education_sort_posts_for_archive($data['posts']);
    }

    if (!empty($uncat)) {
        $uncat = cpp_courses_education_sort_posts_for_archive($uncat);
        $buckets[0] = array(
            'term' => null,
            'posts' => $uncat,
        );
    }

    return $buckets;
}

/**
 * Ordered list of category buckets for output (empty buckets skipped).
 *
 * @param array<int, array{term: WP_Term|null, posts: array<int, WP_Post>}> $buckets
 * @return list<array{term: WP_Term|null, posts: array<int, WP_Post>}>
 */
function cpp_courses_education_ordered_buckets(array $buckets) {
    $out = array();
    $with_term = array();
    $zero = null;

    foreach ($buckets as $tid => $row) {
        if ((int) $tid === 0) {
            $zero = $row;
            continue;
        }
        if (!empty($row['posts']) && $row['term'] instanceof WP_Term) {
            $with_term[] = $row;
        }
    }

    usort(
        $with_term,
        static function ($a, $b) {
            $ta = $a['term'];
            $tb = $b['term'];
            if (!$ta instanceof WP_Term || !$tb instanceof WP_Term) {
                return 0;
            }
            return strcasecmp((string) $ta->name, (string) $tb->name);
        }
    );

    foreach ($with_term as $row) {
        $out[] = $row;
    }

    if ($zero && !empty($zero['posts'])) {
        $out[] = $zero;
    }

    return $out;
}

/**
 * Render one education archive card by SCF display type.
 *
 * @param WP_Post $post
 * @param string  $fancybox_group Term slug for Fancybox grouping (gallery rows).
 * @return void
 */
function cpp_courses_education_render_archive_card(WP_Post $post, $fancybox_group = '') {
    $type = 'link';
    if (function_exists('get_field')) {
        $raw = get_field('cpp_edu_display_type', $post->ID);
        if (is_string($raw) && $raw !== '') {
            $type = $raw;
        }
    }
    $group = is_string($fancybox_group) ? $fancybox_group : '';
    if ($type === 'download') {
        get_template_part('template-parts/education-card', 'download', array('post' => $post));
        return;
    }
    if ($type === 'gallery') {
        get_template_part(
            'template-parts/education-card',
            'gallery',
            array(
                'post' => $post,
                'fancybox_group' => $group,
            )
        );
        return;
    }
    get_template_part('template-parts/education-card', 'link', array('post' => $post));
}
