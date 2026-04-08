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
$current_page = max(1, (int) get_query_var('paged'));
?>
<main class="main main--articles">
    <section class="section section--page-intro section--page-intro--alt">
        <div class="container">
            <div class="page-intro">
                <nav class="breadcrumbs" aria-label="Хлебные крошки">
                    <ol class="breadcrumbs_list">
                        <li class="breadcrumbs_item">
                            <a class="breadcrumbs_link" href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                        </li>
                        <li class="breadcrumbs_item">
                            <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                            <span class="breadcrumbs_current" aria-current="page">Статьи</span>
                        </li>
                    </ol>
                </nav>
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

                        <?php
                        $links = paginate_links(
                            array(
                                'type'      => 'array',
                                'current'   => $current_page,
                                'end_size'  => 0,
                                'mid_size'  => 1,
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
                                        if (strpos($link, 'dots') !== false) {
                                            echo '<span class="pagination_list_link dots">…</span>';
                                        } elseif (strpos($link, 'current') !== false) {
                                            $current_label = trim(wp_strip_all_tags($link));
                                            echo '<span class="pagination_list_link pagination_list_current" aria-current="page">' . esc_html($current_label) . '</span>';
                                        } else {
                                            $link = str_replace('page-numbers', 'pagination_list_link', $link);
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
      payload.append('per_page', '9');

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

