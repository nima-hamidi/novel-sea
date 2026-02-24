<?php
/**
 * Template Name: Authors
 * @package NovelTheme
 */
get_header();

$sort  = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'most_novels';
$paged = max(1, absint($_GET['page'] ?? 1));
$per   = 20;

// نویسندگانی که حداقل ۱ رمان منتشر دارند
$cache_key = "novel_authors_{$sort}_{$paged}";
$authors = get_transient($cache_key);

if ($authors === false) {
    global $wpdb;
    $author_ids = $wpdb->get_col(
        "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type='novel' AND post_status='publish'"
    );

    if (empty($author_ids)) {
        $authors = array('users' => array(), 'total' => 0);
    } else {
        $users_data = array();
        foreach ($author_ids as $aid) {
            $u = get_userdata($aid);
            if (!$u) continue;
            $users_data[] = array(
                'id'        => (int)$aid,
                'name'      => $u->display_name,
                'novels'    => absint(get_user_meta($aid, 'novel_count', true)),
                'followers' => absint(get_user_meta($aid, 'followers_count', true)),
                'avg_rating'=> (float)get_user_meta($aid, 'avg_rating', true),
                'joined'    => $u->user_registered,
            );
        }

        // مرتب‌سازی
        usort($users_data, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'most_followers': return $b['followers'] - $a['followers'];
                case 'best_rating':    return $b['avg_rating'] <=> $a['avg_rating'];
                case 'newest':         return strcmp($b['joined'], $a['joined']);
                default:               return $b['novels'] - $a['novels'];
            }
        });

        $total = count($users_data);
        $users_page = array_slice($users_data, ($paged - 1) * $per, $per);
        $authors = array('users' => $users_page, 'total' => $total, 'pages' => ceil($total / $per));
    }

    set_transient($cache_key, $authors, 6 * HOUR_IN_SECONDS);
}

$sorts = array(
    'most_novels'    => 'بیشترین رمان',
    'most_followers' => 'بیشترین فالوور',
    'best_rating'    => 'بالاترین امتیاز',
    'newest'         => 'جدیدترین',
);
?>

<div class="novel-authors-page">
    <div class="novel-container">
        <h1 class="novel-page-title">✍️ نویسندگان (<?php echo novel_format_number($authors['total']); ?>)</h1>

        <div class="novel-archive-toolbar">
            <div class="novel-sort-group">
                <?php foreach ($sorts as $sk => $sl): ?>
                    <a href="<?php echo esc_url(add_query_arg(array('sort'=>$sk,'page'=>1))); ?>"
                       class="novel-sort-btn <?php echo $sort === $sk ? 'active' : ''; ?>"><?php echo esc_html($sl); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($authors['users'])): ?>
        <div class="novel-grid novel-grid-4">
            <?php foreach ($authors['users'] as $au): ?>
            <div class="novel-author-card">
                <div class="novel-author-card-top">
                    <img src="<?php echo esc_url(novel_get_avatar($au['id'], 80)); ?>" width="80" height="80" alt="" class="novel-author-avatar" loading="lazy">
                    <?php
                    $online = novel_get_online_status($au['id']);
                    if ($online === 'online'): ?>
                        <span class="novel-online-dot novel-online-green"></span>
                    <?php endif; ?>
                </div>
                <h3><a href="<?php echo esc_url(get_author_posts_url($au['id'])); ?>"><?php echo esc_html($au['name']); ?></a></h3>
                <?php $badges = novel_get_user_badge($au['id']); foreach (array_slice($badges, 0, 1) as $b): ?>
                    <span class="novel-user-badge-sm" style="color:<?php echo esc_attr($b['color']); ?>"><?php echo $b['icon']; ?> <?php echo esc_html($b['label']); ?></span>
                <?php endforeach; ?>
                <div class="novel-author-card-stats">
                    <span>📖 <?php echo novel_to_persian($au['novels']); ?> رمان</span>
                    <span>❤ <?php echo novel_format_number($au['followers']); ?></span>
                    <?php if ($au['avg_rating'] > 0): ?>
                    <span>★ <?php echo novel_to_persian($au['avg_rating']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="novel-author-card-actions">
                    <a href="<?php echo esc_url(get_author_posts_url($au['id'])); ?>" class="novel-btn novel-btn-sm novel-btn-outline">👤 پروفایل</a>
                    <?php if (is_user_logged_in() && get_current_user_id() !== $au['id']): ?>
                    <button class="novel-btn novel-btn-sm novel-btn-primary novel-follow-user-btn" data-user="<?php echo $au['id']; ?>">❤ فالو</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (($authors['pages'] ?? 1) > 1): ?>
        <div class="novel-pagination">
            <?php for ($p = 1; $p <= min($authors['pages'], 10); $p++): ?>
                <a href="<?php echo esc_url(add_query_arg('page', $p)); ?>"
                   class="novel-page-num <?php echo $paged === $p ? 'active' : ''; ?>"><?php echo novel_to_persian($p); ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="novel-empty-state"><p>نویسنده‌ای یافت نشد.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>