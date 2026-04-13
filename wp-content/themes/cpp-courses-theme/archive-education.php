<?php
/**
 * Archive template for education CPT (registered in SCF).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$archive_url = get_post_type_archive_link('education');
if (!$archive_url) {
    $archive_url = home_url('/');
}

$filter_slug = cpp_courses_education_get_filter_slug();
$all_posts = cpp_courses_education_get_archive_posts($filter_slug);

if ($filter_slug !== '') {
    $filter_term = get_term_by('slug', $filter_slug, 'education_cats');
    $ordered = array();
    if ($filter_term instanceof WP_Term && !empty($all_posts)) {
        $sorted = cpp_courses_education_sort_posts_for_archive($all_posts);
        $ordered[] = array(
            'term' => $filter_term,
            'posts' => $sorted,
        );
    }
} else {
    $buckets = cpp_courses_education_group_posts_by_category($all_posts);
    $ordered = cpp_courses_education_ordered_buckets($buckets);
}

$intro = '';
if (function_exists('get_field')) {
    $intro_raw = get_field('cpp_edu_archive_intro', 'option');
    $intro = is_string($intro_raw) ? trim($intro_raw) : '';
}

$terms_for_tabs = get_terms(
    array(
        'taxonomy' => 'education_cats',
        'hide_empty' => false,
    )
);
?>
<main class="main main--edu-info">
    <section class="section section--page-intro">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php post_type_archive_title(); ?></h1>
                <?php if ($intro !== '') : ?>
                    <p class="page-intro_desc"><?php echo nl2br(esc_html($intro)); ?></p>
                <?php endif; ?>
                <?php if (!is_wp_error($terms_for_tabs) && !empty($terms_for_tabs)) : ?>
                    <div class="page-intro_tabs">
                        <?php
                        $all_url = remove_query_arg('filter', $archive_url);
                        $all_active = ($filter_slug === '');
                        ?>
                        <a class="button button--<?php echo $all_active ? 'filled' : 'outline'; ?> button--sm" href="<?php echo esc_url($all_url); ?>"<?php echo $all_active ? ' aria-current="page"' : ''; ?>>
                            <span class="button_text"><?php esc_html_e('Все', 'cpp-courses-theme'); ?></span>
                        </a>
                        <?php foreach ($terms_for_tabs as $tab_term) : ?>
                            <?php
                            if (!$tab_term instanceof WP_Term) {
                                continue;
                            }
                            $tab_url = add_query_arg('filter', $tab_term->slug, $archive_url);
                            $tab_active = ($filter_slug === $tab_term->slug);
                            ?>
                            <a class="button button--<?php echo $tab_active ? 'filled' : 'outline'; ?> button--sm" href="<?php echo esc_url($tab_url); ?>"<?php echo $tab_active ? ' aria-current="page"' : ''; ?>>
                                <span class="button_text"><?php echo esc_html($tab_term->name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (empty($ordered)) : ?>
        <section class="section section--orders">
            <div class="container">
                <p class="page-intro_desc"><?php esc_html_e('Записей пока нет.', 'cpp-courses-theme'); ?></p>
            </div>
        </section>
    <?php else : ?>
        <?php foreach ($ordered as $bucket) : ?>
            <?php
            $term = $bucket['term'] ?? null;
            $posts = $bucket['posts'] ?? array();
            if (empty($posts)) {
                continue;
            }
            $heading = ($term instanceof WP_Term) ? $term->name : __('Без категории', 'cpp-courses-theme');
            $anchor = ($term instanceof WP_Term) ? $term->slug : 'bez-kategorii';
            $fancy_slug = ($term instanceof WP_Term) ? $term->slug : 'other';

            $list_posts = array();
            $gallery_posts = array();
            foreach ($posts as $p) {
                if (!$p instanceof WP_Post) {
                    continue;
                }
                $t = function_exists('get_field') ? (string) get_field('cpp_edu_display_type', $p->ID) : 'link';
                if ($t === 'gallery') {
                    $gallery_posts[] = $p;
                } else {
                    $list_posts[] = $p;
                }
            }
            ?>
            <section class="section section--orders" id="<?php echo esc_attr('edu-' . $anchor); ?>">
                <div class="container">
                    <?php if (!empty($list_posts)) : ?>
                        <div class="orders section_content">
                            <div class="section_header">
                                <h2 class="section_title"><?php echo esc_html($heading); ?></h2>
                            </div>
                            <div class="orders_list">
                                <?php
                                foreach ($list_posts as $p) {
                                    cpp_courses_education_render_archive_card($p, $fancy_slug);
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($gallery_posts)) : ?>
                        <div class="orders section_content orders--photos">
                            <?php if (empty($list_posts)) : ?>
                                <div class="section_header">
                                    <h2 class="section_title"><?php echo esc_html($heading); ?></h2>
                                </div>
                            <?php endif; ?>
                            <div class="orders_list orders_list--photos">
                                <?php
                                foreach ($gallery_posts as $p) {
                                    cpp_courses_education_render_archive_card($p, $fancy_slug);
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
