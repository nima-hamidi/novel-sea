<?php get_header(); ?>
<div class="novel-error-page">
    <div class="novel-error-content">
        <div class="novel-error-icon">🗺️</div>
        <h1>گم شدی قهرمان!</h1>
        <p>صفحه‌ای که دنبالش بودی در این دنیا وجود ندارد...</p>
        <form role="search" class="novel-error-search" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="search" name="s" placeholder="جستجوی رمان..." class="novel-input">
            <input type="hidden" name="post_type" value="novel">
            <button type="submit" class="novel-btn novel-btn-primary">جستجو</button>
        </form>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="novel-btn novel-btn-outline">🏠 بازگشت به صفحه اصلی</a>
    </div>
</div>
<?php get_footer(); ?>