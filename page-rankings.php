<?php
/**
 * Template Name: Rankings
 * @package NovelTheme
 */
get_header();
include NOVEL_DIR . '/novel-cards.php';

$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'weekly';
$type   = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'popular';
$paged  = max(1, absint($_GET['page'] ?? 1));

$meta_key_map = array(
    'popular'   => array('weekly'=>'weekly_views','monthly'=>'monthly_views','daily'=>'daily_views','all'=>'total_views'),
    'rating'    => array('weekly'=>'novel_rating_sum','monthly'=>'novel_rating_sum','daily'=>'novel_rating_sum','all'=>'novel_rating_sum'),
    'followers' => array('weekly'=>'followers_count','monthly'=>'followers_count','daily'=>'followers_count','all'=>'followers_count'),
    'comments'  => array('weekly'=>'comments_count','monthly'=>'comments_count','daily'=>'comments_count','all'=>'comments_count'),
    'newest'    => array(),
    'bookmarks' => array('weekly'=>'bookmarks_count','monthly'=>'bookmarks_count','daily'=>'bookmarks_count','all'=>'bookmarks_count'),
    'views'     => array('weekly'=>'weekly_views','monthly'=>'monthly_views','daily'=>'daily_views','all'=>'total_views'),
    'updates'   => array(),
);

$cache_key = "novel_rankings_{$type}_{$period}_{$paged}";
$results = get_transient($cache_key);
if ($results === false) {
    $args = array(
        'post_type' => 'novel', 'post_status' => 'publish',
        'posts_per_page' => 20, 'offset' => ($paged - 1) * 20,
    );
    if ($type === 'newest' || $type === 'updates') {
        $args['orderby'] = 'date'; $args['order'] = 'DESC';
    } else {
        $mk = $meta_key_map[$type][$period] ?? 'total_views';
        $args['meta_key'] = $mk;
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    }
    $q = new WP_Query($args);
    $results = array('posts' => $q->posts, 'total' => $q->found_posts, 'pages' => $q->max_num_pages);
    set_transient($cache_key, $results, HOUR_IN_SECONDS);
}

$periods = array('daily'=>'روزانه','weekly'=>'هفتگی','monthly'=>'ماهانه','all'=>'کل');
$types = array(
    'popular'=>'🔥 محبوب‌ترین','rating'=>'⭐ بالاترین امتیاز','followers'=>'❤ بیشترین دنبال‌کننده',
    'comments'=>'💬 بیشترین دیدگاه','newest'=>'📅 جدیدترین','views'=>'👁 بیشترین بازدید',
    'bookmarks'=>'📚 بیشترین بوکمارک',
);
?>

<div class="novel-rankings-page">
    <div class="novel-container">
        <h1 class="novel-page-title">🏆 رتبه‌بندی رمان‌ها</h1>

        <div class="novel-rank-periods">
            <?php foreach ($periods as $pk => $pl): ?>
                <a href="<?php echo esc_url(add_query_arg(array('period'=>$pk,'type'=>$type,'page'=>1))); ?>"
                   class="novel-filter-btn <?php echo $period === $pk ? 'active' : ''; ?>"><?php echo esc_html($pl); ?></a>
            <?php endforeach; ?>
        </div>

        <div class="novel-rank-types">
            <?php foreach ($types as $tk => $tl): ?>
                <a href="<?php echo esc_url(add_query_arg(array('type'=>$tk,'period'=>$period,'page'=>1))); ?>"
                   class="novel-sort-btn <?php echo $type === $tk ? 'active' : ''; ?>"><?php echo $tl; ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($results['posts'])): ?>
        <div class="novel-rank-list">
            <?php
            $rank = ($paged - 1) * 20;
            foreach ($results['posts'] as $rp):
                $rank++;
                $rp_id = is_object($rp) ? $rp->ID : $rp;
                $medal = '';
                if ($rank === 1) $medal = '🥇';
                elseif ($rank === 2) $medal = '🥈';
                elseif ($rank === 3) $medal = '🥉';

                $rs = (float)get_post_meta($rp_id, 'novel_rating_sum', true);
                $rc = (int)get_post_meta($rp_id, 'novel_rating_count', true);
                $avg = $rc > 0 ? round($rs / $rc, 1) : 0;
                $rv = absint(get_post_meta($rp_id, 'total_views', true));
            ?>
            <a href="<?php echo esc_url(get_permalink($rp_id)); ?>"
               class="novel-rank-item <?php echo $rank <= 3 ? 'novel-rank-top' : ''; ?>">
                <span class="novel-rank-num"><?php echo $medal ?: novel_to_persian($rank); ?></span>
                <img src="<?php echo esc_url(get_the_post_thumbnail_url($rp_id, 'novel-thumb') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>"
                     width="50" height="70" alt="" loading="lazy" class="novel-rank-cover">
                <div class="novel-rank-info">
                    <strong><?php echo esc_html(get_the_title($rp_id)); ?></strong>
                    <span><?php echo esc_html(get_the_author_meta('display_name', get_post_field('post_author', $rp_id))); ?></span>
                </div>
                <div class="novel-rank-stats">
                    <?php if ($avg > 0): ?><span>★<?php echo novel_to_persian($avg); ?></span><?php endif; ?>
                    <span>👁<?php echo novel_format_number($rv); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($results['pages'] > 1): ?>
        <div class="novel-pagination">
            <?php for ($p = 1; $p <= min($results['pages'], 10); $p++): ?>
                <a href="<?php echo esc_url(add_query_arg('page', $p)); ?>"
                   class="novel-page-num <?php echo $paged === $p ? 'active' : ''; ?>"><?php echo novel_to_persian($p); ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="novel-empty-state"><p>نتیجه‌ای یافت نشد.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>