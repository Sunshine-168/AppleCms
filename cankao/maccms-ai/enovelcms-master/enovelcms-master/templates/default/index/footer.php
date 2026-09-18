<?php defined('ROOT_PATH') or die('URLError')?>
</main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <p>&copy; <?= date('Y') ?> <?= h(getSetting('site_name', $db) ?: $lang->get('site_default_name')) ?>. All rights reserved.
                Powered By <a href="https://www.enovelcms.cn/" target="_blank">EnovelCms v <?= ENOVELCMS_VERSION ?></a></p>
            </div>
        </div>
    </footer>
</div>
<script>
window.lang = {
    already_in_bookshelf: '<?= $lang->get('already_in_bookshelf') ?>',
    confirm_delete: '<?= $lang->get('confirm_delete') ?>',
    fill_email_first: '<?= $lang->get('fill_email_first') ?>',
    captcha_error: '<?= $lang->get('captcha_error') ?>',
    sending: '<?= $lang->get('sending') ?>',
    seconds_retry: '<?= $lang->get('seconds_retry') ?>',
    network_error: '<?= $lang->get('network_error') ?>',
    code_sent: '<?= $lang->get('code_sent') ?>',
    code_error: '<?= $lang->get('code_error') ?>',
    search_keyword_required: '<?= $lang->get('search_keyword_required') ?>',
    send_success: '<?= $lang->get('send_success') ?>',
    send_fail: '<?= $lang->get('send_fail') ?>',
    search_book_tip: '<?= $lang->get('search_book_tip') ?>'
};
</script>
<script src="<?= THEME_URL ?>js/main.js"></script>
<?= getSetting('analytics_code', $db) ?>
</body>
</html>