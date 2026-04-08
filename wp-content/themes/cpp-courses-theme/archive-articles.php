<?php
/**
 * Archive template for articles CPT.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="main main--articles">
    <section class="section section--page-intro section--page-intro--alt">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php post_type_archive_title(); ?></h1>
            </div>
        </div>
    </section>

    <section class="section section--blog">
        <div class="container">
            <div class="blog section_content">
                <div class="blog_grid" id="articles-grid" data-page="1" data-max-pages="<?php echo esc_attr((string) $wp_query->max_num_pages); ?>">
                    <?php
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            get_template_part('template-parts/post-card', 'article');
                        }
                    }
                    ?>
                </div>
                <div class="blog_footer">
                    <div class="pagination">
                        <?php if ((int) $wp_query->max_num_pages > 1) : ?>
                            <div class="pagination_more">
                                <button class="button button--filled button--lg" type="button" id="articles-load-more">
                                    <span class="button_text">загрузить еще</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php
                        $links = paginate_links(
                            array(
                                'type'      => 'array',
                                'prev_text' => '<span class="pagination_list_icon pagination_list_icon--prev"></span>',
                                'next_text' => '<span class="pagination_list_icon pagination_list_icon--next"></span>',
                            )
                        );
                        if (!empty($links)) :
                            ?>
                            <ul class="pagination_list">
                                <?php foreach ($links as $link) : ?>
                                    <li class="pagination_list_item">
                                        <?php
                                        $link = str_replace('page-numbers', 'pagination_list_link', $link);
                                        $link = str_replace('current', 'pagination_list_current', $link);
                                        echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();

