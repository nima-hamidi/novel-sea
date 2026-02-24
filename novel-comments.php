<?php
/**
 * Template Part — دیدگاه‌های یکپارچه
 * include در single-novel, single-chapter, single
 * @package NovelTheme
 */
if (!defined('ABSPATH')) exit;

$novel_post_id   = get_the_ID();
$novel_post_type = get_post_type();
$show_tabs       = ($novel_post_type === 'novel');
$current_tab     = isset($_GET['comment_tab']) ? sanitize_text_field($_GET['comment_tab']) : 'comment';
$current_sort    = 'newest';

// شمارنده‌ها
$total_comments = (int)get_comments_number($novel_post_id);
$cache_key = 'novel_comment_counts_' . $novel_post_id;
$counts = get_transient($cache_key);
if (!$counts) {
    $counts = array('comment' => 0, 'review' => 0, 'theory' => 0, 'voice' => 0);
    $all_c = get_comments(array('post_id' => $novel_post_id, 'status' => 'approve', 'parent' => 0, 'fields' => 'ids'));
    foreach ($all_c as $cid) {
        $t = get_comment_meta($cid, 'comment_type_novel', true);
        $t = $t ?: 'comment';
        if (isset($counts[$t])) $counts[$t]++;
    }
    set_transient($cache_key, $counts, HOUR_IN_SECONDS);
}
?>

<section class="novel-comments-section" id="commentsSection" data-post="<?php echo (int)$novel_post_id; ?>">

    <!-- ① شمارنده -->
    <h2 class="novel-comments-title">
        💬 <?php echo novel_to_persian($total_comments); ?> دیدگاه
    </h2>

    <!-- ② تب‌ها -->
    <?php if ($show_tabs): ?>
    <div class="novel-comment-tabs" role="tablist">
        <button class="novel-comment-tab <?php echo $current_tab === 'comment' ? 'active' : ''; ?>" data-tab="comment" role="tab">
            دیدگاه (<?php echo novel_to_persian($counts['comment']); ?>)
        </button>
        <?php if (novel_is_module_active('review')): ?>
        <button class="novel-comment-tab <?php echo $current_tab === 'review' ? 'active' : ''; ?>" data-tab="review" role="tab">
            نقد (<?php echo novel_to_persian($counts['review']); ?>)
        </button>
        <?php endif; ?>
        <?php if (novel_is_module_active('theory')): ?>
        <button class="novel-comment-tab <?php echo $current_tab === 'theory' ? 'active' : ''; ?>" data-tab="theory" role="tab">
            تئوری (<?php echo novel_to_persian($counts['theory']); ?>)
        </button>
        <?php endif; ?>
        <?php if (novel_is_module_active('voice')): ?>
        <button class="novel-comment-tab <?php echo $current_tab === 'voice' ? 'active' : ''; ?>" data-tab="voice" role="tab">
            🎙 یک‌خطی (<?php echo novel_to_persian($counts['voice']); ?>)
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ③ مرتب‌سازی -->
    <div class="novel-comment-sort">
        <button class="novel-sort-btn active" data-sort="newest">جدیدترین</button>
        <button class="novel-sort-btn" data-sort="oldest">قدیمی‌ترین</button>
        <button class="novel-sort-btn" data-sort="most_liked">بیشترین لایک</button>
        <button class="novel-sort-btn" data-sort="most_replied">بیشترین پاسخ</button>
    </div>

    <!-- ④ متن تشویقی -->
    <div class="novel-comment-encourage">
        <p>🖋️ قلمت را رها کن؛ دیدگاه‌هایت بخشی از دنیای داستان‌ها هستند. لطفاً مرتبط با روایت باشند و فضای خیال دیگران را آشفته نکنند.</p>
    </div>

    <!-- ⑤ هشدار -->
    <div class="novel-comment-warning">
        <p>⚠️ دیدگاه شما بلافاصله منتشر می‌شود! در صورت نقض
        <?php
        $rules = novel_get_option('novel_comment_rules_page');
        if ($rules): ?>
            <a href="<?php echo esc_url(get_permalink($rules)); ?>" target="_blank">قوانین</a>
        <?php else: ?>
            قوانین
        <?php endif; ?>
        ، حساب شما محدود خواهد شد.</p>
    </div>

    <!-- ⑥ فرم ارسال -->
    <div class="novel-comment-form-wrap" id="commentFormWrap">
        <?php if (!is_user_logged_in()): ?>
            <div class="novel-comment-login-prompt">
                <p>برای ارسال دیدگاه <a href="<?php echo esc_url(novel_get_auth_url('login')); ?>">وارد شوید</a> یا <a href="<?php echo esc_url(novel_get_auth_url('register')); ?>">ثبت‌نام کنید</a>.</p>
            </div>
        <?php elseif (!novel_is_email_verified(get_current_user_id())): ?>
            <div class="novel-comment-verify-prompt">
                <p>⚠️ ایمیل شما تأیید نشده. <button class="novel-link" id="resendVerifyBtn">ارسال مجدد لینک تأیید</button></p>
            </div>
        <?php else: ?>
            <form class="novel-comment-form" id="commentForm" data-post="<?php echo (int)$novel_post_id; ?>">
                <?php wp_nonce_field('novel_nonce', '_novel_comment_nonce'); ?>
                <input type="hidden" name="comment_tab" id="commentTabInput" value="<?php echo esc_attr($current_tab); ?>">
                <input type="hidden" name="parent_id" id="commentParentId" value="0">

                <div class="novel-comment-form-header">
                    <img src="<?php echo esc_url(novel_get_avatar(get_current_user_id(), 40)); ?>"
                         width="40" height="40" alt="" class="novel-comment-avatar">
                    <span class="novel-comment-user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                </div>

                <!-- ستاره (فقط نقد و یک‌خطی) -->
                <div class="novel-comment-rating-input novel-hidden" id="commentRatingWrap">
                    <label>امتیاز شما:</label>
                    <div class="novel-star-input" id="starInput">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg data-value="<?php echo $i; ?>" width="24" height="24" viewBox="0 0 24 24" fill="#d1d5db" class="novel-star-clickable">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="comment_rating" id="commentRatingValue" value="0">
                </div>

                <div class="novel-comment-textarea-wrap">
                    <textarea name="comment_content" id="commentContent" rows="3"
                              placeholder="دیدگاه خود را بنویسید..."
                              maxlength="<?php echo absint(novel_get_option('novel_comment_max_chars', 1000)); ?>"></textarea>
                    <div class="novel-comment-counter" id="commentCounter">
                        <span id="commentCharCount">۰</span> / <span><?php echo novel_to_persian(novel_get_option('novel_comment_max_chars', 1000)); ?></span>
                    </div>
                </div>

                <div class="novel-comment-form-actions">
                    <label class="novel-checkbox novel-checkbox-sm">
                        <input type="checkbox" name="is_spoiler" id="commentSpoiler" value="1">
                        <span>⚠️ اسپویلر</span>
                    </label>

                    <div class="novel-comment-form-btns">
                        <button type="button" class="novel-btn novel-btn-ghost novel-btn-sm novel-hidden" id="commentCancelReply">انصراف</button>
                        <button type="submit" class="novel-btn novel-btn-primary novel-btn-sm" id="commentSubmitBtn">
                            <span class="novel-btn-text">📤 ارسال</span>
                            <span class="novel-btn-loading novel-hidden">
                                <svg class="novel-spinner" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- ⑦ لیست دیدگاه‌ها -->
    <div class="novel-comments-list" id="commentsList">
        <?php
        $args = array(
            'post_id' => $novel_post_id,
            'status'  => 'approve',
            'parent'  => 0,
            'number'  => 20,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
        );

        // فیلتر بر اساس تب
        if ($show_tabs && $current_tab !== 'comment') {
            $args['meta_query'] = array(
                array('key' => 'comment_type_novel', 'value' => $current_tab),
            );
        } elseif ($show_tabs) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array('key' => 'comment_type_novel', 'value' => 'comment'),
                array('key' => 'comment_type_novel', 'compare' => 'NOT EXISTS'),
            );
        }

        $comments = get_comments($args);

        // Batch query votes
        $user_votes = array();
        $user_reactions = array();
        if (is_user_logged_in() && !empty($comments)) {
            global $wpdb;
            $uid = get_current_user_id();
            $cids = wp_list_pluck($comments, 'comment_ID');
            $cid_str = implode(',', array_map('intval', $cids));

            $votes_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT comment_id, vote FROM {$wpdb->prefix}comment_votes WHERE user_id = %d AND comment_id IN ({$cid_str})",
                $uid
            ));
            foreach ($votes_raw as $v) $user_votes[$v->comment_id] = (int)$v->vote;

            $reacts_raw = $wpdb->get_results($wpdb->prepare(
                "SELECT comment_id, reaction FROM {$wpdb->prefix}comment_reactions WHERE user_id = %d AND comment_id IN ({$cid_str})",
                $uid
            ));
            foreach ($reacts_raw as $r) $user_reactions[$r->comment_id] = $r->reaction;
        }

        if (empty($comments)) {
            echo '<div class="novel-comments-empty"><p>هنوز دیدگاهی نوشته نشده. اولین نفر باشید! ✍️</p></div>';
        }

        foreach ($comments as $comment) {
            $uv = isset($user_votes[$comment->comment_ID]) ? $user_votes[$comment->comment_ID] : 0;
            $ur = isset($user_reactions[$comment->comment_ID]) ? $user_reactions[$comment->comment_ID] : '';
            novel_render_comment($comment, $uv, $ur, 1);
        }
        ?>
    </div>

    <!-- ⑧ بارگذاری بیشتر -->
    <?php if (count($comments) >= 20): ?>
    <div class="novel-comments-loadmore">
        <button class="novel-btn novel-btn-outline novel-btn-block" id="loadMoreComments"
                data-post="<?php echo (int)$novel_post_id; ?>" data-page="2"
                data-tab="<?php echo esc_attr($current_tab); ?>">
            بارگذاری بیشتر ↓
        </button>
        <small class="novel-text-muted">نمایش <?php echo novel_to_persian(min(20, count($comments))); ?> از <?php echo novel_to_persian($total_comments); ?></small>
    </div>
    <?php endif; ?>

</section>

<?php
/**
 * رندر یک دیدگاه
 */
function novel_render_comment($comment, $user_vote = 0, $user_reaction = '', $depth = 1) {
    $cid         = $comment->comment_ID;
    $uid         = (int)$comment->user_id;
    $name        = esc_html($comment->comment_author);
    $avatar      = $uid ? novel_get_avatar($uid, 40) : get_avatar_url($comment->comment_author_email, array('size' => 40));
    $content     = wp_kses_post($comment->comment_content);
    $date        = novel_time_ago($comment->comment_date);
    $is_spoiler  = (bool)get_comment_meta($cid, 'is_spoiler', true);
    $is_pinned   = (bool)get_comment_meta($cid, 'is_pinned', true);
    $is_edited   = (bool)get_comment_meta($cid, 'last_edited', true);
    $comment_type = get_comment_meta($cid, 'comment_type_novel', true) ?: 'comment';
    $likes       = absint(get_comment_meta($cid, 'likes_count', true));
    $dislikes    = absint(get_comment_meta($cid, 'dislikes_count', true));

    // بج‌ها
    $badges = $uid ? novel_get_user_badge($uid) : array();

    // نویسنده رمان؟
    $post_author = (int)get_post_field('post_author', $comment->comment_post_ID);
    if ($uid && $uid === $post_author) {
        array_unshift($badges, array('label' => 'نویسنده', 'icon' => '✍️', 'color' => '#f59e0b'));
    }

    // تعداد پاسخ
    $reply_count = absint(get_comment_meta($cid, 'reply_count', true));

    // ری‌اکشن‌ها
    $reactions = array();
    if (novel_is_module_active('reactions')) {
        global $wpdb;
        $rr = $wpdb->get_results($wpdb->prepare(
            "SELECT reaction, COUNT(id) as cnt FROM {$wpdb->prefix}comment_reactions WHERE comment_id = %d GROUP BY reaction",
            $cid
        ));
        foreach ($rr as $r) $reactions[$r->reaction] = (int)$r->cnt;
    }

    $reaction_icons = array(
        'love' => '😍', 'shocked' => '🤯', 'sad' => '😢', 'angry' => '😡', 'fire' => '🔥',
    );

    $can_edit = false;
    if (is_user_logged_in() && $uid === get_current_user_id()) {
        $edit_time = absint(novel_get_option('novel_edit_time_minutes', 15));
        $comment_time = strtotime($comment->comment_date);
        if ((current_time('timestamp') - $comment_time) < ($edit_time * 60)) {
            $can_edit = true;
        }
    }
    ?>
    <div class="novel-comment <?php echo $is_pinned ? 'novel-comment-pinned' : ''; ?> novel-comment-depth-<?php echo (int)$depth; ?>"
         id="comment-<?php echo (int)$cid; ?>" data-id="<?php echo (int)$cid; ?>">

        <?php if ($is_pinned): ?>
            <div class="novel-comment-pin-label">📌 پین شده</div>
        <?php endif; ?>

        <div class="novel-comment-header">
            <img src="<?php echo esc_url($avatar); ?>" width="40" height="40" alt="" class="novel-comment-avatar" loading="lazy">
            <div class="novel-comment-meta">
                <div class="novel-comment-name-row">
                    <strong><?php echo $name; ?></strong>
                    <?php foreach (array_slice($badges, 0, 3) as $badge): ?>
                        <span class="novel-user-badge" style="background:<?php echo esc_attr($badge['color']); ?>15;color:<?php echo esc_attr($badge['color']); ?>"><?php echo $badge['icon']; ?> <?php echo esc_html($badge['label']); ?></span>
                    <?php endforeach; ?>
                    <?php if ($comment_type === 'theory'): ?>
                        <span class="novel-comment-type-badge novel-badge-purple">🧠 تئوری</span>
                    <?php endif; ?>
                </div>
                <span class="novel-comment-date">
                    <?php echo esc_html($date); ?>
                    <?php if ($is_edited): ?><span class="novel-edited">(ویرایش‌شده)</span><?php endif; ?>
                </span>
            </div>
        </div>

        <!-- محتوا -->
        <div class="novel-comment-body">
            <?php if ($is_spoiler): ?>
                <div class="novel-spoiler-wrap" data-revealed="0">
                    <div class="novel-spoiler-overlay">
                        <span>⚠️ اسپویلر!</span>
                        <button class="novel-btn novel-btn-sm novel-btn-warning novel-spoiler-reveal">نمایش محتوا</button>
                    </div>
                    <div class="novel-spoiler-content novel-blurred"><?php echo $content; ?></div>
                </div>
            <?php else: ?>
                <div class="novel-comment-content"><?php echo $content; ?></div>
            <?php endif; ?>
        </div>

        <!-- ری‌اکشن‌ها -->
        <?php if (novel_is_module_active('reactions') && !empty($reactions)): ?>
        <div class="novel-comment-reactions">
            <?php foreach ($reactions as $rkey => $rcount): ?>
                <button class="novel-reaction-btn <?php echo ($user_reaction === $rkey) ? 'active' : ''; ?>"
                        data-comment="<?php echo (int)$cid; ?>" data-reaction="<?php echo esc_attr($rkey); ?>">
                    <?php echo $reaction_icons[$rkey]; ?>×<?php echo novel_to_persian($rcount); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- دکمه‌ها -->
        <div class="novel-comment-actions">
            <div class="novel-comment-votes">
                <button class="novel-vote-btn novel-vote-up <?php echo $user_vote === 1 ? 'active' : ''; ?>"
                        data-comment="<?php echo (int)$cid; ?>" data-vote="1">
                    👍 <span class="novel-vote-count"><?php echo novel_to_persian($likes); ?></span>
                </button>
                <button class="novel-vote-btn novel-vote-down <?php echo $user_vote === -1 ? 'active' : ''; ?>"
                        data-comment="<?php echo (int)$cid; ?>" data-vote="-1">
                    👎 <span class="novel-vote-count"><?php echo novel_to_persian($dislikes); ?></span>
                </button>
            </div>

            <?php if (is_user_logged_in() && novel_is_email_verified(get_current_user_id())): ?>
            <button class="novel-reply-btn" data-comment="<?php echo (int)$cid; ?>" data-name="<?php echo esc_attr($name); ?>">
                💬 پاسخ
            </button>
            <?php endif; ?>

            <?php if ($can_edit): ?>
            <button class="novel-edit-btn" data-comment="<?php echo (int)$cid; ?>">✏️ ویرایش</button>
            <?php endif; ?>

            <?php if (is_user_logged_in()): ?>
            <button class="novel-report-btn" data-type="comment" data-id="<?php echo (int)$cid; ?>">🚩</button>
            <?php endif; ?>

            <!-- ری‌اکشن اضافه -->
            <?php if (novel_is_module_active('reactions') && is_user_logged_in() && novel_is_email_verified(get_current_user_id())): ?>
            <div class="novel-add-reaction">
                <button class="novel-add-reaction-toggle">😊+</button>
                <div class="novel-reaction-picker novel-hidden">
                    <?php foreach ($reaction_icons as $rk => $ri): ?>
                        <button class="novel-reaction-pick <?php echo ($user_reaction === $rk) ? 'active' : ''; ?>"
                                data-comment="<?php echo (int)$cid; ?>" data-reaction="<?php echo esc_attr($rk); ?>"><?php echo $ri; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- پاسخ‌ها -->
        <?php if ($reply_count > 0): ?>
        <div class="novel-comment-replies-toggle">
            <button class="novel-toggle-replies" data-comment="<?php echo (int)$cid; ?>" data-loaded="0">
                💬 <?php echo novel_to_persian($reply_count); ?> پاسخ
            </button>
        </div>
        <div class="novel-comment-replies novel-hidden" id="replies-<?php echo (int)$cid; ?>"></div>
        <?php endif; ?>

    </div>
    <?php
}