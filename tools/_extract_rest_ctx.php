<?php

$root = dirname(__DIR__);
chdir($root);

$missing = array_values(array_filter(array_map('trim', file($root.'/tools/_missing_keys.txt') ?: [])));
$skip = [
    'nav.annex', 'nav.config_ip', 'page.domains', 'page.images', 'page.quality',
    'ui.ad_settings', 'ui.allow_register', 'ui.banned_hint', 'ui.banned_words', 'ui.basic_settings',
    'ui.close_site', 'ui.close_tip_default', 'ui.close_tip_label', 'ui.comment_need_login', 'ui.cover_watermark',
    'ui.empty_operate', 'ui.empty_operate_hint', 'ui.empty_login', 'ui.empty_login_hint', 'ui.empty_sys_error', 'ui.empty_sys_error_hint',
    'ui.front_urls', 'ui.front_urls_hint', 'ui.gbook_audit', 'ui.look', 'ui.mail_hint', 'ui.mail_out', 'ui.mail_server',
    'ui.more_conds', 'ui.nav_menu', 'ui.nav_latest', 'ui.nav_news', 'ui.require_invite', 'ui.save_settings',
    'ui.seo_settings', 'ui.tab_website', 'ui.upload_image', 'ui.rewrite_how', 'ui.rewrite_how_body', 'ui.rewrite_faq_title', 'ui.rewrite_faq_body',
    'ui.rewrite_now', 'ui.rewrite_detail_now', 'ui.rewrite_lead_before', 'ui.rewrite_lead_after', 'ui.rewrite_laravel', 'ui.rewrite_mac',
    'ui.rewrite_no_html', 'ui.rewrite_suffix_mac', 'ui.rewrite_suffix_hint_a', 'ui.rewrite_suffix_hint_b', 'ui.rewrite_suffix_hint_c',
    'ui.rewrite_nginx_hint', 'ui.rewrite_apache_hint',
    'ui.tpl_lead', 'ui.tpl_lead_hl_a', 'ui.tpl_lead_hl_b', 'ui.tpl_lead_hl_c', 'ui.tpl_lead_plugin_a', 'ui.tpl_lead_plugin_b',
    'ui.tpl_no_open', 'ui.tpl_empty_hint', 'ui.find',
    'ui.home_config', 'ui.page_config', 'ui.other_settings', 'ui.head_code', 'ui.head_code_hint', 'ui.foot_note', 'ui.foot_code_hint',
    'ui.did_an_op', 'ui.unknown_admin', 'ui.toggle_extra', 'ui.said_what', 'ui.error_detail', 'ui.stack_trace', 'ui.error_once',
    'ui.collect_ingest', 'ui.collect_ingest_hint_a', 'ui.collect_ingest_hint_b', 'ui.page_cache_hint_a', 'ui.page_cache_hint_b',
    'ui.play_encrypt', 'ui.play_buffer_sec', 'ui.from_email', 'ui.send_test_mail', 'ui.ph_mail_to', 'ui.login_account', 'ui.smtp_port_hint',
    'ui.title_tpl', 'ui.seo_vod_page', 'ui.seo_type_page', 'ui.seo_play_page', 'ui.seo_tokens_pre', 'ui.seo_tokens_end',
    'ui.front_filters', 'ui.filter_csv_hint', 'ui.go_dict_items', 'ui.attach_storage', 'ui.store_where', 'ui.local_disk', 'ui.object_storage',
    'ui.engine_push', 'ui.engine_push_hint_a', 'ui.engine_push_hint_b',
    'ui.ingest_api', 'ui.ingest_api_hint_a', 'ui.ingest_api_hint_b', 'ui.ingest_api_hint_c', 'ui.ingest_api_hint_d',
    'ui.upload_ip_hint_a', 'ui.upload_ip_hint_b', 'ui.plugin_params', 'ui.plugin_params_hint', 'ui.allowed_ext',
    'ui.watermark_hint', 'ui.ph_watermark', 'ui.ph_site_title', 'ui.ph_keywords_csv', 'ui.ph_csv_or_nl', 'ui.ph_analytics', 'ui.analytics_hint',
    'ui.search_keywords', 'ui.seo_kw_hint', 'ui.code_editor', 'ui.go_attach_lib', 'ui.insert_attach', 'ui.insert',
    'ui.wiz_group_list', 'ui.wiz_group_page', 'ui.wiz_group_format', 'ui.wiz_snippet', 'ui.no_snippet_yet', 'ui.gen_fail',
    'ui.copy_manually', 'ui.copy_fail_pick', 'ui.copy_failed', 'ui.copied_url',
    'ui.comment_audit',
];
$skipMap = array_fill_keys($skip, true);
$need = [];
foreach ($missing as $k) {
    if (! isset($skipMap[$k])) {
        $need[] = $k;
    }
}

$tsv = [];
$tsvFile = $root.'/tools/_keys_from_diff.tsv';
if (is_file($tsvFile)) {
    foreach (file($tsvFile) ?: [] as $line) {
        $line = rtrim($line, "\r\n");
        $tab = strpos($line, "\t");
        if ($tab === false) {
            continue;
        }
        $tsv[substr($line, 0, $tab)] = substr($line, $tab + 1);
    }
}

function collect_files(string $root): array
{
    $files = [];
    $dirs = [$root.'/resources/views', $root.'/app', $root.'/plugins'];
    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), '.php')) {
                $files[] = $f->getPathname();
            }
        }
    }

    return $files;
}

$headDir = $root.'/tools/_head_views';
$headByRel = [];
foreach (glob($headDir.'/*.php') ?: [] as $hp) {
    $base = basename($hp);
    $rel = str_replace('__', '/', preg_replace('/\\.php$/', '', $base));
    // dumps named resources__views__admin__...blade.php → resources/views/admin/...blade.php
    $rel = str_replace('.blade', '.blade.php', $rel);
    if (! str_ends_with($rel, '.php')) {
        $rel .= '.php';
    }
    $headByRel[$rel] = $hp;
}

$needSet = array_fill_keys($need, true);
$hits = [];
foreach (collect_files($root) as $path) {
    $src = (string) file_get_contents($path);
    if (! str_contains($src, "admin_t(")) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $lines = preg_split("/\r\n|\n|\r/", $src);
    foreach ($lines as $i => $line) {
        if (! preg_match_all("/admin_t\(\s*'([^']+)'/", $line, $m)) {
            continue;
        }
        foreach ($m[1] as $key) {
            if (! isset($needSet[$key])) {
                continue;
            }
            $from = max(0, $i - 2);
            $to = min(count($lines) - 1, $i + 2);
            $snip = [];
            for ($j = $from; $j <= $to; $j++) {
                $snip[] = ($j + 1).':'.$lines[$j];
            }
            $hits[$key][] = [
                'file' => $rel,
                'line' => $i + 1,
                'line_text' => $line,
                'snip' => implode("\n", $snip),
            ];
        }
    }
}

$out = [];
$noHit = [];
$noTsv = [];
foreach ($need as $k) {
    $row = [
        'key' => $k,
        'tsv' => $tsv[$k] ?? null,
        'hits' => $hits[$k] ?? [],
        'head' => null,
    ];
    if (! isset($hits[$k])) {
        $noHit[] = $k;
    }
    if (! isset($tsv[$k])) {
        $noTsv[] = $k;
    }
    $first = $hits[$k][0]['file'] ?? null;
    if ($first && isset($headByRel[$first])) {
        $hsrc = (string) file_get_contents($headByRel[$first]);
        $hline = $hits[$k][0]['line'] ?? 0;
        $hlines = preg_split("/\r\n|\n|\r/", $hsrc);
        // skip leading fatal
        $off = 0;
        if (isset($hlines[0]) && str_starts_with($hlines[0], 'fatal:')) {
            $off = 1;
        }
        $idx = $hline - 1 + $off;
        $from = max(0, $idx - 3);
        $to = min(count($hlines) - 1, $idx + 3);
        $bits = [];
        for ($j = $from; $j <= $to; $j++) {
            $bits[] = ($j + 1).':'.$hlines[$j];
        }
        $row['head'] = implode("\n", $bits);
        $row['head_file'] = $first;
    }
    $out[$k] = $row;
}

file_put_contents($root.'/tools/_rest_ctx.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo 'missing='.count($missing).' skip='.count($skip).' need='.count($need).' noHit='.count($noHit).' noTsv='.count($noTsv).PHP_EOL;
echo "NOHIT\n".implode("\n", $noHit)."\n";
echo "NOTSV_SAMPLE\n".implode("\n", array_slice($noTsv, 0, 80))."\n";
echo 'notsv_total='.count($noTsv).PHP_EOL;
