<?php
/**
 * Theme Name: Novel Theme
 * Theme URI: https://noveltheme.ir
 * Description: قالب حرفه‌ای وردپرس برای سایت رمان و ناول
 * Version: 2.0.0
 * Author: Novel Dev Team
 * Text Domain: flavor
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) exit;

// ═══════════════════════════════════════
// ① ثابت‌ها
// ═══════════════════════════════════════
define('NOVEL_VERSION', '2.0.0');
define('NOVEL_DB_VERSION', '2.0.0');
define('NOVEL_DIR', get_template_directory());
define('NOVEL_URL', get_template_directory_uri());

// ═══════════════════════════════════════
// ② لود فایل‌ها
// ═══════════════════════════════════════
require_once NOVEL_DIR . '/novel-core.php';
require_once NOVEL_DIR . '/novel-modules.php';
require_once NOVEL_DIR . '/novel-ajax.php';
require_once NOVEL_DIR . '/novel-api.php';
if (is_admin()) {
    require_once NOVEL_DIR . '/novel-admin.php';
}

// ═══════════════════════════════════════
// ③ After Setup Theme
// ═══════════════════════════════════════
add_action('after_setup_theme', 'novel_setup_theme');
function novel_setup_theme() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script'
    ));
    add_theme_support('automatic-feed-links');

    register_nav_menus(array(
        'primary' => 'منوی اصلی',
        'footer'  => 'منوی فوتر',
        'mobile'  => 'منوی موبایل',
    ));

    add_image_size('novel-card', 300, 420, true);
    add_image_size('novel-card-small', 150, 210, true);
    add_image_size('novel-thumb', 80, 112, true);
    add_image_size('novel-banner', 1200, 400, true);

    load_theme_textdomain('flavor', NOVEL_DIR . '/languages');
}

// ═══════════════════════════════════════
// ④ Enqueue Scripts & Styles
// ═══════════════════════════════════════
add_action('wp_enqueue_scripts', 'novel_enqueue_assets');
function novel_enqueue_assets() {
    // فونت لوکال
    wp_enqueue_style(
        'novel-fonts',
        NOVEL_URL . '/fonts/iransansx.css',
        array(),
        NOVEL_VERSION
    );

    // استایل اصلی
    wp_enqueue_style(
        'novel-style',
        get_stylesheet_uri(),
        array('novel-fonts'),
        filemtime(NOVEL_DIR . '/style.css')
    );

    // اسکریپت اصلی
    wp_enqueue_script(
        'novel-scripts',
        NOVEL_URL . '/novel-scripts.js',
        array(),
        filemtime(NOVEL_DIR . '/novel-scripts.js'),
        true
    );

    wp_localize_script('novel-scripts', 'NovelData', array(
        'ajax_url'     => admin_url('admin-ajax.php'),
        'rest_url'     => rest_url('novel/v1/'),
        'nonce'        => wp_create_nonce('novel_nonce'),
        'rest_nonce'   => wp_create_nonce('wp_rest'),
        'is_logged_in' => is_user_logged_in() ? 1 : 0,
        'user_id'      => get_current_user_id(),
        'is_rtl'       => 1,
        'home_url'     => home_url('/'),
        'theme_url'    => NOVEL_URL,
        'strings'      => novel_get_js_strings(),
    ));
}

function novel_get_js_strings() {
    return array(
        'confirm_delete'  => 'آیا مطمئن هستید؟ این عمل غیرقابل بازگشت است.',
        'loading'         => 'در حال بارگذاری...',
        'error'           => 'خطایی رخ داد. لطفاً دوباره تلاش کنید.',
        'success'         => 'عملیات با موفقیت انجام شد.',
        'copied'          => 'لینک کپی شد ✓',
        'login_required'  => 'برای این کار باید وارد شوید.',
        'verify_required' => 'ابتدا ایمیل خود را تأیید کنید.',
        'wait'            => 'لطفاً کمی صبر کنید...',
        'no_results'      => 'نتیجه‌ای یافت نشد.',
        'load_more'       => 'بارگذاری بیشتر',
        'sending'         => 'در حال ارسال...',
        'saved'           => 'تغییرات ذخیره شد ✅',
    );
}

// ═══════════════════════════════════════
// ⑤ Custom Post Types
// ═══════════════════════════════════════
add_action('init', 'novel_register_post_types', 5);
function novel_register_post_types() {
    // رمان
    register_post_type('novel', array(
        'labels' => array(
            'name'               => 'رمان‌ها',
            'singular_name'      => 'رمان',
            'add_new'            => 'افزودن رمان',
            'add_new_item'       => 'افزودن رمان جدید',
            'edit_item'          => 'ویرایش رمان',
            'view_item'          => 'مشاهده رمان',
            'search_items'       => 'جستجوی رمان',
            'not_found'          => 'رمانی یافت نشد',
            'all_items'          => 'همه رمان‌ها',
            'menu_name'          => '📚 رمان‌ها',
        ),
        'public'              => true,
        'has_archive'         => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-book-alt',
        'menu_position'       => 5,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'rewrite'             => array('slug' => 'novel', 'with_front' => false),
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'show_in_nav_menus'   => true,
    ));

    // قسمت
    register_post_type('chapter', array(
        'labels' => array(
            'name'               => 'قسمت‌ها',
            'singular_name'      => 'قسمت',
            'add_new'            => 'افزودن قسمت',
            'add_new_item'       => 'افزودن قسمت جدید',
            'edit_item'          => 'ویرایش قسمت',
            'view_item'          => 'مشاهده قسمت',
            'search_items'       => 'جستجوی قسمت',
            'not_found'          => 'قسمتی یافت نشد',
            'all_items'          => 'همه قسمت‌ها',
            'menu_name'          => '📄 قسمت‌ها',
        ),
        'public'              => true,
        'has_archive'         => false,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-media-text',
        'menu_position'       => 6,
        'supports'            => array('title', 'editor', 'comments'),
        'rewrite'             => array('slug' => 'chapter', 'with_front' => false),
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'show_in_nav_menus'   => false,
    ));
}

// ═══════════════════════════════════════
// ⑥ Custom Taxonomies
// ═══════════════════════════════════════
add_action('init', 'novel_register_taxonomies', 5);
function novel_register_taxonomies() {
    // ژانر
    register_taxonomy('genre', 'novel', array(
        'labels' => array(
            'name'          => 'ژانرها',
            'singular_name' => 'ژانر',
            'add_new_item'  => 'افزودن ژانر',
            'search_items'  => 'جستجوی ژانر',
            'all_items'     => 'همه ژانرها',
            'menu_name'     => 'ژانرها',
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'genre', 'with_front' => false),
        'show_admin_column' => true,
    ));

    // برچسب رمان
    register_taxonomy('novel_tag', 'novel', array(
        'labels' => array(
            'name'          => 'برچسب‌های رمان',
            'singular_name' => 'برچسب رمان',
            'add_new_item'  => 'افزودن برچسب',
            'menu_name'     => 'برچسب‌ها',
        ),
        'hierarchical'  => false,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'tag', 'with_front' => false),
        'show_admin_column' => true,
    ));

    // وضعیت رمان
    register_taxonomy('novel_status', 'novel', array(
        'labels' => array(
            'name'          => 'وضعیت',
            'singular_name' => 'وضعیت',
            'menu_name'     => 'وضعیت‌ها',
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'status', 'with_front' => false),
        'show_admin_column' => true,
    ));
}

// ═══════════════════════════════════════
// ⑦ Meta Boxes — رمان
// ═══════════════════════════════════════
add_action('add_meta_boxes', 'novel_add_meta_boxes');
function novel_add_meta_boxes() {
    add_meta_box(
        'novel_details',
        '📋 اطلاعات رمان',
        'novel_render_novel_meta_box',
        'novel',
        'normal',
        'high'
    );
    add_meta_box(
        'chapter_details',
        '📋 اطلاعات قسمت',
        'novel_render_chapter_meta_box',
        'chapter',
        'normal',
        'high'
    );
}

function novel_render_novel_meta_box($post) {
    wp_nonce_field('novel_meta_save', 'novel_meta_nonce');

    $fields = array(
        'novel_type'         => get_post_meta($post->ID, 'novel_type', true),
        'novel_english_name' => get_post_meta($post->ID, 'novel_english_name', true),
        'novel_translator'   => get_post_meta($post->ID, 'novel_translator', true),
        'novel_age_rating'   => get_post_meta($post->ID, 'novel_age_rating', true),
        'has_anime'          => get_post_meta($post->ID, 'has_anime', true),
        'has_manga'          => get_post_meta($post->ID, 'has_manga', true),
        'anime_url'          => get_post_meta($post->ID, 'anime_url', true),
        'manga_url'          => get_post_meta($post->ID, 'manga_url', true),
    );
    ?>
    <style>
        .novel-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:12px 0}
        .novel-meta-field{display:flex;flex-direction:column;gap:4px}
        .novel-meta-field label{font-weight:700;font-size:13px}
        .novel-meta-field input,.novel-meta-field select{padding:8px;border:1px solid #ccc;border-radius:6px}
        .novel-meta-full{grid-column:1/-1}
    </style>
    <div class="novel-meta-grid">
        <div class="novel-meta-field">
            <label>نوع رمان</label>
            <select name="novel_type">
                <option value="wn" <?php selected($fields['novel_type'], 'wn'); ?>>وب ناول (WN)</option>
                <option value="ln" <?php selected($fields['novel_type'], 'ln'); ?>>لایت ناول (LN)</option>
            </select>
        </div>
        <div class="novel-meta-field">
            <label>نام انگلیسی</label>
            <input type="text" name="novel_english_name" value="<?php echo esc_attr($fields['novel_english_name']); ?>">
        </div>
        <div class="novel-meta-field">
            <label>مترجم</label>
            <input type="text" name="novel_translator" value="<?php echo esc_attr($fields['novel_translator']); ?>">
        </div>
        <div class="novel-meta-field">
            <label>رده سنی</label>
            <select name="novel_age_rating">
                <option value="all" <?php selected($fields['novel_age_rating'], 'all'); ?>>همه</option>
                <option value="13" <?php selected($fields['novel_age_rating'], '13'); ?>>+۱۳</option>
                <option value="16" <?php selected($fields['novel_age_rating'], '16'); ?>>+۱۶</option>
                <option value="18" <?php selected($fields['novel_age_rating'], '18'); ?>>+۱۸</option>
            </select>
        </div>
        <div class="novel-meta-field">
            <label><input type="checkbox" name="has_anime" value="1" <?php checked($fields['has_anime'], '1'); ?>> نسخه انیمه دارد</label>
            <input type="url" name="anime_url" value="<?php echo esc_url($fields['anime_url']); ?>" placeholder="لینک انیمه">
        </div>
        <div class="novel-meta-field">
            <label><input type="checkbox" name="has_manga" value="1" <?php checked($fields['has_manga'], '1'); ?>> نسخه مانگا دارد</label>
            <input type="url" name="manga_url" value="<?php echo esc_url($fields['manga_url']); ?>" placeholder="لینک مانگا">
        </div>
    </div>
    <?php
}

function novel_render_chapter_meta_box($post) {
    wp_nonce_field('novel_meta_save', 'novel_meta_nonce');

    $fields = array(
        'chapter_number'   => get_post_meta($post->ID, 'chapter_number', true),
        'chapter_volume'   => get_post_meta($post->ID, 'chapter_volume', true),
        'chapter_novel_id' => get_post_meta($post->ID, 'chapter_novel_id', true),
        'is_vip'           => get_post_meta($post->ID, 'is_vip', true),
        'coin_price'       => get_post_meta($post->ID, 'coin_price', true),
        'chapter_recap'    => get_post_meta($post->ID, 'chapter_recap', true),
        'chapter_mood'     => get_post_meta($post->ID, 'chapter_mood', true),
    );

    $novels = get_posts(array(
        'post_type'      => 'novel',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
    ?>
    <div class="novel-meta-grid">
        <div class="novel-meta-field">
            <label>رمان مرتبط</label>
            <select name="chapter_novel_id">
                <option value="">انتخاب کنید</option>
                <?php foreach ($novels as $nid): ?>
                    <option value="<?php echo $nid; ?>" <?php selected($fields['chapter_novel_id'], $nid); ?>>
                        <?php echo esc_html(get_the_title($nid)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="novel-meta-field">
            <label>شماره قسمت</label>
            <input type="number" name="chapter_number" value="<?php echo esc_attr($fields['chapter_number']); ?>" min="1">
        </div>
        <div class="novel-meta-field">
            <label>جلد/فصل (اختیاری)</label>
            <input type="text" name="chapter_volume" value="<?php echo esc_attr($fields['chapter_volume']); ?>">
        </div>
        <div class="novel-meta-field">
            <label>دسترسی</label>
            <select name="is_vip">
                <option value="0" <?php selected($fields['is_vip'], '0'); ?>>رایگان</option>
                <option value="1" <?php selected($fields['is_vip'], '1'); ?>>VIP 🔒</option>
            </select>
        </div>
        <div class="novel-meta-field">
            <label>قیمت سکه (اگر VIP)</label>
            <input type="number" name="coin_price" value="<?php echo esc_attr($fields['coin_price']); ?>" min="1" max="100">
        </div>
        <div class="novel-meta-field">
            <label>حال‌وهوای صوتی</label>
            <select name="chapter_mood">
                <option value="" <?php selected($fields['chapter_mood'], ''); ?>>بدون موسیقی</option>
                <option value="epic_battle" <?php selected($fields['chapter_mood'], 'epic_battle'); ?>>حماسی ⚔️</option>
                <option value="sad" <?php selected($fields['chapter_mood'], 'sad'); ?>>غمگین 😢</option>
                <option value="romantic" <?php selected($fields['chapter_mood'], 'romantic'); ?>>رمانتیک ❤</option>
                <option value="mysterious" <?php selected($fields['chapter_mood'], 'mysterious'); ?>>مرموز 🔮</option>
                <option value="peaceful" <?php selected($fields['chapter_mood'], 'peaceful'); ?>>آرام 🌿</option>
                <option value="horror" <?php selected($fields['chapter_mood'], 'horror'); ?>>ترسناک 👻</option>
                <option value="adventure" <?php selected($fields['chapter_mood'], 'adventure'); ?>>ماجراجویی 🗺</option>
                <option value="celebration" <?php selected($fields['chapter_mood'], 'celebration'); ?>>شاد 🎉</option>
            </select>
        </div>
        <div class="novel-meta-field novel-meta-full">
            <label>خلاصه قسمت قبل (حداکثر ۲۰۰ کاراکتر)</label>
            <textarea name="chapter_recap" maxlength="200" rows="3"><?php echo esc_textarea($fields['chapter_recap']); ?></textarea>
        </div>
    </div>
    <?php
}

add_action('save_post', 'novel_save_meta_boxes', 10, 2);
function novel_save_meta_boxes($post_id, $post) {
    if (!isset($_POST['novel_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['novel_meta_nonce'], 'novel_meta_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Novel meta
    if ($post->post_type === 'novel') {
        $novel_fields = array(
            'novel_type'         => 'sanitize_text_field',
            'novel_english_name' => 'sanitize_text_field',
            'novel_translator'   => 'sanitize_text_field',
            'novel_age_rating'   => 'sanitize_text_field',
            'anime_url'          => 'esc_url_raw',
            'manga_url'          => 'esc_url_raw',
        );
        foreach ($novel_fields as $key => $sanitizer) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $sanitizer($_POST[$key]));
            }
        }
        update_post_meta($post_id, 'has_anime', isset($_POST['has_anime']) ? '1' : '0');
        update_post_meta($post_id, 'has_manga', isset($_POST['has_manga']) ? '1' : '0');

        // مقداردهی اولیه شمارنده‌ها
        if (get_post_meta($post_id, 'novel_rating_sum', true) === '') {
            update_post_meta($post_id, 'novel_rating_sum', 0);
            update_post_meta($post_id, 'novel_rating_count', 0);
            update_post_meta($post_id, 'followers_count', 0);
            update_post_meta($post_id, 'total_views', 0);
            update_post_meta($post_id, 'comments_count', 0);
            update_post_meta($post_id, 'bookmarks_count', 0);
        }
    }

    // Chapter meta
    if ($post->post_type === 'chapter') {
        $chapter_fields = array(
            'chapter_number'   => 'absint',
            'chapter_volume'   => 'sanitize_text_field',
            'chapter_novel_id' => 'absint',
            'coin_price'       => 'absint',
            'chapter_recap'    => 'sanitize_textarea_field',
            'chapter_mood'     => 'sanitize_text_field',
        );
        foreach ($chapter_fields as $key => $sanitizer) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $sanitizer($_POST[$key]));
            }
        }
        update_post_meta($post_id, 'is_vip', isset($_POST['is_vip']) ? absint($_POST['is_vip']) : 0);

        // شمارنده‌ها
        if (get_post_meta($post_id, 'likes_count', true) === '') {
            update_post_meta($post_id, 'likes_count', 0);
            update_post_meta($post_id, 'dislikes_count', 0);
            update_post_meta($post_id, 'total_views', 0);
        }
    }
}

// ═══════════════════════════════════════
// ⑧ تنظیمات پیش‌فرض قالب
// ═══════════════════════════════════════
function novel_get_default_options() {
    return array(
        'novel_primary_color'        => '#7c3aed',
        'novel_site_description'     => 'دنیای داستان‌های فارسی',
        'novel_rules_page'           => 0,
        'novel_comment_rules_page'   => 0,
        'novel_user_writing'         => 1,
        'novel_maintenance'          => 0,
        'novel_maintenance_message'  => 'در حال به‌روزرسانی هستیم. به زودی برمی‌گردیم!',
        'novel_banner_active'        => 0,
        'novel_banner_text'          => '',
        'novel_banner_color'         => 'info',
        'novel_banner_link'          => '',
        'novel_social_telegram'      => '',
        'novel_social_instagram'     => '',
        'novel_social_twitter'       => '',
        'novel_coin_expiry_days'     => 30,
        'novel_author_share_percent' => 70,
        'novel_bad_words'            => '',
        'novel_comment_min_chars'    => 10,
        'novel_comment_max_chars'    => 1000,
        'novel_review_min_words'     => 200,
        'novel_theory_min_words'     => 250,
        'novel_voice_max_chars'      => 100,
        'novel_edit_time_minutes'    => 15,
        'novel_max_pins'             => 3,
        'novel_homepage_sections'    => array(
            'hero', 'continue', 'challenge', 'trending',
            'quiz', 'daily', 'updates', 'popular',
            'newest', 'authors', 'comments', 'poll', 'mood'
        ),
    );
}

function novel_get_option($key, $fallback = null) {
    $defaults = novel_get_default_options();
    $value = get_option($key);
    if ($value === false) {
        return isset($defaults[$key]) ? $defaults[$key] : $fallback;
    }
    return $value;
}

// ═══════════════════════════════════════
// ⑨ ماژول‌ها فعال/غیرفعال
// ═══════════════════════════════════════
function novel_get_modules_list() {
    return array(
        'comments_advanced' => 'دیدگاه پیشرفته',
        'reactions'         => 'ری‌اکشن',
        'spoiler'           => 'اسپویلر',
        'review'            => 'نقد و بررسی',
        'theory'            => 'تئوری',
        'voice'             => 'صدای خواننده',
        'library'           => 'کتابخانه',
        'history'           => 'تاریخچه',
        'notifications'     => 'اعلان',
        'coins'             => 'سکه',
        'quiz'              => 'مسابقه',
        'polls'             => 'نظرسنجی',
        'achievements'      => 'دستاورد',
        'rankings'          => 'رتبه‌بندی',
        'author_panel'      => 'پنل نویسنده',
        'author_banners'    => 'بنر نویسنده',
        'discussions'       => 'اتاق بحث',
        'predictions'       => 'پیش‌بینی داستان',
        'daily_highlight'   => 'قسمت روز',
        'weekly_challenge'  => 'چالش هفتگی',
        'ambient_audio'     => 'موسیقی پس‌زمینه',
        'tts'               => 'متن به گفتار',
        'follow_system'     => 'فالو',
        'share'             => 'اشتراک‌گذاری',
        'dark_mode'         => 'دارک‌مود',
        'advanced_search'   => 'جستجوی پیشرفته',
        'blog'              => 'وبلاگ',
        'maintenance'       => 'تعمیرات',
    );
}

function novel_is_module_active($module) {
    $modules = get_option('novel_modules', array());
    if (empty($modules)) return true; // پیش‌فرض: همه فعال
    return isset($modules[$module]) ? (bool)$modules[$module] : true;
}

// ═══════════════════════════════════════
// ⑩ تاریخ شمسی (الگوریتم خوارزمی)
// ═══════════════════════════════════════
function novel_gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100) + (int)(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * (int)($days / 12053));
    $days %= 12053;
    $jy += 4 * (int)($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + (int)($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return array($jy, $jm, $jd);
}

function novel_jalali_date($format, $timestamp = null) {
    if ($timestamp === null) $timestamp = current_time('timestamp');

    $gy = (int)date('Y', $timestamp);
    $gm = (int)date('m', $timestamp);
    $gd = (int)date('d', $timestamp);

    list($jy, $jm, $jd) = novel_gregorian_to_jalali($gy, $gm, $gd);

    $months = array(
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    );

    $result = $format;
    $result = str_replace('Y', novel_to_persian($jy), $result);
    $result = str_replace('m', novel_to_persian(str_pad($jm, 2, '0', STR_PAD_LEFT)), $result);
    $result = str_replace('d', novel_to_persian(str_pad($jd, 2, '0', STR_PAD_LEFT)), $result);
    $result = str_replace('F', $months[$jm], $result);
    $result = str_replace('j', novel_to_persian($jd), $result);
    $result = str_replace('H', novel_to_persian(date('H', $timestamp)), $result);
    $result = str_replace('i', novel_to_persian(date('i', $timestamp)), $result);

    return $result;
}

// ═══════════════════════════════════════
// ⑪ توابع کمکی عمومی
// ═══════════════════════════════════════
function novel_to_persian($string) {
    $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    $latin   = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($latin, $persian, (string)$string);
}

function novel_format_number($number) {
    if ($number >= 1000000) {
        return novel_to_persian(round($number / 1000000, 1)) . 'M';
    }
    if ($number >= 1000) {
        return novel_to_persian(round($number / 1000, 1)) . 'K';
    }
    return novel_to_persian(number_format($number, 0, '.', '٬'));
}

function novel_time_ago($timestamp) {
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    $diff = current_time('timestamp') - $timestamp;

    if ($diff < 60)       return 'لحظاتی پیش';
    if ($diff < 3600)     return novel_to_persian((int)($diff / 60)) . ' دقیقه پیش';
    if ($diff < 86400)    return novel_to_persian((int)($diff / 3600)) . ' ساعت پیش';
    if ($diff < 604800)   return novel_to_persian((int)($diff / 86400)) . ' روز پیش';
    if ($diff < 2592000)  return novel_to_persian((int)($diff / 604800)) . ' هفته پیش';
    if ($diff < 31536000) return novel_to_persian((int)($diff / 2592000)) . ' ماه پیش';
    return novel_to_persian((int)($diff / 31536000)) . ' سال پیش';
}

function novel_get_avatar($user_id, $size = 64) {
    $avatar_num = get_user_meta($user_id, 'novel_avatar', true);
    if ($avatar_num && $avatar_num > 0 && $avatar_num <= 114) {
        return NOVEL_URL . '/assets/avatars/avatar-' . intval($avatar_num) . '.png';
    }
    return get_avatar_url($user_id, array('size' => $size));
}

function novel_is_email_verified($user_id) {
    return (bool)get_user_meta($user_id, 'email_verified', true);
}

function novel_get_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    // فارسی: تقریباً ۱۵۰ کلمه در دقیقه
    $minutes = max(1, (int)ceil($word_count / 150));
    return $minutes;
}

function novel_get_comment_level($count) {
    $count = absint($count);
    if ($count >= 201) return array('title' => 'استاد', 'icon' => '🎓', 'color' => '#8b5cf6');
    if ($count >= 51)  return array('title' => 'منتقد', 'icon' => '🎭', 'color' => '#f59e0b');
    if ($count >= 11)  return array('title' => 'خواننده', 'icon' => '📖', 'color' => '#3b82f6');
    return array('title' => 'تازه‌وارد', 'icon' => '🌱', 'color' => '#22c55e');
}

function novel_get_user_badge($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return array();

    $badges = array();

    if (in_array('administrator', $user->roles)) {
        $badges[] = array('label' => 'مدیر', 'icon' => '👑', 'color' => '#ef4444');
    }
    if (in_array('editor', $user->roles)) {
        $badges[] = array('label' => 'ویراستار', 'icon' => '📝', 'color' => '#f97316');
    }
    if (in_array('author', $user->roles)) {
        $badges[] = array('label' => 'نویسنده رسمی', 'icon' => '✍️', 'color' => '#3b82f6');
    }

    // RCP check
    if (function_exists('rcp_get_customer_by_user_id')) {
        $customer = rcp_get_customer_by_user_id($user_id);
        if ($customer && rcp_customer_has_active_membership($customer)) {
            $badges[] = array('label' => 'ویژه', 'icon' => '✅', 'color' => '#22c55e');
        }
    }

    // سطح دیدگاه
    $comment_count = get_user_meta($user_id, 'novel_comment_total', true);
    if ($comment_count) {
        $level = novel_get_comment_level($comment_count);
        $badges[] = array('label' => $level['title'], 'icon' => $level['icon'], 'color' => $level['color']);
    }

    return $badges;
}

// ═══════════════════════════════════════
// ⑫ آپدیت وضعیت آنلاین
// ═══════════════════════════════════════
add_action('template_redirect', 'novel_update_last_active');
function novel_update_last_active() {
    if (!is_user_logged_in()) return;
    $user_id = get_current_user_id();
    $last = get_user_meta($user_id, 'novel_last_active', true);
    // آپدیت هر ۲ دقیقه (نه هر بار)
    if (!$last || (current_time('timestamp') - (int)$last) > 120) {
        update_user_meta($user_id, 'novel_last_active', current_time('timestamp'));
    }
}

function novel_get_online_status($user_id) {
    $last = get_user_meta($user_id, 'novel_last_active', true);
    if (!$last) return 'offline';
    $diff = current_time('timestamp') - (int)$last;
    if ($diff < 300)  return 'online';
    if ($diff < 1800) return 'recent';
    return 'offline';
}

// ═══════════════════════════════════════
// ⑬ Redirect wp-login.php
// ═══════════════════════════════════════
add_action('login_init', 'novel_redirect_login');
function novel_redirect_login() {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    if (in_array($action, array('logout', 'postpass', 'rp', 'resetpass', 'confirmaction'))) {
        return;
    }
    if (isset($_GET['interim-login'])) return;

    $auth_page = get_option('novel_auth_page_id');
    if ($auth_page) {
        wp_safe_redirect(get_permalink($auth_page));
        exit;
    }
}

// ═══════════════════════════════════════
// ⑭ Maintenance Mode
// ═══════════════════════════════════════
function novel_is_maintenance() {
    return (bool)novel_get_option('novel_maintenance', 0);
}

add_action('template_redirect', 'novel_check_maintenance', 1);
function novel_check_maintenance() {
    if (!novel_is_maintenance()) return;
    if (current_user_can('manage_options')) return;
    if (is_admin()) return;

    status_header(503);
    header('Retry-After: 3600');
    include NOVEL_DIR . '/maintenance.php';
    exit;
}

// ═══════════════════════════════════════
// ⑮ DB Version Check
// ═══════════════════════════════════════
add_action('init', 'novel_check_db_version');
function novel_check_db_version() {
    $current = get_option('novel_db_version', '0');
    if (version_compare($current, NOVEL_DB_VERSION, '<')) {
        novel_create_tables();
        novel_run_migrations($current, NOVEL_DB_VERSION);
        update_option('novel_db_version', NOVEL_DB_VERSION);
    }
}

// ═══════════════════════════════════════
// ⑯ Body Classes
// ═══════════════════════════════════════
add_filter('body_class', 'novel_body_classes');
function novel_body_classes($classes) {
    $classes[] = 'novel-theme';
    $classes[] = 'rtl';
    if (is_user_logged_in()) {
        $classes[] = 'logged-in-user';
    }
    if (novel_is_module_active('dark_mode')) {
        $classes[] = 'dark-mode-enabled';
    }
    return $classes;
}

// ═══════════════════════════════════════
// ⑰ Comment Approval — فوری
// ═══════════════════════════════════════
add_filter('pre_comment_approved', 'novel_auto_approve_comment', 10, 2);
function novel_auto_approve_comment($approved, $commentdata) {
    if (!is_user_logged_in()) return $approved;
    $user_id = get_current_user_id();
    if (novel_is_email_verified($user_id)) {
        return 1; // تأیید فوری
    }
    return $approved;
}

// ═══════════════════════════════════════
// ⑱ Disable WP Admin Bar for subscribers
// ═══════════════════════════════════════
add_action('after_setup_theme', 'novel_disable_admin_bar');
function novel_disable_admin_bar() {
    if (!current_user_can('edit_posts')) {
        add_filter('show_admin_bar', '__return_false');
    }
}

// ═══════════════════════════════════════
// ⑲ Excerpt Length
// ═══════════════════════════════════════
add_filter('excerpt_length', function() { return 30; });
add_filter('excerpt_more', function() { return '...'; });

// ═══════════════════════════════════════
// ⑳ Custom CSS Variables
// ═══════════════════════════════════════
add_action('wp_head', 'novel_custom_css_vars', 1);
function novel_custom_css_vars() {
    $primary = novel_get_option('novel_primary_color', '#7c3aed');
    echo '<style>:root{';
    echo '--novel-primary:' . esc_attr($primary) . ';';
    echo '--novel-primary-rgb:' . esc_attr(novel_hex_to_rgb($primary)) . ';';
    echo '--novel-primary-light:' . esc_attr(novel_adjust_color($primary, 40)) . ';';
    echo '--novel-primary-dark:' . esc_attr(novel_adjust_color($primary, -30)) . ';';
    echo '}</style>';
}

function novel_hex_to_rgb($hex) {
    $hex = ltrim($hex, '#');
    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
}

function novel_adjust_color($hex, $steps) {
    $hex = ltrim($hex, '#');
    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}