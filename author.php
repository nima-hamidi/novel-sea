<?php
/**
 * پروفایل عمومی نویسنده
 * @package NovelTheme
 */
get_header();

$author_id   = get_queried_object_id();
$author      = get_userdata($author_id);
if (!$author) { include '404.php'; return; }

$name        = $author->display_name;
$bio         = get_user_meta($author_id, 'description', true);
$telegram    = get_user_meta($author_id, 'novel_telegram', true);
$instagram   = get_user_meta($author_id, 'novel_instagram', true);
$joined      = $author->user_registered;
$online      = novel_get_online_status($author_id);
$badges      = novel_get_user_badge($author_id);
$followers   = absint(get_user_meta($author_id, 'followers_count', true));
$following   = absint(get_user_meta($author_id, 'following_count', true));
$comment_tot = absint(get_user_meta($author_id, 'novel_comment_total', true));

global $wpdb;
$novel_count = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author=%d AND post_type='novel' AND post_status='publish'", $author_id
));
$chapter_count = (int)$wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author=%d AND post_type='chapter' AND post_status='publish'", $author_id
));
$total_views = 0;
$author_novels = get_posts(array('post_type'=>'novel','author'=>$author_id,'posts_per_page'=>-1,'post_status'=>'publish','fields'=>'ids'));
foreach ($author_novels as $an_id) {
    $total_views += absint(get_post_meta($an_id, 'total_views', true));
}

// فالو شده؟
$is_followed = false;
if (is_user_logged_in() && get_current_user_id() !== $author_id) {
    $is_followed = (bool)$wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}user_follows WHERE follower_id=%d AND following_id=%d",
        get_current_user_id(), $author_id
    ));
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'novels';

include NOVEL_DIR . '/novel-cards.php';
?>

<div class="novel-author-profile">
    <div class="novel-container">

        <!-- هدر -->
        <div class="novel-author-header">
            <div class="novel-author-avatar-wrap">
                <img src="<?php echo esc_url(novel_get_avatar($author_id, 100)); ?>" width="100" height="100" alt="" class="novel-author-big-avatar">
                <?php if ($online === 'online'): ?>
                    <span class="novel-online-dot novel-online-green novel-online-lg"></span>
                <?php endif; ?>
            </div>

            <div class="novel-author-header-info">
                <h1><?php echo esc_html($name); ?></h1>
                <div class="novel-author-badges">
                    <?php foreach ($badges as $b): ?>
                        <span class="novel-user-badge" style="background:<?php echo esc_attr($b['color']); ?>15;color:<?php echo esc_attr($b['color']); ?>"><?php echo $b['icon']; ?> <?php echo esc_html($b['label']); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if ($bio): ?><p class="novel-author-bio"><?php echo esc_html($bio); ?></p><?php endif; ?>
                <div class="novel-author-meta-row">
                    <span>📅 عضو از: <?php echo novel_jalali_date('F Y', strtotime($joined)); ?></span>
                    <?php if ($telegram): ?><a href="<?php echo esc_url($telegram); ?>" target="_blank">📱 تلگرام</a><?php endif; ?>
                    <?php if ($instagram): ?><a href="<?php echo esc_url($instagram); ?>" target="_blank">📷 اینستاگرام</a><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- آمار -->
        <div class="novel-author-stats-bar">
            <div class="novel-stat-box"><strong><?php echo novel_to_persian($novel_count); ?></strong><small>📖 رمان</small></div>
            <div class="novel-stat-box"><strong><?php echo novel_to_persian($chapter_count); ?></strong><small>📄 قسمت</small></div>
            <div class="novel-stat-box"><strong><?php echo novel_format_number($total_views); ?></strong><small>👁 بازدید</small></div>
            <div class="novel-stat-box"><strong><?php echo novel_format_number($followers); ?></strong><small>❤ فالوور</small></div>
            <div class="novel-stat-box"><strong><?php echo novel_to_persian($comment_tot); ?></strong><small>💬 دیدگاه</small></div>
        </div>

        <!-- دکمه‌ها -->
        <div class="novel-author-actions">
            <?php if (is_user_logged_in() && get_current_user_id() !== $author_id): ?>
            <button class="novel-btn novel-btn-primary novel-follow-user-btn"
                    data-user="<?php echo $author_id; ?>" data-followed="<?php echo $is_followed ? '1' : '0'; ?>">
                <?php echo $is_followed ? '💔 لغو فالو' : '❤ دنبال کردن (' . novel_to_persian($followers) . ')'; ?>
            </button>
            <button class="novel-btn novel-btn-outline novel-report-btn" data-type="user" data-id="<?php echo $author_id; ?>">🚩 گزارش</button>
            <?php endif; ?>
        </div>

        <!-- تب‌ها -->
        <div class="novel-author-tabs">
            <a href="<?php echo esc_url(add_query_arg('tab', 'novels')); ?>" class="novel-tab-btn <?php echo $active_tab === 'novels' ? 'active' : ''; ?>">📖 رمان‌ها</a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'comments')); ?>" class="novel-tab-btn <?php echo $active_tab === 'comments' ? 'active' : ''; ?>">💬 دیدگاه‌ها</a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'followers')); ?>" class="novel-tab-btn <?php echo $active_tab === 'followers' ? 'active' : ''; ?>">❤ فالوورها</a>
        </div>

        <!-- محتوای تب -->
        <div class="novel-author-tab-content">

            <?php if ($active_tab === 'novels'): ?>
                <?php if (!empty($author_novels)): ?>
                <div class="novel-grid novel-grid-4">
                    <?php foreach ($author_novels as $an): ?>
                        <?php novel_render_card($an, 'grid'); ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="novel-empty-state"><p>هنوز رمانی منتشر نکرده.</p></div>
                <?php endif; ?>

            <?php elseif ($active_tab === 'comments'): ?>
                <?php
                $author_comments = get_comments(array('user_id' => $author_id, 'number' => 15, 'status' => 'approve', 'orderby' => 'comment_date', 'order' => 'DESC'));
                if (!empty($author_comments)): ?>
                <div class="novel-my-comments-list">
                    <?php foreach ($author_comments as $ac): ?>
                    <div class="novel-my-comment-item">
                        <p><?php echo esc_html(wp_trim_words($ac->comment_content, 15, '...')); ?></p>
                        <div class="novel-my-comment-meta">
                            <a href="<?php echo esc_url(get_permalink($ac->comment_post_ID)); ?>"><?php echo esc_html(get_the_title($ac->comment_post_ID)); ?></a>
                            <span><?php echo novel_time_ago($ac->comment_date); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="novel-empty-state"><p>دیدگاهی ندارد.</p></div>
                <?php endif; ?>

            <?php elseif ($active_tab === 'followers'): ?>
                <?php
                $f_list = $wpdb->get_results($wpdb->prepare(
                    "SELECT follower_id FROM {$wpdb->prefix}user_follows WHERE following_id=%d ORDER BY created_at DESC LIMIT 20", $author_id
                ));
                if (!empty($f_list)): ?>
                <div class="novel-users-list">
                    <?php foreach ($f_list as $fl):
                        $fu = get_userdata($fl->follower_id);
                        if (!$fu) continue;
                    ?>
                    <div class="novel-user-list-item">
                        <img src="<?php echo esc_url(novel_get_avatar($fl->follower_id, 40)); ?>" width="40" height="40" alt="">
                        <a href="<?php echo esc_url(get_author_posts_url($fl->follower_id)); ?>"><?php echo esc_html($fu->display_name); ?></a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="novel-empty-state"><p>هنوز فالووری ندارد.</p></div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>