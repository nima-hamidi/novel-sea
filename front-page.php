<?php
/**
 * صفحه اصلی
 * @package NovelTheme
 */
get_header();
include NOVEL_DIR . '/novel-cards.php';

$sections = novel_get_option('novel_homepage_sections', array(
    'hero', 'continue', 'challenge', 'trending', 'quiz',
    'daily', 'updates', 'popular', 'newest', 'authors', 'comments', 'poll', 'mood'
));
$is_logged = is_user_logged_in();
$uid = get_current_user_id();
?>

<div class="novel-home">

<?php foreach ($sections as $section): ?>

    <?php // ═══ Hero Slider ═══
    if ($section === 'hero'):
        $featured = get_posts(array(
            'post_type' => 'novel', 'posts_per_page' => 5, 'post_status' => 'publish',
            'meta_key' => '_sticky', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC',
        ));
        if (empty($featured)) {
            $featured = get_posts(array('post_type' => 'novel', 'posts_per_page' => 5, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'));
        }
        if (!empty($featured)):
    ?>
    <section class="novel-hero-section">
        <div class="novel-hero-slider" id="heroSlider">
            <?php foreach ($featured as $fp): ?>
            <div class="novel-hero-slide">
                <div class="novel-hero-bg" style="background-image:url('<?php echo esc_url(get_the_post_thumbnail_url($fp->ID, 'novel-banner') ?: ''); ?>')"></div>
                <div class="novel-container novel-hero-content">
                    <h2><?php echo esc_html(get_the_title($fp->ID)); ?></h2>
                    <p><?php echo esc_html(wp_trim_words(get_the_excerpt($fp->ID), 20, '...')); ?></p>
                    <a href="<?php echo esc_url(get_permalink($fp->ID)); ?>" class="novel-btn novel-btn-primary novel-btn-lg">شروع خواندن ▶</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ ادامه مطالعه ═══
    if ($section === 'continue' && $is_logged):
        global $wpdb;
        $continue = $wpdb->get_results($wpdb->prepare(
            "SELECT novel_id, progress FROM {$wpdb->prefix}user_library
             WHERE user_id = %d AND list_type = 'reading'
             ORDER BY updated_at DESC LIMIT 5", $uid
        ));
        if (!empty($continue)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <h2 class="novel-section-title">▶ ادامه مطالعه</h2>
            <div class="novel-scroll-row">
                <?php foreach ($continue as $c):
                    $nid = (int)$c->novel_id;
                    $progress = (int)$c->progress;
                    $ch_total = absint(get_post_meta($nid, 'chapters_count_cache', true));
                    $percent = $ch_total > 0 ? round(($progress / $ch_total) * 100) : 0;
                    $next_num = $progress + 1;
                    $next_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT p.ID FROM {$wpdb->posts} p
                         INNER JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='chapter_novel_id' AND pm.meta_value=%d
                         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID=pm2.post_id AND pm2.meta_key='chapter_number' AND pm2.meta_value=%d
                         WHERE p.post_status='publish' LIMIT 1", $nid, $next_num
                    ));
                ?>
                <div class="novel-continue-card">
                    <a href="<?php echo esc_url(get_permalink($nid)); ?>" class="novel-continue-cover">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($nid, 'novel-card-small') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>"
                             width="100" height="140" alt="" loading="lazy">
                    </a>
                    <div class="novel-continue-info">
                        <h4><a href="<?php echo esc_url(get_permalink($nid)); ?>"><?php echo esc_html(get_the_title($nid)); ?></a></h4>
                        <div class="novel-progress-bar"><div class="novel-progress-fill" style="width:<?php echo (int)$percent; ?>%"></div></div>
                        <small><?php echo novel_to_persian($progress); ?> از <?php echo novel_to_persian($ch_total); ?> (<?php echo novel_to_persian($percent); ?>%)</small>
                        <?php if ($next_id): ?>
                            <a href="<?php echo esc_url(get_permalink($next_id)); ?>" class="novel-btn novel-btn-sm novel-btn-primary">▶ قسمت <?php echo novel_to_persian($next_num); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ ترند ═══
    if ($section === 'trending'):
        $trending_cache = get_transient('novel_trending');
        if (!$trending_cache) {
            $trending_cache = get_posts(array(
                'post_type' => 'novel', 'posts_per_page' => 10, 'post_status' => 'publish',
                'meta_key' => 'weekly_views', 'orderby' => 'meta_value_num', 'order' => 'DESC',
            ));
            set_transient('novel_trending', $trending_cache, 3 * HOUR_IN_SECONDS);
        }
        if (!empty($trending_cache)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <div class="novel-section-header">
                <h2 class="novel-section-title">🔥 در حال ترند</h2>
                <a href="<?php echo esc_url(add_query_arg(array('sort' => 'popular'), get_post_type_archive_link('novel'))); ?>" class="novel-section-more">بیشتر →</a>
            </div>
            <div class="novel-scroll-row">
                <?php foreach ($trending_cache as $tp): ?>
                    <div class="novel-scroll-item"><?php novel_render_card(is_object($tp) ? $tp->ID : $tp, 'grid'); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ آخرین بروزرسانی‌ها ═══
    if ($section === 'updates'):
        $updates_cache = get_transient('novel_latest_updates');
        if (!$updates_cache) {
            $updates_cache = get_posts(array('post_type' => 'chapter', 'posts_per_page' => 15, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'));
            set_transient('novel_latest_updates', $updates_cache, 15 * MINUTE_IN_SECONDS);
        }
        if (!empty($updates_cache)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <h2 class="novel-section-title">📅 آخرین بروزرسانی‌ها</h2>
            <div class="novel-updates-list">
                <?php foreach ($updates_cache as $up):
                    $up_id = is_object($up) ? $up->ID : $up;
                    $nid = absint(get_post_meta($up_id, 'chapter_novel_id', true));
                    $ch_num = absint(get_post_meta($up_id, 'chapter_number', true));
                    $is_vip_ch = (bool)get_post_meta($up_id, 'is_vip', true);
                    $novel_type_up = get_post_meta($nid, 'novel_type', true);
                ?>
                <a href="<?php echo esc_url(get_permalink($up_id)); ?>" class="novel-update-item">
                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($nid, 'novel-thumb') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>"
                         width="40" height="56" alt="" loading="lazy">
                    <div class="novel-update-info">
                        <strong><?php echo esc_html(get_the_title($nid)); ?></strong>
                        <span>قسمت <?php echo novel_to_persian($ch_num); ?>: <?php echo esc_html(get_the_title($up_id)); ?></span>
                    </div>
                    <div class="novel-update-meta">
                        <span class="novel-badge-inline <?php echo $novel_type_up === 'ln' ? 'novel-badge-purple' : 'novel-badge-green'; ?>"><?php echo $novel_type_up === 'ln' ? 'LN' : 'WN'; ?></span>
                        <?php if ($is_vip_ch): ?><span class="novel-badge-inline novel-badge-gold">VIP</span><?php endif; ?>
                        <small><?php echo novel_time_ago(get_the_date('Y-m-d H:i:s', $up_id)); ?></small>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ رمان‌های محبوب ═══
    if ($section === 'popular'):
        $popular = get_transient('novel_popular');
        if (!$popular) {
            $popular = get_posts(array(
                'post_type' => 'novel', 'posts_per_page' => 12, 'post_status' => 'publish',
                'meta_key' => 'followers_count', 'orderby' => 'meta_value_num', 'order' => 'DESC',
            ));
            set_transient('novel_popular', $popular, 6 * HOUR_IN_SECONDS);
        }
        if (!empty($popular)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <div class="novel-section-header">
                <h2 class="novel-section-title">❤ رمان‌های محبوب</h2>
                <a href="<?php echo esc_url(add_query_arg('sort', 'followers', get_post_type_archive_link('novel'))); ?>" class="novel-section-more">بیشتر →</a>
            </div>
            <div class="novel-grid novel-grid-4">
                <?php foreach (array_slice($popular, 0, 8) as $pp): ?>
                    <?php novel_render_card(is_object($pp) ? $pp->ID : $pp, 'grid'); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ تازه‌ترین ═══
    if ($section === 'newest'):
        $newest = get_posts(array('post_type' => 'novel', 'posts_per_page' => 8, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'));
        if (!empty($newest)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <div class="novel-section-header">
                <h2 class="novel-section-title">🆕 تازه‌ترین رمان‌ها</h2>
                <a href="<?php echo esc_url(add_query_arg('sort', 'newest', get_post_type_archive_link('novel'))); ?>" class="novel-section-more">بیشتر →</a>
            </div>
            <div class="novel-grid novel-grid-4">
                <?php foreach ($newest as $np): ?>
                    <?php novel_render_card($np->ID, 'grid'); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

    <?php // ═══ بنر CTA (مهمان) ═══
    if ($section === 'newest' && !$is_logged): ?>
    <section class="novel-cta-section">
        <div class="novel-container">
            <div class="novel-cta-card">
                <h2>📚 به دنیای داستان‌ها بپیوندید!</h2>
                <p>با عضویت رایگان، کتابخانه شخصی بسازید، در مسابقات شرکت کنید و سکه جمع کنید!</p>
                <div class="novel-cta-btns">
                    <a href="<?php echo esc_url(novel_get_auth_url('register')); ?>" class="novel-btn novel-btn-primary novel-btn-lg">🚀 ثبت‌نام رایگان</a>
                    <a href="<?php echo esc_url(novel_get_auth_url('login')); ?>" class="novel-btn novel-btn-outline novel-btn-lg">ورود</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php // ═══ آخرین دیدگاه‌ها ═══
    if ($section === 'comments'):
        $recent_comments = get_transient('novel_recent_comments');
        if (!$recent_comments) {
            $recent_comments = get_comments(array('number' => 10, 'status' => 'approve', 'type' => 'comment', 'orderby' => 'comment_date', 'order' => 'DESC'));
            set_transient('novel_recent_comments', $recent_comments, 15 * MINUTE_IN_SECONDS);
        }
        if (!empty($recent_comments)):
    ?>
    <section class="novel-section">
        <div class="novel-container">
            <h2 class="novel-section-title">💬 آخرین دیدگاه‌ها</h2>
            <div class="novel-comments-home-grid">
                <?php foreach ($recent_comments as $rc):
                    $rc_uid = (int)$rc->user_id;
                    $rc_post_id = (int)$rc->comment_post_ID;
                    $rc_post_type = get_post_type($rc_post_id);
                    $rc_novel_id = $rc_post_type === 'chapter' ? absint(get_post_meta($rc_post_id, 'chapter_novel_id', true)) : $rc_post_id;
                    $rc_likes = absint(get_comment_meta($rc->comment_ID, 'likes_count', true));
                ?>
                <div class="novel-comment-home-card">
                    <div class="novel-comment-home-header">
                        <img src="<?php echo esc_url($rc_uid ? novel_get_avatar($rc_uid, 32) : get_avatar_url($rc->comment_author_email, array('size' => 32))); ?>"
                             width="32" height="32" alt="" loading="lazy">
                        <a href="<?php echo $rc_uid ? esc_url(get_author_posts_url($rc_uid)) : '#'; ?>"><?php echo esc_html($rc->comment_author); ?></a>
                    </div>
                    <p class="novel-comment-home-text"><?php echo esc_html(wp_trim_words($rc->comment_content, 15, '...')); ?></p>
                    <div class="novel-comment-home-footer">
                        <a href="<?php echo esc_url(get_permalink($rc_novel_id)); ?>" class="novel-link-sm"><?php echo esc_html(get_the_title($rc_novel_id)); ?></a>
                        <span><?php echo novel_time_ago($rc->comment_date); ?></span>
                        <?php if ($rc_likes): ?><span>👍 <?php echo novel_to_persian($rc_likes); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

<?php endforeach; ?>

</div>

<?php get_footer(); ?>