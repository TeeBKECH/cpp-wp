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
$current_page = cpp_courses_get_current_archive_page();
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
                <div class="blog_grid" id="articles-grid" data-page="<?php echo esc_attr((string) $current_page); ?>" data-max-pages="<?php echo esc_attr((string) $wp_query->max_num_pages); ?>">
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
                                <button class="button button--filled button--lg" type="button" id="articles-load-more" data-nonce="<?php echo esc_attr(wp_create_nonce('cpp_articles_nonce')); ?>" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                                    <span class="button_text">загрузить еще</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php echo cpp_courses_render_archive_pagination($current_page, (int) $wp_query->max_num_pages); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const grid = document.getElementById('articles-grid');
  const button = document.getElementById('articles-load-more');
  if (!grid || !button) return;

  let loading = false;
  const maxPages = parseInt(grid.dataset.maxPages || '1', 10);

  button.addEventListener('click', async function () {
    if (loading) return;
    const currentPage = parseInt(grid.dataset.page || '1', 10);
    if (currentPage >= maxPages) {
      button.style.display = 'none';
      return;
    }

    loading = true;
    button.disabled = true;
    const nextPage = currentPage + 1;

    try {
      const payload = new URLSearchParams();
      payload.append('action', 'cpp_load_more_articles');
      payload.append('nonce', button.dataset.nonce || '');
      payload.append('page', String(nextPage));
      payload.append('per_page', '<?php echo esc_js((string) max(1, (int) get_option('posts_per_page', 10))); ?>');

      const response = await fetch(button.dataset.ajaxUrl || '', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: payload.toString()
      });
      const data = await response.json();
      if (!data || !data.success) return;

      if (data.data && data.data.html) {
        grid.insertAdjacentHTML('beforeend', data.data.html);
      }
      grid.dataset.page = String(nextPage);
      if (!data.data || !data.data.has_more) {
        button.style.display = 'none';
      }
    } catch (e) {
      // Keep silent in production layout.
    } finally {
      loading = false;
      button.disabled = false;
    }
  });
});
</script>
<?php
get_footer();

