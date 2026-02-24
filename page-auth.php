<?php
/**
 * Template Name: Auth
 * صفحه ورود و ثبت‌نام
 * @package NovelTheme
 */

if (is_user_logged_in()) {
    wp_safe_redirect(novel_get_dashboard_url());
    exit;
}

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'login';
$redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : novel_get_dashboard_url();

get_header();
?>

<div class="novel-auth-page">
    <div class="novel-auth-card">

        <!-- لوگو -->
        <div class="novel-auth-logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php
                $logo_id = get_theme_mod('custom_logo');
                if ($logo_id) {
                    echo wp_get_attachment_image($logo_id, array(160, 50), false, array('loading' => 'eager'));
                } else {
                    echo '<h1>' . esc_html(get_bloginfo('name')) . '</h1>';
                }
                ?>
            </a>
        </div>

        <!-- تب‌ها -->
        <div class="novel-auth-tabs">
            <a href="<?php echo esc_url(add_query_arg('action', 'login')); ?>"
               class="novel-auth-tab <?php echo $action === 'login' ? 'active' : ''; ?>">ورود</a>
            <a href="<?php echo esc_url(add_query_arg('action', 'register')); ?>"
               class="novel-auth-tab <?php echo $action === 'register' ? 'active' : ''; ?>">ثبت‌نام</a>
        </div>

        <!-- پیام‌های خطا/موفقیت -->
        <div class="novel-auth-messages" id="authMessages"></div>

        <!-- ═══ فرم ورود ═══ -->
        <form class="novel-auth-form <?php echo $action === 'login' ? '' : 'novel-hidden'; ?>"
              id="loginForm" method="post" novalidate>

            <?php wp_nonce_field('novel_login', 'novel_login_nonce'); ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
            <input type="hidden" name="novel_form_start" value="<?php echo time(); ?>">

            <!-- Honeypot -->
            <div style="display:none!important" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <input type="text" name="login" id="loginField" required autofocus
                       placeholder="ایمیل یا نام کاربری" autocomplete="username">
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <input type="password" name="password" id="loginPassword" required
                       placeholder="رمز عبور" autocomplete="current-password">
                <button type="button" class="novel-field-toggle-pass" data-target="loginPassword" aria-label="نمایش رمز">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>

            <div class="novel-field-row">
                <label class="novel-checkbox">
                    <input type="checkbox" name="remember" value="1" checked>
                    <span>مرا به خاطر بسپار</span>
                </label>
                <a href="#" class="novel-link" id="forgotPassLink">رمز عبور را فراموش کردم</a>
            </div>

            <button type="submit" class="novel-btn novel-btn-primary novel-btn-block novel-btn-lg" id="loginSubmit">
                <span class="novel-btn-text">ورود</span>
                <span class="novel-btn-loading novel-hidden">
                    <svg class="novel-spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg>
                </span>
            </button>

            <p class="novel-auth-switch">
                حساب ندارید؟ <a href="<?php echo esc_url(add_query_arg('action', 'register')); ?>">ثبت‌نام کنید</a>
            </p>
        </form>

        <!-- ═══ فرم ثبت‌نام ═══ -->
        <form class="novel-auth-form <?php echo $action === 'register' ? '' : 'novel-hidden'; ?>"
              id="registerForm" method="post" novalidate>

            <?php wp_nonce_field('novel_register', 'novel_register_nonce'); ?>
            <input type="hidden" name="novel_form_start" value="<?php echo time(); ?>">

            <!-- Honeypot -->
            <div style="display:none!important" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <input type="text" name="display_name" id="regName" required
                       placeholder="نام نمایشی (۳ تا ۲۰ کاراکتر)" autocomplete="name" minlength="3" maxlength="20">
                <span class="novel-field-status" id="regNameStatus"></span>
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <input type="email" name="email" id="regEmail" required
                       placeholder="ایمیل" autocomplete="email">
                <span class="novel-field-status" id="regEmailStatus"></span>
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <input type="password" name="password" id="regPassword" required
                       placeholder="رمز عبور (حداقل ۸ کاراکتر)" autocomplete="new-password" minlength="8">
                <button type="button" class="novel-field-toggle-pass" data-target="regPassword" aria-label="نمایش رمز">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <div class="novel-password-strength" id="passStrength">
                <div class="novel-pass-bar"><div class="novel-pass-bar-fill" id="passStrengthBar"></div></div>
                <span class="novel-pass-label" id="passStrengthLabel"></span>
            </div>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <input type="password" name="password_confirm" id="regPasswordConfirm" required
                       placeholder="تکرار رمز عبور" autocomplete="new-password">
                <span class="novel-field-status" id="regPassConfirmStatus"></span>
            </div>

            <label class="novel-checkbox novel-checkbox-required">
                <input type="checkbox" name="agree_rules" id="regAgree" required>
                <span>
                    <a href="<?php echo esc_url(get_permalink(novel_get_option('novel_rules_page'))); ?>" target="_blank">قوانین سایت</a> و
                    <a href="<?php echo esc_url(get_permalink(novel_get_option('novel_comment_rules_page'))); ?>" target="_blank">قوانین دیدگاه‌گذاری</a> را خوانده‌ام و می‌پذیرم
                </span>
            </label>

            <button type="submit" class="novel-btn novel-btn-primary novel-btn-block novel-btn-lg" id="registerSubmit" disabled>
                <span class="novel-btn-text">ثبت‌نام</span>
                <span class="novel-btn-loading novel-hidden">
                    <svg class="novel-spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg>
                </span>
            </button>

            <p class="novel-auth-switch">
                حساب دارید؟ <a href="<?php echo esc_url(add_query_arg('action', 'login')); ?>">وارد شوید</a>
            </p>
        </form>

        <!-- ═══ فرم فراموشی رمز ═══ -->
        <form class="novel-auth-form novel-hidden" id="forgotForm" method="post" novalidate>
            <?php wp_nonce_field('novel_forgot', 'novel_forgot_nonce'); ?>

            <h3 class="novel-auth-subtitle">🔑 بازیابی رمز عبور</h3>
            <p class="novel-text-muted">ایمیل خود را وارد کنید تا لینک بازیابی ارسال شود.</p>

            <div class="novel-field">
                <div class="novel-field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <input type="email" name="forgot_email" id="forgotEmail" required placeholder="ایمیل" autocomplete="email">
            </div>

            <button type="submit" class="novel-btn novel-btn-primary novel-btn-block novel-btn-lg" id="forgotSubmit">
                <span class="novel-btn-text">ارسال لینک بازیابی</span>
                <span class="novel-btn-loading novel-hidden">
                    <svg class="novel-spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg>
                </span>
            </button>

            <p class="novel-auth-switch">
                <a href="#" id="backToLogin">← بازگشت به ورود</a>
            </p>
        </form>

    </div>
</div>

<?php get_footer(); ?>