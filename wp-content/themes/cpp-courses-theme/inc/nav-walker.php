<?php
/**
 * Custom nav walker for header/footer menus.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Outputs minimal markup matching the existing HTML:
 * - <ul class="header_nav"><li><a class="header_nav-link" ...>...</a></li></ul>
 */
class Cpp_Courses_Header_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        // No dropdown support in current layout.
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        // No dropdown support in current layout.
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        if ($depth > 0) {
            return;
        }
        $atts = array();
        $atts['href'] = !empty($item->url) ? $item->url : '';
        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = esc_url($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);
        $output .= '<li><a class="header_nav-link"' . $attributes . '>' . esc_html($title) . '</a></li>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // start_el closes li.
    }
}

