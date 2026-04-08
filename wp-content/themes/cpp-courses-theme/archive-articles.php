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
$base_url = get_post_type_archive_link('articles');
$posts_per_page = max(1, (int) get_option('posts_per_page', 10));
// Offset should start AFTER the posts already shown on the current page.
$initial_offset = $current_page * $posts_per_page;
$pagination_links = paginate_links(
    array(
        'base'      => trailingslashit((string) $base_url) . '%_%',
        'format'    => '?page=%#%',
        'type'      => 'array',
        'current'   => $current_page,
        'total'     => (int) $wp_query->max_num_pages,
        // Keep pagination compact; final list is post-processed below to max 7 items.
        'mid_size'  => 0,
        'end_size'  => 1,
        'prev_text' => '<span class="pagination_list_icon pagination_list_icon--prev"></span>',
        'next_text' => '<span class="pagination_list_icon pagination_list_icon--next"></span>',
    )
);
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
                    $rendered_ids = array();
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            $rendered_ids[] = (int) get_the_ID();
                            get_template_part('template-parts/post-card', 'article');
                        }
                    }
                    ?>
                </div>
                <div class="blog_footer">
                    <div class="pagination">
                        <?php if ((int) $wp_query->max_num_pages > 1) : ?>
                            <div class="pagination_more">
                                <button
                                    class="button button--filled button--lg"
                                    type="button"
                                    id="articles-load-more"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('cpp_articles_nonce')); ?>"
                                    data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                                    data-exclude-ids="<?php echo esc_attr(implode(',', array_unique(array_filter($rendered_ids)))); ?>"
                                    data-offset="<?php echo esc_attr((string) $initial_offset); ?>"
                                    data-post-type="articles"
                                >
                                    <span class="button_text">загрузить еще</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($pagination_links)) : ?>
                            <ul class="pagination_list">
                                <?php foreach ($pagination_links as $link) : ?>
                                    <li class="pagination_list_item">
                                        <?php
                                        if (strpos($link, 'dots') !== false) {
                                            echo '<span class="pagination_list_link dots">…</span>';
                                        } elseif (strpos($link, 'current') !== false) {
                                            $label = trim(wp_strip_all_tags($link));
                                            echo '<span class="pagination_list_link pagination_list_current" aria-current="page">' . esc_html($label) . '</span>';
                                        } else {
                                            $link = str_replace('page-numbers', 'pagination_list_link', $link);
                                            $link = str_replace('prev pagination_list_link', 'pagination_list_link pagination_list_link--prev', $link);
                                            $link = str_replace('next pagination_list_link', 'pagination_list_link pagination_list_link--next', $link);
                                            echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        }
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  const grid = document.getElementById('articles-grid');
  const button = document.getElementById('articles-load-more');
  if (!grid || !button) return;

  let loading = false;
  const postType = button.dataset.postType || 'articles';

  button.addEventListener('click', async function () {
    if (loading) return;
    loading = true;
    button.disabled = true;

    try {
      const payload = new URLSearchParams();
      payload.append('action', 'cpp_load_more_articles');
      payload.append('nonce', button.dataset.nonce || '');
      payload.append('post_type', postType);
      payload.append('offset', button.dataset.offset || '0');
      payload.append('exclude_ids', button.dataset.excludeIds || '');

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
      if (data.data && typeof data.data.next_offset !== 'undefined') {
        button.dataset.offset = String(data.data.next_offset);
      }
      if (data.data && data.data.appended_exclude_ids) {
        button.dataset.excludeIds = String(data.data.appended_exclude_ids);
      }
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

