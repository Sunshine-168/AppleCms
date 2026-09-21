<?php

/**
 * Admin user list / compact pager labels (founder remark, local IP, pager).
 * Applied by tools/patch-display-i18n.php.
 */
$t = static fn (
    string $en,
    string $zh,
    string $de,
    string $es,
    string $fr,
    string $ko,
    string $pt,
    string $ja,
): array => [
    'en' => $en,
    'zh_cn' => $zh,
    'de' => $de,
    'es' => $es,
    'fr' => $fr,
    'ko' => $ko,
    'pt' => $pt,
    'ja' => $ja,
];

return [
    'ui.super_admin' => $t('Super admin', '超级管理员', 'Super-Admin', 'Superadmin', 'Super-admin', '최고 관리자', 'Superadmin', 'スーパー管理者'),
    'ui.ip_local' => $t('Local', '本机地址', 'Lokal', 'Local', 'Local', '로컬', 'Local', 'ローカル'),
    'ui.ip_lan' => $t('LAN', '内网', 'LAN', 'LAN', 'LAN', '내부망', 'LAN', 'LAN'),
    'ui.ip_docker' => $t('LAN (Docker)', '内网（Docker）', 'LAN (Docker)', 'LAN (Docker)', 'LAN (Docker)', '내부망 (Docker)', 'LAN (Docker)', 'LAN（Docker）'),
    'ui.pager_total' => $t(':n rows', '共:n条', ':n Einträge', ':n filas', ':n lignes', ':n건', ':n linhas', ':n件'),
    'ui.prev_page' => $t('Previous', '上一页', 'Zurück', 'Anterior', 'Précédent', '이전', 'Anterior', '前へ'),
    'ui.next_page' => $t('Next', '下一页', 'Weiter', 'Siguiente', 'Suivant', '다음', 'Seguinte', '次へ'),
    'ui.pager' => $t('Pagination', '分页', 'Paginierung', 'Paginación', 'Pagination', '페이지', 'Paginação', 'ページ'),
];
