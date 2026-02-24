<?php
/**
 * Footer — فوتر + Bottom Nav
 * @package NovelTheme
 */
if (!defined('ABSPATH')) exit;
?>
</main><!-- #main-content -->

<footer class="novel-footer">
    <div class="novel-container">
        <div class="novel-footer-grid">
            <!-- ستون ۱ -->
            <div class="novel-footer-col">
                <h3 class="novel-footer-title">لینک‌های سریع</h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'novel-footer-links',
                    'fallback_cb'    => 'novel_footer_default_links',
                    'depth'          => 1,
                ));
                ?>
            </div>

            <!-- ستون ۲ -->
            <div class="novel-footer-col novel-footer-about">
                <?php
                $logo_id = get_theme_mod('custom_logo');
                if ($logo_id) {
                    echo '<div class="novel-footer-logo">';
                    echo wp_get_attachment_image($logo_id, array(120, 40), false, array('loading' => 'lazy'));
                    echo '</div>';
                }
                ?>
                <p><?php echo esc_html(novel_get_option('novel_site_description', 'دنیای داستان‌های فارسی')); ?></p>
                <div class="novel-footer-social">
                    <?php
                    $socials = array(
                        'telegram'  => array('url' => novel_get_option('novel_social_telegram'), 'label' => 'تلگرام', 'icon' => 'M21.2 6.4l-3 14.2c-.2.9-1 1.2-1.6.8l-4.5-3.3-2.1 2.1c-.2.3-.5.4-.8.4l.3-4.5L18.3 8c.3-.3-.1-.4-.5-.2l-10 6.3-4.3-1.3c-.9-.3-.9-.9.2-1.4l16.9-6.5c.8-.3 1.4.2 1.2 1.5z'),
                        'instagram' => array('url' => novel_get_option('novel_social_instagram'), 'label' => 'اینستاگرام', 'icon' => 'M16 4H8a4 4 0 00-4 4v8a4 4 0 004 4h8a4 4 0 004-4V8a4 4 0 00-4-4zm-4 11a3 3 0 110-6 3 3 0 010 6zm3.5-5.5a1 1 0 110-2 1 1 0 010 2z'),
                        'twitter'   => array('url' => novel_get_option('novel_social_twitter'), 'label' => 'ایکس', 'icon' => 'M18.9 1.15h3.68l-8.04 9.2L24 22.85h-7.4l-5.8-7.58-6.63 7.58H.49l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93zm-1.3 19.5h2.04L6.49 3.24H4.3L17.6 20.65z'),
                    );
                    foreach ($socials as $key => $social):
                        if (empty($social['url'])) continue;
                    ?>
                        <a href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener" class="novel-social-link novel-social-<?php echo $key; ?>" aria-label="<?php echo esc_attr($social['label']); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="<?php echo $social['icon']; ?>"/></svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ستون ۳ -->
            <div class="novel-footer-col">
                <h3 class="novel-footer-title">راهنما</h3>
                <ul class="novel-footer-links">
                    <?php
                    $rules = novel_get_option('novel_rules_page');
                    if ($rules): ?>
                    <li><a href="<?php echo esc_url(get_permalink($rules)); ?>">قوانین سایت</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo esc_url(home_url('/about/')); ?>">درباره ما</a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">تماس با ما</a></li>
                </ul>
            </div>
        </div>

        <!-- آمار -->
        <div class="novel-footer-stats">
            <?php
            $stats = novel_get_site_stats();
            ?>
            <span>📖 <?php echo novel_format_number($stats['novels']); ?> رمان</span>
            <span>📄 <?php echo novel_format_number($stats['chapters']); ?> قسمت</span>
            <span>👥 <?php echo novel_format_number($stats['users']); ?> کاربر</span>
            <span>💬 <?php echo novel_format_number($stats['comments']); ?> دیدگاه</span>
        </div>

        <!-- کپی‌رایت -->
        <div class="novel-footer-copy">
            <p>© <?php echo novel_to_persian(novel_jalali_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. تمامی حقوق محفوظ است.</p>
            <p>طراحی با ❤ برای خوانندگان فارسی‌زبان</p>
        </div>
    </div>
</footer>

<!-- Bottom Navigation — موبایل -->
<nav class="novel-bottom-nav novel-mobile-only" id="bottomNav" aria-label="ناوبری پایین">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="novel-bnav-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>خانه</span>
    </a>
    <a href="<?php echo esc_url(home_url('/?s=&post_type=novel')); ?>" class="novel-bnav-item <?php echo is_search() ? 'active' : ''; ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <span>جستجو</span>
    </a>
    <?php if (is_user_logged_in()): ?>
    <a href="<?php echo esc_url(novel_get_dashboard_url('library')); ?>" class="novel-bnav-item">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span>کتابخانه</span>
    </a>
    <a href="<?php echo esc_url(novel_get_dashboard_url('notifications')); ?>" class="novel-bnav-item novel-bnav-notif">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <?php if (novel_get_unread_count(get_current_user_id()) > 0): ?>
        <span class="novel-bnav-badge"></span>
        <?php endif; ?>
        <span>اعلان</span>
    </a>
    <a href="<?php echo esc_url(novel_get_dashboard_url()); ?>" class="novel-bnav-item">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>من</span>
    </a>
    <?php else: ?>
    <a href="<?php echo esc_url(novel_get_auth_url('login')); ?>" class="novel-bnav-item">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        <span>ورود</span>
    </a>
    <?php endif; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>
<?php

// ═══ Footer Helpers ═══
function novel_get_site_stats() {
    $stats = get_transient('novel_site_stats');
    if ($stats !== false) return $stats;

    $stats = array(
        'novels'   => wp_count_posts('novel')->publish,
        'chapters' => wp_count_posts('chapter')->publish,
        'users'    => (int)count_users()['total_users'],
        'comments' => (int)wp_count_comments()->approved,
    );
    set_transient('novel_site_stats', $stats, HOUR_IN_SECONDS);
    return $stats;
}

function novel_footer_default_links() {
    echo '<ul class="novel-footer-links">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">صفحه اصلی</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('novel')) . '">رمان‌ها</a></li>';
    echo '</ul>';
}