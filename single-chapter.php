<?php
/**
 * صفحه تک قسمت — حالت مطالعه
 * @package NovelTheme
 */
get_header();

if (have_posts()) : the_post();

$chapter_id  = get_the_ID();
$novel_id    = absint(get_post_meta($chapter_id, 'chapter_novel_id', true));
$ch_number   = absint(get_post_meta($chapter_id, 'chapter_number', true));
$ch_volume   = get_post_meta($chapter_id, 'chapter_volume', true);
$is_vip      = (bool)get_post_meta($chapter_id, 'is_vip', true);
$coin_price  = absint(get_post_meta($chapter_id, 'coin_price', true));
$recap       = get_post_meta($chapter_id, 'chapter_recap', true);
$mood        = get_post_meta($chapter_id, 'chapter_mood', true);
$content     = get_the_content();
$word_count  = str_word_count(strip_tags($content));
$read_time   = novel_get_reading_time($content);
$likes       = absint(get_post_meta($chapter_id, 'likes_count', true));
$dislikes    = absint(get_post_meta($chapter_id, 'dislikes_count', true));
$total_votes = $likes + $dislikes;
$approval    = $total_votes > 0 ? round(($likes / $total_votes) * 100) : 0;
$views       = absint(get_post_meta($chapter_id, 'total_views', true));

$novel_title = $novel_id ? get_the_title($novel_id) : '';
$novel_link  = $novel_id ? get_permalink($novel_id) : '#';

// قسمت‌های قبلی/بعدی
global $wpdb;
$prev_ch = $wpdb->get_var($wpdb->prepare(
    "SELECT p.ID FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'chapter_novel_id' AND pm.meta_value = %d
     INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'chapter_number'
     WHERE p.post_type = 'chapter' AND p.post_status = 'publish' AND CAST(pm2.meta_value AS UNSIGNED) < %d
     ORDER BY CAST(pm2.meta_value AS UNSIGNED) DESC LIMIT 1",
    $novel_id, $ch_number
));
$next_ch = $wpdb->get_var($wpdb->prepare(
    "SELECT p.ID FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'chapter_novel_id' AND pm.meta_value = %d
     INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'chapter_number'
     WHERE p.post_type = 'chapter' AND p.post_status = 'publish' AND CAST(pm2.meta_value AS UNSIGNED) > %d
     ORDER BY CAST(pm2.meta_value AS UNSIGNED) ASC LIMIT 1",
    $novel_id, $ch_number
));

// دسترسی VIP
$can_read = true;
$user_id = get_current_user_id();
if ($is_vip) {
    $can_read = novel_user_can_read_chapter($user_id, $chapter_id);
}

// ثبت بازدید + تاریخچه
novel_record_view($chapter_id);
if ($user_id && $novel_id) {
    $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->prefix}reading_history (user_id, chapter_id, novel_id, read_at)
         VALUES (%d, %d, %d, %s)",
        $user_id, $chapter_id, $novel_id, current_time('mysql')
    ));
    // آپدیت progress
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->prefix}user_library SET progress = GREATEST(progress, %d), updated_at = %s
         WHERE user_id = %d AND novel_id = %d",
        $ch_number, current_time('mysql'), $user_id, $novel_id
    ));
}

// رأی فعلی کاربر
$user_vote = 0;
if ($user_id) {
    $uv = $wpdb->get_var($wpdb->prepare(
        "SELECT vote FROM {$wpdb->prefix}chapter_votes WHERE chapter_id = %d AND user_id = %d",
        $chapter_id, $user_id
    ));
    $user_vote = $uv ? (int)$uv : 0;
}
?>

<!-- Progress Bar -->
<div class="novel-reading-progress" id="readingProgress">
    <div class="novel-progress-fill" id="progressFill"></div>
</div>

<!-- Breadcrumb -->
<nav class="novel-breadcrumb" aria-label="مسیر">
    <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a> ›
    <a href="<?php echo esc_url($novel_link); ?>"><?php echo esc_html($novel_title); ?></a> ›
    <span>قسمت <?php echo novel_to_persian($ch_number); ?></span>
</nav>

<article class="novel-chapter">

    <!-- هدر -->
    <div class="novel-chapter-header">
        <a href="<?php echo esc_url($novel_link); ?>" class="novel-chapter-novel-link"><?php echo esc_html($novel_title); ?></a>
        <h1 class="novel-chapter-title">قسمت <?php echo novel_to_persian($ch_number); ?>: <?php the_title(); ?></h1>
        <div class="novel-chapter-meta">
            <span>✍️ <?php the_author(); ?></span>
            <span>📅 <?php echo novel_jalali_date('j F Y'); ?></span>
            <span>⏱ <?php echo novel_to_persian($read_time); ?> دقیقه</span>
            <span>📝 <?php echo novel_format_number($word_count); ?> کلمه</span>
            <span>👁 <?php echo novel_format_number($views); ?></span>
        </div>
    </div>

    <!-- ناوبری بالا -->
    <div class="novel-chapter-nav">
        <?php if ($prev_ch): ?>
            <a href="<?php echo esc_url(get_permalink($prev_ch)); ?>" class="novel-btn novel-btn-outline novel-btn-sm">⬅ قبلی</a>
        <?php else: ?>
            <span class="novel-btn novel-btn-outline novel-btn-sm novel-btn-disabled">⬅ قبلی</span>
        <?php endif; ?>

        <a href="<?php echo esc_url($novel_link); ?>" class="novel-btn novel-btn-ghost novel-btn-sm">📋 لیست</a>

        <?php if ($next_ch): ?>
            <a href="<?php echo esc_url(get_permalink($next_ch)); ?>" class="novel-btn novel-btn-outline novel-btn-sm">بعدی ➡</a>
        <?php else: ?>
            <span class="novel-btn novel-btn-outline novel-btn-sm novel-btn-disabled">بعدی ➡</span>
        <?php endif; ?>
    </div>

    <!-- لایک بالا -->
    <div class="novel-chapter-votes" data-chapter="<?php echo (int)$chapter_id; ?>">
        <button class="novel-vote-btn novel-vote-up <?php echo $user_vote === 1 ? 'active' : ''; ?>"
                data-chapter="<?php echo (int)$chapter_id; ?>" data-vote="1">
            👍 <span class="novel-vote-count"><?php echo novel_to_persian($likes); ?></span>
        </button>
        <button class="novel-vote-btn novel-vote-down <?php echo $user_vote === -1 ? 'active' : ''; ?>"
                data-chapter="<?php echo (int)$chapter_id; ?>" data-vote="-1">
            👎 <span class="novel-vote-count"><?php echo novel_to_persian($dislikes); ?></span>
        </button>
        <?php if ($total_votes > 0): ?>
            <span class="novel-approval novel-approval-<?php echo $approval >= 80 ? 'high' : ($approval >= 50 ? 'mid' : 'low'); ?>">
                <?php echo novel_to_persian($approval); ?>% پسندیدند
            </span>
        <?php endif; ?>
    </div>

    <!-- خلاصه قسمت قبل -->
    <?php if ($recap): ?>
    <details class="novel-chapter-recap" open>
        <summary>📋 خلاصه قسمت قبل</summary>
        <p><?php echo esc_html($recap); ?></p>
    </details>
    <?php endif; ?>

    <!-- ═══ محتوای قسمت ═══ -->
    <?php if ($can_read): ?>
        <div class="novel-chapter-content" id="chapterContent">
            <?php echo apply_filters('the_content', $content); ?>
        </div>
    <?php else: ?>
        <!-- پیش‌نمایش -->
        <div class="novel-chapter-content novel-chapter-preview">
            <?php
            $paragraphs = explode('</p>', $content);
            echo implode('</p>', array_slice($paragraphs, 0, 2)) . '</p>';
            ?>
        </div>

        <!-- قفل VIP -->
        <div class="novel-vip-lock">
            <div class="novel-vip-lock-inner">
                <div class="novel-vip-icon">🔒</div>
                <h3>این قسمت مخصوص اعضای ویژه است</h3>
                <div class="novel-vip-actions">
                    <a href="<?php echo esc_url(novel_get_dashboard_url('subscription')); ?>" class="novel-btn novel-btn-primary">💎 خرید اشتراک</a>
                    <?php if ($coin_price > 0 && novel_is_module_active('coins')): ?>
                        <button class="novel-btn novel-btn-outline novel-buy-chapter"
                                data-chapter="<?php echo (int)$chapter_id; ?>"
                                data-price="<?php echo (int)$coin_price; ?>">
                            🪙 خرید با <?php echo novel_to_persian($coin_price); ?> سکه
                        </button>
                    <?php endif; ?>
                </div>
                <?php if ($user_id && novel_is_module_active('coins')): ?>
                    <small>موجودی شما: 🪙 <?php echo novel_format_number(novel_get_balance($user_id)); ?> سکه</small>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- لایک پایین -->
    <?php if ($can_read): ?>
    <div class="novel-chapter-votes novel-chapter-votes-bottom" data-chapter="<?php echo (int)$chapter_id; ?>">
        <button class="novel-vote-btn novel-vote-up <?php echo $user_vote === 1 ? 'active' : ''; ?>"
                data-chapter="<?php echo (int)$chapter_id; ?>" data-vote="1">
            👍 <span class="novel-vote-count"><?php echo novel_to_persian($likes); ?></span>
        </button>
        <button class="novel-vote-btn novel-vote-down <?php echo $user_vote === -1 ? 'active' : ''; ?>"
                data-chapter="<?php echo (int)$chapter_id; ?>" data-vote="-1">
            👎 <span class="novel-vote-count"><?php echo novel_to_persian($dislikes); ?></span>
        </button>
        <?php if ($total_votes > 0): ?>
            <span class="novel-approval novel-approval-<?php echo $approval >= 80 ? 'high' : ($approval >= 50 ? 'mid' : 'low'); ?>">
                <?php echo novel_to_persian($approval); ?>% پسندیدند
            </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ناوبری پایین -->
    <div class="novel-chapter-nav">
        <?php if ($prev_ch): ?>
            <a href="<?php echo esc_url(get_permalink($prev_ch)); ?>" class="novel-btn novel-btn-outline novel-btn-sm">⬅ قسمت قبل</a>
        <?php else: ?>
            <span class="novel-btn novel-btn-outline novel-btn-sm novel-btn-disabled">⬅ قبلی</span>
        <?php endif; ?>
        <a href="<?php echo esc_url($novel_link); ?>" class="novel-btn novel-btn-ghost novel-btn-sm">📋 لیست</a>
        <?php if ($next_ch): ?>
            <a href="<?php echo esc_url(get_permalink($next_ch)); ?>" class="novel-btn novel-btn-primary novel-btn-sm">قسمت بعد ➡</a>
        <?php else: ?>
            <span class="novel-btn novel-btn-outline novel-btn-sm novel-btn-disabled">بعدی ➡</span>
        <?php endif; ?>
    </div>

    <!-- تنظیمات خواندن (Floating) -->
    <?php if ($can_read): ?>
    <button class="novel-reader-settings-toggle" id="readerSettingsToggle" aria-label="تنظیمات خواندن">⚙️</button>
    <div class="novel-reader-settings novel-hidden" id="readerSettings">
        <h4>تنظیمات مطالعه</h4>
        <div class="novel-setting-row">
            <label>اندازه فونت</label>
            <input type="range" id="readerFontSize" min="14" max="28" step="2" value="18">
            <span id="readerFontSizeLabel">۱۸</span>
        </div>
        <div class="novel-setting-row">
            <label>فاصله خطوط</label>
            <input type="range" id="readerLineHeight" min="1.4" max="2.6" step="0.2" value="1.8">
            <span id="readerLineHeightLabel">۱.۸</span>
        </div>
        <div class="novel-setting-row">
            <label>عرض متن</label>
            <input type="range" id="readerWidth" min="500" max="1200" step="50" value="800">
        </div>
        <div class="novel-setting-row">
            <label>تم خواندن</label>
            <div class="novel-reader-themes">
                <button class="novel-reader-theme active" data-theme="white" style="background:#fff;color:#333" aria-label="سفید">آ</button>
                <button class="novel-reader-theme" data-theme="sepia" style="background:#f4ecd8;color:#5b4636" aria-label="سپیا">آ</button>
                <button class="novel-reader-theme" data-theme="dark" style="background:#2d2d2d;color:#d4d4d4" aria-label="تیره">آ</button>
                <button class="novel-reader-theme" data-theme="black" style="background:#000;color:#ccc" aria-label="مشکی">آ</button>
            </div>
        </div>
        <?php if ($mood && novel_is_module_active('ambient_audio')): ?>
        <div class="novel-setting-row">
            <label>🎵 موسیقی پس‌زمینه</label>
            <div class="novel-ambient-controls">
                <button id="ambientToggle" class="novel-btn novel-btn-sm novel-btn-ghost" data-mood="<?php echo esc_attr($mood); ?>">▶ پخش</button>
                <input type="range" id="ambientVolume" min="0" max="100" value="30">
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ═══ دیدگاه‌ها ═══ -->
    <?php if ($can_read): ?>
        <?php include NOVEL_DIR . '/novel-comments.php'; ?>
    <?php endif; ?>

</article>

<?php endif; get_footer(); ?>