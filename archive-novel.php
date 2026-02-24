<?php
/**
 * آرشیو رمان‌ها
 * @package NovelTheme
 */
get_header();

$current_type   = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$current_sort   = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
$current_view   = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'grid';
$paged          = max(1, get_query_var('paged', 1));

$args = array(
    'post_type'      => 'novel',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'paged'          => $paged,
);

// فیلتر نوع
if ($current_type) {
    $args['meta_query'][] = array('key' => 'novel_type', 'value' => $current_type);
}

// فیلتر وضعیت
if ($current_status) {
    $args['tax_query'][] = array(
        'taxonomy' => 'novel_status',
        'field'    => 'slug',
        'terms'    => $current_status,
    );
}

// مرتب‌سازی
switch ($current_sort) {
    case 'popular':
        $args['meta_key'] = 'total_views';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    case 'rating':
        $args['meta_key'] = 'novel_rating_sum';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    case 'followers':
        $args['meta_key'] = 'followers_count';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    case 'chapters':
        $args['meta_key'] = 'chapters_count_cache';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    default:
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
}

$query = new WP_Query($args);
$total = $query->found_posts;

include NOVEL_DIR . '/novel-cards.php';
?>

<div class="novel-archive-page">
    <div class="novel-container">

        <h1 class="novel-page-title">📚 همه رمان‌ها (<?php echo novel_format_number($total); ?>)</h1>

        <!-- فیلترها -->
        <div class="novel-archive-filters">
            <div class="novel-filter-group">
                <a href="<?php echo esc_url(remove_query_arg('type')); ?>" class="novel-filter-btn <?php echo !$current_type ? 'active' : ''; ?>">همه</a>
                <a href="<?php echo esc_url(add_query_arg('type', 'ln')); ?>" class="novel-filter-btn <?php echo $current_type === 'ln' ? 'active' : ''; ?>">LN</a>
                <a href="<?php echo esc_url(add_query_arg('type', 'wn')); ?>" class="novel-filter-btn <?php echo $current_type === 'wn' ? 'active' : ''; ?>">WN</a>
            </div>

            <div class="novel-filter-group">
                <?php
                $statuses = get_terms(array('taxonomy' => 'novel_status', 'hide_empty' => true));
                ?>
                <a href="<?php echo esc_url(remove_query_arg('status')); ?>" class="novel-filter-btn <?php echo !$current_status ? 'active' : ''; ?>">همه وضعیت</a>
                <?php foreach ($statuses as $st): ?>
                    <a href="<?php echo esc_url(add_query_arg('status', $st->slug)); ?>" class="novel-filter-btn <?php echo $current_status === $st->slug ? 'active' : ''; ?>"><?php echo esc_html($st->name); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- مرتب‌سازی + نمایش -->
        <div class="novel-archive-toolbar">
            <div class="novel-sort-group">
                <?php
                $sorts = array(
                    'newest'    => 'جدیدترین',
                    'popular'   => 'محبوب‌ترین',
                    'rating'    => 'بالاترین امتیاز',
                    'followers' => 'بیشترین دنبال‌کننده',
                    'chapters'  => 'بیشترین قسمت',
                );
                foreach ($sorts as $sk => $sl): ?>
                    <a href="<?php echo esc_url(add_query_arg('sort', $sk)); ?>" class="novel-sort-btn <?php echo $current_sort === $sk ? 'active' : ''; ?>"><?php echo esc_html($sl); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="novel-view-toggle">
                <a href="<?php echo esc_url(add_query_arg('view', 'grid')); ?>" class="novel-view-btn <?php echo $current_view === 'grid' ? 'active' : ''; ?>" aria-label="نمایش گرید">▦</a>
                <a href="<?php echo esc_url(add_query_arg('view', 'list')); ?>" class="novel-view-btn <?php echo $current_view === 'list' ? 'active' : ''; ?>" aria-label="نمایش لیست">≡</a>
            </div>
        </div>

        <!-- لیست رمان‌ها -->
        <?php if ($query->have_posts()): ?>
            <div class="novel-archive-grid <?php echo $current_view === 'list' ? 'novel-archive-list-view' : ''; ?>">
                <?php while ($query->have_posts()): $query->the_post(); ?>
                    <?php novel_render_card(get_the_ID(), $current_view === 'list' ? 'list' : 'grid'); ?>
                <?php endwhile; ?>
            </div>

            <!-- صفحه‌بندی -->
            <?php if ($query->max_num_pages > 1): ?>
            <div class="novel-pagination">
                <?php
                echo paginate_links(array(
                    'total'     => $query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '←',
                    'next_text' => '→',
                    'mid_size'  => 2,
                ));
                ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="novel-empty-state">
                <p>رمانی یافت نشد 😕</p>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</div>

<?php get_footer(); ?>