<?php
/**
 * Novel Core — جداول، فعال‌سازی، مهاجرت، کران
 *
 * @package NovelTheme
 */

if (!defined('ABSPATH')) exit;

// ═══════════════════════════════════════
// بخش ۱: ساخت جداول
// ═══════════════════════════════════════
function novel_create_tables() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $prefix  = $wpdb->prefix;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $tables = array();

    // ── ۱: رأی دیدگاه ──
    $tables[] = "CREATE TABLE {$prefix}comment_votes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        comment_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        vote TINYINT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_vote (comment_id, user_id),
        INDEX idx_comment (comment_id)
    ) {$charset};";

    // ── ۲: گزارش‌ها ──
    $tables[] = "CREATE TABLE {$prefix}reports (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reporter_id BIGINT UNSIGNED NOT NULL,
        reported_type VARCHAR(20) NOT NULL,
        reported_id BIGINT UNSIGNED NOT NULL,
        reason VARCHAR(50) NOT NULL,
        description TEXT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME NULL,
        reviewed_by BIGINT UNSIGNED NULL,
        INDEX idx_status (status),
        INDEX idx_type_id (reported_type, reported_id),
        UNIQUE KEY unique_report (reporter_id, reported_type, reported_id)
    ) {$charset};";

    // ── ۳: ری‌اکشن دیدگاه ──
    $tables[] = "CREATE TABLE {$prefix}comment_reactions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        comment_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        reaction VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_reaction (comment_id, user_id),
        INDEX idx_comment_reaction (comment_id, reaction)
    ) {$charset};";

    // ── ۴: رأی قسمت ──
    $tables[] = "CREATE TABLE {$prefix}chapter_votes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        chapter_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        vote TINYINT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_vote (chapter_id, user_id),
        INDEX idx_chapter (chapter_id)
    ) {$charset};";

    // ── ۵: مفید/غیرمفید نقد ──
    $tables[] = "CREATE TABLE {$prefix}review_helpfulness (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        review_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        helpful TINYINT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_help (review_id, user_id),
        INDEX idx_review (review_id)
    ) {$charset};";

    // ── ۶: نقد نویسنده ──
    $tables[] = "CREATE TABLE {$prefix}author_reviews (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        author_id BIGINT UNSIGNED NOT NULL,
        reviewer_id BIGINT UNSIGNED NOT NULL,
        content TEXT NOT NULL,
        rating TINYINT UNSIGNED DEFAULT 0,
        parent_id BIGINT UNSIGNED DEFAULT 0,
        likes_count INT UNSIGNED DEFAULT 0,
        dislikes_count INT UNSIGNED DEFAULT 0,
        status VARCHAR(20) DEFAULT 'approved',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_author (author_id),
        INDEX idx_parent (parent_id)
    ) {$charset};";

    // ── ۷: فالو کاربر ──
    $tables[] = "CREATE TABLE {$prefix}user_follows (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        follower_id BIGINT UNSIGNED NOT NULL,
        following_id BIGINT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_follow (follower_id, following_id),
        INDEX idx_following (following_id)
    ) {$charset};";

    // ── ۸: دنبال کردن رمان ──
    $tables[] = "CREATE TABLE {$prefix}novel_follows (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        notify TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_follow (user_id, novel_id),
        INDEX idx_novel (novel_id)
    ) {$charset};";

    // ── ۹: کتابخانه ──
    $tables[] = "CREATE TABLE {$prefix}user_library (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        list_type VARCHAR(20) NOT NULL,
        progress INT UNSIGNED DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_lib (user_id, novel_id),
        INDEX idx_user_list (user_id, list_type)
    ) {$charset};";

    // ── ۱۰: تاریخچه مطالعه ──
    $tables[] = "CREATE TABLE {$prefix}reading_history (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        chapter_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_read (user_id, chapter_id),
        INDEX idx_user_novel (user_id, novel_id),
        INDEX idx_read_at (user_id, read_at)
    ) {$charset};";

    // ── ۱۱: اعلان‌ها ──
    $tables[] = "CREATE TABLE {$prefix}notifications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(300) NOT NULL,
        message TEXT NULL,
        link VARCHAR(500) NULL,
        is_read TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_read (user_id, is_read),
        INDEX idx_created (created_at)
    ) {$charset};";

    // ── ۱۲: سکه‌ها ──
    $tables[] = "CREATE TABLE {$prefix}user_coins (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        amount INT NOT NULL,
        balance INT UNSIGNED NOT NULL,
        type VARCHAR(30) NOT NULL,
        description VARCHAR(300) NULL,
        reference_id BIGINT UNSIGNED NULL,
        expires_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_expires (expires_at),
        INDEX idx_type (type)
    ) {$charset};";

    // ── ۱۳: خرید قسمت ──
    $tables[] = "CREATE TABLE {$prefix}chapter_purchases (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        chapter_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        coins_spent INT UNSIGNED NOT NULL,
        purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_purchase (user_id, chapter_id),
        INDEX idx_novel (novel_id)
    ) {$charset};";

    // ── ۱۴: درآمد نویسنده ──
    $tables[] = "CREATE TABLE {$prefix}author_earnings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        author_id BIGINT UNSIGNED NOT NULL,
        amount DECIMAL(12,0) NOT NULL,
        type VARCHAR(30) NOT NULL,
        reference_id BIGINT UNSIGNED NULL,
        novel_id BIGINT UNSIGNED NULL,
        chapter_id BIGINT UNSIGNED NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_author (author_id),
        INDEX idx_type (type)
    ) {$charset};";

    // ── ۱۵: واریزی نویسنده ──
    $tables[] = "CREATE TABLE {$prefix}author_payouts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        author_id BIGINT UNSIGNED NOT NULL,
        amount DECIMAL(12,0) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        bank_info TEXT NULL,
        admin_note TEXT NULL,
        requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        paid_at DATETIME NULL,
        paid_by BIGINT UNSIGNED NULL,
        INDEX idx_author (author_id),
        INDEX idx_status (status)
    ) {$charset};";

    // ── ۱۶: نظرسنجی ──
    $tables[] = "CREATE TABLE {$prefix}polls (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(300) NOT NULL,
        description TEXT NULL,
        type VARCHAR(10) DEFAULT 'single',
        status VARCHAR(10) DEFAULT 'active',
        start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        end_date DATETIME NULL,
        created_by BIGINT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) {$charset};";

    // ── ۱۷: گزینه‌های نظرسنجی ──
    $tables[] = "CREATE TABLE {$prefix}poll_options (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        poll_id BIGINT UNSIGNED NOT NULL,
        option_text VARCHAR(300) NOT NULL,
        votes_count INT UNSIGNED DEFAULT 0,
        INDEX idx_poll (poll_id)
    ) {$charset};";

    // ── ۱۸: رأی نظرسنجی ──
    $tables[] = "CREATE TABLE {$prefix}poll_votes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        poll_id BIGINT UNSIGNED NOT NULL,
        option_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_vote (poll_id, user_id)
    ) {$charset};";

    // ── ۱۹: دستاوردها ──
    $tables[] = "CREATE TABLE {$prefix}user_achievements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        achievement_key VARCHAR(50) NOT NULL,
        earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_ach (user_id, achievement_key),
        INDEX idx_user (user_id)
    ) {$charset};";

    // ── ۲۰: بازدید ──
    $tables[] = "CREATE TABLE {$prefix}novel_views (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        view_date DATE NOT NULL,
        view_count INT UNSIGNED DEFAULT 1,
        UNIQUE KEY unique_view (post_id, view_date),
        INDEX idx_date (view_date)
    ) {$charset};";

    // ── ۲۱: مسابقه ──
    $tables[] = "CREATE TABLE {$prefix}quizzes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(300) NOT NULL,
        description TEXT NULL,
        novel_id BIGINT UNSIGNED NULL,
        quiz_type VARCHAR(20) DEFAULT 'manual',
        questions_count TINYINT UNSIGNED DEFAULT 10,
        time_per_question TINYINT UNSIGNED DEFAULT 20,
        prize_1st INT UNSIGNED DEFAULT 50,
        prize_2nd INT UNSIGNED DEFAULT 30,
        prize_3rd INT UNSIGNED DEFAULT 20,
        prize_participation INT UNSIGNED DEFAULT 2,
        status VARCHAR(20) DEFAULT 'draft',
        start_time DATETIME NULL,
        end_time DATETIME NULL,
        created_by BIGINT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_novel (novel_id)
    ) {$charset};";

    // ── ۲۲: سوالات مسابقه ──
    $tables[] = "CREATE TABLE {$prefix}quiz_questions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        quiz_id BIGINT UNSIGNED NOT NULL,
        question_text TEXT NOT NULL,
        options JSON NOT NULL,
        correct_option TINYINT UNSIGNED NOT NULL,
        difficulty TINYINT UNSIGNED DEFAULT 1,
        INDEX idx_quiz (quiz_id)
    ) {$charset};";

    // ── ۲۳: تلاش مسابقه ──
    $tables[] = "CREATE TABLE {$prefix}quiz_attempts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        quiz_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        score INT UNSIGNED DEFAULT 0,
        correct_count TINYINT UNSIGNED DEFAULT 0,
        total_time INT UNSIGNED DEFAULT 0,
        prize_coins INT UNSIGNED DEFAULT 0,
        completed_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_attempt (quiz_id, user_id),
        INDEX idx_quiz_score (quiz_id, score)
    ) {$charset};";

    // ── ۲۴: پاسخ‌های مسابقه ──
    $tables[] = "CREATE TABLE {$prefix}quiz_answers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        attempt_id BIGINT UNSIGNED NOT NULL,
        question_id BIGINT UNSIGNED NOT NULL,
        selected_option TINYINT UNSIGNED NOT NULL,
        is_correct TINYINT NOT NULL,
        time_spent TINYINT UNSIGNED DEFAULT 0,
        INDEX idx_attempt (attempt_id)
    ) {$charset};";

    // ── ۲۵: بنر نویسنده ──
    $tables[] = "CREATE TABLE {$prefix}author_banners (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        author_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        banner_image VARCHAR(500) NOT NULL,
        banner_link VARCHAR(500) NOT NULL,
        position VARCHAR(20) DEFAULT 'above_chapter',
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT NULL,
        impressions INT UNSIGNED DEFAULT 0,
        clicks INT UNSIGNED DEFAULT 0,
        revenue DECIMAL(12,0) DEFAULT 0,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        approved_at DATETIME NULL,
        approved_by BIGINT UNSIGNED NULL,
        expires_at DATETIME NULL,
        INDEX idx_author (author_id),
        INDEX idx_novel_status (novel_id, status),
        INDEX idx_expires (expires_at)
    ) {$charset};";

    // ── ۲۶: صف ایمیل ──
    $tables[] = "CREATE TABLE {$prefix}email_queue (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        to_email VARCHAR(200) NOT NULL,
        to_user_id BIGINT UNSIGNED NULL,
        subject VARCHAR(300) NOT NULL,
        body TEXT NOT NULL,
        status VARCHAR(10) DEFAULT 'pending',
        attempts TINYINT DEFAULT 0,
        scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        error_message TEXT NULL,
        INDEX idx_status (status),
        INDEX idx_scheduled (scheduled_at)
    ) {$charset};";

    // ── ۲۷: اتاق بحث ──
    $tables[] = "CREATE TABLE {$prefix}discussion_rooms (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        novel_id BIGINT UNSIGNED NOT NULL,
        is_active TINYINT DEFAULT 1,
        is_locked TINYINT DEFAULT 0,
        online_count INT UNSIGNED DEFAULT 0,
        total_messages BIGINT UNSIGNED DEFAULT 0,
        pinned_message_id BIGINT UNSIGNED NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_novel (novel_id)
    ) {$charset};";

    // ── ۲۸: پیام‌های اتاق ──
    $tables[] = "CREATE TABLE {$prefix}discussion_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        room_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        message_type VARCHAR(10) DEFAULT 'text',
        is_spoiler TINYINT DEFAULT 0,
        reply_to BIGINT UNSIGNED NULL,
        meta_data JSON NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        INDEX idx_room_created (room_id, created_at),
        INDEX idx_expires (expires_at)
    ) {$charset};";

    // ── ۲۹: mute اتاق ──
    $tables[] = "CREATE TABLE {$prefix}discussion_mutes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        room_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        muted_by BIGINT UNSIGNED NOT NULL,
        reason VARCHAR(200) NULL,
        muted_until DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_mute (room_id, user_id)
    ) {$charset};";

    // ── ۳۰: پیش‌بینی داستان ──
    $tables[] = "CREATE TABLE {$prefix}story_predictions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        chapter_id BIGINT UNSIGNED NOT NULL,
        novel_id BIGINT UNSIGNED NOT NULL,
        author_id BIGINT UNSIGNED NOT NULL,
        question TEXT NOT NULL,
        options JSON NOT NULL,
        correct_option TINYINT NULL,
        status VARCHAR(10) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        resolved_at DATETIME NULL,
        INDEX idx_chapter (chapter_id),
        INDEX idx_status (status)
    ) {$charset};";

    // ── ۳۱: رأی پیش‌بینی ──
    $tables[] = "CREATE TABLE {$prefix}prediction_votes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        prediction_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        selected_option TINYINT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_vote (prediction_id, user_id),
        INDEX idx_prediction (prediction_id)
    ) {$charset};";

    // ── ۳۲: چالش هفتگی ──
    $tables[] = "CREATE TABLE {$prefix}weekly_challenges (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        challenge_type VARCHAR(30) NOT NULL,
        target_count INT UNSIGNED NOT NULL,
        prize_coins INT UNSIGNED DEFAULT 5,
        week_start DATE NOT NULL,
        week_end DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_week (week_start)
    ) {$charset};";

    // اجرای dbDelta
    foreach ($tables as $sql) {
        dbDelta($sql);
    }

    novel_log('Tables created/updated successfully', 'info');
}

// ═══════════════════════════════════════
// بخش ۲: Migration
// ═══════════════════════════════════════
function novel_run_migrations($from, $to) {
    // v1.0 → v2.0
    if (version_compare($from, '2.0.0', '<')) {
        // Migration اولیه — جداول تازه ساخته شدند
        novel_log("Migration: {$from} → {$to}", 'info');
    }
}

// ═══════════════════════════════════════
// بخش ۳: Activation Hook
// ═══════════════════════════════════════
register_activation_hook(__FILE__, 'novel_activate');
function novel_activate() {
    // ثبت CPT قبل از flush
    novel_register_post_types();
    novel_register_taxonomies();

    // ساخت جداول
    novel_create_tables();

    // صفحات پیش‌فرض
    novel_create_default_pages();

    // ژانرها و وضعیت‌ها
    novel_seed_taxonomies();

    // تنظیمات پیش‌فرض
    novel_set_default_options();

    // کران
    novel_schedule_crons();

    // ذخیره نسخه
    update_option('novel_db_version', NOVEL_DB_VERSION);

    flush_rewrite_rules();
}

// ═══════════════════════════════════════
// بخش ۴: Deactivation Hook
// ═══════════════════════════════════════
register_deactivation_hook(__FILE__, 'novel_deactivate');
function novel_deactivate() {
    novel_unschedule_crons();
    flush_rewrite_rules();
    // ⚠️ جداول و داده‌ها حذف نمی‌شوند
}

// ═══════════════════════════════════════
// بخش ۵: صفحات پیش‌فرض
// ═══════════════════════════════════════
function novel_create_default_pages() {
    $pages = array(
        array(
            'option_key' => 'novel_auth_page_id',
            'title'      => 'ورود و ثبت‌نام',
            'template'   => 'page-auth.php',
            'slug'       => 'auth',
        ),
        array(
            'option_key' => 'novel_dashboard_page_id',
            'title'      => 'داشبورد',
            'template'   => 'page-dashboard.php',
            'slug'       => 'dashboard',
        ),
        array(
            'option_key' => 'novel_rankings_page_id',
            'title'      => 'رتبه‌بندی',
            'template'   => 'page-rankings.php',
            'slug'       => 'rankings',
        ),
        array(
            'option_key' => 'novel_authors_page_id',
            'title'      => 'نویسندگان',
            'template'   => 'page-authors.php',
            'slug'       => 'authors',
        ),
    );

    foreach ($pages as $page) {
        $existing = get_option($page['option_key']);
        if ($existing && get_post_status($existing) === 'publish') continue;

        $post_id = wp_insert_post(array(
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_wp_page_template', $page['template']);
            update_option($page['option_key'], $post_id);
        }
    }
}

// ═══════════════════════════════════════
// بخش ۶: Seed Taxonomies
// ═══════════════════════════════════════
function novel_seed_taxonomies() {
    // ژانرها
    $genres = array(
        'اکشن', 'فانتزی', 'رمانس', 'ماجراجویی', 'علمی‌تخیلی',
        'ترسناک', 'کمدی', 'درام', 'روانشناختی', 'مرموز',
        'زندگی روزمره', 'اسلایس آف لایف', 'هارم', 'هنر رزمی',
        'ایسه‌کای', 'گیم', 'مکانیکی', 'تاریخی', 'تراژدی', 'شونن',
    );
    foreach ($genres as $genre) {
        if (!term_exists($genre, 'genre')) {
            wp_insert_term($genre, 'genre');
        }
    }

    // وضعیت‌ها
    $statuses = array('در حال انتشار', 'تکمیل', 'متوقف', 'در حال ترجمه');
    foreach ($statuses as $status) {
        if (!term_exists($status, 'novel_status')) {
            wp_insert_term($status, 'novel_status');
        }
    }
}

// ═══════════════════════════════════════
// بخش ۷: Cron
// ═══════════════════════════════════════
add_filter('cron_schedules', 'novel_cron_schedules');
function novel_cron_schedules($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => 'هر دقیقه',
    );
    $schedules['every_5min'] = array(
        'interval' => 300,
        'display'  => 'هر ۵ دقیقه',
    );
    return $schedules;
}

function novel_schedule_crons() {
    $crons = array(
        'novel_cron_every_minute' => 'every_minute',
        'novel_cron_every_5min'   => 'every_5min',
        'novel_cron_hourly'       => 'hourly',
        'novel_cron_daily'        => 'daily',
    );
    foreach ($crons as $hook => $recurrence) {
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), $recurrence, $hook);
        }
    }
}

function novel_unschedule_crons() {
    $hooks = array(
        'novel_cron_every_minute',
        'novel_cron_every_5min',
        'novel_cron_hourly',
        'novel_cron_daily',
    );
    foreach ($hooks as $hook) {
        $timestamp = wp_next_scheduled($hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $hook);
        }
    }
}

// ── Cron Handlers ──
add_action('novel_cron_every_minute', 'novel_process_email_queue');
function novel_process_email_queue() {
    global $wpdb;
    $prefix = $wpdb->prefix;

    $emails = $wpdb->get_results($wpdb->prepare(
        "SELECT id, to_email, subject, body FROM {$prefix}email_queue
         WHERE status = 'pending' AND scheduled_at <= %s AND attempts < 3
         ORDER BY scheduled_at ASC LIMIT 20",
        current_time('mysql')
    ));

    foreach ($emails as $email) {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = wp_mail($email->to_email, $email->subject, $email->body, $headers);

        if ($sent) {
            $wpdb->update(
                "{$prefix}email_queue",
                array('status' => 'sent', 'sent_at' => current_time('mysql')),
                array('id' => $email->id),
                array('%s', '%s'),
                array('%d')
            );
        } else {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$prefix}email_queue SET attempts = attempts + 1, error_message = %s WHERE id = %d",
                'wp_mail failed',
                $email->id
            ));
        }
    }
}

add_action('novel_cron_every_5min', 'novel_update_room_online_counts');
function novel_update_room_online_counts() {
    // پیاده‌سازی در novel-modules.php (بخش DISCUSSIONS)
}

add_action('novel_cron_hourly', 'novel_hourly_tasks');
function novel_hourly_tasks() {
    global $wpdb;
    $prefix = $wpdb->prefix;

    // انقضای سکه
    if (novel_is_module_active('coins')) {
        $expired = $wpdb->get_results(
            "SELECT id, user_id, amount FROM {$prefix}user_coins
             WHERE expires_at IS NOT NULL AND expires_at < NOW() AND amount > 0 AND type != 'expired'"
        );
        foreach ($expired as $coin) {
            $balance = novel_get_balance($coin->user_id);
            $wpdb->insert("{$prefix}user_coins", array(
                'user_id'     => $coin->user_id,
                'amount'      => -absint($coin->amount),
                'balance'     => max(0, $balance - absint($coin->amount)),
                'type'        => 'expired',
                'description' => 'انقضای خودکار سکه',
                'reference_id'=> $coin->id,
                'created_at'  => current_time('mysql'),
            ));
            update_user_meta($coin->user_id, 'coin_balance', max(0, $balance - absint($coin->amount)));
        }
    }

    // پاکسازی پیام اتاق بحث
    if (novel_is_module_active('discussions')) {
        $wpdb->query(
            "DELETE FROM {$prefix}discussion_messages WHERE expires_at < NOW()"
        );
    }

    // آپدیت بازدید کل
    $novels = $wpdb->get_results(
        "SELECT post_id, SUM(view_count) as total
         FROM {$prefix}novel_views GROUP BY post_id"
    );
    foreach ($novels as $novel) {
        update_post_meta($novel->post_id, 'total_views', $novel->total);
    }

    // آپدیت بازدید هفتگی
    $week_ago = date('Y-m-d', strtotime('-7 days'));
    $weekly = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, SUM(view_count) as total
         FROM {$prefix}novel_views WHERE view_date >= %s GROUP BY post_id",
        $week_ago
    ));
    foreach ($weekly as $w) {
        update_post_meta($w->post_id, 'weekly_views', $w->total);
    }

    // آپدیت بازدید ماهانه
    $month_ago = date('Y-m-d', strtotime('-30 days'));
    $monthly = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, SUM(view_count) as total
         FROM {$prefix}novel_views WHERE view_date >= %s GROUP BY post_id",
        $month_ago
    ));
    foreach ($monthly as $m) {
        update_post_meta($m->post_id, 'monthly_views', $m->total);
    }
}

add_action('novel_cron_daily', 'novel_daily_tasks');
function novel_daily_tasks() {
    global $wpdb;
    $prefix = $wpdb->prefix;

    // قسمت روز
    if (novel_is_module_active('daily_highlight')) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $highlight = $wpdb->get_row($wpdb->prepare(
            "SELECT p.ID, p.post_author FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'likes_count'
             LEFT JOIN {$prefix}novel_views nv ON p.ID = nv.post_id AND nv.view_date = %s
             WHERE p.post_type = 'chapter' AND p.post_status = 'publish'
             ORDER BY (COALESCE(CAST(pm.meta_value AS UNSIGNED), 0) + COALESCE(nv.view_count, 0)) DESC
             LIMIT 1",
            $yesterday
        ));
        if ($highlight) {
            update_option('novel_daily_highlight', array(
                'chapter_id' => $highlight->ID,
                'date'       => current_time('Y-m-d'),
            ));
        }
    }

    // چالش هفتگی — شنبه
    if (novel_is_module_active('weekly_challenge') && date('w') == '6') {
        $types = array(
            array('type' => 'reading_count', 'target' => 10),
            array('type' => 'comment_count', 'target' => 5),
            array('type' => 'review_count', 'target' => 1),
            array('type' => 'quiz_count', 'target' => 1),
        );
        $challenge = $types[array_rand($types)];
        $week_start = date('Y-m-d');
        $week_end   = date('Y-m-d', strtotime('+6 days'));

        $wpdb->insert("{$prefix}weekly_challenges", array(
            'challenge_type' => $challenge['type'],
            'target_count'   => $challenge['target'],
            'prize_coins'    => 5,
            'week_start'     => $week_start,
            'week_end'       => $week_end,
        ));
    }

    // انقضای بنر
    if (novel_is_module_active('author_banners')) {
        $wpdb->query(
            "UPDATE {$prefix}author_banners SET status = 'expired'
             WHERE status = 'approved' AND expires_at IS NOT NULL AND expires_at < NOW()"
        );
    }

    // پاکسازی صف ایمیل قدیمی (۳۰ روز)
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$prefix}email_queue WHERE status IN ('sent','failed') AND scheduled_at < %s",
        date('Y-m-d H:i:s', strtotime('-30 days'))
    ));

    // پاکسازی اعلان قدیمی (۹۰ روز)
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$prefix}notifications WHERE created_at < %s",
        date('Y-m-d H:i:s', strtotime('-90 days'))
    ));
}

// ═══════════════════════════════════════
// بخش ۸: تنظیمات پیش‌فرض
// ═══════════════════════════════════════
function novel_set_default_options() {
    $defaults = novel_get_default_options();
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            update_option($key, $value);
        }
    }

    // تمام ماژول‌ها فعال
    if (get_option('novel_modules') === false) {
        $modules = array();
        foreach (novel_get_modules_list() as $key => $label) {
            $modules[$key] = 1;
        }
        update_option('novel_modules', $modules);
    }

    // بسته‌های سکه
    if (get_option('novel_coin_packages') === false) {
        update_option('novel_coin_packages', array(
            array('name' => 'پایه', 'coins' => 10, 'price' => 10000),
            array('name' => 'نقره‌ای', 'coins' => 50, 'price' => 45000),
            array('name' => 'طلایی', 'coins' => 100, 'price' => 80000),
            array('name' => 'الماسی', 'coins' => 300, 'price' => 200000),
        ));
    }
}

// ═══════════════════════════════════════
// بخش ۹: توابع سکه
// ═══════════════════════════════════════
function novel_get_balance($user_id) {
    $balance = get_user_meta($user_id, 'coin_balance', true);
    return $balance !== '' ? absint($balance) : 0;
}

function novel_add_coins($user_id, $amount, $type, $description = '', $expires_days = null) {
    if (!novel_is_module_active('coins')) return false;
    global $wpdb;
    $prefix = $wpdb->prefix;

    $amount   = absint($amount);
    $balance  = novel_get_balance($user_id);
    $new_bal  = $balance + $amount;
    $expires  = null;

    if ($expires_days) {
        $expires = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));
    } elseif ($expiry = novel_get_option('novel_coin_expiry_days', 30)) {
        $expires = date('Y-m-d H:i:s', strtotime("+{$expiry} days"));
    }

    $result = $wpdb->insert("{$prefix}user_coins", array(
        'user_id'     => $user_id,
        'amount'      => $amount,
        'balance'     => $new_bal,
        'type'        => $type,
        'description' => sanitize_text_field($description),
        'expires_at'  => $expires,
        'created_at'  => current_time('mysql'),
    ));

    if ($result) {
        update_user_meta($user_id, 'coin_balance', $new_bal);
        delete_transient('novel_balance_' . $user_id);
        return true;
    }
    return false;
}

function novel_spend_coins($user_id, $amount, $type, $description = '', $reference_id = null) {
    if (!novel_is_module_active('coins')) return false;
    global $wpdb;
    $prefix = $wpdb->prefix;

    $amount  = absint($amount);
    $balance = novel_get_balance($user_id);

    if ($balance < $amount) return false;

    $new_bal = $balance - $amount;

    $result = $wpdb->insert("{$prefix}user_coins", array(
        'user_id'      => $user_id,
        'amount'       => -$amount,
        'balance'      => $new_bal,
        'type'         => $type,
        'description'  => sanitize_text_field($description),
        'reference_id' => $reference_id ? absint($reference_id) : null,
        'created_at'   => current_time('mysql'),
    ));

    if ($result) {
        update_user_meta($user_id, 'coin_balance', $new_bal);
        delete_transient('novel_balance_' . $user_id);
        return true;
    }
    return false;
}

// ═══════════════════════════════════════
// بخش ۱۰: اعلان
// ═══════════════════════════════════════
function novel_send_notification($user_id, $type, $title, $message = '', $link = '') {
    if (!novel_is_module_active('notifications')) return;
    global $wpdb;
    $prefix = $wpdb->prefix;

    // بررسی تنظیمات کاربر
    $prefs = get_user_meta($user_id, 'novel_notification_prefs', true);
    if (is_array($prefs) && isset($prefs[$type]) && !$prefs[$type]) return;

    $wpdb->insert("{$prefix}notifications", array(
        'user_id'    => $user_id,
        'type'       => sanitize_text_field($type),
        'title'      => sanitize_text_field($title),
        'message'    => $message ? sanitize_text_field($message) : null,
        'link'       => $link ? esc_url_raw($link) : null,
        'is_read'    => 0,
        'created_at' => current_time('mysql'),
    ));

    delete_transient('novel_unread_count_' . $user_id);

    // ایمیل هم بفرستیم؟
    if (is_array($prefs) && !empty($prefs['email_' . $type])) {
        $user = get_userdata($user_id);
        if ($user) {
            novel_queue_email($user->user_email, $user_id, $title, $message);
        }
    }
}

function novel_queue_email($to_email, $user_id, $subject, $body) {
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}email_queue", array(
        'to_email'    => sanitize_email($to_email),
        'to_user_id'  => absint($user_id),
        'subject'     => sanitize_text_field($subject),
        'body'        => wp_kses_post($body),
        'status'      => 'pending',
        'scheduled_at'=> current_time('mysql'),
    ));
}

function novel_get_unread_count($user_id) {
    $cache_key = 'novel_unread_count_' . $user_id;
    $count = get_transient($cache_key);
    if ($count !== false) return (int)$count;

    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$wpdb->prefix}notifications WHERE user_id = %d AND is_read = 0",
        $user_id
    ));
    $count = absint($count);
    set_transient($cache_key, $count, 30);
    return $count;
}

// ═══════════════════════════════════════
// بخش ۱۱: Error Logging
// ═══════════════════════════════════════
function novel_log($message, $level = 'info', $context = array()) {
    if (WP_DEBUG && WP_DEBUG_LOG) {
        error_log(sprintf(
            '[Novel %s] [%s] %s %s',
            NOVEL_VERSION,
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        ));
    }
}

// ═══════════════════════════════════════
// بخش ۱۲: VIP Access Check
// ═══════════════════════════════════════
function novel_user_has_subscription($user_id) {
    if (!function_exists('rcp_get_customer_by_user_id')) return false;
    $customer = rcp_get_customer_by_user_id($user_id);
    if (!$customer) return false;
    return rcp_customer_has_active_membership($customer);
}

function novel_user_can_read_chapter($user_id, $chapter_id) {
    $is_vip = get_post_meta($chapter_id, 'is_vip', true);
    if (!$is_vip || $is_vip === '0') return true;

    if (!$user_id) return false;

    // اشتراک RCP
    if (novel_user_has_subscription($user_id)) return true;

    // خرید جداگانه
    global $wpdb;
    $purchased = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}chapter_purchases WHERE user_id = %d AND chapter_id = %d",
        $user_id, $chapter_id
    ));

    return (bool)$purchased;
}

// ═══════════════════════════════════════
// بخش ۱۳: ثبت بازدید
// ═══════════════════════════════════════
function novel_record_view($post_id) {
    global $wpdb;
    $today = current_time('Y-m-d');

    $wpdb->query($wpdb->prepare(
        "INSERT INTO {$wpdb->prefix}novel_views (post_id, view_date, view_count)
         VALUES (%d, %s, 1)
         ON DUPLICATE KEY UPDATE view_count = view_count + 1",
        $post_id, $today
    ));
}