<?php
/**
 * نوشته عادی (وبلاگ)
 * @package NovelTheme
 */
get_header();

if (have_posts()) : the_post(); ?>

<div class="novel-single-post">
    <div class="novel-container novel-post-layout">
        <article class="novel-post-content">
            <?php if (has_post_thumbnail()): ?>
            <div class="novel-post-featured">
                <?php the_post_thumbnail('large', array('loading' => 'eager')); ?>
            </div>
            <?php endif; ?>

            <h1 class="novel-post-title"><?php the_title(); ?></h1>

            <div class="novel-post-meta">
                <img src="<?php echo esc_url(novel_get_avatar(get_the_author_meta('ID'), 32)); ?>" width="32" height="32" alt="">
                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a>
                <span>📅 <?php echo novel_jalali_date('j F Y'); ?></span>
                <span>⏱ <?php echo novel_to_persian(novel_get_reading_time(get_the_content())); ?> دقیقه</span>
            </div>

            <div class="novel-post-body">
                <?php the_content(); ?>
            </div>

            <?php if (has_tag()): ?>
            <div class="novel-post-tags">
                <?php the_tags('<span class="novel-tag-chip">#', '</span><span class="novel-tag-chip">#', '</span>'); ?>
            </div>
            <?php endif; ?>
        </article>
    </div>

    <?php include NOVEL_DIR . '/novel-comments.php'; ?>
</div>

<?php endif; get_footer(); ?>