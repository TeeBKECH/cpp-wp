<?php
/**
 * SEO text block from site options (archives / services footers).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$html = cpp_services_archive_seo_content();
if ($html === '') {
    return;
}

$title = cpp_services_archive_seo_title();
?>
<section class="section section--seo-text">
    <div class="container">
        <div class="seo-text section_content">
            <?php if ($title !== '') : ?>
                <div class="section_header">
                    <h2 class="section_title"><?php echo esc_html($title); ?></h2>
                </div>
            <?php endif; ?>
            <div class="seo-text_content entry-content"><?php echo apply_filters('the_content', $html); ?></div>
        </div>
    </div>
</section>
