<?php
/**
 * SCF block: certificate section (matches .page-content_section--certificate).
 *
 * @package CppCoursesTheme
 *
 * @var array $block The block settings and attributes.
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = function_exists('get_field') ? trim((string) get_field('cpp_cert_title')) : '';
$text = function_exists('get_field') ? (string) get_field('cpp_cert_text') : '';
$files = function_exists('get_field') ? get_field('cpp_cert_files') : null;
$image = function_exists('get_field') ? get_field('cpp_cert_image') : null;

if (!is_array($files)) {
    $files = array();
}

$img_url = '';
$img_full = '';
if (is_array($image) && !empty($image['url'])) {
    $img_url = (string) $image['url'];
    $img_full = $img_url;
    if (!empty($image['sizes']['large'])) {
        $img_full = (string) $image['sizes']['large'];
    }
}
$img_alt = '';
if (is_array($image)) {
    $img_alt = !empty($image['alt']) ? (string) $image['alt'] : '';
}
if ($img_alt === '' && $title !== '') {
    $img_alt = $title;
}
if ($img_alt === '') {
    $img_alt = __('Изображение', 'cpp-courses-theme');
}

$bid = isset($block['anchor']) && is_string($block['anchor']) && $block['anchor'] !== ''
    ? sanitize_title($block['anchor'])
    : 'c' . (isset($block['id']) ? preg_replace('/\W/', '', (string) $block['id']) : wp_unique_id());
$fancy_group = 'cert-' . $bid;

$has_body = $text !== '' || !empty($files);
if ($title === '' && !$has_body && $img_url === '') {
    return;
}
?>
<div class="wp-block-acf-cpp-certificate">
    <div class="page-content_section page-content_section--certificate">
        <?php if ($title !== '') : ?>
            <div class="section_header">
                <h2 class="section_title"><?php echo esc_html($title); ?></h2>
            </div>
        <?php endif; ?>

        <?php if ($has_body) : ?>
            <div class="page-content_certificate">
                <?php if ($text !== '') : ?>
                    <div class="page-content_certificate-text entry-content">
                        <?php echo apply_filters('the_content', $text); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($files)) : ?>
                    <div class="page-content_certificate-options">
                        <?php
                        foreach ($files as $row) {
                            $label = isset($row['label']) ? trim((string) $row['label']) : '';
                            $file = isset($row['file']) ? $row['file'] : null;
                            $url = is_array($file) && !empty($file['url']) ? (string) $file['url'] : '';
                            if ($url === '') {
                                continue;
                            }
                            if ($label === '') {
                                $label = __('Скачать', 'cpp-courses-theme');
                            }
                            ?>
                            <a class="button button--icon button--sm" href="<?php echo esc_url($url); ?>" download>
                                <div class="button_icon button_icon--download" aria-hidden="true"></div>
                                <span class="button_text"><?php echo esc_html($label); ?></span>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($img_url !== '') : ?>
            <div
                class="page-content_certificate-img"
                data-fancybox="<?php echo esc_attr($fancy_group); ?>"
                data-src="<?php echo esc_url($img_full); ?>"
                role="button"
                tabindex="0"
                aria-label="<?php echo esc_attr($img_alt); ?>"
            >
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" loading="lazy" />
            </div>
        <?php endif; ?>
    </div>
</div>
