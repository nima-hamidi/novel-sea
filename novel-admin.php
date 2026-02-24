<?php
/**
 * Novel Admin — پنل ادمین
 * @package NovelTheme
 */
if (!defined('ABSPATH')) exit;

// ═══ منو اصلی ═══
add_action('admin_menu', 'novel_admin_menu');
function novel_admin_menu() {
    add_menu_page('تنظیمات ناول', '⚙️ تنظیمات ناول', 'manage_options', 'novel-settings', 'novel_settings_page', 'dashicons-admin-generic', 3);
    add_submenu_page('novel-settings', 'تنظیمات عمومی', 'عمومی', 'manage_options', 'novel-settings', 'novel_settings_page');
    add_submenu_page('novel-settings', 'ماژول‌ها', 'ماژول‌ها', 'manage_options', 'novel-modules-page', 'novel_modules_page');
    add_submenu_page('novel-settings', 'گزارش‌ها', '🚩 گزارش‌ها', 'manage_options', 'novel-reports', 'novel_reports_page');
}

// ═══ صفحه تنظیمات عمومی ═══
function novel_settings_page() {
    if (isset($_POST['novel_settings_save']) && wp_verify_nonce($_POST['_novel_admin_nonce'], 'novel_admin_settings')) {
        $fields = array(
            'novel_primary_color', 'novel_site_description', 'novel_social_telegram',
            'novel_social_instagram', 'novel_social_twitter', 'novel_banner_text',
            'novel_banner_color', 'novel_banner_link', 'novel_maintenance_message',
            'novel_bad_words',
        );
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                update_option($f, sanitize_text_field($_POST[$f]));
            }
        }
        $checkboxes = array('novel_banner_active', 'novel_maintenance', 'novel_user_writing');
        foreach ($checkboxes as $cb) {
            update_option($cb, isset($_POST[$cb]) ? 1 : 0);
        }
        $numbers = array('novel_rules_page', 'novel_comment_rules_page', 'novel_coin_expiry_days',
            'novel_author_share_percent', 'novel_comment_min_chars', 'novel_comment_max_chars',
            'novel_review_min_words', 'novel_edit_time_minutes', 'novel_max_pins');
        foreach ($numbers as $n) {
            if (isset($_POST[$n])) update_option($n, absint($_POST[$n]));
        }
        echo '<div class="notice notice-success"><p>تنظیمات ذخیره شد ✅</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>⚙️ تنظیمات ناول</h1>
        <form method="post">
            <?php wp_nonce_field('novel_admin_settings', '_novel_admin_nonce'); ?>
            <table class="form-table">
                <tr><th>رنگ اصلی</th><td><input type="color" name="novel_primary_color" value="<?php echo esc_attr(novel_get_option('novel_primary_color')); ?>"></td></tr>
                <tr><th>توضیح سایت</th><td><input type="text" name="novel_site_description" value="<?php echo esc_attr(novel_get_option('novel_site_description')); ?>" class="regular-text"></td></tr>
                <tr><th>لینک تلگرام</th><td><input type="url" name="novel_social_telegram" value="<?php echo esc_attr(novel_get_option('novel_social_telegram')); ?>" class="regular-text"></td></tr>
                <tr><th>لینک اینستاگرام</th><td><input type="url" name="novel_social_instagram" value="<?php echo esc_attr(novel_get_option('novel_social_instagram')); ?>" class="regular-text"></td></tr>
                <tr><th>لینک X/توییتر</th><td><input type="url" name="novel_social_twitter" value="<?php echo esc_attr(novel_get_option('novel_social_twitter')); ?>" class="regular-text"></td></tr>
                <tr><th>صفحه قوانین</th><td><?php wp_dropdown_pages(array('name'=>'novel_rules_page','selected'=>novel_get_option('novel_rules_page'),'show_option_none'=>'انتخاب کنید')); ?></td></tr>
                <tr><th>صفحه قوانین دیدگاه</th><td><?php wp_dropdown_pages(array('name'=>'novel_comment_rules_page','selected'=>novel_get_option('novel_comment_rules_page'),'show_option_none'=>'انتخاب کنید')); ?></td></tr>
                <tr><th>نویسندگی کاربر</th><td><label><input type="checkbox" name="novel_user_writing" value="1" <?php checked(novel_get_option('novel_user_writing')); ?>> فعال</label></td></tr>

                <tr><th colspan="2"><h2>بنر اطلاعیه</h2></th></tr>
                <tr><th>فعال</th><td><label><input type="checkbox" name="novel_banner_active" value="1" <?php checked(novel_get_option('novel_banner_active')); ?>> نمایش بنر</label></td></tr>
                <tr><th>متن بنر</th><td><input type="text" name="novel_banner_text" value="<?php echo esc_attr(novel_get_option('novel_banner_text')); ?>" class="regular-text"></td></tr>
                <tr><th>رنگ</th><td>
                    <select name="novel_banner_color">
                        <?php foreach (array('info'=>'آبی','warning'=>'زرد','danger'=>'قرمز','success'=>'سبز') as $k=>$v): ?>
                        <option value="<?php echo $k; ?>" <?php selected(novel_get_option('novel_banner_color'), $k); ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td></tr>

                <tr><th colspan="2"><h2>حالت تعمیرات</h2></th></tr>
                <tr><th>فعال</th><td><label><input type="checkbox" name="novel_maintenance" value="1" <?php checked(novel_get_option('novel_maintenance')); ?>> فعال‌سازی تعمیرات</label></td></tr>
                <tr><th>پیام</th><td><textarea name="novel_maintenance_message" class="large-text" rows="3"><?php echo esc_textarea(novel_get_option('novel_maintenance_message')); ?></textarea></td></tr>

                <tr><th colspan="2"><h2>تنظیمات دیدگاه</h2></th></tr>
                <tr><th>حداقل کاراکتر</th><td><input type="number" name="novel_comment_min_chars" value="<?php echo absint(novel_get_option('novel_comment_min_chars')); ?>" min="1"></td></tr>
                <tr><th>حداکثر کاراکتر</th><td><input type="number" name="novel_comment_max_chars" value="<?php echo absint(novel_get_option('novel_comment_max_chars')); ?>" min="50"></td></tr>
                <tr><th>حداقل کلمه نقد</th><td><input type="number" name="novel_review_min_words" value="<?php echo absint(novel_get_option('novel_review_min_words')); ?>"></td></tr>
                <tr><th>زمان ویرایش (دقیقه)</th><td><input type="number" name="novel_edit_time_minutes" value="<?php echo absint(novel_get_option('novel_edit_time_minutes')); ?>"></td></tr>
                <tr><th>حداکثر پین</th><td><input type="number" name="novel_max_pins" value="<?php echo absint(novel_get_option('novel_max_pins')); ?>" min="1" max="10"></td></tr>
                <tr><th>کلمات رکیک</th><td><textarea name="novel_bad_words" class="large-text" rows="3" placeholder="کلمه۱, کلمه۲, ..."><?php echo esc_textarea(novel_get_option('novel_bad_words')); ?></textarea></td></tr>

                <tr><th colspan="2"><h2>تنظیمات مالی</h2></th></tr>
                <tr><th>روز انقضای سکه</th><td><input type="number" name="novel_coin_expiry_days" value="<?php echo absint(novel_get_option('novel_coin_expiry_days')); ?>"></td></tr>
                <tr><th>درصد نویسنده از فروش</th><td><input type="number" name="novel_author_share_percent" value="<?php echo absint(novel_get_option('novel_author_share_percent')); ?>" min="0" max="100">%</td></tr>
            </table>
            <p class="submit"><button type="submit" name="novel_settings_save" class="button button-primary">💾 ذخیره تنظیمات</button></p>
        </form>
    </div>
    <?php
}

// ═══ صفحه ماژول‌ها ═══
function novel_modules_page() {
    if (isset($_POST['novel_modules_save']) && wp_verify_nonce($_POST['_novel_modules_nonce'], 'novel_modules_settings')) {
        $modules = array();
        foreach (novel_get_modules_list() as $key => $label) {
            $modules[$key] = isset($_POST['module_' . $key]) ? 1 : 0;
        }
        update_option('novel_modules', $modules);
        echo '<div class="notice notice-success"><p>ماژول‌ها ذخیره شد ✅</p></div>';
    }

    $current = get_option('novel_modules', array());
    ?>
    <div class="wrap">
        <h1>🧩 مدیریت ماژول‌ها</h1>
        <p>هر ماژول را می‌توانید فعال یا غیرفعال کنید. غیرفعال‌سازی باعث خطا نمی‌شود.</p>
        <form method="post">
            <?php wp_nonce_field('novel_modules_settings', '_novel_modules_nonce'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>ماژول</th><th>وضعیت</th></tr></thead>
                <tbody>
                <?php foreach (novel_get_modules_list() as $key => $label): ?>
                <tr>
                    <td><strong><?php echo esc_html($label); ?></strong> <code><?php echo esc_html($key); ?></code></td>
                    <td>
                        <label class="novel-admin-toggle">
                            <input type="checkbox" name="module_<?php echo esc_attr($key); ?>" value="1" <?php checked(isset($current[$key]) ? $current[$key] : 1); ?>>
                            <span>فعال</span>
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="submit"><button type="submit" name="novel_modules_save" class="button button-primary">💾 ذخیره</button></p>
        </form>
    </div>
    <?php
}

// ═══ صفحه گزارش‌ها ═══
function novel_reports_page() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'pending';

    // عملیات
    if (isset($_GET['action_report']) && isset($_GET['report_id']) && wp_verify_nonce($_GET['_wpnonce'], 'novel_report_action')) {
        $rid = absint($_GET['report_id']);
        $act = sanitize_text_field($_GET['action_report']);
        $valid = array('reviewed', 'resolved', 'rejected');
        if (in_array($act, $valid)) {
            $wpdb->update("{$prefix}reports",
                array('status' => $act, 'reviewed_at' => current_time('mysql'), 'reviewed_by' => get_current_user_id()),
                array('id' => $rid)
            );
            echo '<div class="notice notice-success"><p>گزارش بروز شد.</p></div>';
        }
    }

    $where = $wpdb->prepare("WHERE status = %s", $status_filter);
    $reports = $wpdb->get_results("SELECT * FROM {$prefix}reports {$where} ORDER BY created_at DESC LIMIT 50");
    $counts = array(
        'pending'  => (int)$wpdb->get_var("SELECT COUNT(id) FROM {$prefix}reports WHERE status='pending'"),
        'reviewed' => (int)$wpdb->get_var("SELECT COUNT(id) FROM {$prefix}reports WHERE status='reviewed'"),
        'resolved' => (int)$wpdb->get_var("SELECT COUNT(id) FROM {$prefix}reports WHERE status='resolved'"),
        'rejected' => (int)$wpdb->get_var("SELECT COUNT(id) FROM {$prefix}reports WHERE status='rejected'"),
    );
    ?>
    <div class="wrap">
        <h1>🚩 گزارش‌ها</h1>
        <ul class="subsubsub">
            <?php foreach (array('pending'=>'در انتظار','reviewed'=>'بررسی‌شده','resolved'=>'حل‌شده','rejected'=>'رد‌شده') as $sk=>$sl): ?>
            <li><a href="<?php echo esc_url(add_query_arg('status', $sk)); ?>" class="<?php echo $status_filter === $sk ? 'current' : ''; ?>"><?php echo $sl; ?> (<?php echo $counts[$sk]; ?>)</a> |</li>
            <?php endforeach; ?>
        </ul>
        <table class="wp-list-table widefat fixed striped" style="margin-top:16px">
            <thead><tr><th>#</th><th>نوع</th><th>دلیل</th><th>گزارش‌دهنده</th><th>تاریخ</th><th>عملیات</th></tr></thead>
            <tbody>
            <?php if (empty($reports)): ?>
                <tr><td colspan="6">گزارشی یافت نشد.</td></tr>
            <?php else: foreach ($reports as $r): ?>
                <tr>
                    <td><?php echo $r->id; ?></td>
                    <td><?php echo esc_html($r->reported_type); ?> #<?php echo $r->reported_id; ?></td>
                    <td><?php echo esc_html($r->reason); ?> <?php if ($r->description) echo '<br><small>' . esc_html($r->description) . '</small>'; ?></td>
                    <td><?php $ru = get_userdata($r->reporter_id); echo $ru ? esc_html($ru->display_name) : '#' . $r->reporter_id; ?></td>
                    <td><?php echo novel_jalali_date('j F Y H:i', strtotime($r->created_at)); ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                        <a href="<?php echo wp_nonce_url(add_query_arg(array('action_report'=>'reviewed','report_id'=>$r->id)), 'novel_report_action'); ?>" class="button button-small">✅ بررسی</a>
                        <a href="<?php echo wp_nonce_url(add_query_arg(array('action_report'=>'resolved','report_id'=>$r->id)), 'novel_report_action'); ?>" class="button button-small button-primary">✓ حل</a>
                        <a href="<?php echo wp_nonce_url(add_query_arg(array('action_report'=>'rejected','report_id'=>$r->id)), 'novel_report_action'); ?>" class="button button-small">❌ رد</a>
                        <?php else: echo esc_html($r->status); endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ═══ AJAX‌های ادمین داشبورد ═══
add_action('wp_ajax_novel_save_profile', 'novel_ajax_save_profile');
function novel_ajax_save_profile() {
    check_ajax_referer('novel_nonce', '_profile_nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $uid = get_current_user_id();
    $name = sanitize_text_field($_POST['display_name']);

    if (mb_strlen($name) < 3 || mb_strlen($name) > 20) {
        wp_send_json_error(array('message' => 'نام: ۳ تا ۲۰ کاراکتر'));
    }

    wp_update_user(array('ID' => $uid, 'display_name' => $name));
    update_user_meta($uid, 'description', sanitize_textarea_field($_POST['bio'] ?? ''));
    update_user_meta($uid, 'novel_telegram', esc_url_raw($_POST['telegram'] ?? ''));
    update_user_meta($uid, 'novel_instagram', esc_url_raw($_POST['instagram'] ?? ''));

    wp_send_json_success(array('message' => 'پروفایل ذخیره شد ✅'));
}

add_action('wp_ajax_novel_save_avatar', 'novel_ajax_save_avatar');
function novel_ajax_save_avatar() {
    check_ajax_referer('novel_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $avatar = absint($_POST['avatar_id']);
    if ($avatar < 1 || $avatar > 114) wp_send_json_error(array('message' => 'آواتار نامعتبر'));

    update_user_meta(get_current_user_id(), 'novel_avatar', $avatar);
    wp_send_json_success(array('message' => 'آواتار ذخیره شد ✅', 'url' => novel_get_avatar(get_current_user_id(), 64)));
}

add_action('wp_ajax_novel_save_settings', 'novel_ajax_save_settings');
function novel_ajax_save_settings() {
    check_ajax_referer('novel_nonce', '_settings_nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $uid = get_current_user_id();

    $privacy = array();
    if (isset($_POST['privacy']) && is_array($_POST['privacy'])) {
        foreach ($_POST['privacy'] as $k => $v) {
            $privacy[sanitize_text_field($k)] = 1;
        }
    }
    update_user_meta($uid, 'novel_privacy_prefs', $privacy);

    $notif = array();
    if (isset($_POST['notif']) && is_array($_POST['notif'])) {
        foreach ($_POST['notif'] as $k => $v) {
            $notif[sanitize_text_field($k)] = 1;
        }
    }
    update_user_meta($uid, 'novel_notification_prefs', $notif);

    wp_send_json_success(array('message' => 'تنظیمات ذخیره شد ✅'));
}

add_action('wp_ajax_novel_clear_history', 'novel_ajax_clear_history');
function novel_ajax_clear_history() {
    check_ajax_referer('novel_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'reading_history', array('user_id' => get_current_user_id()));
    wp_send_json_success(array('message' => 'تاریخچه پاک شد.'));
}

add_action('wp_ajax_novel_delete_account', 'novel_ajax_delete_account');
function novel_ajax_delete_account() {
    check_ajax_referer('novel_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $uid = get_current_user_id();
    $password = $_POST['password'] ?? '';
    $user = get_userdata($uid);

    if (!wp_check_password($password, $user->user_pass, $uid)) {
        wp_send_json_error(array('message' => 'رمز عبور اشتباه است.'));
    }

    // پاکسازی جداول سفارشی
    global $wpdb;
    $tables = array('comment_votes','comment_reactions','user_follows','novel_follows','user_library',
        'reading_history','notifications','user_coins','chapter_purchases','user_achievements',
        'quiz_attempts','quiz_answers','prediction_votes','discussion_messages','reports');
    foreach ($tables as $t) {
        $wpdb->delete($wpdb->prefix . $t, array('user_id' => $uid));
    }
    $wpdb->delete($wpdb->prefix . 'user_follows', array('follower_id' => $uid));
    $wpdb->delete($wpdb->prefix . 'user_follows', array('following_id' => $uid));

    // دیدگاه‌ها → anonymous
    $wpdb->update($wpdb->comments, array('comment_author' => 'کاربر حذف‌شده', 'comment_author_email' => '', 'user_id' => 0), array('user_id' => $uid));

    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($uid);

    wp_send_json_success(array('message' => 'حساب حذف شد.', 'redirect' => home_url('/')));
}