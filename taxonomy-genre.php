<?php
/**
 * صفحه ژانر
 * @package NovelTheme
 */
get_header();
include NOVEL_DIR . '/novel-cards.php';

$term = get_queried_object();
$paged = max(1, get_query_var('paged', 1));
$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';

$args = array(
    'post_type'      => 'novel',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'paged'          => $paged,
    'tax_query'      => array(
        array('taxonomy' => 'genre', 'field' => 'term_id', 'terms' => $term->term_id),
    ),
);

switch ($current_sort) {
    case 'popular':  $args['meta_key'] = 'total_views'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
    case 'rating':   $args['meta_key'] = 'novel_rating_sum'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
    default:         $args['orderby'] = 'date'; $args['order'] = 'DESC';
}

$query = new WP_Query($args);
?>

<div class="novel-archive-page">
    <div class="novel-container">
        <h1 class="novel-page-title">🏷️ <?php echo esc_html($term->name); ?> (<?php echo novel_format_number($term->count); ?> رمان)</h1>
        <?php if ($term->description): ?>
            <p class="novel-page-desc"><?php echo esc_html($term->description); ?></p>
        <?php endif; ?>

        <div class="novel-archive-toolbar">
            <div class="novel-sort-group">
                <a href="<?php echo esc_url(add_query_arg('sort', 'newest')); ?>" class="novel-sort-btn <?php echo $current_sort === 'newest' ? 'active' : ''; ?>">جدیدترین</a>
                <a href="<?php echo esc_url(add_query_arg('sort', 'popular')); ?>" class="novel-sort-btn <?php echo $current_sort === 'popular' ? 'active' : ''; ?>">محبوب‌ترین</a>
                <a href="<?php echo esc_url(add_query_arg('sort', 'rating')); ?>" class="novel-sort-btn <?php echo $current_sort === 'rating' ? 'active' : ''; ?>">بالاترین امتیاز</a>
            </div>
        </div>

        <?php if ($query->have_posts()): ?>
        <div class="novel-grid novel-grid-4">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php novel_render_card(get_the_ID(), 'grid'); ?>
            <?php endwhile; ?>
        </div>

        <?php if ($query->max_num_pages > 1): ?>
        <div class="novel-pagination">
            <?php echo paginate_links(array('total' => $query->max_num_pages, 'current' => $paged, 'prev_text' => '←', 'next_text' => '→')); ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="novel-empty-state"><p>رمانی در این ژانر یافت نشد.</p></div>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</div>

<?php get_footer(); ?>