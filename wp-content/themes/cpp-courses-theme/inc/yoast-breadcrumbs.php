<?php
/**
 * Yoast breadcrumbs markup adapter.
 *
 * Keeps Yoast as the data source + uses separator from Yoast settings,
 * but renders HTML in theme layout format.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render Yoast breadcrumbs using theme markup.
 *
 * @return void
 */
function cpp_courses_render_yoast_breadcrumbs() {
    if (!function_exists('yoast_breadcrumb')) {
        return;
    }

    $adapter = new Cpp_Courses_Yoast_Breadcrumbs_Adapter();
    $adapter->render();
}

add_action('cpp_courses_breadcrumbs', 'cpp_courses_render_yoast_breadcrumbs');

/**
 * Adapter class based on wp-kama approach.
 *
 * Important: We DO NOT override Yoast separator so it stays editable in admin.
 */
class Cpp_Courses_Yoast_Breadcrumbs_Adapter {
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @return void
     */
    public function render() {
        $this->position = 0;

        add_filter('wpseo_breadcrumb_single_link', array($this, 'filter_single_link'), 10, 2);
        add_filter('wpseo_breadcrumb_output_wrapper', array($this, 'filter_output_wrapper'));
        add_filter('wpseo_breadcrumb_output', array($this, 'filter_output'));
        add_filter('wpseo_breadcrumb_separator', array($this, 'filter_separator'));

        // Output without custom wrapper; Yoast will call our filters.
        yoast_breadcrumb();

        remove_filter('wpseo_breadcrumb_single_link', array($this, 'filter_single_link'), 10);
        remove_filter('wpseo_breadcrumb_output_wrapper', array($this, 'filter_output_wrapper'));
        remove_filter('wpseo_breadcrumb_output', array($this, 'filter_output'));
        remove_filter('wpseo_breadcrumb_separator', array($this, 'filter_separator'));
    }

    /**
     * Convert Yoast separator into our markup.
     *
     * Separator value remains editable in Yoast settings.
     *
     * @param string $separator
     * @return string
     */
    public function filter_separator($separator) {
        $sep = trim((string) $separator);
        if ($sep === '') {
            $sep = '/';
        }
        return '<span class="breadcrumbs_sep" aria-hidden="true">' . esc_html($sep) . '</span>';
    }

    /**
     * Prevent Yoast from wrapping all crumbs into a single <span>.
     *
     * @return string
     */
    public function filter_output_wrapper() {
        return 'cppbcrumbswrapper';
    }

    /**
     * Convert Yoast final HTML to our nav/ol wrapper.
     *
     * @param string $html
     * @return string
     */
    public function filter_output($html) {
        $html = str_replace(array('<cppbcrumbswrapper>', '</cppbcrumbswrapper>'), '', (string) $html);

        return '<nav class="breadcrumbs" aria-label="Хлебные крошки"><ol class="breadcrumbs_list">' . $html . '</ol></nav>';
    }

    /**
     * Convert Yoast link HTML into our <li> markup.
     *
     * We intentionally do not insert separators ourselves: Yoast will still output the separator
     * from its settings between single_link items.
     *
     * @param string               $link_html
     * @param array<string, mixed> $link_data
     * @return string
     */
    public function filter_single_link($link_html, $link_data) {
        $text = isset($link_data['text']) ? (string) $link_data['text'] : '';
        $url = isset($link_data['url']) ? (string) $link_data['url'] : '';

        $is_last = (strpos((string) $link_html, 'breadcrumb_last') !== false);

        $inner = '';
        if (!$is_last && !empty($url)) {
            $inner .= '<a class="breadcrumbs_link" href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
        } else {
            $inner .= '<span class="breadcrumbs_current" aria-current="page">' . esc_html($text) . '</span>';
        }

        // Inject schema position meta (optional but harmless).
        $inner .= '<meta itemprop="position" content="' . esc_attr((string) (++$this->position)) . '" />';

        return '<li class="breadcrumbs_item">' . $inner . '</li>';
    }
}

add_action('cpp_courses_breadcrumbs', 'cpp_courses_render_yoast_breadcrumbs');

