<?php
/**
 * Contact Form 7: match theme markup (no extra p/br from wpautop).
 *
 * CF7 runs wpautop() on the form template by default, which wraps tags and inserts
 * <br>, breaking layouts like label > [field] and checkbox acceptance (siblings must
 * stay inside <label>).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disable CF7 automatic <p> and <br> insertion (CF 5.4+).
 *
 * @return bool
 */
function cpp_courses_cf7_disable_autop() {
    return false;
}
add_filter('wpcf7_autop_or_not', 'cpp_courses_cf7_disable_autop');
