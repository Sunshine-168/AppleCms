document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('mobileToggle');
    const nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('show');
        });
    }

const userMenu = document.querySelector('.user-menu');
if (userMenu) {
    let hideTimeout;
    const dropdown = userMenu.querySelector('.user-dropdown');
    function showDropdown() {
        if (hideTimeout) clearTimeout(hideTimeout);
        if (dropdown) dropdown.style.display = 'block';
    }
    function hideDropdown() {
        if (hideTimeout) clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
            if (dropdown) dropdown.style.display = 'none';
        }, 200);
    }
    userMenu.addEventListener('mouseenter', showDropdown);
    userMenu.addEventListener('mouseleave', hideDropdown);
    if (dropdown) {
        dropdown.addEventListener('mouseenter', showDropdown);
        dropdown.addEventListener('mouseleave', hideDropdown);
    }
}

    document.body.addEventListener('click', function(e) {
        const addBtn = e.target.closest('.add-bookshelf');
        if (addBtn) {
            e.preventDefault();
            const novelId = addBtn.dataset.novelId;
            if (!novelId) return;
            fetch('/api/ajax/add_bookshelf.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'novel_id=' + encodeURIComponent(novelId)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.code === 1) {
                    addBtn.disabled = true;
                    addBtn.innerHTML = '<i class="fas fa-check"></i> ' + window.lang.already_in_bookshelf;
                }
            })
            .catch(err => alert('Request failed'));
        }
    });

    document.body.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-bookshelf');
        if (removeBtn) {
            e.preventDefault();
            if (!confirm(window.lang.confirm_delete)) return;
            const novelId = removeBtn.dataset.novelId;
            fetch('/api/ajax/remove_bookshelf.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'novel_id=' + novelId
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.code === 1) {
                    const item = removeBtn.closest('.bookshelf-item');
                    if (item) item.remove();
                    if (document.querySelectorAll('.bookshelf-item').length === 0) location.reload();
                }
            });
        }
    });

    const chapterContent = document.getElementById('chapterContent');
    if (chapterContent) {
        let fontSize = parseInt(localStorage.getItem('readFontSize') || '18');
        let theme = localStorage.getItem('readTheme') || 'light';
        const fontInc = document.getElementById('fontIncrease');
        const fontDec = document.getElementById('fontDecrease');
        const themeToggle = document.getElementById('themeToggle');
        function applyFont() {
            if (chapterContent) chapterContent.style.fontSize = fontSize + 'px';
            localStorage.setItem('readFontSize', fontSize);
        }
        function applyTheme() {
            if (theme === 'dark') {
                document.body.classList.add('dark-read');
                if (themeToggle) themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                document.body.classList.remove('dark-read');
                if (themeToggle) themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
            localStorage.setItem('readTheme', theme);
        }
        if (fontInc) fontInc.addEventListener('click', () => { fontSize = Math.min(28, fontSize+2); applyFont(); });
        if (fontDec) fontDec.addEventListener('click', () => { fontSize = Math.max(12, fontSize-2); applyFont(); });
        if (themeToggle) themeToggle.addEventListener('click', () => { theme = theme === 'light' ? 'dark' : 'light'; applyTheme(); });
        applyFont();
        applyTheme();
    }

    const rankTabs = document.querySelectorAll('.rank-tab');
    if (rankTabs.length) {
        rankTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const type = this.dataset.type;
                if (type) window.location.href = '/rank?type=' + type;
            });
        });
    }

    document.body.addEventListener('click', function(e) {
        const sendBtn = e.target.closest('#sendCodeBtn');
        if (!sendBtn) return;
        e.preventDefault();
        const emailInput = document.getElementById('email');
        const captchaInput = document.getElementById('captchaInput');
        const email = emailInput ? emailInput.value : '';
        const captcha = captchaInput ? captchaInput.value : '';
        if (!email) {
            alert(window.lang.fill_email_first);
            return;
        }
        if (!captcha) {
            alert(window.lang.captcha_error);
            return;
        }
        sendBtn.disabled = true;
        const originalText = sendBtn.innerText;
        sendBtn.innerText = window.lang.sending;
        const url = window.location.pathname.includes('register') ? '?send_code=1&email=' + encodeURIComponent(email) + '&captcha=' + encodeURIComponent(captcha) : '?step=send_code&email=' + encodeURIComponent(email) + '&captcha=' + encodeURIComponent(captcha);
        fetch(url)
            .then(res => res.json ? res.json() : res.text())
            .then(data => {
                let msg = '';
                if (typeof data === 'object') msg = data.code === 1 ? window.lang.send_success : window.lang.send_fail;
                else msg = data;
                alert(msg);
                if (typeof data === 'object' && data.code === 1) {
                    let countdown = 60;
                    const timer = setInterval(() => {
                        countdown--;
                        if (countdown <= 0) {
                            clearInterval(timer);
                            sendBtn.disabled = false;
                            sendBtn.innerText = originalText;
                        } else {
                            sendBtn.innerText = countdown + window.lang.seconds_retry;
                        }
                    }, 1000);
                } else {
                    sendBtn.disabled = false;
                    sendBtn.innerText = originalText;
                }
                const captchaImg = document.getElementById('captchaImg');
                if (captchaImg) captchaImg.src = '/api/captcha.php?' + Math.random();
            })
            .catch(err => {
                alert(window.lang.network_error);
                sendBtn.disabled = false;
                sendBtn.innerText = originalText;
            });
    });
});

(function() {
    const searchBtn = document.getElementById('searchBtn');
    const overlay = document.getElementById('searchOverlay');
    const closeBtn = document.getElementById('closeSearchBtn');
    const submitBtn = document.getElementById('submitSearch');
    const keywordInput = document.getElementById('searchKeyword');
    if (!searchBtn || !overlay) return;
    function openSearch() {
        overlay.classList.add('active');
        if (keywordInput) keywordInput.focus();
    }
    function closeSearch() {
        overlay.classList.remove('active');
        if (keywordInput) keywordInput.value = '';
    }
    function doSearch() {
        let keyword = keywordInput ? keywordInput.value.trim() : '';
        if (keyword === '') {
            alert(window.lang.search_keyword_required);
            return;
        }
        window.location.href = '/search?q=' + encodeURIComponent(keyword);
    }
    searchBtn.addEventListener('click', openSearch);
    if (closeBtn) closeBtn.addEventListener('click', closeSearch);
    if (submitBtn) submitBtn.addEventListener('click', doSearch);
    if (keywordInput) keywordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') doSearch();
    });
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeSearch();
    });
})();