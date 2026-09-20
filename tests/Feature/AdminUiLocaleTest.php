<?php

namespace Tests\Feature;

use App\Support\AdminUi;
use Tests\TestCase;

class AdminUiLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    public function test_admin_ui_has_the_same_nine_languages_as_maccms(): void
    {
        $codes = AdminUi::codes();
        $this->assertSame(['de', 'en', 'es', 'fr', 'ja', 'ko', 'pt', 'zh_cn', 'zh_tw'], $codes);
        $this->assertSame('zh_cn', AdminUi::normalize('zh-cn'));
        $this->assertSame('en', AdminUi::normalize('en-us'));
        $this->assertSame('ja', AdminUi::normalize('ja-jp'));
        $this->assertSame('zh_tw', AdminUi::normalize('zh-tw'));
        $this->assertSame('', AdminUi::normalize('xx'));
    }

    public function test_topbar_language_dropdown_lists_all_nine(): void
    {
        $html = $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->get('/admin/welcome')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lang-pick', $html);
        $this->assertStringContainsString('Deutsch', $html);
        $this->assertStringContainsString('English', $html);
        $this->assertStringContainsString('Español', $html);
        $this->assertStringContainsString('Français', $html);
        $this->assertStringContainsString('日本語', $html);
        $this->assertStringContainsString('한국어', $html);
        $this->assertStringContainsString('Português', $html);
        $this->assertStringContainsString('简体中文', $html);
        $this->assertStringContainsString('繁體中文', $html);
        $this->assertStringContainsString('>DE<', $html);
        $this->assertStringContainsString('>US<', $html);
        $this->assertStringContainsString('清理缓存', $html);
        $this->assertStringContainsString('锁屏操作', $html);
        $this->assertStringContainsString('id="lockOverlay"', $html);
        $this->assertStringContainsString('id="unlockPassword"', $html);
        $this->assertStringContainsString("fetch('/admin/unlock'", $html);
        $this->assertStringContainsString('id="unlockLogout"', $html);
        $this->assertStringContainsString('class="topbar-icon topbar-front"', $html);
        $this->assertStringContainsString('fa-external-link-alt', $html);
        $this->assertStringContainsString('account-menu', $html);
        $this->assertStringContainsString('fa-ellipsis-h', $html);
        $this->assertStringContainsString('id="quickCacheClear"', $html);
        $this->assertStringContainsString('id="lockScreen"', $html);
        $this->assertStringContainsString('id="logoutForm"', $html);
        $this->assertStringContainsString('side-foot', $html);
        $this->assertStringNotContainsString('user-chip', $html);
        $this->assertStringNotContainsString('account-menu-who', $html);
        $this->assertStringNotContainsString('class="ui-switch"', $html);
        $this->assertStringNotContainsString('tool-menu', $html);
        $this->assertStringNotContainsString('quickLogout', $html);
    }

    public function test_switching_to_japanese_translates_articles_board(): void
    {
        $html = $this->withSession([
            'admin_uid' => 1,
            'admin_username' => 'admin',
            'admin_ui_locale' => 'ja',
        ])->get('/admin/video/arts')->assertOk()->getContent();

        $this->assertStringContainsString('html lang="ja"', $html);
        $this->assertStringContainsString('記事を作成', $html);
        $this->assertStringContainsString('セクション', $html);
        $this->assertStringContainsString('ゴミ箱', $html);
        $this->assertStringContainsString('公開済み', $html);
        $this->assertStringContainsString('下書き', $html);
        $this->assertStringNotContainsString('Write article', $html);
        $this->assertStringNotContainsString('>Sections<', $html);
        $this->assertStringNotContainsString('No matching content.', $html);
    }

    public function test_switching_to_german_translates_workspace_chrome(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->from('/admin/welcome')
            ->post('/admin/ui-locale', ['ui_locale' => 'de'])
            ->assertRedirect();

        $html = $this->withSession([
            'admin_uid' => 1,
            'admin_username' => 'admin',
            'admin_ui_locale' => 'de',
        ])->get('/admin/welcome')->assertOk()->getContent();

        $this->assertStringContainsString('Arbeitsplatz', $html);
        $this->assertStringContainsString('Cache leeren', $html);
        $this->assertStringContainsString('Bildschirm sperren', $html);
        $this->assertStringContainsString('html lang="de"', $html);
    }

    public function test_video_board_uses_native_chrome_not_english(): void
    {
        $cases = [
            ['de', 'de', 'Video hinzufügen', 'Papierkorb', 'Die Mediathek ist leer'],
            ['es', 'es', 'Añadir vídeo', 'Papelera', 'La mediateca está vacía'],
            ['fr', 'fr', 'Ajouter une vidéo', 'Corbeille', 'La médiathèque est vide'],
            ['ko', 'ko', '영상 추가', '휴지통', '영상 라이브러리가 비어 있습니다'],
            ['pt', 'pt', 'Adicionar vídeo', 'Reciclagem', 'A biblioteca está vazia'],
            ['zh_tw', 'zh-TW', '新增影片', '回收桶', '片庫還是空的'],
        ];
        foreach ($cases as [$locale, $htmlLang, $addVideo, $recycle, $empty]) {
            $html = $this->withSession([
                'admin_uid' => 1,
                'admin_username' => 'admin',
                'admin_ui_locale' => $locale,
            ])->get('/admin/video')->assertOk()->getContent();

            $this->assertStringContainsString('html lang="'.$htmlLang.'"', $html, $locale);
            $this->assertStringContainsString($addVideo, $html, $locale);
            $this->assertStringContainsString($recycle, $html, $locale);
            $this->assertStringContainsString($empty, $html, $locale);
            $this->assertStringNotContainsString('>Add video<', $html, $locale);
            $this->assertStringNotContainsString('>Recycle bin<', $html, $locale);
            $this->assertStringNotContainsString('Library is empty', $html, $locale);
            $this->assertStringNotContainsString('Search title', $html, $locale);
            $this->assertStringNotContainsString('More filters', $html, $locale);
            $this->assertStringNotContainsString('Fill-in &amp; tools', $html, $locale);
            $this->assertStringNotContainsString('Connect a collect source', $html, $locale);
        }
    }

    public function test_admin_user_board_uses_native_chrome_not_chinese(): void
    {
        $cases = [
            ['de', 'Admin hinzufügen', 'Anmeldename', 'Noch nie angemeldet'],
            ['es', 'Añadir admin', 'Usuario', 'Nunca ha iniciado sesión'],
            ['fr', 'Ajouter un admin', 'Identifiant', 'Jamais connecté'],
            ['ja', '管理者を追加', 'ログイン名', '未ログイン'],
            ['ko', '관리자 추가', '로그인 이름', '로그인 기록 없음'],
            ['pt', 'Adicionar admin', 'Utilizador', 'Nunca entrou'],
        ];
        foreach ($cases as [$locale, $add, $login, $never]) {
            $html = $this->withSession([
                'admin_uid' => 1,
                'admin_username' => 'admin',
                'admin_ui_locale' => $locale,
            ])->get('/admin/user')->assertOk()->getContent();

            $this->assertStringContainsString($add, $html, $locale);
            $this->assertStringContainsString($login, $html, $locale);
            $this->assertStringContainsString($never, $html, $locale);
            $this->assertStringNotContainsString('新增管理员', $html, $locale);
            $this->assertStringNotContainsString('>添加<', $html, $locale);
            $this->assertStringNotContainsString('从未登录', $html, $locale);
        }
    }

    public function test_tag_boards_and_menus_use_native_chrome_not_chinese(): void
    {
        $cases = [
            ['de', '/admin/video/manga-tags', 'Vollformular', 'Tag hinzufügen', 'Ungenutzt'],
            ['es', '/admin/video/manga-tags', 'Formulario completo', 'Añadir etiqueta', 'Sin uso'],
            ['fr', '/admin/video/manga-tags', 'Formulaire complet', 'Ajouter un tag', 'Inutilisé'],
            ['ja', '/admin/video/manga-tags', 'フォーム', 'タグを追加', '未使用'],
            ['ko', '/admin/video/manga-tags', '전체 양식', '태그 추가', '미사용'],
            ['pt', '/admin/video/manga-tags', 'Formulário completo', 'Adicionar etiqueta', 'Não usado'],
            ['de', '/admin/video/gallery-tags', 'Vollformular', 'Tag hinzufügen', 'Ungenutzt'],
            ['es', '/admin/video/gallery-authors', 'Formulario completo', 'Añadir autor', 'Sin uso'],
            ['de', '/admin/system/menus', 'Seite hinzufügen', 'Noch keine berechtigten Seiten', 'Schaltfläche'],
            ['es', '/admin/system/menus', 'Añadir página', 'Aún no hay páginas autorizables', 'Botón'],
            ['ja', '/admin/system/menus', 'ページを追加', '認可できるページがありません。', 'ボタン'],
            ['de', '/admin/video/manga-tags/create', 'Tag anlegen', 'Zurück zu Tags', 'Optional; wird aus dem Namen erzeugt'],
            ['es', '/admin/video/manga-authors/create', 'Nuevo autor', 'Volver a autores', 'Añadir autor'],
            ['de', '/admin/video/manga-types', 'Eigener Kategoriebaum', 'Vollformular', 'Kategorien'],
            ['de', '/admin/video/roles', 'Figurenarchiv', 'Rolle hinzufügen', 'Keine Admin-Rollen'],
            ['es', '/admin/video/roles', 'Biblioteca de personajes', 'Añadir rol', 'No son roles de admin'],
            ['de', '/admin/video/manga-types/create', 'Kategorie hinzufügen', 'URL-Alias', 'Speichern und Unterkategorie'],
            ['es', '/admin/video/manga-types/create', 'Añadir categoría', 'Alias de URL', 'Mostrar en el sitio'],
            ['ja', '/admin/video/manga-types/create', 'カテゴリを追加', 'URL別名', 'サイトに表示'],
            ['de', '/admin/video/tools/quality', 'Inhaltsqualität', 'Keine URL', 'keine Bewertung'],
            ['es', '/admin/video/audits', 'Reglas de auditoría de ingestión', 'Añadir regla', 'Omitir'],
            ['de', '/admin/video/chat_messages', 'Chatraum', 'Gemeldet', 'Beiträge'],
            ['es', '/admin/video/websites', 'Añadir enlace', 'Sin categoría', 'Visible'],
            ['ja', '/admin/video/danmaku', '弾幕', '通報済み', 'プレイヤー'],
            ['de', '/admin/system/monitor/login-logs', 'Jederzeit', 'Heute', 'Nur ich'],
            ['ja', '/admin/video/adverts', '広告', '上部バー', 'プレイヤー'],
            ['de', '/admin/video/coupons', 'Gutscheine', 'Allgemein', 'Betrag ab'],
            ['es', '/admin/video/pay_channels', 'Canales de pago', 'Añadir canal', 'Pedidos de recarga'],
            ['ja', '/admin/video/arts/create', '記事を作成', '読者に見えるタイトル', '欄と表示'],
            ['de', '/admin/video/arts/create', 'Kurztext', 'Angeheftet', 'Beliebt'],
            ['de', '/admin/video/flinks?desk=settings', 'Modus', 'Normal (nach Rang)', 'Front-Antrag'],
            ['ja', '/admin/video/pay_channels?desk=stats', '支払い統計', '未払い', '入金累計'],
            ['de', '/admin/video/manga-comments/create', 'Kommentar anlegen', 'Spitzname', 'Werk wählen'],
            ['ja', '/admin/video/mangas', '漫画ライブラリ', '映像カテゴリではありません', '完全フォーム'],
            ['de', '/admin/video/scout', 'Volltextsuche', 'Index neu aufbauen', 'Laravel Scout'],
            ['ja', '/admin/video/publish_pages', '公開ページ', '回線グループ', 'スイッチ'],
            ['es', '/admin/video/galleries?desk=favors', 'cuando los miembros marcan', 'El admin no puede', 'galería queda'],
            ['ja', '/admin/video/novels?desk=types', '1作品1分類', 'カテゴリ名を検索', 'カテゴリ'],
            ['de', '/admin/video/botlogs', 'Spider-Protokoll', 'Heute', 'IPs können nicht gesperrt werden'],
        ];
        foreach ($cases as [$locale, $path, $a, $b, $c]) {
            $html = $this->withSession([
                'admin_uid' => 1,
                'admin_username' => 'admin',
                'admin_ui_locale' => $locale,
            ])->get($path)->assertOk()->getContent();

            $this->assertStringContainsString($a, $html, $locale.' '.$path);
            $this->assertStringContainsString($b, $html, $locale.' '.$path);
            $this->assertStringContainsString($c, $html, $locale.' '.$path);
            $this->assertStringNotContainsString('新增标签', $html, $locale.' '.$path);
            $this->assertStringNotContainsString('>完整表单<', $html, $locale.' '.$path);
            $this->assertStringNotContainsString('>加一页<', $html, $locale.' '.$path);
            $this->assertStringNotContainsString('角色勾权限时看到的名字', $html, $locale.' '.$path);
        }
    }

    public function test_login_page_lists_nine_languages(): void
    {
        $html = $this->get('/admin/login?ui=zh_cn')->assertOk()->getContent();
        $this->assertStringContainsString('Deutsch', $html);
        $this->assertStringContainsString('繁體中文', $html);
        $this->assertStringContainsString('日本語', $html);
        $this->assertStringContainsString('ui=de', $html);
    }

    public function test_unlock_rejects_empty_password(): void
    {
        $this->withSession(['admin_uid' => 1, 'admin_username' => 'admin'])
            ->postJson('/admin/unlock', ['password' => '  '])
            ->assertOk()
            ->assertJsonPath('code', 1)
            ->assertJsonPath('msg', '请输入密码');
    }
}
