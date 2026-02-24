<?php
/**
 * Header — هدر سایت
 * @package NovelTheme
 */
if (!defined('ABSPATH')) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="theme-color" content="<?php echo esc_attr(novel_get_option('novel_primary_color', '#7c3aed')); ?>">
<?php if (is_singular('novel')): ?>
<script type="application/ld+json"><?php echo novel_schema_novel(get_the_ID()); ?></script>
<?php endif; ?>
<?php if (is_singular('chapter')): ?>
<script type="application/ld+json"><?php echo novel_schema_chapter(get_the_ID()); ?></script>
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-theme="light">

<a href="#main-content" class="novel-skip-link">رفتن به محتوا</a>

<?php if (novel_get_option('novel_banner_active')): ?>
<div class="novel-announcement-bar novel-announcement-<?php echo esc_attr(novel_get_option('novel_banner_color', 'info')); ?>" id="announcementBar">
    <div class="novel-container">
        <p>
            <?php echo esc_html(novel_get_option('novel_banner_text')); ?>
            <?php $bl = novel_get_option('novel_banner_link'); if ($bl): ?>
                <a href="<?php echo esc_url($bl); ?>">بیشتر بخوانید ←</a>
            <?php endif; ?>
        </p>
        <button class="novel-announcement-close" onclick="novelCloseAnnouncement()" aria-label="بستن">×</button>
    </div>
</div>
<?php endif; ?>

<header class="novel-header" id="mainHeader">
    <div class="novel-container novel-header-inner">

        <!-- لوگو -->
        <div class="novel-header-logo">
            <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="صفحه اصلی">
                <?php
                $logo_id = get_theme_mod('custom_logo');
                if ($logo_id) {
                    echo wp_get_attachment_image($logo_id, 'full', false, array('class' => 'novel-logo-img', 'loading' => 'eager'));
                } else {
                    echo '<span class="novel-logo-text">' . esc_html(get_bloginfo('name')) . '</span>';
                }
                ?>
            </a>
        </div>

        <!-- منو دسکتاپ -->
        <nav class="novel-header-nav novel-desktop-only" aria-label="منوی اصلی">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'novel-nav-list',
                'fallback_cb'    => 'novel_default_menu',
                'depth'          => 2,
            ));
            ?>
        </nav>

        <!-- دکمه‌های هدر -->
        <div class="novel-header-actions">

            <!-- جستجو -->
            <button class="novel-header-btn novel-search-toggle" id="searchToggle" aria-label="جستجو" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>

            <!-- دارک مود -->
            <?php if (novel_is_module_active('dark_mode')): ?>
            <button class="novel-header-btn novel-theme-toggle" id="themeToggle" aria-label="تغییر تم">
                <svg class="novel-icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="novel-icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <?php endif; ?>

            <!-- اعلان -->
            <?php if (is_user_logged_in() && novel_is_module_active('notifications')): ?>
            <div class="novel-header-notification" id="notifWrapper">
                <button class="novel-header-btn novel-notif-toggle" id="notifToggle" aria-label="اعلان‌ها" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php
                    $unread = novel_get_unread_count(get_current_user_id());
                    if ($unread > 0):
                    ?>
                    <span class="novel-notif-badge" id="notifBadge"><?php echo novel_to_persian($unread > 99 ? '99+' : $unread); ?></span>
                    <?php endif; ?>
                </button>
                <div class="novel-notif-dropdown" id="notifDropdown" aria-hidden="true">
                    <div class="novel-notif-header">
                        <strong>🔔 اعلان‌ها</strong>
                        <button class="novel-notif-read-all" id="notifReadAll">همه خوانده</button>
                    </div>
                    <div class="novel-notif-list" id="notifList">
                        <div class="novel-loading-sm">در حال بارگذاری...</div>
                    </div>
                    <a href="<?php echo esc_url(novel_get_dashboard_url('notifications')); ?>" class="novel-notif-footer">مشاهده همه اعلان‌ها →</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- پروفایل / ورود -->
            <?php if (is_user_logged_in()): ?>
            <div class="novel-header-profile" id="profileWrapper">
                <button class="novel-header-btn novel-profile-toggle" id="profileToggle" aria-expanded="false">
                    <img src="<?php echo esc_url(novel_get_avatar(get_current_user_id(), 32)); ?>"
                         alt="<?php echo esc_attr(wp_get_current_user()->display_name); ?>"
                         width="32" height="32" class="novel-header-avatar" loading="eager">
                </button>
                <div class="novel-profile-dropdown" id="profileDropdown" aria-hidden="true">
                    <div class="novel-profile-dd-header">
                        <img src="<?php echo esc_url(novel_get_avatar(get_current_user_id(), 48)); ?>" width="48" height="48" alt="" class="novel-dd-avatar">
                        <div>
                            <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong>
                            <small><?php echo esc_html(wp_get_current_user()->user_email); ?></small>
                        </div>
                    </div>
                    <div class="novel-profile-dd-body">
                        <a href="<?php echo esc_url(novel_get_dashboard_url()); ?>">📊 داشبورد</a>
                        <a href="<?php echo esc_url(novel_get_dashboard_url('library')); ?>">📚 کتابخانه</a>
                        <?php if (novel_is_module_active('coins')): ?>
                        <a href="<?php echo esc_url(novel_get_dashboard_url('coins')); ?>">🪙 سکه: <?php echo novel_format_number(novel_get_balance(get_current_user_id())); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(novel_get_dashboard_url('settings')); ?>">⚙️ تنظیمات</a>
                    </div>
                    <div class="novel-profile-dd-footer">
                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">🚪 خروج</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <a href="<?php echo esc_url(novel_get_auth_url('login')); ?>" class="novel-btn novel-btn-primary novel-btn-sm">ورود / ثبت‌نام</a>
            <?php endif; ?>

            <!-- همبرگر موبایل -->
            <button class="novel-header-btn novel-mobile-menu-toggle novel-mobile-only" id="mobileMenuToggle" aria-label="منو" aria-expanded="false">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>

        </div>
    </div>

    <!-- جستجوی overlay -->
    <div class="novel-search-overlay" id="searchOverlay" aria-hidden="true">
        <div class="novel-search-overlay-inner">
            <form role="search" class="novel-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" class="novel-search-input" id="searchInput"
                       placeholder="جستجوی رمان، نویسنده..." autocomplete="off" aria-label="جستجو">
                <input type="hidden" name="post_type" value="novel">
                <button type="button" class="novel-search-close" id="searchClose" aria-label="بستن جستجو">×</button>
            </form>
            <div class="novel-search-results" id="searchResults" aria-live="polite"></div>
        </div>
    </div>
</header>

<!-- منوی موبایل -->
<div class="novel-mobile-overlay" id="mobileOverlay"></div>
<aside class="novel-mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="novel-mobile-menu-header">
        <?php if (is_user_logged_in()): ?>
        <div class="novel-mobile-user">
            <img src="<?php echo esc_url(novel_get_avatar(get_current_user_id(), 48)); ?>" width="48" height="48" alt="" class="novel-mobile-avatar">
            <div>
                <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong>
                <?php if (novel_is_module_active('coins')): ?>
                <small>🪙 <?php echo novel_format_number(novel_get_balance(get_current_user_id())); ?> سکه</small>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <a href="<?php echo esc_url(novel_get_auth_url('login')); ?>" class="novel-btn novel-btn-primary novel-btn-block">ورود / ثبت‌نام</a>
        <?php endif; ?>
        <button class="novel-mobile-close" id="mobileMenuClose" aria-label="بستن">×</button>
    </div>
    <nav class="novel-mobile-menu-nav">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'mobile',
            'container'      => false,
            'menu_class'     => 'novel-mobile-nav-list',
            'fallback_cb'    => 'novel_default_menu',
            'depth'          => 1,
        ));
        ?>
    </nav>
</aside>

<main id="main-content" class="novel-main">
<?php

// ═══ Helper Functions for Header ═══
function novel_get_dashboard_url($tab = '') {
    $page_id = get_option('novel_dashboard_page_id');
    $url = $page_id ? get_permalink($page_id) : home_url('/dashboard/');
    if ($tab) $url = add_query_arg('tab', $tab, $url);
    return $url;
}

function novel_get_auth_url($action = 'login') {
    $page_id = get_option('novel_auth_page_id');
    $url = $page_id ? get_permalink($page_id) : home_url('/auth/');
    return add_query_arg('action', $action, $url);
}

function novel_default_menu() {
    echo '<ul class="novel-nav-list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">خانه</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('novel')) . '">رمان‌ها</a></li>';
    $rankings = get_option('novel_rankings_page_id');
    if ($rankings) echo '<li><a href="' . esc_url(get_permalink($rankings)) . '">رتبه‌بندی</a></li>';
    $authors = get_option('novel_authors_page_id');
    if ($authors) echo '<li><a href="' . esc_url(get_permalink($authors)) . '">نویسندگان</a></li>';
    echo '</ul>';
}

function novel_schema_novel($post_id) {
    $title  = get_the_title($post_id);
    $author = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
    $image  = get_the_post_thumbnail_url($post_id, 'novel-card');
    $rating_sum   = (float)get_post_meta($post_id, 'novel_rating_sum', true);
    $rating_count = (int)get_post_meta($post_id, 'novel_rating_count', true);
    $avg = $rating_count > 0 ? round($rating_sum / $rating_count, 1) : 0;

    $schema = array(
        '@context'       => 'https://schema.org',
        '@type'          => 'Book',
        'name'           => $title,
        'author'         => array('@type' => 'Person', 'name' => $author),
        'url'            => get_permalink($post_id),
        'inLanguage'     => 'fa',
        'image'          => $image ?: '',
        'description'    => wp_trim_words(get_the_excerpt($post_id), 30, '...'),
        'datePublished'  => get_the_date('c', $post_id),
    );
    if ($avg > 0) {
        $schema['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => $avg,
            'reviewCount' => $rating_count,
            'bestRating'  => 5,
            'worstRating' => 1,
        );
    }
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function novel_schema_chapter($post_id) {
    $novel_id = get_post_meta($post_id, 'chapter_novel_id', true);
    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Chapter',
        'name'        => get_the_title($post_id),
        'position'    => get_post_meta($post_id, 'chapter_number', true),
        'isPartOf'    => array(
            '@type' => 'Book',
            'name'  => $novel_id ? get_the_title($novel_id) : '',
            'url'   => $novel_id ? get_permalink($novel_id) : '',
        ),
        'author'      => array('@type' => 'Person', 'name' => get_the_author_meta('display_name', get_post_field('post_author', $post_id))),
        'datePublished' => get_the_date('c', $post_id),
        'url'         => get_permalink($post_id),
    );
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}