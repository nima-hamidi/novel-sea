<?php
/**
 * Template Name: Dashboard
 * داشبورد کاربر — ۱۶ تب
 * @package NovelTheme
 */

if (!is_user_logged_in()) {
    wp_safe_redirect(novel_get_auth_url('login') . '&redirect_to=' . urlencode(novel_get_dashboard_url()));
    exit;
}

get_header();

$uid  = get_current_user_id();
$user = wp_get_current_user();
$tab  = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

// آیا نویسنده است
global $wpdb;
$has_novels = (bool)$wpdb->get_var($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_author=%d AND post_type='novel' AND post_status IN ('publish','pending','draft') LIMIT 1", $uid
));

$tabs = array(
    'overview'      => array('icon' => '📊', 'label' => 'خلاصه'),
    'profile'       => array('icon' => '👤', 'label' => 'ویرایش پروفایل'),
    'settings'      => array('icon' => '⚙️', 'label' => 'تنظیمات'),
    'library'       => array('icon' => '📚', 'label' => 'کتابخانه'),
    'history'       => array('icon' => '📜', 'label' => 'تاریخچه'),
    'following'     => array('icon' => '❤', 'label' => 'دنبال‌شده‌ها'),
    'comments'      => array('icon' => '💬', 'label' => 'دیدگاه‌های من'),
    'notifications' => array('icon' => '🔔', 'label' => 'اعلان‌ها'),
    'followers'     => array('icon' => '👥', 'label' => 'فالوورها'),
    'achievements'  => array('icon' => '🏆', 'label' => 'دستاوردها'),
    'coins'         => array('icon' => '🪙', 'label' => 'سکه‌ها'),
    'subscription'  => array('icon' => '💎', 'label' => 'اشتراک'),
    'quizzes'       => array('icon' => '🎮', 'label' => 'مسابقات من'),
);

if ($has_novels || novel_get_option('novel_user_writing')) {
    $tabs['my_novels']  = array('icon' => '📖', 'label' => 'رمان‌های من');
    $tabs['add_novel']  = array('icon' => '➕', 'label' => 'افزودن رمان');
    $tabs['earnings']   = array('icon' => '💰', 'label' => 'درآمد');
}

// تأیید ایمیل بنر
$email_verified = novel_is_email_verified($uid);
?>

<div class="novel-dashboard">
    <div class="novel-container novel-dashboard-layout">

        <!-- سایدبار -->
        <aside class="novel-dash-sidebar" id="dashSidebar">
            <div class="novel-dash-user-card">
                <img src="<?php echo esc_url(novel_get_avatar($uid, 64)); ?>" width="64" height="64" alt="" class="novel-dash-avatar">
                <div>
                    <strong><?php echo esc_html($user->display_name); ?></strong>
                    <?php $badges = novel_get_user_badge($uid); foreach (array_slice($badges, 0, 2) as $b): ?>
                        <span class="novel-user-badge-sm" style="color:<?php echo esc_attr($b['color']); ?>"><?php echo $b['icon']; ?> <?php echo esc_html($b['label']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <nav class="novel-dash-nav">
                <?php foreach ($tabs as $tk => $tv): ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', $tk, novel_get_dashboard_url())); ?>"
                       class="novel-dash-nav-item <?php echo $tab === $tk ? 'active' : ''; ?>">
                        <span class="novel-dash-nav-icon"><?php echo $tv['icon']; ?></span>
                        <span><?php echo esc_html($tv['label']); ?></span>
                        <?php if ($tk === 'notifications'):
                            $uc = novel_get_unread_count($uid);
                            if ($uc > 0): ?>
                            <span class="novel-dash-badge"><?php echo novel_to_persian($uc); ?></span>
                        <?php endif; endif; ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="novel-dash-nav-item novel-dash-logout">
                    <span class="novel-dash-nav-icon">🚪</span><span>خروج</span>
                </a>
            </nav>
        </aside>

        <!-- محتوای اصلی -->
        <div class="novel-dash-content">

            <?php if (!$email_verified): ?>
            <div class="novel-alert novel-alert-warning">
                ⚠️ ایمیل شما تأیید نشده. بدون تأیید، امکان ارسال دیدگاه و استفاده از امکانات ندارید.
                <button class="novel-btn novel-btn-sm novel-btn-warning" id="resendVerifyBtn">ارسال مجدد لینک تأیید</button>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['verified'])): ?>
            <div class="novel-alert novel-alert-success">✅ ایمیل شما با موفقیت تأیید شد!</div>
            <?php endif; ?>

            <!-- ═══════════ تب خلاصه ═══════════ -->
            <?php if ($tab === 'overview'): ?>
            <div class="novel-dash-section">
                <h2>سلام <?php echo esc_html($user->display_name); ?>! 👋</h2>

                <div class="novel-dash-stats-grid">
                    <?php
                    $stats = array(
                        array('icon' => '📖', 'val' => absint(get_user_meta($uid, 'novel_comment_total', true)), 'label' => 'دیدگاه'),
                        array('icon' => '🪙', 'val' => novel_get_balance($uid), 'label' => 'سکه'),
                        array('icon' => '❤', 'val' => absint(get_user_meta($uid, 'followers_count', true)), 'label' => 'فالوور'),
                        array('icon' => '🏆', 'val' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$wpdb->prefix}user_achievements WHERE user_id=%d", $uid)), 'label' => 'مدال'),
                    );
                    foreach ($stats as $s): ?>
                    <div class="novel-dash-stat-card">
                        <span class="novel-dash-stat-icon"><?php echo $s['icon']; ?></span>
                        <strong><?php echo novel_format_number($s['val']); ?></strong>
                        <small><?php echo esc_html($s['label']); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ادامه مطالعه -->
                <?php
                $continue = $wpdb->get_results($wpdb->prepare(
                    "SELECT novel_id, progress FROM {$wpdb->prefix}user_library WHERE user_id=%d AND list_type='reading' ORDER BY updated_at DESC LIMIT 3", $uid
                ));
                if (!empty($continue)): ?>
                <h3>▶ ادامه مطالعه</h3>
                <div class="novel-dash-continue">
                    <?php foreach ($continue as $c):
                        $nid = (int)$c->novel_id;
                        $ch_total = absint(get_post_meta($nid, 'chapters_count_cache', true));
                        $pct = $ch_total > 0 ? round(((int)$c->progress / $ch_total) * 100) : 0;
                    ?>
                    <a href="<?php echo esc_url(get_permalink($nid)); ?>" class="novel-dash-continue-item">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($nid, 'novel-thumb') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>" width="50" height="70" alt="" loading="lazy">
                        <div>
                            <strong><?php echo esc_html(get_the_title($nid)); ?></strong>
                            <div class="novel-progress-bar-sm"><div style="width:<?php echo (int)$pct; ?>%"></div></div>
                            <small><?php echo novel_to_persian($c->progress); ?>/<?php echo novel_to_persian($ch_total); ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب ویرایش پروفایل ═══════════ -->
            <?php elseif ($tab === 'profile'): ?>
            <div class="novel-dash-section">
                <h2>👤 ویرایش پروفایل</h2>
                <form id="profileForm" class="novel-form">
                    <?php wp_nonce_field('novel_nonce', '_profile_nonce'); ?>

                    <div class="novel-form-group">
                        <label>نام نمایشی</label>
                        <input type="text" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" minlength="3" maxlength="20" required>
                    </div>

                    <div class="novel-form-group">
                        <label>بیوگرافی (حداکثر ۲۰۰ کاراکتر)</label>
                        <textarea name="bio" maxlength="200" rows="3"><?php echo esc_textarea(get_user_meta($uid, 'description', true)); ?></textarea>
                        <small class="novel-char-counter"><span id="bioCharCount">۰</span>/۲۰۰</small>
                    </div>

                    <div class="novel-form-group">
                        <label>لینک تلگرام</label>
                        <input type="url" name="telegram" value="<?php echo esc_attr(get_user_meta($uid, 'novel_telegram', true)); ?>" placeholder="https://t.me/username">
                    </div>

                    <div class="novel-form-group">
                        <label>لینک اینستاگرام</label>
                        <input type="url" name="instagram" value="<?php echo esc_attr(get_user_meta($uid, 'novel_instagram', true)); ?>" placeholder="https://instagram.com/username">
                    </div>

                    <button type="submit" class="novel-btn novel-btn-primary">💾 ذخیره تغییرات</button>
                </form>

                <!-- آواتار -->
                <h3 class="novel-mt-24">🖼️ انتخاب آواتار</h3>
                <div class="novel-avatar-grid" id="avatarGrid">
                    <?php
                    $current_av = absint(get_user_meta($uid, 'novel_avatar', true));
                    for ($i = 1; $i <= 114; $i++): ?>
                    <button class="novel-avatar-pick <?php echo $current_av === $i ? 'active' : ''; ?>"
                            data-avatar="<?php echo $i; ?>">
                        <img src="<?php echo esc_url(NOVEL_URL . '/assets/avatars/avatar-' . $i . '.png'); ?>"
                             width="60" height="60" alt="آواتار <?php echo $i; ?>" loading="lazy">
                    </button>
                    <?php endfor; ?>
                </div>

                <!-- حذف حساب -->
                <div class="novel-danger-zone novel-mt-24">
                    <h3>🔴 منطقه خطرناک</h3>
                    <p>حذف حساب غیرقابل بازگشت است.</p>
                    <button class="novel-btn novel-btn-danger novel-btn-sm" id="deleteAccountBtn">🗑 حذف حساب کاربری</button>
                </div>
            </div>

            <!-- ═══════════ تب تنظیمات ═══════════ -->
            <?php elseif ($tab === 'settings'): ?>
            <div class="novel-dash-section">
                <h2>⚙️ تنظیمات</h2>
                <form id="settingsForm" class="novel-form">
                    <?php wp_nonce_field('novel_nonce', '_settings_nonce'); ?>

                    <h3>حریم خصوصی</h3>
                    <?php
                    $privacy = get_user_meta($uid, 'novel_privacy_prefs', true) ?: array();
                    $priv_opts = array(
                        'show_online'  => 'نمایش وضعیت آنلاین من',
                        'show_library' => 'نمایش کتابخانه من به دیگران',
                        'show_stats'   => 'نمایش آمار مطالعه من',
                    );
                    foreach ($priv_opts as $pk => $pl): ?>
                    <label class="novel-checkbox">
                        <input type="checkbox" name="privacy[<?php echo esc_attr($pk); ?>]" value="1" <?php checked(!empty($privacy[$pk])); ?>>
                        <span><?php echo esc_html($pl); ?></span>
                    </label>
                    <?php endforeach; ?>

                    <h3 class="novel-mt-24">اعلان‌ها</h3>
                    <?php
                    $notif_prefs = get_user_meta($uid, 'novel_notification_prefs', true) ?: array();
                    $notif_opts = array(
                        'comment_reply' => 'پاسخ دیدگاه',
                        'comment_like'  => 'لایک دیدگاه',
                        'mention'       => 'منشن شدن',
                        'new_chapter'   => 'قسمت جدید رمان دنبال‌شده',
                        'quiz_started'  => 'شروع مسابقه',
                    );
                    foreach ($notif_opts as $nk => $nl): ?>
                    <label class="novel-checkbox">
                        <input type="checkbox" name="notif[<?php echo esc_attr($nk); ?>]" value="1" <?php checked($notif_prefs[$nk] ?? 1); ?>>
                        <span><?php echo esc_html($nl); ?></span>
                    </label>
                    <label class="novel-checkbox novel-checkbox-indent">
                        <input type="checkbox" name="notif[email_<?php echo esc_attr($nk); ?>]" value="1" <?php checked(!empty($notif_prefs['email_' . $nk])); ?>>
                        <span>ایمیل هم ارسال شود</span>
                    </label>
                    <?php endforeach; ?>

                    <button type="submit" class="novel-btn novel-btn-primary novel-mt-16">💾 ذخیره تنظیمات</button>
                </form>
            </div>

            <!-- ═══════════ تب کتابخانه ═══════════ -->
            <?php elseif ($tab === 'library'): ?>
            <div class="novel-dash-section">
                <h2>📚 کتابخانه من</h2>
                <?php
                $lib_filter = isset($_GET['list']) ? sanitize_text_field($_GET['list']) : '';
                $lib_types = array(
                    ''          => 'همه',
                    'reading'   => '📖 خواندن',
                    'plan'      => '📋 برنامه',
                    'completed' => '✅ تکمیل',
                    'dropped'   => '🚫 رها',
                    'on_hold'   => '⏸ نگه‌داشته',
                );
                ?>
                <div class="novel-filter-tabs">
                    <?php foreach ($lib_types as $lt => $ll): ?>
                        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'library', 'list' => $lt), novel_get_dashboard_url())); ?>"
                           class="novel-filter-btn <?php echo $lib_filter === $lt ? 'active' : ''; ?>"><?php echo $ll; ?></a>
                    <?php endforeach; ?>
                </div>

                <?php
                $lib_where = $lib_filter ? $wpdb->prepare(" AND list_type=%s", $lib_filter) : '';
                $lib_items = $wpdb->get_results($wpdb->prepare(
                    "SELECT novel_id, list_type, progress, updated_at FROM {$wpdb->prefix}user_library WHERE user_id=%d {$lib_where} ORDER BY updated_at DESC LIMIT 15",
                    $uid
                ));

                if (empty($lib_items)): ?>
                    <div class="novel-empty-state"><p>کتابخانه خالی است. <a href="<?php echo esc_url(get_post_type_archive_link('novel')); ?>">رمان‌ها را مرور کنید</a></p></div>
                <?php else: ?>
                    <div class="novel-lib-list">
                    <?php foreach ($lib_items as $li):
                        $nid = (int)$li->novel_id;
                        $ch_total = absint(get_post_meta($nid, 'chapters_count_cache', true));
                        $pct = $ch_total > 0 ? round(((int)$li->progress / $ch_total) * 100) : 0;
                        $avg = 0;
                        $rs = (float)get_post_meta($nid, 'novel_rating_sum', true);
                        $rc = (int)get_post_meta($nid, 'novel_rating_count', true);
                        if ($rc > 0) $avg = round($rs / $rc, 1);
                    ?>
                    <div class="novel-lib-item">
                        <a href="<?php echo esc_url(get_permalink($nid)); ?>" class="novel-lib-cover">
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($nid, 'novel-thumb') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>" width="60" height="84" alt="" loading="lazy">
                        </a>
                        <div class="novel-lib-info">
                            <h4><a href="<?php echo esc_url(get_permalink($nid)); ?>"><?php echo esc_html(get_the_title($nid)); ?></a></h4>
                            <?php if ($avg > 0): ?><span>★ <?php echo novel_to_persian($avg); ?></span><?php endif; ?>
                            <div class="novel-progress-bar-sm"><div style="width:<?php echo (int)$pct; ?>%"></div></div>
                            <small><?php echo novel_to_persian($li->progress); ?> از <?php echo novel_to_persian($ch_total); ?> (<?php echo novel_to_persian($pct); ?>%)</small>
                            <small class="novel-text-muted"><?php echo novel_time_ago($li->updated_at); ?></small>
                        </div>
                        <div class="novel-lib-actions">
                            <div class="novel-library-dropdown">
                                <button class="novel-btn novel-btn-sm novel-btn-ghost novel-library-toggle">📋 ▾</button>
                                <div class="novel-library-menu novel-hidden">
                                    <?php foreach (array('reading'=>'📖 خواندن','plan'=>'📋 برنامه','completed'=>'✅ تکمیل','dropped'=>'🚫 رها','on_hold'=>'⏸ نگه‌داشته') as $lk=>$lv): ?>
                                    <button class="novel-library-item <?php echo $li->list_type === $lk ? 'active' : ''; ?>" data-novel="<?php echo $nid; ?>" data-type="<?php echo $lk; ?>"><?php echo $lv; ?></button>
                                    <?php endforeach; ?>
                                    <button class="novel-library-item novel-library-remove" data-novel="<?php echo $nid; ?>" data-type="remove">🗑 حذف</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب تاریخچه ═══════════ -->
            <?php elseif ($tab === 'history'): ?>
            <div class="novel-dash-section">
                <h2>📜 تاریخچه مطالعه</h2>
                <?php
                $history = $wpdb->get_results($wpdb->prepare(
                    "SELECT chapter_id, novel_id, read_at FROM {$wpdb->prefix}reading_history WHERE user_id=%d ORDER BY read_at DESC LIMIT 30", $uid
                ));
                if (empty($history)): ?>
                    <div class="novel-empty-state"><p>تاریخچه‌ای ندارید.</p></div>
                <?php else: ?>
                    <div class="novel-history-list">
                    <?php foreach ($history as $h): ?>
                        <a href="<?php echo esc_url(get_permalink($h->chapter_id)); ?>" class="novel-history-item">
                            <span><?php echo esc_html(get_the_title($h->novel_id)); ?></span>
                            <span>قسمت <?php echo novel_to_persian(get_post_meta($h->chapter_id, 'chapter_number', true)); ?>: <?php echo esc_html(get_the_title($h->chapter_id)); ?></span>
                            <small><?php echo novel_time_ago($h->read_at); ?></small>
                        </a>
                    <?php endforeach; ?>
                    </div>
                    <button class="novel-btn novel-btn-danger novel-btn-sm novel-mt-16" id="clearHistoryBtn">🗑 پاک کردن تاریخچه</button>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب اعلان‌ها ═══════════ -->
            <?php elseif ($tab === 'notifications'): ?>
            <div class="novel-dash-section">
                <div class="novel-dash-section-header">
                    <h2>🔔 اعلان‌ها</h2>
                    <button class="novel-btn novel-btn-sm novel-btn-ghost" id="markAllReadBtn">✓ همه خوانده شد</button>
                </div>
                <?php
                $notifs = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, type, title, message, link, is_read, created_at FROM {$wpdb->prefix}notifications WHERE user_id=%d ORDER BY created_at DESC LIMIT 20", $uid
                ));
                $n_icons = array('new_chapter'=>'📖','comment_reply'=>'💬','comment_like'=>'👍','mention'=>'📣','new_follower'=>'❤','coin_received'=>'🪙','achievement'=>'🏆','quiz_started'=>'🎮','system'=>'📢');

                if (empty($notifs)): ?>
                    <div class="novel-empty-state"><p>اعلان جدیدی ندارید 📭</p></div>
                <?php else: ?>
                    <div class="novel-notif-full-list">
                    <?php foreach ($notifs as $n): ?>
                        <a href="<?php echo $n->link ? esc_url($n->link) : '#'; ?>"
                           class="novel-notif-full-item <?php echo $n->is_read ? '' : 'novel-notif-unread'; ?>">
                            <span class="novel-notif-icon"><?php echo $n_icons[$n->type] ?? '🔔'; ?></span>
                            <div class="novel-notif-body">
                                <strong><?php echo esc_html($n->title); ?></strong>
                                <?php if ($n->message): ?><p><?php echo esc_html($n->message); ?></p><?php endif; ?>
                                <small><?php echo novel_time_ago($n->created_at); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب دیدگاه‌های من ═══════════ -->
            <?php elseif ($tab === 'comments'): ?>
            <div class="novel-dash-section">
                <h2>💬 دیدگاه‌های من</h2>
                <?php
                $my_comments = get_comments(array('user_id' => $uid, 'number' => 15, 'orderby' => 'comment_date', 'order' => 'DESC'));
                if (empty($my_comments)): ?>
                    <div class="novel-empty-state"><p>هنوز دیدگاهی ننوشته‌اید.</p></div>
                <?php else: ?>
                    <div class="novel-my-comments-list">
                    <?php foreach ($my_comments as $mc):
                        $mc_type = get_comment_meta($mc->comment_ID, 'comment_type_novel', true) ?: 'comment';
                        $mc_likes = absint(get_comment_meta($mc->comment_ID, 'likes_count', true));
                        $type_labels = array('comment'=>'دیدگاه','review'=>'نقد','theory'=>'تئوری','voice'=>'یک‌خطی');
                    ?>
                    <div class="novel-my-comment-item">
                        <span class="novel-badge novel-badge-sm"><?php echo esc_html($type_labels[$mc_type] ?? $mc_type); ?></span>
                        <p><?php echo esc_html(wp_trim_words($mc->comment_content, 12, '...')); ?></p>
                        <div class="novel-my-comment-meta">
                            <a href="<?php echo esc_url(get_permalink($mc->comment_post_ID)); ?>"><?php echo esc_html(get_the_title($mc->comment_post_ID)); ?></a>
                            <span><?php echo novel_jalali_date('j F Y', strtotime($mc->comment_date)); ?></span>
                            <?php if ($mc_likes): ?><span>👍 <?php echo novel_to_persian($mc_likes); ?></span><?php endif; ?>
                            <span><?php echo $mc->comment_approved === '1' ? '✅' : '⏳'; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب فالوورها ═══════════ -->
            <?php elseif ($tab === 'followers'): ?>
            <div class="novel-dash-section">
                <h2>👥 فالوورها</h2>
                <?php
                $my_followers = $wpdb->get_results($wpdb->prepare(
                    "SELECT follower_id, created_at FROM {$wpdb->prefix}user_follows WHERE following_id=%d ORDER BY created_at DESC LIMIT 20", $uid
                ));
                if (empty($my_followers)): ?>
                    <div class="novel-empty-state"><p>هنوز فالووری ندارید.</p></div>
                <?php else: ?>
                    <div class="novel-users-list">
                    <?php foreach ($my_followers as $f):
                        $fu = get_userdata($f->follower_id);
                        if (!$fu) continue;
                    ?>
                    <div class="novel-user-list-item">
                        <img src="<?php echo esc_url(novel_get_avatar($f->follower_id, 40)); ?>" width="40" height="40" alt="">
                        <a href="<?php echo esc_url(get_author_posts_url($f->follower_id)); ?>"><?php echo esc_html($fu->display_name); ?></a>
                        <small><?php echo novel_time_ago($f->created_at); ?></small>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب دستاوردها ═══════════ -->
            <?php elseif ($tab === 'achievements'): ?>
            <div class="novel-dash-section">
                <h2>🏆 دستاوردها</h2>
                <?php
                $earned_raw = $wpdb->get_results($wpdb->prepare(
                    "SELECT achievement_key, earned_at FROM {$wpdb->prefix}user_achievements WHERE user_id=%d", $uid
                ));
                $earned = array();
                foreach ($earned_raw as $e) $earned[$e->achievement_key] = $e->earned_at;

                $all_achievements = novel_get_all_achievements();
                $earned_count = count($earned);
                $total_count = count($all_achievements);
                ?>
                <p><?php echo novel_to_persian($earned_count); ?> از <?php echo novel_to_persian($total_count); ?> دستاورد</p>

                <div class="novel-achievements-grid">
                    <?php foreach ($all_achievements as $ak => $ai): ?>
                    <div class="novel-achievement-card <?php echo isset($earned[$ak]) ? 'novel-achievement-earned' : 'novel-achievement-locked'; ?>">
                        <span class="novel-achievement-icon"><?php echo isset($earned[$ak]) ? $ai['icon'] : '🔒'; ?></span>
                        <strong><?php echo esc_html($ai['title']); ?></strong>
                        <small><?php echo esc_html($ai['condition']); ?></small>
                        <?php if (isset($earned[$ak])): ?>
                            <small class="novel-text-success"><?php echo novel_jalali_date('j F Y', strtotime($earned[$ak])); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ═══════════ تب سکه‌ها ═══════════ -->
            <?php elseif ($tab === 'coins'): ?>
            <div class="novel-dash-section">
                <h2>🪙 سکه‌های من</h2>
                <div class="novel-coin-balance">
                    <span class="novel-coin-amount">🪙 <?php echo novel_format_number(novel_get_balance($uid)); ?></span>
                    <span>سکه</span>
                </div>

                <h3>خرید سکه</h3>
                <div class="novel-coin-packages">
                    <?php $packages = get_option('novel_coin_packages', array());
                    foreach ($packages as $i => $pkg): ?>
                    <div class="novel-coin-package <?php echo $i === 1 ? 'novel-coin-recommended' : ''; ?>">
                        <?php if ($i === 1): ?><span class="novel-coin-rec-badge">پیشنهاد ویژه ⭐</span><?php endif; ?>
                        <strong><?php echo esc_html($pkg['name']); ?></strong>
                        <span class="novel-coin-pkg-amount">🪙 <?php echo novel_to_persian($pkg['coins']); ?></span>
                        <span class="novel-coin-pkg-price"><?php echo novel_format_number($pkg['price']); ?> ریال</span>
                        <button class="novel-btn novel-btn-primary novel-btn-sm novel-buy-coin" data-package="<?php echo $i; ?>">خرید</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="novel-mt-24">تاریخچه تراکنش‌ها</h3>
                <?php
                $transactions = $wpdb->get_results($wpdb->prepare(
                    "SELECT amount, balance, type, description, created_at FROM {$wpdb->prefix}user_coins WHERE user_id=%d ORDER BY id DESC LIMIT 15", $uid
                ));
                if (!empty($transactions)): ?>
                <div class="novel-table-wrap">
                    <table class="novel-table">
                        <thead><tr><th>تاریخ</th><th>نوع</th><th>مبلغ</th><th>موجودی</th><th>توضیح</th></tr></thead>
                        <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo novel_jalali_date('j F', strtotime($t->created_at)); ?></td>
                            <td><?php echo esc_html($t->type); ?></td>
                            <td class="<?php echo (int)$t->amount > 0 ? 'novel-text-success' : 'novel-text-danger'; ?>">
                                <?php echo (int)$t->amount > 0 ? '+' : ''; ?><?php echo novel_to_persian($t->amount); ?>
                            </td>
                            <td><?php echo novel_to_persian($t->balance); ?></td>
                            <td><?php echo esc_html($t->description); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب رمان‌های من ═══════════ -->
            <?php elseif ($tab === 'my_novels'): ?>
            <div class="novel-dash-section">
                <h2>📖 رمان‌های من</h2>
                <?php
                $my_novels = get_posts(array(
                    'post_type' => 'novel', 'author' => $uid, 'posts_per_page' => 20,
                    'post_status' => array('publish', 'pending', 'draft'), 'orderby' => 'date', 'order' => 'DESC',
                ));
                if (empty($my_novels)): ?>
                    <div class="novel-empty-state">
                        <p>هنوز رمانی ننوشته‌اید.</p>
                        <a href="<?php echo esc_url(add_query_arg('tab', 'add_novel', novel_get_dashboard_url())); ?>" class="novel-btn novel-btn-primary">➕ اولین رمان را بنویسید!</a>
                    </div>
                <?php else: ?>
                    <div class="novel-my-novels-list">
                    <?php foreach ($my_novels as $mn):
                        $mn_status = $mn->post_status;
                        $mn_status_labels = array('publish'=>'✅ منتشر','pending'=>'⏳ در انتظار','draft'=>'📝 پیش‌نویس');
                        $mn_ch_count = absint(get_post_meta($mn->ID, 'chapters_count_cache', true));
                    ?>
                    <div class="novel-my-novel-item">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($mn->ID, 'novel-thumb') ?: NOVEL_URL . '/assets/images/default-cover.png'); ?>" width="60" height="84" alt="" loading="lazy">
                        <div class="novel-my-novel-info">
                            <h4><a href="<?php echo esc_url(get_permalink($mn->ID)); ?>"><?php echo esc_html($mn->post_title); ?></a></h4>
                            <span><?php echo $mn_status_labels[$mn_status] ?? $mn_status; ?></span>
                            <span>📖 <?php echo novel_to_persian($mn_ch_count); ?> قسمت</span>
                            <span>👁 <?php echo novel_format_number(absint(get_post_meta($mn->ID, 'total_views', true))); ?></span>
                        </div>
                        <div class="novel-my-novel-actions">
                            <a href="<?php echo esc_url(get_edit_post_link($mn->ID)); ?>" class="novel-btn novel-btn-sm novel-btn-ghost">✏️</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════ تب افزودن رمان ═══════════ -->
            <?php elseif ($tab === 'add_novel'): ?>
            <div class="novel-dash-section">
                <h2>➕ افزودن رمان جدید</h2>
                <?php if (!novel_get_option('novel_user_writing')): ?>
                    <div class="novel-alert novel-alert-info">نویسندگی کاربران فعلاً غیرفعال است.</div>
                <?php else: ?>
                <form id="addNovelForm" class="novel-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('novel_nonce', '_add_novel_nonce'); ?>

                    <div class="novel-form-group">
                        <label>نام فارسی رمان *</label>
                        <input type="text" name="novel_title" required placeholder="نام فارسی رمان">
                    </div>
                    <div class="novel-form-group">
                        <label>نام انگلیسی *</label>
                        <input type="text" name="novel_english" required placeholder="English Name">
                    </div>
                    <div class="novel-form-group">
                        <label>نوع رمان *</label>
                        <select name="novel_type" required>
                            <option value="wn">وب ناول (WN)</option>
                            <option value="ln">لایت ناول (LN)</option>
                        </select>
                    </div>
                    <div class="novel-form-group">
                        <label>ژانر * (حداقل یک مورد)</label>
                        <div class="novel-checkbox-grid">
                        <?php $all_genres = get_terms(array('taxonomy'=>'genre','hide_empty'=>false));
                        foreach ($all_genres as $g): ?>
                            <label class="novel-checkbox"><input type="checkbox" name="genres[]" value="<?php echo $g->term_id; ?>"><span><?php echo esc_html($g->name); ?></span></label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="novel-form-group">
                        <label>خلاصه * (حداقل ۵۰ کلمه)</label>
                        <textarea name="novel_excerpt" rows="5" required></textarea>
                    </div>
                    <div class="novel-form-group">
                        <label>تصویر جلد (JPG/PNG، حداکثر ۲ مگابایت)</label>
                        <input type="file" name="novel_cover" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <button type="submit" class="novel-btn novel-btn-primary novel-btn-lg">📤 ارسال برای بررسی</button>
                </form>
                <?php endif; ?>
            </div>

            <?php endif; // end tab switch ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>

<?php
function novel_get_all_achievements() {
    return array(
        'first_comment'  => array('icon'=>'💬','title'=>'اولین دیدگاه','condition'=>'۱ دیدگاه'),
        'commenter_100'  => array('icon'=>'🗣','title'=>'صد دیدگاه','condition'=>'۱۰۰ دیدگاه'),
        'first_review'   => array('icon'=>'📝','title'=>'اولین نقد','condition'=>'۱ نقد'),
        'first_novel'    => array('icon'=>'✍️','title'=>'اولین رمان','condition'=>'۱ رمان'),
        'member_1year'   => array('icon'=>'🎂','title'=>'یک‌ساله','condition'=>'۳۶۵ روز عضویت'),
        'reader_10'      => array('icon'=>'📖','title'=>'۱۰ رمان خوانده','condition'=>'۱۰ تکمیل'),
        'chapters_100'   => array('icon'=>'📄','title'=>'۱۰۰ قسمت','condition'=>'۱۰۰ قسمت خوانده'),
        'chapters_1000'  => array('icon'=>'🏆','title'=>'هزارخوان','condition'=>'۱۰۰۰ قسمت'),
        'liked_50'       => array('icon'=>'👍','title'=>'محبوب','condition'=>'۵۰ لایک دریافتی'),
        'follower_10'    => array('icon'=>'👥','title'=>'۱۰ فالوور','condition'=>'۱۰ فالوور'),
        'follower_100'   => array('icon'=>'🌟','title'=>'اینفلوئنسر','condition'=>'۱۰۰ فالوور'),
        'quiz_first'     => array('icon'=>'🎮','title'=>'اولین مسابقه','condition'=>'۱ مسابقه'),
        'quiz_champion'  => array('icon'=>'🏆','title'=>'چمپیون','condition'=>'رتبه ۱'),
    );
}