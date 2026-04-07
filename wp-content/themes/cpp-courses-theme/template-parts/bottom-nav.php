<?php
/**
 * Bottom navigation (mobile).
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

$items = cpp_courses_get_option('cpp_bottom_nav', array());
if (empty($items) || !is_array($items)) {
    return;
}
?>
<nav class="bottom-nav">
    <?php foreach ($items as $item) :
        $label = isset($item['label']) ? (string) $item['label'] : '';
        $url   = isset($item['url']) ? (string) $item['url'] : '';
        $icon  = isset($item['icon']) ? $item['icon'] : null;
        $icon_url = cpp_courses_image_field_url($icon);
        if (empty($label) || empty($url)) {
            continue;
        }
        ?>
        <a class="bottom-nav_item<?php echo esc_attr(cpp_courses_is_current_url($url) ? ' bottom-nav_item--active' : ''); ?>" href="<?php echo esc_url($url); ?>">
            <?php if (!empty($icon_url)) : ?>
                <div class="bottom-nav_item-icon">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($label); ?>" />
                </div>
            <?php endif; ?>
            <span class="bottom-nav_item-label"><?php echo esc_html($label); ?></span>
        </a>
    <?php endforeach; ?>
</nav>

