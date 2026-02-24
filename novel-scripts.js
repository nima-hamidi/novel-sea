/**
 * Novel Theme — Frontend JavaScript
 * @package NovelTheme
 */
(function() {
'use strict';

const D = document;
const $ = (s, p) => (p || D).querySelector(s);
const $$ = (s, p) => [...(p || D).querySelectorAll(s)];
const ND = window.NovelData || {};

// ═══════════════════════════════════════
// ① Header: Auto-hide + Dropdowns
// ═══════════════════════════════════════
let lastScroll = 0;
const header = $('#mainHeader');
if (header) {
    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        if (y > 100 && y > lastScroll) header.classList.add('novel-header-hidden');
        else header.classList.remove('novel-header-hidden');
        lastScroll = y;
    }, { passive: true });
}

// Toggle dropdown helper
function setupDropdown(toggleId, dropdownId, wrapperId) {
    const toggle = D.getElementById(toggleId);
    const dropdown = D.getElementById(dropdownId);
    if (!toggle || !dropdown) return;
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = dropdown.getAttribute('aria-hidden') === 'false';
        dropdown.setAttribute('aria-hidden', open ? 'true' : 'false');
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
    D.addEventListener('click', (e) => {
        const wrapper = D.getElementById(wrapperId);
        if (wrapper && !wrapper.contains(e.target)) {
            dropdown.setAttribute('aria-hidden', 'true');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
}
setupDropdown('notifToggle', 'notifDropdown', 'notifWrapper');
setupDropdown('profileToggle', 'profileDropdown', 'profileWrapper');

// ═══════════════════════════════════════
// ② Dark Mode
// ═══════════════════════════════════════
const themeToggle = $('#themeToggle');
if (themeToggle) {
    const saved = localStorage.getItem('novel_theme') || 'light';
    D.body.dataset.theme = saved;
    themeToggle.addEventListener('click', () => {
        const next = D.body.dataset.theme === 'dark' ? 'light' : 'dark';
        D.body.dataset.theme = next;
        localStorage.setItem('novel_theme', next);
    });
}

// ═══════════════════════════════════════
// ③ Mobile Menu
// ═══════════════════════════════════════
const mobileToggle = $('#mobileMenuToggle');
const mobileMenu = $('#mobileMenu');
const mobileOverlay = $('#mobileOverlay');
const mobileClose = $('#mobileMenuClose');
if (mobileToggle && mobileMenu) {
    const openMenu = () => { mobileMenu.setAttribute('aria-hidden','false'); mobileOverlay.classList.add('active'); };
    const closeMenu = () => { mobileMenu.setAttribute('aria-hidden','true'); mobileOverlay.classList.remove('active'); };
    mobileToggle.addEventListener('click', openMenu);
    if (mobileClose) mobileClose.addEventListener('click', closeMenu);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMenu);
}

// ═══════════════════════════════════════
// ④ Search
// ═══════════════════════════════════════
const searchToggle = $('#searchToggle');
const searchOverlay = $('#searchOverlay');
const searchClose = $('#searchClose');
const searchInput = $('#searchInput');
const searchResults = $('#searchResults');
let searchTimer;

if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', () => {
        searchOverlay.setAttribute('aria-hidden','false');
        setTimeout(() => searchInput && searchInput.focus(), 100);
    });
    if (searchClose) searchClose.addEventListener('click', () => searchOverlay.setAttribute('aria-hidden','true'));
    D.addEventListener('keydown', (e) => { if (e.key === 'Escape') searchOverlay.setAttribute('aria-hidden','true'); });

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = searchInput.value.trim();
            if (q.length < 2) { searchResults.innerHTML = ''; return; }
            searchTimer = setTimeout(() => {
                const fd = new FormData();
                fd.append('action', 'novel_live_search');
                fd.append('nonce', ND.nonce);
                fd.append('q', q);
                fetch(ND.ajax_url, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => { if (d.success) searchResults.innerHTML = d.data.html; });
            }, 300);
        });
    }
}

// ═══════════════════════════════════════
// ⑤ Announcement Bar
// ═══════════════════════════════════════
window.novelCloseAnnouncement = function() {
    const bar = $('#announcementBar');
    if (bar) { bar.style.display = 'none'; localStorage.setItem('novel_announcement_closed', Date.now()); }
};
(function() {
    const bar = $('#announcementBar');
    if (!bar) return;
    const closed = localStorage.getItem('novel_announcement_closed');
    if (closed && (Date.now() - parseInt(closed)) < 86400000) bar.style.display = 'none';
})();

// ═══════════════════════════════════════
// ⑥ AJAX Helper
// ═══════════════════════════════════════
function novelAjax(action, data, callback) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', ND.nonce);
    if (data) Object.entries(data).forEach(([k,v]) => fd.append(k, v));
    fetch(ND.ajax_url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => callback(d))
        .catch(() => novelToast(ND.strings?.error || 'خطا', 'danger'));
}

function novelToast(msg, type = 'success') {
    const t = D.createElement('div');
    t.className = `novel-toast novel-toast-${type}`;
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);z-index:9999;padding:12px 24px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,.15);animation:novel-fadeUp .3s ease;';
    if (type === 'success') t.style.background = '#22c55e'; t.style.color = '#fff';
    if (type === 'danger') { t.style.background = '#ef4444'; t.style.color = '#fff'; }
    D.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// ═══════════════════════════════════════
// ⑦ Auth Forms
// ═══════════════════════════════════════
const loginForm = $('#loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const btn = $('#loginSubmit');
        btn.disabled = true;
        btn.querySelector('.novel-btn-text').classList.add('novel-hidden');
        btn.querySelector('.novel-btn-loading').classList.remove('novel-hidden');
        novelAjax('novel_login', Object.fromEntries(new FormData(loginForm)), (d) => {
            btn.disabled = false;
            btn.querySelector('.novel-btn-text').classList.remove('novel-hidden');
            btn.querySelector('.novel-btn-loading').classList.add('novel-hidden');
            if (d.success) { novelToast(d.data.message); setTimeout(() => window.location.href = d.data.redirect, 500); }
            else { novelToast(d.data.message, 'danger'); }
        });
    });
}

const registerForm = $('#registerForm');
if (registerForm) {
    // Username check
    const regName = $('#regName');
    let nameTimer;
    if (regName) regName.addEventListener('input', () => {
        clearTimeout(nameTimer);
        const v = regName.value.trim();
        const s = $('#regNameStatus');
        if (v.length < 3) { s.textContent = '⚠️'; s.style.color = '#f59e0b'; return; }
        nameTimer = setTimeout(() => {
            novelAjax('novel_check_username', { display_name: v }, (d) => {
                s.textContent = d.success ? '✅' : '❌';
                s.style.color = d.success ? '#22c55e' : '#ef4444';
            });
        }, 500);
    });

    // Email check
    const regEmail = $('#regEmail');
    let emailTimer;
    if (regEmail) regEmail.addEventListener('input', () => {
        clearTimeout(emailTimer);
        emailTimer = setTimeout(() => {
            novelAjax('novel_check_email', { email: regEmail.value }, (d) => {
                const s = $('#regEmailStatus');
                s.textContent = d.success ? '✅' : '❌';
                s.style.color = d.success ? '#22c55e' : '#ef4444';
            });
        }, 500);
    });

    // Password strength
    const regPass = $('#regPassword');
    if (regPass) regPass.addEventListener('input', () => {
        const v = regPass.value;
        const bar = $('#passStrengthBar');
        const label = $('#passStrengthLabel');
        let str = 0;
        if (v.length >= 8) str++;
        if (/\d/.test(v)) str++;
        if (/[A-Z]/.test(v)) str++;
        if (/[^a-zA-Z0-9]/.test(v)) str++;
        const colors = ['#ef4444','#f59e0b','#22c55e','#22c55e'];
        const labels = ['ضعیف','متوسط','قوی','خیلی قوی'];
        bar.style.width = (str * 25) + '%';
        bar.style.background = colors[Math.max(0,str-1)] || '#ef4444';
        label.textContent = v.length > 0 ? labels[Math.max(0,str-1)] || 'ضعیف' : '';
    });

    // Confirm password
    const regConfirm = $('#regPasswordConfirm');
    if (regConfirm) regConfirm.addEventListener('input', () => {
        const s = $('#regPassConfirmStatus');
        s.textContent = regConfirm.value === regPass.value ? '✅' : '❌';
        s.style.color = regConfirm.value === regPass.value ? '#22c55e' : '#ef4444';
    });

    // Agree checkbox → enable button
    const regAgree = $('#regAgree');
    const regSubmit = $('#registerSubmit');
    if (regAgree && regSubmit) regAgree.addEventListener('change', () => { regSubmit.disabled = !regAgree.checked; });

    registerForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const btn = regSubmit;
        btn.disabled = true;
        btn.querySelector('.novel-btn-text').classList.add('novel-hidden');
        btn.querySelector('.novel-btn-loading').classList.remove('novel-hidden');
        novelAjax('novel_register', Object.fromEntries(new FormData(registerForm)), (d) => {
            btn.disabled = false;
            btn.querySelector('.novel-btn-text').classList.remove('novel-hidden');
            btn.querySelector('.novel-btn-loading').classList.add('novel-hidden');
            if (d.success) novelToast(d.data.message);
            else novelToast(d.data.message, 'danger');
        });
    });
}

// Forgot password
const forgotLink = $('#forgotPassLink');
const forgotForm = $('#forgotForm');
const backToLogin = $('#backToLogin');
if (forgotLink && forgotForm && loginForm) {
    forgotLink.addEventListener('click', (e) => { e.preventDefault(); loginForm.classList.add('novel-hidden'); forgotForm.classList.remove('novel-hidden'); });
    if (backToLogin) backToLogin.addEventListener('click', (e) => { e.preventDefault(); forgotForm.classList.add('novel-hidden'); loginForm.classList.remove('novel-hidden'); });
    forgotForm.addEventListener('submit', (e) => {
        e.preventDefault();
        novelAjax('novel_forgot_password', Object.fromEntries(new FormData(forgotForm)), (d) => {
            novelToast(d.data?.message || 'ارسال شد');
        });
    });
}

// Toggle password visibility
$$('.novel-field-toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = D.getElementById(btn.dataset.target);
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
});

// ═══════════════════════════════════════
// ⑧ Voting (Comments + Chapters)
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const voteBtn = e.target.closest('.novel-vote-btn[data-comment]');
    if (voteBtn) {
        if (!ND.is_logged_in) { novelToast(ND.strings?.login_required || 'وارد شوید', 'danger'); return; }
        novelAjax('novel_comment_vote', { comment_id: voteBtn.dataset.comment, vote: voteBtn.dataset.vote }, (d) => {
            if (!d.success) { novelToast(d.data?.message || 'خطا', 'danger'); return; }
            const parent = voteBtn.closest('.novel-comment-votes') || voteBtn.closest('.novel-comment-actions');
            if (parent) {
                const up = parent.querySelector('.novel-vote-up');
                const down = parent.querySelector('.novel-vote-down');
                if (up) { up.querySelector('.novel-vote-count').textContent = d.data.likes; up.classList.toggle('active', d.data.user_vote === 1); }
                if (down) { down.querySelector('.novel-vote-count').textContent = d.data.dislikes; down.classList.toggle('active', d.data.user_vote === -1); }
            }
        });
    }

    const chVote = e.target.closest('.novel-vote-btn[data-chapter]');
    if (chVote && !chVote.dataset.comment) {
        if (!ND.is_logged_in) { novelToast(ND.strings?.login_required || 'وارد شوید', 'danger'); return; }
        novelAjax('novel_chapter_vote', { chapter_id: chVote.dataset.chapter, vote: chVote.dataset.vote }, (d) => {
            if (!d.success) { novelToast(d.data?.message || 'خطا', 'danger'); return; }
            $$('.novel-chapter-votes[data-chapter="'+chVote.dataset.chapter+'"]').forEach(wrap => {
                const up = wrap.querySelector('.novel-vote-up');
                const down = wrap.querySelector('.novel-vote-down');
                if (up) { up.querySelector('.novel-vote-count').textContent = d.data.likes; up.classList.toggle('active', d.data.user_vote === 1); }
                if (down) { down.querySelector('.novel-vote-count').textContent = d.data.dislikes; down.classList.toggle('active', d.data.user_vote === -1); }
                const pct = wrap.querySelector('.novel-approval');
                if (pct) pct.textContent = d.data.percent + '% پسندیدند';
            });
        });
    }
});

// ═══════════════════════════════════════
// ⑨ Reactions
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const pick = e.target.closest('.novel-reaction-pick, .novel-reaction-btn');
    if (pick && pick.dataset.comment && pick.dataset.reaction) {
        novelAjax('novel_comment_reaction', { comment_id: pick.dataset.comment, reaction: pick.dataset.reaction }, (d) => {
            if (d.success) novelToast('✅');
        });
    }
    const toggleR = e.target.closest('.novel-add-reaction-toggle');
    if (toggleR) {
        const picker = toggleR.nextElementSibling;
        if (picker) picker.classList.toggle('novel-hidden');
    }
});

// ═══════════════════════════════════════
// ⑩ Follow Novel
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-follow-btn[data-novel]');
    if (!btn) return;
    if (!ND.is_logged_in) { novelToast(ND.strings?.login_required || 'وارد شوید', 'danger'); return; }
    novelAjax('novel_follow_novel', { novel_id: btn.dataset.novel }, (d) => {
        if (!d.success) { novelToast(d.data?.message || 'خطا', 'danger'); return; }
        btn.dataset.followed = d.data.is_followed ? '1' : '0';
        btn.textContent = d.data.is_followed ? '💔 لغو' : '❤ دنبال کردن';
        novelToast(d.data.is_followed ? 'دنبال شد ❤' : 'لغو شد');
    });
});

// Follow User
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-follow-user-btn[data-user]');
    if (!btn) return;
    if (!ND.is_logged_in) { novelToast(ND.strings?.login_required || 'وارد شوید', 'danger'); return; }
    novelAjax('novel_follow_user', { user_id: btn.dataset.user }, (d) => {
        if (d.success) {
            btn.textContent = d.data.is_followed ? '💔 لغو فالو' : '❤ فالو';
            novelToast(d.data.is_followed ? 'فالو شد ❤' : 'لغو شد');
        }
    });
});

// ═══════════════════════════════════════
// ⑪ Library
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const toggle = e.target.closest('.novel-library-toggle');
    if (toggle) { const menu = toggle.nextElementSibling; if (menu) menu.classList.toggle('novel-hidden'); }

    const item = e.target.closest('.novel-library-item');
    if (item) {
        const act = item.classList.contains('novel-library-remove') ? 'remove' : 'add';
        novelAjax('novel_library_action', { novel_id: item.dataset.novel, list_type: item.dataset.type, lib_action: act }, (d) => {
            if (d.success) {
                novelToast(d.data.message);
                const menu = item.closest('.novel-library-menu');
                if (menu) menu.classList.add('novel-hidden');
            } else novelToast(d.data?.message || 'خطا', 'danger');
        });
    }
});

// ═══════════════════════════════════════
// ⑫ Star Rating (Novel)
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const star = e.target.closest('.novel-star-rate-star');
    if (!star) return;
    if (!ND.is_logged_in) { novelToast(ND.strings?.login_required, 'danger'); return; }
    const rating = parseInt(star.dataset.value);
    const wrap = star.closest('.novel-star-rate');
    const novelId = star.closest('[data-novel]')?.dataset.novel;
    if (!novelId) return;
    novelAjax('novel_rate', { novel_id: novelId, rating: rating }, (d) => {
        if (d.success) {
            wrap.dataset.current = d.data.my_rating;
            $$('.novel-star-rate-star', wrap).forEach((s,i) => { s.setAttribute('fill', i < d.data.my_rating ? '#fbbf24' : '#d1d5db'); });
            const txt = wrap.parentElement?.querySelector('.novel-rating-text');
            if (txt) txt.textContent = d.data.avg + ' (' + d.data.count + ' رأی)';
            novelToast('امتیاز ثبت شد ⭐');
        }
    });
});

// ═══════════════════════════════════════
// ⑬ Copy Link
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-copy-link');
    if (!btn) return;
    navigator.clipboard.writeText(btn.dataset.url || window.location.href).then(() => novelToast(ND.strings?.copied || 'کپی شد ✓'));
});

// ═══════════════════════════════════════
// ⑭ Spoiler Reveal
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-spoiler-reveal');
    if (btn) { const wrap = btn.closest('.novel-spoiler-wrap'); if (wrap) wrap.dataset.revealed = '1'; }
});

// ═══════════════════════════════════════
// ⑮ Reading Progress
// ═══════════════════════════════════════
const progressFill = $('#progressFill');
if (progressFill) {
    window.addEventListener('scroll', () => {
        const h = D.documentElement.scrollHeight - window.innerHeight;
        if (h > 0) progressFill.style.width = Math.min(100, (window.scrollY / h) * 100) + '%';
    }, { passive: true });
}

// ═══════════════════════════════════════
// ⑯ Reader Settings
// ═══════════════════════════════════════
const readerToggle = $('#readerSettingsToggle');
const readerPanel = $('#readerSettings');
if (readerToggle && readerPanel) {
    readerToggle.addEventListener('click', () => readerPanel.classList.toggle('novel-hidden'));

    const content = $('#chapterContent');
    const saved = JSON.parse(localStorage.getItem('novel_reader') || '{}');

    function applyReader(s) {
        if (!content) return;
        if (s.fontSize) content.style.fontSize = s.fontSize + 'px';
        if (s.lineHeight) content.style.lineHeight = s.lineHeight;
        if (s.maxWidth) content.style.maxWidth = s.maxWidth + 'px';
    }
    applyReader(saved);

    const fontSize = $('#readerFontSize');
    if (fontSize) { fontSize.value = saved.fontSize || 18; fontSize.addEventListener('input', () => { saved.fontSize = fontSize.value; applyReader(saved); localStorage.setItem('novel_reader', JSON.stringify(saved)); const l = $('#readerFontSizeLabel'); if (l) l.textContent = fontSize.value; }); }

    const lineHeight = $('#readerLineHeight');
    if (lineHeight) { lineHeight.value = saved.lineHeight || 1.8; lineHeight.addEventListener('input', () => { saved.lineHeight = lineHeight.value; applyReader(saved); localStorage.setItem('novel_reader', JSON.stringify(saved)); const l = $('#readerLineHeightLabel'); if (l) l.textContent = lineHeight.value; }); }

    const width = $('#readerWidth');
    if (width) { width.value = saved.maxWidth || 800; width.addEventListener('input', () => { saved.maxWidth = width.value; applyReader(saved); localStorage.setItem('novel_reader', JSON.stringify(saved)); }); }

    $$('.novel-reader-theme').forEach(btn => {
        btn.addEventListener('click', () => {
            $$('.novel-reader-theme').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const themes = { white:{bg:'#fff',color:'#333'}, sepia:{bg:'#f4ecd8',color:'#5b4636'}, dark:{bg:'#2d2d2d',color:'#d4d4d4'}, black:{bg:'#000',color:'#ccc'} };
            const t = themes[btn.dataset.theme];
            if (content && t) { content.style.background = t.bg; content.style.color = t.color; content.style.padding = '24px'; content.style.borderRadius = '12px'; }
        });
    });
}

// ═══════════════════════════════════════
// ⑰ Notifications
// ═══════════════════════════════════════
const notifToggle = $('#notifToggle');
if (notifToggle && ND.is_logged_in) {
    notifToggle.addEventListener('click', () => {
        novelAjax('novel_get_notifications', {}, (d) => {
            if (d.success) { const list = $('#notifList'); if (list) list.innerHTML = d.data.html; }
        });
    });

    const readAll = $('#notifReadAll');
    if (readAll) readAll.addEventListener('click', () => {
        novelAjax('novel_mark_notifications_read', {}, (d) => {
            if (d.success) { const badge = $('#notifBadge'); if (badge) badge.style.display = 'none'; novelToast('همه خوانده شد ✅'); }
        });
    });

    // Polling
    setInterval(() => {
        novelAjax('novel_unread_count', {}, (d) => {
            if (d.success) {
                const badge = $('#notifBadge');
                if (badge) { badge.style.display = d.data.count > 0 ? 'flex' : 'none'; badge.textContent = d.data.count > 99 ? '99+' : d.data.count; }
            }
        });
    }, 60000);
}

// ═══════════════════════════════════════
// ⑱ Report
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-report-btn');
    if (!btn) return;
    if (!ND.is_logged_in) { novelToast(ND.strings?.login_required, 'danger'); return; }
    const reason = prompt('دلیل گزارش: اسپم / توهین / محتوای نامناسب / سایر');
    if (!reason) return;
    novelAjax('novel_report', { reported_type: btn.dataset.type, reported_id: btn.dataset.id, reason: reason }, (d) => {
        novelToast(d.success ? d.data.message : (d.data?.message || 'خطا'), d.success ? 'success' : 'danger');
    });
});

// ═══════════════════════════════════════
// ⑲ Dashboard Forms
// ═══════════════════════════════════════
const profileForm = $('#profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const fd = Object.fromEntries(new FormData(profileForm));
        fd._profile_nonce = $('[name="_profile_nonce"]', profileForm)?.value;
        novelAjax('novel_save_profile', fd, (d) => novelToast(d.data?.message || (d.success ? '✅' : '❌'), d.success ? 'success' : 'danger'));
    });
}

const settingsForm = $('#settingsForm');
if (settingsForm) {
    settingsForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const fd = new FormData(settingsForm);
        const obj = {};
        fd.forEach((v,k) => { obj[k] = v; });
        obj._settings_nonce = $('[name="_settings_nonce"]', settingsForm)?.value;
        novelAjax('novel_save_settings', obj, (d) => novelToast(d.data?.message || '✅'));
    });
}

// Avatar selection
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-avatar-pick');
    if (!btn) return;
    $$('.novel-avatar-pick').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    novelAjax('novel_save_avatar', { avatar_id: btn.dataset.avatar }, (d) => { if (d.success) novelToast(d.data.message); });
});

// Resend verify
const resendBtn = D.getElementById('resendVerifyBtn');
if (resendBtn) resendBtn.addEventListener('click', () => {
    novelAjax('novel_resend_verify', {}, (d) => novelToast(d.data?.message || '✅', d.success ? 'success' : 'danger'));
});

// Clear history
const clearHist = D.getElementById('clearHistoryBtn');
if (clearHist) clearHist.addEventListener('click', () => {
    if (!confirm('تاریخچه پاک شود؟')) return;
    novelAjax('novel_clear_history', {}, (d) => { if (d.success) { novelToast('پاک شد'); location.reload(); } });
});

// Mark all read (dashboard)
const markAll = D.getElementById('markAllReadBtn');
if (markAll) markAll.addEventListener('click', () => {
    novelAjax('novel_mark_notifications_read', {}, (d) => { if (d.success) { novelToast('✅'); location.reload(); } });
});

// Delete account
const delBtn = D.getElementById('deleteAccountBtn');
if (delBtn) delBtn.addEventListener('click', () => {
    const pw = prompt('برای حذف حساب رمز عبور خود را وارد کنید:');
    if (!pw) return;
    if (!confirm('آیا مطمئن هستید؟ این عمل غیرقابل بازگشت است!')) return;
    novelAjax('novel_delete_account', { password: pw }, (d) => {
        if (d.success) { novelToast(d.data.message); setTimeout(() => window.location.href = d.data.redirect, 1000); }
        else novelToast(d.data?.message || 'خطا', 'danger');
    });
});

// ═══════════════════════════════════════
// ⑳ VIP Purchase
// ═══════════════════════════════════════
D.addEventListener('click', (e) => {
    const btn = e.target.closest('.novel-buy-chapter');
    if (!btn) return;
    if (!confirm('آیا مطمئنید؟ ' + btn.dataset.price + ' سکه کسر می‌شود.')) return;
    novelAjax('novel_purchase_chapter', { chapter_id: btn.dataset.chapter }, (d) => {
        if (d.success) { novelToast(d.data?.message || 'خریداری شد ✅'); location.reload(); }
        else novelToast(d.data?.message || 'خطا', 'danger');
    });
});

// ═══════════════════════════════════════
// Ambient Audio
// ═══════════════════════════════════════
const ambientToggle = D.getElementById('ambientToggle');
if (ambientToggle) {
    let audio;
    ambientToggle.addEventListener('click', () => {
        if (!audio) {
            audio = new Audio(ND.theme_url + '/assets/audio/ambient/' + ambientToggle.dataset.mood + '.mp3');
            audio.loop = true; audio.volume = 0.3;
        }
        if (audio.paused) { audio.play(); ambientToggle.textContent = '⏸ مکث'; }
        else { audio.pause(); ambientToggle.textContent = '▶ پخش'; }
    });
    const vol = D.getElementById('ambientVolume');
    if (vol) vol.addEventListener('input', () => { if (audio) audio.volume = vol.value / 100; });
}

})();