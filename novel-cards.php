<?php
/**
 * Template Part — کارت رمان
 * Usage: include novel-cards.php after setting $novel_card_post and $novel_card_mode
 * @package NovelTheme
 */
if (!defined('ABSPATH')) exit;

/**
 * رندر کارت رمان
 * @param int    $post_id
 * @param string $mode   'grid' | 'list' | 'small'
 */
function novel_render_card($post_id, $mode = 'grid') {
    $title      = get_the_title($post_id);
    $link       = get_permalink($post_id);
    $thumb      = get_the_post_thumbnail_url($post_id, $mode === 'list' ? 'novel-thumb' : 'novel-card');
    $author     = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
    $author_url = get_author_posts_url(get_post_field('post_author', $post_id));
    $type       = get_post_meta($post_id, 'novel_type', true);
    $type_label = ($type === 'ln') ? 'LN' : 'WN';
    $type_class = ($type === 'ln') ? 'novel-badge-purple' : 'novel-badge-green';

    $rating_sum   = (float)get_post_meta($post_id, 'novel_rating_sum', true);
    $rating_count = (int)get_post_meta($post_id, 'novel_rating_count', true);
    $avg          = $rating_count > 0 ? round($rating_sum / $rating_count, 1) : 0;

    $views     = absint(get_post_meta($post_id, 'total_views', true));
    $followers = absint(get_post_meta($post_id, 'followers_count', true));

    // تعداد قسمت (cache)
    $ch_count = get_post_meta($post_id, 'chapters_count_cache', true);
    if ($ch_count === '') {
        global $wpdb;
        $ch_count = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type='chapter' AND post_status='publish' AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='chapter_novel_id' AND meta_value=%d)",
            $post_id
        ));
        update_post_meta($post_id, 'chapters_count_cache', $ch_count);
    }

    $genres = wp_get_post_terms($post_id, 'genre', array('fields' => 'all'));
    $status_terms = wp_get_post_terms($post_id, 'novel_status', array('fields' => 'names'));
    $status_name = !empty($status_terms) ? $status_terms[0] : '';

    $is_vip = novel_has_vip_chapters($post_id);

    $is_followed = false;
    $library_type = '';
    if (is_user_logged_in()) {
        global $wpdb;
        $uid = get_current_user_id();
        $is_followed = (bool)$wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}novel_follows WHERE user_id=%d AND novel_id=%d", $uid, $post_id
        ));
        $library_type = $wpdb->get_var($wpdb->prepare(
            "SELECT list_type FROM {$wpdb->prefix}user_library WHERE user_id=%d AND novel_id=%d", $uid, $post_id
        ));
    }

    if ($mode === 'list') {
        novel_render_card_list($post_id, $title, $link, $thumb, $author, $type_label, $type_class, $avg, $ch_count, $views, $genres);
    } else {
        novel_render_card_grid($post_id, $title, $link, $thumb, $author, $author_url, $type_label, $type_class, $avg, $ch_count, $views, $followers, $genres, $status_name, $is_vip, $is_followed);
    }
}

function novel_render_card_grid($post_id, $title, $link, $thumb, $author, $author_url, $type_label, $type_class, $avg, $ch_count, $views, $followers, $genres, $status_name, $is_vip, $is_followed) {
    ?>
    <article class="novel-card" data-id="<?php echo (int)$post_id; ?>">
        <a href="<?php echo esc_url($link); ?>" class="novel-card-cover">
            <div class="novel-card-img-wrap">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>"
                         width="300" height="420" loading="lazy" class="novel-card-img"
                         onerror="this.src='<?php echo esc_url(NOVEL_URL . '/assets/images/default-cover.png'); ?>'">
                <?php else: ?>
                    <div class="novel-card-placeholder">📖</div>
                <?php endif; ?>

                <!-- بج‌ها -->
                <div class="novel-card-badges">
                    <span class="novel-badge <?php echo esc_attr($type_class); ?>"><?php echo esc_html($type_label); ?></span>
                    <?php if ($is_vip): ?>
                        <span class="novel-badge novel-badge-gold">VIP 👑</span>
                    <?php endif; ?>
                    <?php if ($status_name === 'تکمیل'): ?>
                        <span class="novel-badge novel-badge-teal">تکمیل ✅</span>
                    <?php elseif ($status_name === 'متوقف'): ?>
                        <span class="novel-badge novel-badge-red">متوقف ⏸</span>
                    <?php endif; ?>
                </div>

                <!-- امتیاز -->
                <?php if ($avg > 0): ?>
                <div class="novel-card-rating">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#fbbf24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span><?php echo novel_to_persian($avg); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </a>

        <div class="novel-card-body">
            <h3 class="novel-card-title">
                <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
            </h3>
            <a href="<?php echo esc_url($author_url); ?>" class="novel-card-author"><?php echo esc_html($author); ?></a>

            <?php if (!empty($genres)): ?>
            <div class="novel-card-genres">
                <?php foreach (array_slice($genres, 0, 3) as $genre): ?>
                    <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="novel-genre-tag"><?php echo esc_html($genre->name); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="novel-card-stats">
                <span>📖 <?php echo novel_format_number($ch_count); ?></span>
                <span>👁 <?php echo novel_format_number($views); ?></span>
                <span>❤ <?php echo novel_format_number($followers); ?></span>
            </div>

            <button class="novel-btn novel-btn-sm novel-btn-follow novel-follow-btn"
                    data-novel="<?php echo (int)$post_id; ?>"
                    data-followed="<?php echo $is_followed ? '1' : '0'; ?>">
                <?php echo $is_followed ? '💔 لغو' : '❤ دنبال کردن'; ?>
            </button>
        </div>
    </article>
    <?php
}

function novel_render_card_list($post_id, $title, $link, $thumb, $author, $type_label, $type_class, $avg, $ch_count, $views, $genres) {
    ?>
    <article class="novel-card-list">
        <a href="<?php echo esc_url($link); ?>" class="novel-card-list-img">
            <?php if ($thumb): ?>
                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>"
                     width="60" height="84" loading="lazy"
                     onerror="this.src='<?php echo esc_url(NOVEL_URL . '/assets/images/default-cover.png'); ?>'">
            <?php endif; ?>
        </a>
        <div class="novel-card-list-info">
            <div class="novel-card-list-top">
                <h3><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></h3>
                <div class="novel-card-list-meta">
                    <?php if ($avg > 0): ?>★<?php echo novel_to_persian($avg); ?><?php endif; ?>
                    | <span class="novel-badge-inline <?php echo esc_attr($type_class); ?>"><?php echo esc_html($type_label); ?></span>
                    | 📖<?php echo novel_to_persian($ch_count); ?>
                </div>
            </div>
            <div class="novel-card-list-bottom">
                <span><?php echo esc_html($author); ?></span>
                <?php if (!empty($genres)): ?>
                    | <?php foreach (array_slice($genres, 0, 2) as $g): ?>
                        <span class="novel-genre-tag-sm"><?php echo esc_html($g->name); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <span>👁<?php echo novel_format_number($views); ?></span>
            </div>
        </div>
    </article>
    <?php
}

// ── Helper ──
function novel_has_vip_chapters($novel_id) {
    $cached = get_post_meta($novel_id, 'has_vip_cache', true);
    if ($cached !== '') return (bool)$cached;

    global $wpdb;
    $has = (bool)$wpdb->get_var($wpdb->prepare(
        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id
         WHERE pm.meta_key='chapter_novel_id' AND pm.meta_value=%d
         AND pm2.meta_key='is_vip' AND pm2.meta_value='1' LIMIT 1",
        $novel_id
    ));
    update_post_meta($novel_id, 'has_vip_cache', $has ? 1 : 0);
    return $has;
}

/**
 * رندر ستاره SVG
 */
function novel_render_stars($rating, $size = 16) {
    $full  = floor($rating);
    $half  = ($rating - $full) >= 0.3 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = '<div class="novel-stars" aria-label="امتیاز ' . novel_to_persian($rating) . ' از ۵">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="#fbbf24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    }
    if ($half) {
        $html .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"><defs><linearGradient id="hg"><stop offset="50%" stop-color="#fbbf24"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs><path fill="url(#hg)" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="#d1d5db"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    }
    $html .= '</div>';
    return $html;
}