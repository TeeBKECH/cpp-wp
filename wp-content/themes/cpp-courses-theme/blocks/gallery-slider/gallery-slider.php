<?php
/**
 * SCF block: service gallery slider (matches .gallery.gallery--service + Swiper init on .gallery_items).
 *
 * @package CppCoursesTheme
 *
 * @var array $block The block settings and attributes.
 */

if (!defined('ABSPATH')) {
    exit;
}

$slides = function_exists('get_field') ? get_field('cpp_gallery_slides') : null;
if (!is_array($slides)) {
    $slides = array();
}

$bid = isset($block['anchor']) && is_string($block['anchor']) && $block['anchor'] !== ''
    ? sanitize_title($block['anchor'])
    : 'g' . (isset($block['id']) ? preg_replace('/\W/', '', (string) $block['id']) : wp_unique_id());
$fancy_group = 'gallery-' . $bid;
?>
<div class="page-content_gallery wp-block-acf-cpp-gallery-slider">
    <div class="gallery gallery--service">
        <div class="gallery_items">
            <?php foreach ($slides as $row) : ?>
                <?php
                $img = isset($row['image']) ? $row['image'] : null;
                $url = is_array($img) && !empty($img['url']) ? (string) $img['url'] : '';
                if ($url === '') {
                    continue;
                }
                $alt = isset($row['alt']) ? trim((string) $row['alt']) : '';
                if ($alt === '' && is_array($img) && !empty($img['alt'])) {
                    $alt = (string) $img['alt'];
                }
                if ($alt === '') {
                    $alt = __('Изображение', 'cpp-courses-theme');
                }
                ?>
                <div class="gallery_item" data-fancybox="<?php echo esc_attr($fancy_group); ?>" data-src="<?php echo esc_url($url); ?>">
                    <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
                </div>
            <?php endforeach; ?>
        </div>
        <div class="gallery_nav">
            <nav class="swiper_navigation swiper_navigation--gallery" aria-label="<?php esc_attr_e('Навигация галереи', 'cpp-courses-theme'); ?>">
                <button class="swiper_navigation-btn swiper_navigation-btn--prev" type="button" aria-label="<?php esc_attr_e('Назад', 'cpp-courses-theme'); ?>"></button>
                <div class="swiper_navigation-pages"></div>
                <button class="swiper_navigation-btn swiper_navigation-btn--next" type="button" aria-label="<?php esc_attr_e('Вперёд', 'cpp-courses-theme'); ?>"></button>
            </nav>
        </div>
    </div>
</div>
