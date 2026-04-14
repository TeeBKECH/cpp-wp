<?php
/**
 * FAQ accordion for current service (SCF fields on post).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$faq_title = function_exists('get_field') ? (string) get_field('cpp_svc_faq_title') : '';
$faq_subtitle = function_exists('get_field') ? (string) get_field('cpp_svc_faq_subtitle') : '';
$faq_items = function_exists('get_field') ? get_field('cpp_svc_faq_items') : null;

if (empty($faq_items) || !is_array($faq_items)) {
    return;
}
if ($faq_title === '') {
    $faq_title = __('Часто задаваемые вопросы', 'cpp-courses-theme');
}
?>
<section class="section section--faq">
    <div class="container">
        <div class="faq section_content">
            <div class="section_header">
                <h2 class="section_title"><?php echo esc_html($faq_title); ?></h2>
                <?php if ($faq_subtitle !== '') : ?>
                    <p class="section_subtitle"><?php echo esc_html($faq_subtitle); ?></p>
                <?php endif; ?>
            </div>
            <div class="faq_list">
                <?php
                $fi = 0;
                foreach ($faq_items as $fitem) :
                    $q = isset($fitem['question']) ? (string) $fitem['question'] : '';
                    $a = isset($fitem['answer']) ? $fitem['answer'] : '';
                    if ($q === '') {
                        continue;
                    }
                    $open = (0 === $fi) ? ' open' : '';
                    $fi++;
                    ?>
                    <div class="accordion_item<?php echo esc_attr($open); ?>" data-accordion="data-accordion">
                        <div class="accordion_item_head" data-accordion-trigger="data-accordion-trigger">
                            <div class="accordion_item_title"><?php echo esc_html($q); ?></div>
                            <div class="accordion_item_icon"></div>
                        </div>
                        <div class="accordion_item_body">
                            <div class="accordion_item_content">
                                <?php echo wp_kses_post($a); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
