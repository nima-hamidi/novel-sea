<?php
/**
 * صفحه تک رمان
 * @package NovelTheme
 */
get_header();

if (have_posts()) : the_post();

$novel_id    = get_the_ID();
$title       = get_the_title();
$eng_name    = get_post_meta($novel_id, 'novel_english_name', true);
$author_id   = (int)get_the_author_meta('ID');
$author_name = get_the_author();
$author_url  = get_author_posts_url($author_id);
$translator  = get_post_meta($novel_id, 'novel_translator', true);
$novel_type  = get_post_meta($novel_id, 'novel_type', true);
$type_label  = ($novel_type === 'ln') ? 'لایت ناول (LN)' : 'وب ناول (WN)';
$age_rating  = get_post_meta($novel_id, 'novel_age_rating', true);
$has_anime   = get_post_meta($novel_id, 'has_anime', true);
$has_manga   = get_post_meta($novel_id, 'has_manga', true);
$anime_url   = get_post_meta($novel_id, 'anime_url', true);
$manga_url   = get_post_meta($novel_id, 'manga_url', true);
$cover       = get_the_post_thumbnail_url($novel_id, 'novel-card');

$rating_sum   = (float)get_post_meta($novel_id, 'novel_rating_sum', true);
$rating_count = (int)get_post_meta($novel_id, 'novel_rating_count', true);
$avg_rating   = $rating_count > 0 ? round($rating_sum / $rating_count, 1) : 0;

$views      = absint(get_post_meta($novel_id, 'total_views', true));
$followers  = absint(get_post_meta($novel_id, 'followers_count', true));
$genres     = wp_get_post_terms($novel_id, 'genre', array('fields' => 'all'));
$tags       = wp_get_post_terms($novel_id, 'novel_tag', array('fields' => 'all'));
$status_t   = wp_get_post_terms($novel_id, 'novel_status', array('fields' => 'names'));
$status_name = !empty($status_t) ? $status_t[0] : 'نامشخص';

// قسمت‌ها
global $wpdb;
$chapters = $wpdb->get_results($wpdb->prepare(
    "SELECT p.ID, p.post_title, p.post_date,
            pm1.meta_value as chapter_number,
            pm2.meta_value as chapter_volume,
            pm3.meta_value as is_vip,
            pm4.meta_value as likes_count,
            pm5.meta_value as total_views
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm0 ON p.ID = pm0.post_id AND pm0.meta_key = 'chapter_novel_id' AND pm0.meta_value = %d
     LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'chapter_number'
     LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'chapter_volume'
     LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'is_vip'
     LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'likes_count'
     LEFT JOIN {$wpdb->postmeta} pm5 ON p.ID = pm5.post_id AND pm5.meta_key = 'total_views'
     WHERE p.post_type = 'chapter' AND p.post_status = 'publish'
     ORDER BY CAST(pm1.meta_value AS UNSIGNED) ASC",
    $novel_id
));

$ch_count   = count($chapters);
$free_count = 0;
$vip_count  = 0;
foreach ($chapters as $ch) {
    if ($ch->is_vip && $ch->is_vip !== '0') $vip_count++; else $free_count++;
}

// کتابخانه و فالو
$is_followed = false;
$library_type = '';
$my_rating = 0;
$read_chapters = array();
if (is_user_logged_in()) {
    $uid = get_current_user_id();
    $is_followed = (bool)$wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}novel_follows WHERE user_id=%d AND novel_id=%d", $uid, $novel_id
    ));
    $library_type = $wpdb->get_var($wpdb->prepare(
        "SELECT list_type FROM {$wpdb->prefix}user_library WHERE user_id=%d AND novel_id=%d", $uid, $novel_id
    )) ?: '';
    $my_rating = absint(get_post_meta($novel_id, 'novel_user_rating_' . $uid, true));

    if (!empty($chapters)) {
        $ch_ids = wp_list_pluck($chapters, 'ID');
        $ch_ids_str = implode(',', array_map('intval', $ch_ids));
        $read_raw = $wpdb->get_col($wpdb->prepare(
            "SELECT chapter_id FROM {$wpdb->prefix}reading_history WHERE user_id = %d AND chapter_id IN ({$ch_ids_str})",
            $uid
        ));
        $read_chapters = array_map('intval', $read_raw);
    }
}

// ادامه مطالعه
$continue_ch = null;
if (!empty($read_chapters) && !empty($chapters)) {
    $last_read_num = 0;
    foreach ($chapters as $ch) {
        if (in_array((int)$ch->ID, $read_chapters)) {
            $last_read_num = max($last_read_num, (int)$ch->chapter_number);
        }
    }
    foreach ($chapters as $ch) {
        if ((int)$ch->chapter_number === $last_read_num + 1) {
            $continue_ch = $ch;
            break;
        }
    }
}
?>

<!-- Breadcrumb -->
<nav class="novel-breadcrumb" aria-label="مسیر">
    <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
    <span>›</span>
    <a href="<?php echo esc_url(get_post_type_archive_link('novel')); ?>">رمان‌ها</a>
    <span>›</span>
    <span><?php echo esc_html($title); ?></span>
</nav>

<article class="novel-single">

    <!-- ═══ هدر رمان ═══ -->
    <div class="novel-single-header">
        <div class="novel-single-cover">
            <?php if ($cover): ?>
                <img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($title); ?>"
                     width="300" height="420" class="novel-cover-img" loading="eager">
            <?php else: ?>
                <div class="novel-cover-placeholder">📖</div>
            <?php endif; ?>
        </div>

        <div class="novel-single-info">
            <h1 class="novel-single-title"><?php echo esc_html($title); ?></h1>
            <?php if ($eng_name): ?>
                <p class="novel-single-eng"><?php echo esc_html($eng_name); ?></p>
            <?php endif; ?>

            <div class="novel-single-meta">
                <span>نویسنده: <a href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($author_name); ?></a></span>
                <?php if ($translator): ?>
                    <span>مترجم: <?php echo esc_html($translator); ?></span>
                <?php endif; ?>
            </div>

            <!-- امتیاز -->
            <div class="novel-single-rating" id="novelRating" data-novel="<?php echo (int)$novel_id; ?>">
                <div class="novel-star-rate" id="novelStarRate" data-current="<?php echo (int)$my_rating; ?>">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg data-value="<?php echo $i; ?>" width="28" height="28" viewBox="0 0 24 24"
                             fill="<?php echo $i <= $my_rating ? '#fbbf24' : '#d1d5db'; ?>" class="novel-star-clickable novel-star-rate-star">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    <?php endfor; ?>
                </div>
                <span class="novel-rating-text">
                    <?php echo novel_to_persian($avg_rating); ?> (<?php echo novel_to_persian($rating_count); ?> رأی)
                </span>
            </div>

            <!-- آمار -->
            <div class="novel-single-stats">
                <span>📖 <?php echo novel_to_persian($ch_count); ?> قسمت</span>
                <span>👁 <?php echo novel_format_number($views); ?></span>
                <span>❤ <?php echo novel_format_number($followers); ?> دنبال</span>
            </div>

            <!-- بج‌ها -->
            <div class="novel-single-badges">
                <span class="novel-badge <?php echo $novel_type === 'ln' ? 'novel-badge-purple' : 'novel-badge-green'; ?>">
                    <?php echo esc_html($type_label); ?>
                </span>
                <span class="novel-badge"><?php echo esc_html($status_name); ?></span>
                <?php if ($has_anime): ?>
                    <?php if ($anime_url): ?><a href="<?php echo esc_url($anime_url); ?>" target="_blank" class="novel-badge novel-badge-blue">🎬 انیمه</a>
                    <?php else: ?><span class="novel-badge novel-badge-blue">🎬 انیمه</span><?php endif; ?>
                <?php endif; ?>
                <?php if ($has_manga): ?>
                    <?php if ($manga_url): ?><a href="<?php echo esc_url($manga_url); ?>" target="_blank" class="novel-badge novel-badge-pink">📚 مانگا</a>
                    <?php else: ?><span class="novel-badge novel-badge-pink">📚 مانگا</span><?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- ژانرها -->
            <div class="novel-single-genres">
                <?php foreach ($genres as $genre): ?>
                    <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="novel-genre-tag"><?php echo esc_html($genre->name); ?></a>
                <?php endforeach; ?>
            </div>

            <!-- تگ‌ها -->
            <?php if (!empty($tags)): ?>
            <div class="novel-single-tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="novel-tag-chip">#<?php echo esc_html($tag->name); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ دکمه‌های عمل ═══ -->
    <div class="novel-single-actions">
        <button class="novel-btn novel-btn-primary novel-follow-btn"
                data-novel="<?php echo (int)$novel_id; ?>"
                data-followed="<?php echo $is_followed ? '1' : '0'; ?>">
            <?php echo $is_followed ? '💔 لغو دنبال (' . novel_to_persian($followers) . ')' : '❤ دنبال کردن (' . novel_to_persian($followers) . ')'; ?>
        </button>

        <?php if (novel_is_module_active('library')): ?>
        <div class="novel-library-dropdown">
            <button class="novel-btn novel-btn-outline novel-library-toggle">
                📚 <?php echo $library_type ? novel_library_label($library_type) : 'کتابخانه'; ?> ▾
            </button>
            <div class="novel-library-menu novel-hidden">
                <?php
                $lib_types = array(
                    'reading'   => '📖 در حال خواندن',
                    'plan'      => '📋 می‌خوام بخوانم',
                    'completed' => '✅ تکمیل شده',
                    'dropped'   => '🚫 رها شده',
                    'on_hold'   => '⏸ نگه‌داشته',
                );
                foreach ($lib_types as $lt => $ll): ?>
                    <button class="novel-library-item <?php echo $library_type === $lt ? 'active' : ''; ?>"
                            data-novel="<?php echo (int)$novel_id; ?>" data-type="<?php echo esc_attr($lt); ?>">
                        <?php echo $ll; ?>
                    </button>
                <?php endforeach; ?>
                <?php if ($library_type): ?>
                    <button class="novel-library-item novel-library-remove"
                            data-novel="<?php echo (int)$novel_id; ?>" data-type="remove">🗑 حذف از کتابخانه</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (novel_is_module_active('share')): ?>
        <div class="novel-share-btns">
            <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode($title); ?>" target="_blank" class="novel-share-btn novel-share-telegram" aria-label="تلگرام">📱</a>
            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($title . ' ' . get_permalink()); ?>" target="_blank" class="novel-share-btn novel-share-whatsapp" aria-label="واتساپ">💬</a>
            <button class="novel-share-btn novel-copy-link" data-url="<?php echo esc_url(get_permalink()); ?>" aria-label="کپی لینک">🔗</button>
        </div>
        <?php endif; ?>

        <?php if ($continue_ch): ?>
            <a href="<?php echo esc_url(get_permalink($continue_ch->ID)); ?>" class="novel-btn novel-btn-success">
                ▶ ادامه مطالعه: قسمت <?php echo novel_to_persian($continue_ch->chapter_number); ?>
            </a>
        <?php elseif (!empty($chapters)): ?>
            <a href="<?php echo esc_url(get_permalink($chapters[0]->ID)); ?>" class="novel-btn novel-btn-success">
                ▶ شروع خواندن: قسمت <?php echo novel_to_persian($chapters[0]->chapter_number); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- ═══ خلاصه ═══ -->
    <div class="novel-single-synopsis">
        <h2>خلاصه داستان</h2>
        <div class="novel-synopsis-content" id="synopsisContent">
            <?php the_content(); ?>
        </div>
        <button class="novel-synopsis-toggle novel-hidden" id="synopsisToggle">بیشتر بخوانید ▼</button>
    </div>

    <!-- ═══ اطلاعات ═══ -->
    <div class="novel-single-details">
        <h2>اطلاعات رمان</h2>
        <div class="novel-details-grid">
            <div class="novel-detail-item"><span class="novel-detail-label">نوع</span><span><?php echo esc_html($type_label); ?></span></div>
            <div class="novel-detail-item"><span class="novel-detail-label">وضعیت</span><span><?php echo esc_html($status_name); ?></span></div>
            <div class="novel-detail-item"><span class="novel-detail-label">نویسنده</span><a href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($author_name); ?></a></div>
            <?php if ($translator): ?>
            <div class="novel-detail-item"><span class="novel-detail-label">مترجم</span><span><?php echo esc_html($translator); ?></span></div>
            <?php endif; ?>
            <div class="novel-detail-item"><span class="novel-detail-label">رده سنی</span><span><?php echo $age_rating && $age_rating !== 'all' ? '+' . novel_to_persian($age_rating) : 'همه'; ?></span></div>
            <div class="novel-detail-item"><span class="novel-detail-label">قسمت رایگان</span><span><?php echo novel_to_persian($free_count); ?></span></div>
            <?php if ($vip_count > 0): ?>
            <div class="novel-detail-item"><span class="novel-detail-label">قسمت VIP</span><span><?php echo novel_to_persian($vip_count); ?> 👑</span></div>
            <?php endif; ?>
            <div class="novel-detail-item"><span class="novel-detail-label">تاریخ شروع</span><span><?php echo novel_jalali_date('j F Y', strtotime(get_the_date('Y-m-d'))); ?></span></div>
            <?php if (!empty($chapters)): ?>
            <div class="novel-detail-item"><span class="novel-detail-label">آخرین بروزرسانی</span><span><?php echo novel_jalali_date('j F Y', strtotime(end($chapters)->post_date)); ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ لیست قسمت‌ها ═══ -->
    <div class="novel-single-chapters">
        <div class="novel-chapters-header">
            <h2>📋 لیست قسمت‌ها (<?php echo novel_to_persian($ch_count); ?>)</h2>
            <button class="novel-btn novel-btn-sm novel-btn-ghost" id="chapterSortToggle" data-order="asc">ترتیب ↕</button>
        </div>

        <div class="novel-chapters-list" id="chaptersList">
            <?php
            $current_volume = '';
            foreach ($chapters as $ch):
                $ch_num   = absint($ch->chapter_number);
                $ch_vip   = ($ch->is_vip && $ch->is_vip !== '0');
                $ch_read  = in_array((int)$ch->ID, $read_chapters);
                $ch_views = absint($ch->total_views);
                $ch_likes = absint($ch->likes_count);
                $ch_vol   = $ch->chapter_volume;

                // فصل‌بندی
                if ($ch_vol && $ch_vol !== $current_volume):
                    $current_volume = $ch_vol;
            ?>
                <div class="novel-chapter-volume-divider">📂 <?php echo esc_html($ch_vol); ?></div>
            <?php endif; ?>

                <a href="<?php echo esc_url(get_permalink($ch->ID)); ?>"
                   class="novel-chapter-row <?php echo $ch_vip ? 'novel-chapter-vip' : ''; ?> <?php echo $ch_read ? 'novel-chapter-read' : ''; ?>">
                    <span class="novel-ch-num"><?php echo novel_to_persian($ch_num); ?></span>
                    <span class="novel-ch-title"><?php echo esc_html($ch->post_title); ?></span>
                    <span class="novel-ch-date"><?php echo novel_jalali_date('j F', strtotime($ch->post_date)); ?></span>
                    <span class="novel-ch-stats">
                        👁<?php echo novel_format_number($ch_views); ?>
                        <?php if ($ch_likes): ?> 👍<?php echo novel_to_persian($ch_likes); ?><?php endif; ?>
                    </span>
                    <span class="novel-ch-access">
                        <?php echo $ch_vip ? '🔒 VIP' : '✅'; ?>
                    </span>
                    <?php if ($ch_read): ?>
                        <span class="novel-ch-check">✓</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═══ دیدگاه‌ها ═══ -->
    <?php include NOVEL_DIR . '/novel-comments.php'; ?>

</article>

<?php endif; get_footer(); ?>

<?php
function novel_library_label($type) {
    $labels = array(
        'reading'   => '📖 در حال خواندن',
        'plan'      => '📋 برنامه',
        'completed' => '✅ تکمیل',
        'dropped'   => '🚫 رها',
        'on_hold'   => '⏸ نگه‌داشته',
    );
    return isset($labels[$type]) ? $labels[$type] : $type;
}