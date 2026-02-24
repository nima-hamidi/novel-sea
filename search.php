<?php
/**
 * نتایج جستجو
 * @package NovelTheme
 */
get_header();
include NOVEL_DIR . '/novel-cards.php';

$q = get_search_query();
$paged = max(1, get_query_var('paged', 1));
?>

<div class="novel-archive-page">
    <div class="novel-container">
        <h1 class="novel-page-title">🔍 نتایج جستجو برای «<?php echo esc_html($q); ?>»</h1>

        <?php if (have_posts()): ?>
        <div class="novel-grid novel-grid-4">
            <?php while (have_posts()): the_post();
                if (get_post_type() === 'novel') {
                    novel_render_card(get_the_ID(), 'grid');
                }
            endwhile; ?>
        </div>

        <?php if ($wp_query->max_num_pages > 1): ?>
        <div class="novel-pagination">
            <?php echo paginate_links(array('total' => $wp_query->max_num_pages, 'current' => $paged, 'prev_text' => '←', 'next_text' => '→')); ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="novel-empty-state">
            <p>نتیجه‌ای یافت نشد 😕</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('novel')); ?>" class="novel-btn novel-btn-primary">مشاهده همه رمان‌ها</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>