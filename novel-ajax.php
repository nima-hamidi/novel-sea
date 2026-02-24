<?php
/**
 * Novel AJAX Handlers
 * @package NovelTheme
 */

if (!defined('ABSPATH')) exit;

// ═══════════════════════════════════════
// بخش AUTH AJAX
// ═══════════════════════════════════════

// ── ورود ──
add_action('wp_ajax_nopriv_novel_login', 'novel_ajax_login');
function novel_ajax_login() {
    check_ajax_referer('novel_login', 'novel_login_nonce');

    // Honeypot
    if (!empty($_POST['website'])) {
        wp_send_json_error(array('message' => 'درخواست نامعتبر.'));
    }

    // Anti-bot timing
    $start = isset($_POST['novel_form_start']) ? absint($_POST['novel_form_start']) : 0;
    if ($start && (time() - $start) < 3) {
        wp_send_json_error(array('message' => 'لطفاً کمی صبر کنید.'));
    }

    // Rate limit
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    $rate_key = 'novel_login_attempts_' . md5($ip);
    $attempts = (int)get_transient($rate_key);
    if ($attempts >= 5) {
        wp_send_json_error(array('message' => 'تلاش‌های زیاد. ۱۵ دقیقه صبر کنید.'));
    }

    $login    = sanitize_text_field($_POST['login']);
    $password = $_POST['password'];
    $remember = !empty($_POST['remember']);

    if (empty($login) || empty($password)) {
        wp_send_json_error(array('message' => 'تمام فیلدها الزامی هستند.'));
    }

    $user = wp_authenticate($login, $password);

    if (is_wp_error($user)) {
        set_transient($rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
        wp_send_json_error(array('message' => 'ایمیل/نام‌کاربری یا رمز عبور اشتباه است.'));
    }

    // Clear rate limit
    delete_transient($rate_key);

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);

    $redirect = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : novel_get_dashboard_url();

    wp_send_json_success(array(
        'message'  => 'ورود موفق! در حال انتقال...',
        'redirect' => $redirect,
    ));
}

// ── ثبت‌نام ──
add_action('wp_ajax_nopriv_novel_register', 'novel_ajax_register');
function novel_ajax_register() {
    check_ajax_referer('novel_register', 'novel_register_nonce');

    // Honeypot
    if (!empty($_POST['website'])) {
        wp_send_json_error(array('message' => 'درخواست نامعتبر.'));
    }

    // Anti-bot
    $start = isset($_POST['novel_form_start']) ? absint($_POST['novel_form_start']) : 0;
    if ($start && (time() - $start) < 3) {
        wp_send_json_error(array('message' => 'لطفاً کمی صبر کنید.'));
    }

    // Rate limit
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    $rate_key = 'novel_register_rate_' . md5($ip);
    $attempts = (int)get_transient($rate_key);
    if ($attempts >= 3) {
        wp_send_json_error(array('message' => 'تعداد ثبت‌نام زیاد. ۱ ساعت صبر کنید.'));
    }

    $display_name = sanitize_text_field($_POST['display_name']);
    $email        = sanitize_email($_POST['email']);
    $password     = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];

    // Validation
    if (empty($display_name) || empty($email) || empty($password) || empty($pass_confirm)) {
        wp_send_json_error(array('message' => 'تمام فیلدها الزامی هستند.'));
    }

    if (!preg_match('/^[\x{0600}-\x{06FF}\sa-zA-Z0-9_]{3,20}$/u', $display_name)) {
        wp_send_json_error(array('message' => 'نام نمایشی: ۳ تا ۲۰ کاراکتر (فارسی/انگلیسی/عدد)'));
    }

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'ایمیل نامعتبر است.'));
    }

    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'این ایمیل قبلاً ثبت شده. ورود؟'));
    }

    if (username_exists($display_name)) {
        wp_send_json_error(array('message' => 'این نام قبلاً گرفته شده.'));
    }

    if (mb_strlen($password) < 8) {
        wp_send_json_error(array('message' => 'رمز عبور حداقل ۸ کاراکتر باشد.'));
    }

    if ($password !== $pass_confirm) {
        wp_send_json_error(array('message' => 'رمزها مطابقت ندارند.'));
    }

    if (empty($_POST['agree_rules'])) {
        wp_send_json_error(array('message' => 'پذیرش قوانین الزامی است.'));
    }

    // ساخت حساب
    $user_login = sanitize_user(str_replace(' ', '_', $display_name), true);
    // اگر تکراری → اضافه عدد
    $base_login = $user_login;
    $counter = 1;
    while (username_exists($user_login)) {
        $user_login = $base_login . '_' . $counter;
        $counter++;
    }

    $user_id = wp_insert_user(array(
        'user_login'   => $user_login,
        'user_email'   => $email,
        'user_pass'    => $password,
        'display_name' => $display_name,
        'role'         => 'subscriber',
    ));

    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => 'خطا در ساخت حساب. لطفاً دوباره تلاش کنید.'));
    }

    // تأیید ایمیل
    $token = wp_generate_password(32, false);
    update_user_meta($user_id, 'email_verified', 0);
    update_user_meta($user_id, 'email_verify_token', $token);
    update_user_meta($user_id, 'email_verify_expiry', time() + DAY_IN_SECONDS);
    update_user_meta($user_id, 'novel_comment_total', 0);
    update_user_meta($user_id, 'coin_balance', 0);

    // ارسال ایمیل تأیید
    $verify_url = add_query_arg(array('novel_verify_email' => $token), home_url('/'));
    $body = novel_render_verify_email($display_name, $verify_url);
    wp_mail($email, 'تأیید ایمیل — ' . get_bloginfo('name'), $body, array('Content-Type: text/html; charset=UTF-8'));

    // Rate limit
    set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);

    wp_send_json_success(array(
        'message' => 'حساب شما ساخته شد! لینک تأیید به ایمیل شما ارسال شد. لطفاً ایمیل (و پوشه spam) را بررسی کنید.',
    ));
}

// ── بررسی یکتایی نام ──
add_action('wp_ajax_nopriv_novel_check_username', 'novel_ajax_check_username');
add_action('wp_ajax_novel_check_username', 'novel_ajax_check_username');
function novel_ajax_check_username() {
    check_ajax_referer('novel_nonce', 'nonce');
    $name = sanitize_text_field($_POST['display_name']);

    if (mb_strlen($name) < 3) {
        wp_send_json_error(array('message' => 'حداقل ۳ کاراکتر'));
    }
    if (mb_strlen($name) > 20) {
        wp_send_json_error(array('message' => 'حداکثر ۲۰ کاراکتر'));
    }

    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->users} WHERE display_name = %s LIMIT 1",
        $name
    ));

    if ($exists) {
        wp_send_json_error(array('message' => 'این نام قبلاً گرفته شده'));
    }

    wp_send_json_success(array('message' => 'نام در دسترس است'));
}

// ── بررسی یکتایی ایمیل ──
add_action('wp_ajax_nopriv_novel_check_email', 'novel_ajax_check_email');
function novel_ajax_check_email() {
    check_ajax_referer('novel_nonce', 'nonce');
    $email = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'ایمیل نامعتبر'));
    }

    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'این ایمیل قبلاً ثبت شده'));
    }

    wp_send_json_success(array('message' => 'ایمیل آزاد است'));
}

// ── فراموشی رمز ──
add_action('wp_ajax_nopriv_novel_forgot_password', 'novel_ajax_forgot_password');
function novel_ajax_forgot_password() {
    check_ajax_referer('novel_forgot', 'novel_forgot_nonce');

    $email = sanitize_email($_POST['forgot_email']);
    if (!is_email($email) || !email_exists($email)) {
        // پیام یکسان (امنیت)
        wp_send_json_success(array('message' => 'اگر حسابی با این ایمیل وجود دارد، لینک بازیابی ارسال شد.'));
    }

    $user = get_user_by('email', $email);
    if ($user) {
        retrieve_password($user->user_login);
    }

    wp_send_json_success(array('message' => 'اگر حسابی با این ایمیل وجود دارد، لینک بازیابی ارسال شد.'));
}

// ── ارسال مجدد تأیید ایمیل ──
add_action('wp_ajax_novel_resend_verify', 'novel_ajax_resend_verify');
function novel_ajax_resend_verify() {
    check_ajax_referer('novel_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error(array('message' => 'وارد شوید.'));

    if (novel_is_email_verified($user_id)) {
        wp_send_json_error(array('message' => 'ایمیل شما قبلاً تأیید شده.'));
    }

    // Cooldown
    $cd_key = 'novel_resend_verify_' . $user_id;
    if (get_transient($cd_key)) {
        wp_send_json_error(array('message' => 'لطفاً ۲ دقیقه صبر کنید.'));
    }

    $token = wp_generate_password(32, false);
    update_user_meta($user_id, 'email_verify_token', $token);
    update_user_meta($user_id, 'email_verify_expiry', time() + DAY_IN_SECONDS);

    $user = get_userdata($user_id);
    $verify_url = add_query_arg(array('novel_verify_email' => $token), home_url('/'));
    $body = novel_render_verify_email($user->display_name, $verify_url);
    wp_mail($user->user_email, 'تأیید ایمیل — ' . get_bloginfo('name'), $body, array('Content-Type: text/html; charset=UTF-8'));

    set_transient($cd_key, 1, 2 * MINUTE_IN_SECONDS);

    wp_send_json_success(array('message' => 'لینک جدید ارسال شد. صندوق ایمیل + spam را بررسی کنید.'));
}

// ── تأیید ایمیل (init hook) ──
add_action('init', 'novel_handle_email_verify');
function novel_handle_email_verify() {
    if (!isset($_GET['novel_verify_email'])) return;

    $token = sanitize_text_field($_GET['novel_verify_email']);
    if (empty($token)) return;

    global $wpdb;
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'email_verify_token' AND meta_value = %s",
        $token
    ));

    if (!$user_id) {
        wp_safe_redirect(add_query_arg('verify_error', 'invalid', home_url('/')));
        exit;
    }

    $expiry = get_user_meta($user_id, 'email_verify_expiry', true);
    if ($expiry && time() > (int)$expiry) {
        wp_safe_redirect(add_query_arg('verify_error', 'expired', home_url('/')));
        exit;
    }

    update_user_meta($user_id, 'email_verified', 1);
    delete_user_meta($user_id, 'email_verify_token');
    delete_user_meta($user_id, 'email_verify_expiry');

    // لاگین خودکار
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_safe_redirect(add_query_arg('verified', '1', novel_get_dashboard_url()));
    exit;
}

// ── قالب ایمیل تأیید ──
function novel_render_verify_email($name, $url) {
    $primary = novel_get_option('novel_primary_color', '#7c3aed');
    $site_name = get_bloginfo('name');

    ob_start();
    ?>
    <!DOCTYPE html>
    <html dir="rtl" lang="fa">
    <head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;background:#f3f4f6;font-family:Tahoma,sans-serif;direction:rtl">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
        <tr><td style="background:<?php echo esc_attr($primary); ?>;padding:32px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px"><?php echo esc_html($site_name); ?></h1>
        </td></tr>
        <tr><td style="padding:40px 32px">
            <h2 style="margin:0 0 16px;color:#333;font-size:18px">سلام <?php echo esc_html($name); ?>! 👋</h2>
            <p style="color:#666;line-height:1.8;font-size:14px">از ثبت‌نام شما در <?php echo esc_html($site_name); ?> متشکریم. برای تأیید ایمیل خود روی دکمه زیر کلیک کنید:</p>
            <div style="text-align:center;margin:32px 0">
                <a href="<?php echo esc_url($url); ?>" style="display:inline-block;background:<?php echo esc_attr($primary); ?>;color:#fff;padding:14px 40px;border-radius:12px;text-decoration:none;font-size:16px;font-weight:bold">✅ تأیید ایمیل</a>
            </div>
            <p style="color:#999;font-size:12px;line-height:1.6">این لینک ۲۴ ساعت اعتبار دارد. اگر شما ثبت‌نام نکرده‌اید، این ایمیل را نادیده بگیرید.</p>
        </td></tr>
        <tr><td style="background:#f9fafb;padding:20px 32px;text-align:center">
            <p style="margin:0;color:#aaa;font-size:11px">© <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?></p>
        </td></tr>
    </table>
    </body></html>
    <?php
    return ob_get_clean();
}

// ═══════════════════════════════════════
// بخش LIVE SEARCH AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_live_search', 'novel_ajax_live_search');
add_action('wp_ajax_nopriv_novel_live_search', 'novel_ajax_live_search');
function novel_ajax_live_search() {
    check_ajax_referer('novel_nonce', 'nonce');

    $q = sanitize_text_field($_POST['q']);
    if (mb_strlen($q) < 2) {
        wp_send_json_success(array('html' => ''));
    }

    // جستجوی عنوان فارسی
    $results = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => 5,
        's'              => $q,
        'post_status'    => 'publish',
    ));

    // جستجوی نام انگلیسی
    if (count($results) < 5) {
        $meta_results = get_posts(array(
            'post_type'      => 'novel',
            'posts_per_page' => 5 - count($results),
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => 'novel_english_name',
                    'value'   => $q,
                    'compare' => 'LIKE',
                ),
            ),
            'post__not_in'   => wp_list_pluck($results, 'ID'),
        ));
        $results = array_merge($results, $meta_results);
    }

    if (empty($results)) {
        wp_send_json_success(array('html' => '<div class="novel-search-empty">' . esc_html(NovelData['strings']['no_results'] ?? 'نتیجه‌ای یافت نشد.') . '</div>'));
    }

    $html = '';
    foreach ($results as $post) {
        $thumb = get_the_post_thumbnail_url($post->ID, 'novel-thumb');
        $type  = get_post_meta($post->ID, 'novel_type', true);
        $type_label = $type === 'ln' ? 'LN' : 'WN';
        $rating_sum   = (float)get_post_meta($post->ID, 'novel_rating_sum', true);
        $rating_count = (int)get_post_meta($post->ID, 'novel_rating_count', true);
        $avg = $rating_count > 0 ? round($rating_sum / $rating_count, 1) : 0;
        $genres = wp_get_post_terms($post->ID, 'genre', array('fields' => 'names'));
        $genre_str = !empty($genres) ? implode('، ', array_slice($genres, 0, 2)) : '';

        $html .= '<a href="' . esc_url(get_permalink($post->ID)) . '" class="novel-search-item">';
        $html .= '<div class="novel-search-thumb">';
        if ($thumb) {
            $html .= '<img src="' . esc_url($thumb) . '" width="40" height="56" alt="" loading="lazy">';
        }
        $html .= '</div>';
        $html .= '<div class="novel-search-info">';
        $html .= '<strong>' . esc_html($post->post_title) . '</strong>';
        $html .= '<small>' . esc_html($type_label);
        if ($genre_str) $html .= ' | ' . esc_html($genre_str);
        $html .= '</small>';
        $html .= '</div>';
        if ($avg > 0) {
            $html .= '<span class="novel-search-rating">★ ' . novel_to_persian($avg) . '</span>';
        }
        $html .= '</a>';
    }

    $html .= '<a href="' . esc_url(add_query_arg(array('s' => $q, 'post_type' => 'novel'), home_url('/'))) . '" class="novel-search-more">🔍 نتایج بیشتر برای «' . esc_html($q) . '»</a>';

    wp_send_json_success(array('html' => $html));
}

// ═══════════════════════════════════════
// بخش COMMENT VOTE AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_comment_vote', 'novel_ajax_comment_vote');
function novel_ajax_comment_vote() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'برای رأی‌دادن وارد شوید.'));
    }

    $user_id    = get_current_user_id();
    $comment_id = absint($_POST['comment_id']);
    $vote       = intval($_POST['vote']); // 1 or -1

    if (!in_array($vote, array(1, -1))) {
        wp_send_json_error(array('message' => 'رأی نامعتبر.'));
    }

    if (!novel_is_email_verified($user_id)) {
        wp_send_json_error(array('message' => 'ابتدا ایمیل خود را تأیید کنید.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'comment_votes';

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, vote FROM {$table} WHERE comment_id = %d AND user_id = %d",
        $comment_id, $user_id
    ));

    if ($existing) {
        if ((int)$existing->vote === $vote) {
            // لغو رأی
            $wpdb->delete($table, array('id' => $existing->id), array('%d'));
        } else {
            // تغییر رأی
            $wpdb->update($table, array('vote' => $vote), array('id' => $existing->id), array('%d'), array('%d'));
        }
    } else {
        // رأی جدید
        $wpdb->insert($table, array(
            'comment_id' => $comment_id,
            'user_id'    => $user_id,
            'vote'       => $vote,
            'created_at' => current_time('mysql'),
        ));
    }

    // بروزرسانی denormalized
    $likes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE comment_id = %d AND vote = 1",
        $comment_id
    ));
    $dislikes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE comment_id = %d AND vote = -1",
        $comment_id
    ));

    update_comment_meta($comment_id, 'likes_count', $likes);
    update_comment_meta($comment_id, 'dislikes_count', $dislikes);

    // رأی فعلی کاربر
    $user_vote = $wpdb->get_var($wpdb->prepare(
        "SELECT vote FROM {$table} WHERE comment_id = %d AND user_id = %d",
        $comment_id, $user_id
    ));

    wp_send_json_success(array(
        'likes'     => $likes,
        'dislikes'  => $dislikes,
        'user_vote' => $user_vote ? (int)$user_vote : 0,
    ));
}

// ═══════════════════════════════════════
// بخش COMMENT REACTION AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_comment_reaction', 'novel_ajax_comment_reaction');
function novel_ajax_comment_reaction() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in() || !novel_is_email_verified(get_current_user_id())) {
        wp_send_json_error(array('message' => 'دسترسی ندارید.'));
    }

    $user_id    = get_current_user_id();
    $comment_id = absint($_POST['comment_id']);
    $reaction   = sanitize_text_field($_POST['reaction']);

    $valid = array('love', 'shocked', 'sad', 'angry', 'fire');
    if (!in_array($reaction, $valid)) {
        wp_send_json_error(array('message' => 'ری‌اکشن نامعتبر.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'comment_reactions';

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, reaction FROM {$table} WHERE comment_id = %d AND user_id = %d",
        $comment_id, $user_id
    ));

    if ($existing) {
        if ($existing->reaction === $reaction) {
            $wpdb->delete($table, array('id' => $existing->id), array('%d'));
        } else {
            $wpdb->update($table, array('reaction' => $reaction), array('id' => $existing->id), array('%s'), array('%d'));
        }
    } else {
        $wpdb->insert($table, array(
            'comment_id' => $comment_id,
            'user_id'    => $user_id,
            'reaction'   => $reaction,
            'created_at' => current_time('mysql'),
        ));
    }

    // شمارش ری‌اکشن‌ها
    $reactions = $wpdb->get_results($wpdb->prepare(
        "SELECT reaction, COUNT(id) as cnt FROM {$table} WHERE comment_id = %d GROUP BY reaction",
        $comment_id
    ), OBJECT_K);

    $result = array();
    foreach ($reactions as $r => $row) {
        $result[$r] = (int)$row->cnt;
    }

    $user_reaction = $wpdb->get_var($wpdb->prepare(
        "SELECT reaction FROM {$table} WHERE comment_id = %d AND user_id = %d",
        $comment_id, $user_id
    ));

    wp_send_json_success(array(
        'reactions'     => $result,
        'user_reaction' => $user_reaction ?: '',
    ));
}

// ═══════════════════════════════════════
// بخش CHAPTER VOTE AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_chapter_vote', 'novel_ajax_chapter_vote');
function novel_ajax_chapter_vote() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in() || !novel_is_email_verified(get_current_user_id())) {
        wp_send_json_error(array('message' => 'دسترسی ندارید.'));
    }

    $user_id    = get_current_user_id();
    $chapter_id = absint($_POST['chapter_id']);
    $vote       = intval($_POST['vote']);

    if (!in_array($vote, array(1, -1))) {
        wp_send_json_error(array('message' => 'رأی نامعتبر.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'chapter_votes';

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, vote FROM {$table} WHERE chapter_id = %d AND user_id = %d",
        $chapter_id, $user_id
    ));

    if ($existing) {
        if ((int)$existing->vote === $vote) {
            $wpdb->delete($table, array('id' => $existing->id), array('%d'));
        } else {
            $wpdb->update($table, array('vote' => $vote), array('id' => $existing->id), array('%d'), array('%d'));
        }
    } else {
        $wpdb->insert($table, array(
            'chapter_id' => $chapter_id,
            'user_id'    => $user_id,
            'vote'       => $vote,
            'created_at' => current_time('mysql'),
        ));
    }

    $likes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE chapter_id = %d AND vote = 1", $chapter_id
    ));
    $dislikes = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE chapter_id = %d AND vote = -1", $chapter_id
    ));

    update_post_meta($chapter_id, 'likes_count', $likes);
    update_post_meta($chapter_id, 'dislikes_count', $dislikes);

    $user_vote = $wpdb->get_var($wpdb->prepare(
        "SELECT vote FROM {$table} WHERE chapter_id = %d AND user_id = %d", $chapter_id, $user_id
    ));

    $total = $likes + $dislikes;
    $percent = $total > 0 ? round(($likes / $total) * 100) : 0;

    wp_send_json_success(array(
        'likes'    => $likes,
        'dislikes' => $dislikes,
        'percent'  => $percent,
        'user_vote'=> $user_vote ? (int)$user_vote : 0,
    ));
}

// ═══════════════════════════════════════
// بخش NOVEL RATING AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_rate', 'novel_ajax_rate_novel');
function novel_ajax_rate_novel() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in() || !novel_is_email_verified(get_current_user_id())) {
        wp_send_json_error(array('message' => 'دسترسی ندارید.'));
    }

    $user_id  = get_current_user_id();
    $novel_id = absint($_POST['novel_id']);
    $rating   = absint($_POST['rating']);

    if ($rating < 1 || $rating > 5) {
        wp_send_json_error(array('message' => 'امتیاز نامعتبر.'));
    }

    $meta_key  = 'novel_user_rating_' . $user_id;
    $prev      = get_post_meta($novel_id, $meta_key, true);
    $sum       = (float)get_post_meta($novel_id, 'novel_rating_sum', true);
    $count     = (int)get_post_meta($novel_id, 'novel_rating_count', true);

    if ($prev) {
        // تغییر امتیاز
        $sum = $sum - (float)$prev + $rating;
    } else {
        // امتیاز جدید
        $sum   += $rating;
        $count += 1;
    }

    update_post_meta($novel_id, $meta_key, $rating);
    update_post_meta($novel_id, 'novel_rating_sum', $sum);
    update_post_meta($novel_id, 'novel_rating_count', $count);

    $avg = $count > 0 ? round($sum / $count, 1) : 0;

    wp_send_json_success(array(
        'avg'       => $avg,
        'count'     => $count,
        'my_rating' => $rating,
    ));
}

// ═══════════════════════════════════════
// بخش NOVEL FOLLOW AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_follow_novel', 'novel_ajax_follow_novel');
function novel_ajax_follow_novel() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'وارد شوید.'));
    }

    $user_id  = get_current_user_id();
    $novel_id = absint($_POST['novel_id']);

    global $wpdb;
    $table = $wpdb->prefix . 'novel_follows';

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE user_id = %d AND novel_id = %d", $user_id, $novel_id
    ));

    if ($existing) {
        $wpdb->delete($table, array('id' => $existing), array('%d'));
        $is_followed = false;
    } else {
        $wpdb->insert($table, array(
            'user_id'    => $user_id,
            'novel_id'   => $novel_id,
            'notify'     => 1,
            'created_at' => current_time('mysql'),
        ));
        $is_followed = true;
    }

    // شمارنده
    $count = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE novel_id = %d", $novel_id
    ));
    update_post_meta($novel_id, 'followers_count', $count);

    wp_send_json_success(array(
        'is_followed'     => $is_followed,
        'followers_count' => $count,
    ));
}

// ═══════════════════════════════════════
// بخش USER FOLLOW AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_follow_user', 'novel_ajax_follow_user');
function novel_ajax_follow_user() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'وارد شوید.'));
    }

    $follower_id  = get_current_user_id();
    $following_id = absint($_POST['user_id']);

    if ($follower_id === $following_id) {
        wp_send_json_error(array('message' => 'نمی‌توانید خودتان را فالو کنید.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_follows';

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE follower_id = %d AND following_id = %d",
        $follower_id, $following_id
    ));

    if ($existing) {
        $wpdb->delete($table, array('id' => $existing), array('%d'));
        $is_followed = false;
    } else {
        $wpdb->insert($table, array(
            'follower_id'  => $follower_id,
            'following_id' => $following_id,
            'created_at'   => current_time('mysql'),
        ));
        $is_followed = true;

        // اعلان
        $follower_name = get_userdata($follower_id)->display_name;
        novel_send_notification(
            $following_id,
            'new_follower',
            $follower_name . ' شما را دنبال کرد',
            '',
            get_author_posts_url($follower_id)
        );
    }

    // شمارنده
    $count = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE following_id = %d", $following_id
    ));
    update_user_meta($following_id, 'followers_count', $count);

    $following_count = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE follower_id = %d", $follower_id
    ));
    update_user_meta($follower_id, 'following_count', $following_count);

    wp_send_json_success(array(
        'is_followed'     => $is_followed,
        'followers_count' => $count,
    ));
}

// ═══════════════════════════════════════
// بخش LIBRARY AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_library_action', 'novel_ajax_library_action');
function novel_ajax_library_action() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'وارد شوید.'));
    }

    $user_id   = get_current_user_id();
    $novel_id  = absint($_POST['novel_id']);
    $list_type = sanitize_text_field($_POST['list_type']);
    $action    = sanitize_text_field($_POST['lib_action']); // add, remove

    $valid_types = array('reading', 'plan', 'completed', 'dropped', 'on_hold');
    if (!in_array($list_type, $valid_types) && $action !== 'remove') {
        wp_send_json_error(array('message' => 'نوع لیست نامعتبر.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_library';

    if ($action === 'remove') {
        $wpdb->delete($table, array('user_id' => $user_id, 'novel_id' => $novel_id));
        wp_send_json_success(array('message' => 'از کتابخانه حذف شد.', 'list_type' => ''));
    }

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE user_id = %d AND novel_id = %d", $user_id, $novel_id
    ));

    $labels = array(
        'reading'   => 'در حال خواندن',
        'plan'      => 'می‌خوام بخوانم',
        'completed' => 'تکمیل شده',
        'dropped'   => 'رها شده',
        'on_hold'   => 'نگه‌داشته',
    );

    if ($existing) {
        $wpdb->update($table, array('list_type' => $list_type), array('id' => $existing));
    } else {
        $wpdb->insert($table, array(
            'user_id'   => $user_id,
            'novel_id'  => $novel_id,
            'list_type' => $list_type,
            'progress'  => 0,
        ));
    }

    // شمارنده
    $bm_count = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(id) FROM {$table} WHERE novel_id = %d", $novel_id
    ));
    update_post_meta($novel_id, 'bookmarks_count', $bm_count);

    wp_send_json_success(array(
        'message'   => 'به «' . $labels[$list_type] . '» اضافه شد ✅',
        'list_type' => $list_type,
    ));
}

// ═══════════════════════════════════════
// بخش REPORT AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_report', 'novel_ajax_report');
function novel_ajax_report() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'وارد شوید.'));
    }

    $user_id       = get_current_user_id();
    $reported_type = sanitize_text_field($_POST['reported_type']);
    $reported_id   = absint($_POST['reported_id']);
    $reason        = sanitize_text_field($_POST['reason']);
    $description   = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

    $valid_types = array('comment', 'chapter', 'user');
    if (!in_array($reported_type, $valid_types)) {
        wp_send_json_error(array('message' => 'نوع گزارش نامعتبر.'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'reports';

    // بررسی تکراری
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE reporter_id = %d AND reported_type = %s AND reported_id = %d",
        $user_id, $reported_type, $reported_id
    ));

    if ($exists) {
        wp_send_json_error(array('message' => 'قبلاً گزارش داده‌اید.'));
    }

    $wpdb->insert($table, array(
        'reporter_id'   => $user_id,
        'reported_type' => $reported_type,
        'reported_id'   => $reported_id,
        'reason'        => $reason,
        'description'   => $description,
        'status'        => 'pending',
        'created_at'    => current_time('mysql'),
    ));

    wp_send_json_success(array('message' => 'گزارش شما ثبت شد و بررسی خواهد شد ✅'));
}

// ═══════════════════════════════════════
// بخش NOTIFICATION AJAX
// ═══════════════════════════════════════
add_action('wp_ajax_novel_get_notifications', 'novel_ajax_get_notifications');
function novel_ajax_get_notifications() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) wp_send_json_error();

    global $wpdb;
    $user_id = get_current_user_id();
    $prefix  = $wpdb->prefix;

    $notifs = $wpdb->get_results($wpdb->prepare(
        "SELECT id, type, title, link, is_read, created_at
         FROM {$prefix}notifications
         WHERE user_id = %d
         ORDER BY created_at DESC LIMIT 10",
        $user_id
    ));

    $icons = array(
        'new_chapter'     => '📖', 'comment_reply' => '💬', 'comment_like' => '👍',
        'mention'         => '📣', 'new_follower'  => '❤', 'coin_received' => '🪙',
        'achievement'     => '🏆', 'quiz_started'  => '🎮', 'system'        => '📢',
    );

    $html = '';
    foreach ($notifs as $n) {
        $icon  = isset($icons[$n->type]) ? $icons[$n->type] : '🔔';
        $class = $n->is_read ? '' : 'novel-notif-unread';
        $time  = novel_time_ago($n->created_at);
        $link  = $n->link ? esc_url($n->link) : '#';

        $html .= '<a href="' . $link . '" class="novel-notif-item ' . $class . '" data-id="' . (int)$n->id . '">';
        $html .= '<span class="novel-notif-icon">' . $icon . '</span>';
        $html .= '<div class="novel-notif-content"><span>' . esc_html($n->title) . '</span><small>' . esc_html($time) . '</small></div>';
        $html .= '</a>';
    }

    if (empty($notifs)) {
        $html = '<div class="novel-notif-empty">اعلان جدیدی ندارید 📭</div>';
    }

    wp_send_json_success(array(
        'html'  => $html,
        'count' => novel_get_unread_count($user_id),
    ));
}

add_action('wp_ajax_novel_mark_notifications_read', 'novel_ajax_mark_notifications_read');
function novel_ajax_mark_notifications_read() {
    check_ajax_referer('novel_nonce', 'nonce');

    if (!is_user_logged_in()) wp_send_json_error();

    global $wpdb;
    $user_id = get_current_user_id();

    $wpdb->update(
        $wpdb->prefix . 'notifications',
        array('is_read' => 1),
        array('user_id' => $user_id, 'is_read' => 0),
        array('%d'),
        array('%d', '%d')
    );

    delete_transient('novel_unread_count_' . $user_id);

    wp_send_json_success(array('count' => 0));
}

add_action('wp_ajax_novel_unread_count', 'novel_ajax_unread_count');
function novel_ajax_unread_count() {
    check_ajax_referer('novel_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();
    wp_send_json_success(array('count' => novel_get_unread_count(get_current_user_id())));
}