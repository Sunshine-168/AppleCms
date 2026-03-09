<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\System\IndexController;
use App\Http\Controllers\Web\Content\VodController;
use App\Http\Controllers\Web\Content\ArtController;
use App\Http\Controllers\Web\Content\ActorController;
use App\Http\Controllers\Web\Content\CommentController;
use App\Http\Controllers\Web\Content\GbookController;
use App\Http\Controllers\Web\Content\PlotController;
use App\Http\Controllers\Web\Content\RoleController as FrontRoleController;
use App\Http\Controllers\Web\User\UlogController;
use App\Http\Controllers\Web\User\UserController;
use App\Http\Controllers\Web\Content\WebsiteController as FrontWebsiteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\System\AjaxController;
use App\Http\Controllers\Web\System\LabelController;
use App\Http\Controllers\Web\System\MapController;
use App\Http\Controllers\Web\System\MyErrorController;
use App\Http\Controllers\Web\System\PaymentController;
use App\Http\Controllers\Web\System\QrcodeController;
use App\Http\Controllers\Web\System\RssController;
use App\Http\Controllers\Web\System\SearchController;
use App\Http\Controllers\Web\System\VerifyController;
use App\Http\Controllers\Web\Content\TopicController;

// Admin Controllers
use App\Http\Controllers\Admin\ActorController as AdminActorController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AnnexController;
use App\Http\Controllers\Admin\ArtController as AdminArtController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VodController as AdminVodController;
use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\GbookController as AdminGbookController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\CollectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\IndexController as AdminIndexController;
use App\Http\Controllers\Admin\MangaController;
use App\Http\Controllers\Admin\PlogController;
use App\Http\Controllers\Admin\UlogController as AdminUlogController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\CjController;
use App\Http\Controllers\Admin\VodplayerController;
use App\Http\Controllers\Admin\VoddownerController;
use App\Http\Controllers\Admin\VodserverController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\SafetyController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\MakeController;
use App\Http\Controllers\Admin\ImagesController;
use App\Http\Controllers\Admin\TimmingController;
use App\Http\Controllers\Admin\UrlsendController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Install\InstallController;

// Install Routes (must be before other routes)
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::get('/step2', [InstallController::class, 'step2'])->name('install.step2');
    Route::get('/step3', [InstallController::class, 'step3'])->name('install.step3');
    Route::post('/step4', [InstallController::class, 'step4'])->name('install.step4');
    Route::post('/step5', [InstallController::class, 'step5'])->name('install.step5');
});

// Frontend Routes
Route::get('/', [IndexController::class, 'index']);

Route::get('/vod', [VodController::class, 'index'])->name('vod.index');
Route::get('/vod/type/{id}', [VodController::class, 'type'])->name('vod.type');
Route::get('/vod/detail/{id}', [VodController::class, 'detail'])->name('vod.detail');
Route::get('/vod/play/{id}/{sid}/{nid}', [VodController::class, 'play'])->name('vod.play');
Route::get('/vod/search', [VodController::class, 'search'])->name('vod.search');

Route::get('/art', [ArtController::class, 'index'])->name('art.index');
Route::get('/art/type/{id}', [ArtController::class, 'type'])->name('art.type');
Route::get('/art/detail/{id}', [ArtController::class, 'detail'])->name('art.detail');
Route::get('/art/search', [ArtController::class, 'search'])->name('art.search');

Route::get('/actor', [ActorController::class, 'index'])->name('actor.index');
Route::get('/actor/detail/{id}', [ActorController::class, 'detail'])->name('actor.detail');
Route::get('/actor/search', [ActorController::class, 'search'])->name('actor.search');

Route::get('/topic', [TopicController::class, 'index'])->name('topic.index');
Route::get('/topic/detail/{id}', [TopicController::class, 'detail'])->name('topic.detail');
Route::get('/topic/search', [TopicController::class, 'search'])->name('topic.search');

Route::get('/role', [FrontRoleController::class, 'index'])->name('role.index');
Route::get('/role/detail/{id}', [FrontRoleController::class, 'detail'])->name('role.detail');
Route::get('/role/search', [FrontRoleController::class, 'search'])->name('role.search');

Route::get('/website', [FrontWebsiteController::class, 'index'])->name('website.index');
Route::get('/website/type/{id}', [FrontWebsiteController::class, 'type'])->name('website.type');
Route::get('/website/detail/{id}', [FrontWebsiteController::class, 'detail'])->name('website.detail');
Route::get('/website/search', [FrontWebsiteController::class, 'search'])->name('website.search');

Route::get('/plot', [PlotController::class, 'index'])->name('plot.index');
Route::get('/plot/detail/{id}', [PlotController::class, 'detail'])->name('plot.detail');
Route::get('/plot/search', [PlotController::class, 'search'])->name('plot.search');

Route::get('/comment', [CommentController::class, 'index'])->name('comment.index');
Route::post('/comment/save', [CommentController::class, 'save'])->name('comment.save');
Route::match(['get', 'post'], '/index.php/comment/ajax.html', [CommentController::class, 'index'])->name('comment.ajax.legacy');
Route::post('/index.php/comment/saveData', [CommentController::class, 'save'])->name('comment.save.legacy');
Route::get('/index.php/comment/report.html', [CommentController::class, 'report'])->name('comment.report.legacy');

Route::get('/gbook', [GbookController::class, 'index'])->name('gbook.index');
Route::post('/gbook/save', [GbookController::class, 'save'])->name('gbook.save');
Route::match(['get', 'post'], '/index.php/gbook/index', [GbookController::class, 'index'])->name('gbook.index.legacy');
Route::post('/index.php/gbook/saveData', [GbookController::class, 'save'])->name('gbook.save.legacy');
Route::get('/index.php/gbook/report.html', [GbookController::class, 'report'])->name('gbook.report.legacy');

// User Routes
Route::get('/user/index', [UserController::class, 'index'])->name('user.index');
Route::middleware('auth')->group(function () {
    Route::get('/user/ulog', [UlogController::class, 'index'])->name('user.ulog');
    Route::post('/user/ulog/save', [UlogController::class, 'save'])->name('user.ulog.save');
    Route::post('/user/ulog/delete', [UlogController::class, 'delete'])->name('user.ulog.delete');
    Route::match(['get', 'post'], '/index.php/user/ajax_ulog', [UlogController::class, 'legacyAjax'])->name('user.ulog.legacy');
    Route::get('/index.php/user/ajax_buy_popedom.html', [UlogController::class, 'buyPopedomLegacy'])->name('user.buy_popedom.legacy');
});

Route::match(['get', 'post'], '/user/regcheck', [UserController::class, 'regcheck'])->name('user.regcheck');
Route::match(['get', 'post'], '/user/reg', [UserController::class, 'reg'])->name('user.reg');
Route::match(['get', 'post'], '/user/reg_msg', [UserController::class, 'regMsg'])->name('user.reg_msg');
Route::match(['get', 'post'], '/user/findpass', [UserController::class, 'findpass'])->name('user.findpass');
Route::match(['get', 'post'], '/user/findpass_msg', [UserController::class, 'findpassMsg'])->name('user.findpass_msg');
Route::match(['get', 'post'], '/user/findpass_reset', [UserController::class, 'findpassReset'])->name('user.findpass_reset');
Route::match(['get', 'post'], '/user/bind', [UserController::class, 'bind'])->name('user.bind');
Route::match(['get', 'post'], '/user/bindmsg', [UserController::class, 'bindmsg'])->name('user.bindmsg');
Route::match(['get', 'post'], '/user/unbind', [UserController::class, 'unbind'])->name('user.unbind');
Route::match(['get', 'post'], '/user/info', [UserController::class, 'info'])->name('user.info');
Route::match(['get', 'post'], '/user/portrait', [UserController::class, 'portrait'])->name('user.portrait');
Route::match(['get', 'post'], '/user/buy', [UserController::class, 'buy'])->name('user.buy');
Route::get('/user/pay', [UserController::class, 'pay'])->name('user.pay');
Route::match(['get', 'post'], '/user/gopay', [UserController::class, 'gopay'])->name('user.gopay');
Route::match(['get', 'post'], '/user/upgrade', [UserController::class, 'upgrade'])->name('user.upgrade');
Route::get('/user/plays', [UserController::class, 'plays'])->name('user.plays');
Route::get('/user/downs', [UserController::class, 'downs'])->name('user.downs');
Route::get('/user/favs', [UserController::class, 'favs'])->name('user.favs');
Route::get('/user/plog', [UserController::class, 'plog'])->name('user.plog');
Route::get('/user/reward', [UserController::class, 'reward'])->name('user.reward');
Route::match(['get', 'post'], '/user/cash', [UserController::class, 'cash'])->name('user.cash');
Route::get('/user/comment', [UserController::class, 'comment'])->name('user.comment');
Route::get('/user/gbook', [UserController::class, 'gbook'])->name('user.gbook');
Route::get('/user/popedom', [UserController::class, 'popedom'])->name('user.popedom');
Route::get('/user/orders', [UserController::class, 'orders'])->name('user.orders');
Route::get('/user/order_info', [UserController::class, 'orderInfo'])->name('user.order_info');
Route::get('/user/cards', [UserController::class, 'cards'])->name('user.cards');
Route::get('/user/qrcode', [UserController::class, 'qrcode'])->name('user.qrcode');
Route::match(['get', 'post'], '/user/ulog_del', [UserController::class, 'ulogDel'])->name('user.ulog_del');
Route::match(['get', 'post'], '/user/plog_del', [UserController::class, 'plogDel'])->name('user.plog_del');
Route::match(['get', 'post'], '/user/cash_del', [UserController::class, 'cashDel'])->name('user.cash_del');
Route::get('/user/oauth/{type?}', [UserController::class, 'oauth'])->name('user.oauth');
Route::get('/user/logincallback', [UserController::class, 'logincallback'])->name('user.logincallback.query');
Route::match(['get', 'post'], '/user/logincallback/type/{type}/{code?}', [UserController::class, 'logincallback'])->name('user.logincallback');
Route::get('/user/visit', [UserController::class, 'visit'])->name('user.visit');

// Auth Routes
Route::get('/user/login', [LoginController::class, 'login'])->name('login');
Route::post('/user/login', [LoginController::class, 'loginPost']);
Route::get('/user/logout', [LoginController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/index.php/user/regcheck', [UserController::class, 'regcheck'])->name('user.regcheck.legacy');
Route::match(['get', 'post'], '/index.php/user/reg', [UserController::class, 'reg'])->name('user.reg.legacy');
Route::match(['get', 'post'], '/index.php/user/reg_msg', [UserController::class, 'regMsg'])->name('user.reg_msg.legacy');
Route::match(['get', 'post'], '/index.php/user/findpass', [UserController::class, 'findpass'])->name('user.findpass.legacy');
Route::match(['get', 'post'], '/index.php/user/findpass_msg', [UserController::class, 'findpassMsg'])->name('user.findpass_msg.legacy');
Route::match(['get', 'post'], '/index.php/user/findpass_reset', [UserController::class, 'findpassReset'])->name('user.findpass_reset.legacy');
Route::match(['get', 'post'], '/index.php/user/bind', [UserController::class, 'bind'])->name('user.bind.legacy');
Route::match(['get', 'post'], '/index.php/user/bindmsg', [UserController::class, 'bindmsg'])->name('user.bindmsg.legacy');
Route::match(['get', 'post'], '/index.php/user/unbind', [UserController::class, 'unbind'])->name('user.unbind.legacy');
Route::match(['get', 'post'], '/index.php/user/info', [UserController::class, 'info'])->name('user.info.legacy');
Route::match(['get', 'post'], '/index.php/user/portrait', [UserController::class, 'portrait'])->name('user.portrait.legacy');
Route::match(['get', 'post'], '/index.php/user/buy', [UserController::class, 'buy'])->name('user.buy.legacy');
Route::get('/index.php/user/pay', [UserController::class, 'pay'])->name('user.pay.legacy');
Route::match(['get', 'post'], '/index.php/user/gopay', [UserController::class, 'gopay'])->name('user.gopay.legacy');
Route::match(['get', 'post'], '/index.php/user/upgrade', [UserController::class, 'upgrade'])->name('user.upgrade.legacy');
Route::get('/index.php/user/plays', [UserController::class, 'plays'])->name('user.plays.legacy');
Route::get('/index.php/user/downs', [UserController::class, 'downs'])->name('user.downs.legacy');
Route::get('/index.php/user/favs', [UserController::class, 'favs'])->name('user.favs.legacy');
Route::get('/index.php/user/plog', [UserController::class, 'plog'])->name('user.plog.legacy');
Route::get('/index.php/user/reward', [UserController::class, 'reward'])->name('user.reward.legacy');
Route::match(['get', 'post'], '/index.php/user/cash', [UserController::class, 'cash'])->name('user.cash.legacy');
Route::get('/index.php/user/comment', [UserController::class, 'comment'])->name('user.comment.legacy');
Route::get('/index.php/user/gbook', [UserController::class, 'gbook'])->name('user.gbook.legacy');
Route::get('/index.php/user/popedom', [UserController::class, 'popedom'])->name('user.popedom.legacy');
Route::get('/index.php/user/orders', [UserController::class, 'orders'])->name('user.orders.legacy');
Route::get('/index.php/user/order_info', [UserController::class, 'orderInfo'])->name('user.order_info.legacy');
Route::get('/index.php/user/cards', [UserController::class, 'cards'])->name('user.cards.legacy');
Route::get('/index.php/user/qrcode', [UserController::class, 'qrcode'])->name('user.qrcode.legacy');
Route::match(['get', 'post'], '/index.php/user/ulog_del', [UserController::class, 'ulogDel'])->name('user.ulog_del.legacy');
Route::match(['get', 'post'], '/index.php/user/plog_del', [UserController::class, 'plogDel'])->name('user.plog_del.legacy');
Route::match(['get', 'post'], '/index.php/user/cash_del', [UserController::class, 'cashDel'])->name('user.cash_del.legacy');
Route::get('/index.php/user/oauth/{type?}', [UserController::class, 'oauth'])->name('user.oauth.legacy');
Route::get('/index.php/user/logincallback', [UserController::class, 'logincallback'])->name('user.logincallback.query.legacy');
Route::match(['get', 'post'], '/index.php/user/logincallback/type/{type}/{code?}', [UserController::class, 'logincallback'])->name('user.logincallback.legacy');
Route::get('/index.php/user/visit', [UserController::class, 'visit'])->name('user.visit.legacy');
Route::get('/index.php/user/ajax_login', [LoginController::class, 'ajaxLogin'])->name('login.ajax.legacy');
Route::get('/index.php/user/ajax_info', [LoginController::class, 'ajaxInfo'])->name('login.info.legacy');
Route::match(['get', 'post'], '/index.php/user/login', [LoginController::class, 'loginPost'])->name('login.post.legacy');
Route::match(['get', 'post'], '/index.php/user/logout', [LoginController::class, 'logout'])->name('logout.legacy');
Route::redirect('/admin', '/admin/index');

// System Routes
Route::get('/ajax/data', [AjaxController::class, 'data'])->name('ajax.data');
Route::get('/ajax/suggest', [AjaxController::class, 'suggest'])->name('ajax.suggest');
Route::get('/index.php/ajax/hits', [AjaxController::class, 'hits'])->name('ajax.hits.legacy');
Route::match(['get', 'post'], '/index.php/ajax/score', [AjaxController::class, 'score'])->name('ajax.score.legacy');
Route::get('/index.php/ajax/digg.html', [AjaxController::class, 'digg'])->name('ajax.digg.legacy');
Route::get('/index.php/ajax/referer', [AjaxController::class, 'referer'])->name('ajax.referer.legacy');
Route::get('/index.php/ajax/pwd.html', [AjaxController::class, 'pwd'])->name('ajax.pwd.legacy');
Route::get('/index.php/ajax/desktop', [AjaxController::class, 'desktop'])->name('ajax.desktop.legacy');

Route::get('/label', [LabelController::class, 'index'])->name('label.index');

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/qrcode', [QrcodeController::class, 'index'])->name('qrcode.index');
Route::get('/index.php/qrcode/index.html', [QrcodeController::class, 'index'])->name('qrcode.index.legacy');
Route::match(['get', 'post'], '/payment/notify', [PaymentController::class, 'notify'])->name('payment.notify');
Route::view('/index/index.html', 'public.template_intro')->name('index.template.intro');
Route::view('/index.php/index/index.html', 'public.template_intro')->name('index.template.intro.legacy');
Route::get('/verify/page/{type?}', [VerifyController::class, 'page'])->name('verify.page');
Route::get('/verify/{id?}', [VerifyController::class, 'index'])->name('verify.index');
Route::match(['get', 'post'], '/verify/check/{verify?}/{id?}', [VerifyController::class, 'check'])->name('verify.check');
Route::get('/index.php/public/verify.html', [VerifyController::class, 'page'])->name('verify.page.legacy');
Route::get('/index.php/verify/index.html', [VerifyController::class, 'index'])->name('verify.index.legacy');
Route::match(['get', 'post'], '/index.php/ajax/verify_check', [VerifyController::class, 'check'])->name('verify.check.legacy');

Route::get('/rss', [RssController::class, 'index'])->name('rss.index');
Route::get('/rss/baidu', [RssController::class, 'baidu'])->name('rss.baidu');
Route::get('/rss/google', [RssController::class, 'google'])->name('rss.google');
Route::get('/rss/so', [RssController::class, 'so'])->name('rss.so');
Route::get('/rss/sogou', [RssController::class, 'sogou'])->name('rss.sogou');
Route::get('/rss/bing', [RssController::class, 'bing'])->name('rss.bing');
Route::get('/rss/sm', [RssController::class, 'sm'])->name('rss.sm');

Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// Legacy ThinkPHP-style route aliases from maccms10/application/route.php
Route::get('/index-{page?}', function (?int $page = null) {
    $params = [];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->to(url('/') . ($params ? '?' . http_build_query($params) : ''));
})->whereNumber('page');

Route::get('/topic-{page?}', function (?int $page = null) {
    $params = [];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('topic.index', $params);
})->whereNumber('page');
Route::get('/topicdetail-{id}', fn (int $id) => redirect()->route('topic.detail', ['id' => $id]))->whereNumber('id');

Route::get('/actor-{page?}', function (?int $page = null) {
    $params = [];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('actor.index', $params);
})->whereNumber('page');
Route::get('/actordetail-{id}', fn (int $id) => redirect()->route('actor.detail', ['id' => $id]))->whereNumber('id');

Route::get('/role-{page?}', function (?int $page = null) {
    $params = [];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('role.index', $params);
})->whereNumber('page');
Route::get('/roledetail-{id}', fn (int $id) => redirect()->route('role.detail', ['id' => $id]))->whereNumber('id');

Route::get('/vodtype/{id}-{page?}', function (int $id, ?int $page = null) {
    $params = ['id' => $id];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('vod.type', $params);
})->whereNumber('id')->whereNumber('page');
Route::get('/voddetail/{id}', fn (int $id) => redirect()->route('vod.detail', ['id' => $id]))->whereNumber('id');
Route::get('/vodplay/{id}-{sid}-{nid}', fn (int $id, int $sid, int $nid) => redirect()->route('vod.play', compact('id', 'sid', 'nid')))
    ->whereNumber('id')->whereNumber('sid')->whereNumber('nid');
Route::get('/vodsearch/{wd?}-{actor?}-{area?}-{by?}-{class?}-{director?}-{lang?}-{letter?}-{level?}-{order?}-{page?}-{state?}-{tag?}-{year?}', function (
    ?string $wd = null,
    ?string $actor = null,
    ?string $area = null,
    ?string $by = null,
    ?string $class = null,
    ?string $director = null,
    ?string $lang = null,
    ?string $letter = null,
    ?string $level = null,
    ?string $order = null,
    ?int $page = null,
    ?string $state = null,
    ?string $tag = null,
    ?string $year = null
) {
    $params = array_filter([
        'wd' => $wd,
        'actor' => $actor,
        'area' => $area,
        'by' => $by,
        'class' => $class,
        'director' => $director,
        'lang' => $lang,
        'letter' => $letter,
        'level' => $level,
        'order' => $order,
        'page' => $page,
        'state' => $state,
        'tag' => $tag,
        'year' => $year,
    ], fn ($value) => $value !== null && $value !== '');

    return redirect()->route('vod.search', $params);
})->whereNumber('page');
Route::get('/vodplot/{id}-{page?}', function (int $id, ?int $page = null) {
    $params = ['id' => $id];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('plot.detail', $params);
})->whereNumber('id')->whereNumber('page');

Route::get('/arttype/{id}-{page?}', function (int $id, ?int $page = null) {
    $params = ['id' => $id];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('art.type', $params);
})->whereNumber('id')->whereNumber('page');
Route::get('/artdetail-{id}-{page?}', function (int $id, ?int $page = null) {
    $params = ['id' => $id];
    if (!empty($page) && $page > 1) {
        $params['page'] = $page;
    }
    return redirect()->route('art.detail', $params);
})->whereNumber('id')->whereNumber('page');
Route::get('/artsearch/{wd?}-{by?}-{class?}-{level?}-{letter?}-{order?}-{page?}-{tag?}', function (
    ?string $wd = null,
    ?string $by = null,
    ?string $class = null,
    ?string $level = null,
    ?string $letter = null,
    ?string $order = null,
    ?int $page = null,
    ?string $tag = null
) {
    $params = array_filter([
        'wd' => $wd,
        'by' => $by,
        'class' => $class,
        'level' => $level,
        'letter' => $letter,
        'order' => $order,
        'page' => $page,
        'tag' => $tag,
    ], fn ($value) => $value !== null && $value !== '');

    return redirect()->route('art.search', $params);
})->whereNumber('page');

Route::get('/label-{file}', function (string $file) {
    return redirect()->route('label.index', ['file' => $file]);
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::match(['get', 'post'], '/login', [AdminIndexController::class, 'login'])->name('admin.login');
    Route::get('/logout', [AdminIndexController::class, 'logout'])->name('admin.logout');
    Route::get('/index', [AdminIndexController::class, 'index'])->name('admin.index');
    Route::get('/welcome', [AdminIndexController::class, 'welcome'])->name('admin.index.welcome');
    Route::get('/quickmenu', [AdminIndexController::class, 'quickmenu'])->name('admin.index.quickmenu');
    Route::get('/clear', [AdminIndexController::class, 'clear'])->name('admin.index.clear');
    
    Route::get('/actor', [AdminActorController::class, 'index'])->name('admin.actor.index');
    Route::match(['get', 'post'], '/actor/info/{id?}', [AdminActorController::class, 'info'])->name('admin.actor.info');
    Route::post('/actor/save/{id?}', [AdminActorController::class, 'info'])->name('admin.actor.info.save');
    Route::get('/actor/del', [AdminActorController::class, 'del'])->name('admin.actor.del');

    Route::get('/addon', [AddonController::class, 'index'])->name('admin.addon.index');
    Route::get('/addon/downloaded', [AddonController::class, 'downloaded'])->name('admin.addon.downloaded');
    Route::match(['get', 'post'], '/addon/config/{name?}', [AddonController::class, 'config'])->name('admin.addon.config');
    Route::get('/addon/state', [AddonController::class, 'state'])->name('admin.addon.state');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.admin.index');
    Route::match(['get', 'post'], '/admin/info/{id?}', [AdminController::class, 'info'])->name('admin.admin.info');
    Route::get('/admin/del', [AdminController::class, 'del'])->name('admin.admin.del');

    Route::get('/annex', [AnnexController::class, 'index'])->name('admin.annex.index');
    Route::get('/annex/file', [AnnexController::class, 'file'])->name('admin.annex.file');
    Route::get('/annex/del', [AnnexController::class, 'del'])->name('admin.annex.del');
    Route::get('/annex/check', [AnnexController::class, 'check'])->name('admin.annex.check');
    Route::get('/annex/init', [AnnexController::class, 'init'])->name('admin.annex.init');

    Route::get('/art', [AdminArtController::class, 'index'])->name('admin.art.index');
    Route::match(['get', 'post'], '/art/info/{id?}', [AdminArtController::class, 'info'])->name('admin.art.info');
    Route::get('/art/del', [AdminArtController::class, 'del'])->name('admin.art.del');

    Route::get('/user', [AdminUserController::class, 'index'])->name('admin.user.index');
    Route::get('/user/reward', [AdminUserController::class, 'reward'])->name('admin.user.reward');
    Route::match(['get', 'post'], '/user/info/{id?}', [AdminUserController::class, 'info'])->name('admin.user.info');
    Route::get('/user/del', [AdminUserController::class, 'del'])->name('admin.user.del');

    Route::get('/vod', [AdminVodController::class, 'index'])->name('admin.vod.index');
    Route::match(['get', 'post'], '/vod/info/{id?}', [AdminVodController::class, 'info'])->name('admin.vod.info');
    Route::get('/vod/del', [AdminVodController::class, 'del'])->name('admin.vod.del');

    Route::get('/card', [CardController::class, 'index'])->name('admin.card.index');
    Route::match(['get', 'post'], '/card/info/{id?}', [CardController::class, 'info'])->name('admin.card.info');
    Route::get('/card/del', [CardController::class, 'del'])->name('admin.card.del');

    Route::get('/cash', [CashController::class, 'index'])->name('admin.cash.index');
    Route::get('/cash/del', [CashController::class, 'del'])->name('admin.cash.del');
    Route::get('/cash/audit', [CashController::class, 'audit'])->name('admin.cash.audit');

    Route::get('/gbook', [AdminGbookController::class, 'index'])->name('admin.gbook.index');
    Route::get('/gbook/del', [AdminGbookController::class, 'del'])->name('admin.gbook.del');

    Route::get('/group', [GroupController::class, 'index'])->name('admin.group.index');
    Route::match(['get', 'post'], '/group/info/{id?}', [GroupController::class, 'info'])->name('admin.group.info');
    Route::get('/group/del', [GroupController::class, 'del'])->name('admin.group.del');

    Route::get('/type', [TypeController::class, 'index'])->name('admin.type.index');
    Route::match(['get', 'post'], '/type/info/{id?}', [TypeController::class, 'info'])->name('admin.type.info');
    Route::get('/type/del', [TypeController::class, 'del'])->name('admin.type.del');

    Route::get('/comment', [AdminCommentController::class, 'index'])->name('admin.comment.index');
    Route::match(['get', 'post'], '/comment/info/{id?}', [AdminCommentController::class, 'info'])->name('admin.comment.info');
    Route::get('/comment/del', [AdminCommentController::class, 'del'])->name('admin.comment.del');
    Route::post('/comment/field', [AdminCommentController::class, 'field'])->name('admin.comment.field');

    Route::get('/link', [LinkController::class, 'index'])->name('admin.link.index');
    Route::match(['get', 'post'], '/link/info/{id?}', [LinkController::class, 'info'])->name('admin.link.info');
    Route::get('/link/del', [LinkController::class, 'del'])->name('admin.link.del');
    Route::post('/link/batch', [LinkController::class, 'batch'])->name('admin.link.batch');

    Route::get('/topic', [AdminTopicController::class, 'index'])->name('admin.topic.index');
    Route::match(['get', 'post'], '/topic/info/{id?}', [AdminTopicController::class, 'info'])->name('admin.topic.info');
    Route::get('/topic/del', [AdminTopicController::class, 'del'])->name('admin.topic.del');
    Route::post('/topic/field', [AdminTopicController::class, 'field'])->name('admin.topic.field');

    Route::get('/upload', [UploadController::class, 'index'])->name('admin.upload.index');
    Route::post('/upload/upload', [UploadController::class, 'upload'])->name('admin.upload.upload');
    Route::get('/upload/test', [UploadController::class, 'test'])->name('admin.upload.test');

    Route::get('/collect', [CollectController::class, 'index'])->name('admin.collect.index');
    Route::match(['get', 'post'], '/collect/info/{id?}', [CollectController::class, 'info'])->name('admin.collect.info');
    Route::get('/collect/del', [CollectController::class, 'del'])->name('admin.collect.del');
    Route::get('/collect/union', [CollectController::class, 'union'])->name('admin.collect.union');
    Route::post('/collect/test', [CollectController::class, 'test'])->name('admin.collect.test');

    Route::get('/role', [RoleController::class, 'index'])->name('admin.role.index');
    Route::match(['get', 'post'], '/role/info/{id?}', [RoleController::class, 'info'])->name('admin.role.info');
    Route::get('/role/del', [RoleController::class, 'del'])->name('admin.role.del');

    Route::get('/website', [WebsiteController::class, 'index'])->name('admin.website.index');
    Route::match(['get', 'post'], '/website/info/{id?}', [WebsiteController::class, 'info'])->name('admin.website.info');
    Route::get('/website/del', [WebsiteController::class, 'del'])->name('admin.website.del');

    Route::get('/order', [OrderController::class, 'index'])->name('admin.order.index');
    Route::get('/order/del', [OrderController::class, 'del'])->name('admin.order.del');

    // 新增控制器路由
    Route::get('/manga', [MangaController::class, 'index'])->name('admin.manga.index');
    Route::match(['get', 'post'], '/manga/info/{id?}', [MangaController::class, 'info'])->name('admin.manga.info');
    Route::get('/manga/del', [MangaController::class, 'del'])->name('admin.manga.del');

    Route::get('/plog', [PlogController::class, 'index'])->name('admin.plog.index');
    Route::get('/plog/del', [PlogController::class, 'del'])->name('admin.plog.del');

    Route::get('/ulog', [AdminUlogController::class, 'index'])->name('admin.ulog.index');
    Route::get('/ulog/del', [AdminUlogController::class, 'del'])->name('admin.ulog.del');

    Route::get('/visit', [VisitController::class, 'index'])->name('admin.visit.index');
    Route::get('/visit/del', [VisitController::class, 'del'])->name('admin.visit.del');

    Route::get('/database', [DatabaseController::class, 'index'])->name('admin.database.index');
    Route::post('/database/export', [DatabaseController::class, 'export'])->name('admin.database.export');
    Route::post('/database/import', [DatabaseController::class, 'import'])->name('admin.database.import');
    Route::post('/database/optimize', [DatabaseController::class, 'optimize'])->name('admin.database.optimize');
    Route::post('/database/repair', [DatabaseController::class, 'repair'])->name('admin.database.repair');
    Route::get('/database/del', [DatabaseController::class, 'del'])->name('admin.database.del');
    Route::match(['get', 'post'], '/database/sql', [DatabaseController::class, 'sql'])->name('admin.database.sql');
    Route::match(['get', 'post'], '/database/rep', [DatabaseController::class, 'rep'])->name('admin.database.rep');

    Route::get('/cj', [CjController::class, 'index'])->name('admin.cj.index');
    Route::match(['get', 'post'], '/cj/info/{id?}', [CjController::class, 'info'])->name('admin.cj.info');
    Route::match(['get', 'post'], '/cj/program/{id}', [CjController::class, 'program'])->name('admin.cj.program');
    Route::get('/cj/publish/{id}', [CjController::class, 'publish'])->name('admin.cj.publish');
    Route::get('/cj/show/{id}', [CjController::class, 'show'])->name('admin.cj.show');
    Route::match(['get', 'post'], '/cj/show_url', [CjController::class, 'showUrl'])->name('admin.cj.show_url');
    Route::get('/cj/col_url/{id}', [CjController::class, 'colUrl'])->name('admin.cj.col_url');
    Route::get('/cj/col_content/{id}', [CjController::class, 'colContent'])->name('admin.cj.col_content');
    Route::get('/cj/content_into/{id}', [CjController::class, 'contentInto'])->name('admin.cj.content_into');
    Route::get('/cj/content_del', [CjController::class, 'contentDel'])->name('admin.cj.content_del');
    Route::get('/cj/del', [CjController::class, 'del'])->name('admin.cj.del');
    Route::get('/cj/export/{id}', [CjController::class, 'export'])->name('admin.cj.export');
    Route::post('/cj/import', [CjController::class, 'import'])->name('admin.cj.import');

    Route::get('/vodplayer', [VodplayerController::class, 'index'])->name('admin.vodplayer.index');
    Route::match(['get', 'post'], '/vodplayer/info/{id?}', [VodplayerController::class, 'info'])->name('admin.vodplayer.info');
    Route::get('/vodplayer/del', [VodplayerController::class, 'del'])->name('admin.vodplayer.del');
    Route::post('/vodplayer/field', [VodplayerController::class, 'field'])->name('admin.vodplayer.field');

    Route::get('/voddowner', [VoddownerController::class, 'index'])->name('admin.voddowner.index');
    Route::match(['get', 'post'], '/voddowner/info/{id?}', [VoddownerController::class, 'info'])->name('admin.voddowner.info');
    Route::get('/voddowner/del', [VoddownerController::class, 'del'])->name('admin.voddowner.del');
    Route::post('/voddowner/field', [VoddownerController::class, 'field'])->name('admin.voddowner.field');

    Route::get('/vodserver', [VodserverController::class, 'index'])->name('admin.vodserver.index');
    Route::match(['get', 'post'], '/vodserver/info/{id?}', [VodserverController::class, 'info'])->name('admin.vodserver.info');
    Route::get('/vodserver/del', [VodserverController::class, 'del'])->name('admin.vodserver.del');
    Route::post('/vodserver/field', [VodserverController::class, 'field'])->name('admin.vodserver.field');

    Route::get('/template', [TemplateController::class, 'index'])->name('admin.template.index');
    Route::match(['get', 'post'], '/template/info', [TemplateController::class, 'info'])->name('admin.template.info');
    Route::get('/template/del', [TemplateController::class, 'del'])->name('admin.template.del');
    Route::get('/template/ads', [TemplateController::class, 'ads'])->name('admin.template.ads');
    Route::get('/template/wizard', [TemplateController::class, 'wizard'])->name('admin.template.wizard');

    Route::get('/safety', [SafetyController::class, 'index'])->name('admin.safety.index');
    Route::match(['get', 'post'], '/safety/file', [SafetyController::class, 'file'])->name('admin.safety.file');
    Route::match(['get', 'post'], '/safety/data', [SafetyController::class, 'data'])->name('admin.safety.data');

    Route::get('/domain', [DomainController::class, 'index'])->name('admin.domain.index');
    Route::get('/domain/del', [DomainController::class, 'del'])->name('admin.domain.del');
    Route::get('/domain/export', [DomainController::class, 'export'])->name('admin.domain.export');
    Route::post('/domain/import', [DomainController::class, 'import'])->name('admin.domain.import');

    Route::get('/make', [MakeController::class, 'opt'])->name('admin.make.opt');
    Route::post('/make/index', [MakeController::class, 'makeIndex'])->name('admin.make.index');
    Route::post('/make/type', [MakeController::class, 'makeType'])->name('admin.make.type');
    Route::post('/make/detail', [MakeController::class, 'makeDetail'])->name('admin.make.detail');
    Route::post('/make/topic', [MakeController::class, 'makeTopic'])->name('admin.make.topic');
    Route::post('/make/rss', [MakeController::class, 'makeRss'])->name('admin.make.rss');
    Route::post('/make/map', [MakeController::class, 'makeMap'])->name('admin.make.map');
    Route::post('/make/label', [MakeController::class, 'makeLabel'])->name('admin.make.label');

    Route::get('/images', [ImagesController::class, 'opt'])->name('admin.images.opt');
    Route::post('/images/sync', [ImagesController::class, 'sync'])->name('admin.images.sync');
    Route::post('/images/del', [ImagesController::class, 'del'])->name('admin.images.del');

    Route::get('/timming', [TimmingController::class, 'index'])->name('admin.timming.index');
    Route::match(['get', 'post'], '/timming/info/{id?}', [TimmingController::class, 'info'])->name('admin.timming.info');
    Route::get('/timming/del', [TimmingController::class, 'del'])->name('admin.timming.del');
    Route::post('/timming/field', [TimmingController::class, 'field'])->name('admin.timming.field');

    Route::get('/urlsend', [UrlsendController::class, 'index'])->name('admin.urlsend.index');
    Route::post('/urlsend/data', [UrlsendController::class, 'data'])->name('admin.urlsend.data');
    Route::match(['get', 'post'], '/urlsend/push', [UrlsendController::class, 'push'])->name('admin.urlsend.push');

    Route::get('/update', [UpdateController::class, 'index'])->name('admin.update.index');
    Route::get('/update/step1/{file}', [UpdateController::class, 'step1'])->name('admin.update.step1');
    Route::get('/update/step2', [UpdateController::class, 'step2'])->name('admin.update.step2');
    Route::get('/update/step3', [UpdateController::class, 'step3'])->name('admin.update.step3');
    Route::get('/update/check', [UpdateController::class, 'check'])->name('admin.update.check');

    // 系统设置路由
    Route::match(['get', 'post'], '/system/config', [SystemController::class, 'config'])->name('admin.system.config');
    Route::match(['get', 'post'], '/system/configseo', [SystemController::class, 'configseo'])->name('admin.system.configseo');
    Route::match(['get', 'post'], '/system/configuser', [SystemController::class, 'configuser'])->name('admin.system.configuser');
    Route::match(['get', 'post'], '/system/configcomment', [SystemController::class, 'configcomment'])->name('admin.system.configcomment');
    Route::match(['get', 'post'], '/system/configupload', [SystemController::class, 'configupload'])->name('admin.system.configupload');
    Route::match(['get', 'post'], '/system/configinterface', [SystemController::class, 'configinterface'])->name('admin.system.configinterface');
    Route::match(['get', 'post'], '/system/configpay', [SystemController::class, 'configpay'])->name('admin.system.configpay');
    Route::match(['get', 'post'], '/system/configcollect', [SystemController::class, 'configcollect'])->name('admin.system.configcollect');
    Route::match(['get', 'post'], '/system/configapi', [SystemController::class, 'configapi'])->name('admin.system.configapi');
    Route::match(['get', 'post'], '/system/configconnect', [SystemController::class, 'configconnect'])->name('admin.system.configconnect');
    Route::match(['get', 'post'], '/system/configweixin', [SystemController::class, 'configweixin'])->name('admin.system.configweixin');
    Route::match(['get', 'post'], '/system/configview', [SystemController::class, 'configview'])->name('admin.system.configview');
    Route::match(['get', 'post'], '/system/configpath', [SystemController::class, 'configpath'])->name('admin.system.configpath');
    Route::match(['get', 'post'], '/system/configrewrite', [SystemController::class, 'configrewrite'])->name('admin.system.configrewrite');
    Route::match(['get', 'post'], '/system/configemail', [SystemController::class, 'configemail'])->name('admin.system.configemail');
    Route::match(['get', 'post'], '/system/configplay', [SystemController::class, 'configplay'])->name('admin.system.configplay');
    Route::match(['get', 'post'], '/system/configsms', [SystemController::class, 'configsms'])->name('admin.system.configsms');
    Route::post('/system/test_email', [SystemController::class, 'testEmail'])->name('admin.system.test_email');
    Route::post('/system/test_cache', [SystemController::class, 'testCache'])->name('admin.system.test_cache');
});

Route::fallback([MyErrorController::class, 'notFound']);
