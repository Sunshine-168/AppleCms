<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerCmsTags();
    }


    /**
     * 注册模板标签
     * @return void
     */
    protected function registerCmsTags(): void
    {

    }

    /**
     * 注册 maccms 模板标签
     */
//    protected function registerMaccmsTags()
//    {
//        // maccms:link 标签
//        Blade::directive('maccmslink', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getLinkList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmslink', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // 兼容原标签语法：{maccms:link ...} 转换为 @maccmslink
//        // 注意：这个需要在模板中手动转换，或者使用 convert_tags.php 脚本
//
//        // maccms:area 标签
//        Blade::directive('maccmsarea', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getAreaList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsarea', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:lang 标签
//        Blade::directive('maccmslang', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getLangList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmslang', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:year 标签
//        Blade::directive('maccmsyear', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getYearList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsyear', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:type 标签
//        Blade::directive('maccmstype', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getTypeList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmstype', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:vod 标签
//        Blade::directive('maccmsvod', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getVodList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsvod', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:art 标签
//        Blade::directive('maccmsart', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getArtList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsart', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:actor 标签
//        Blade::directive('maccmsactor', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getActorList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsactor', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:topic 标签
//        Blade::directive('maccmstopic', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getTopicList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmstopic', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:comment 标签
//        Blade::directive('maccmscomment', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getCommentList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmscomment', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:gbook 标签
//        Blade::directive('maccmsgbook', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getGbookList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsgbook', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:role 标签
//        Blade::directive('maccmsrole', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getRoleList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsrole', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:website 标签
//        Blade::directive('maccmswebsite', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getWebsiteList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmswebsite', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:class 标签
//        Blade::directive('maccmsclass', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getClassList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsclass', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:version 标签
//        Blade::directive('maccmsversion', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getVersionList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsversion', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:state 标签
//        Blade::directive('maccmsstate', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getStateList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsstate', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:letter 标签
//        Blade::directive('maccmsletter', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getLetterList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsletter', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//
//        // maccms:manga 标签
//        Blade::directive('maccmsmanga', function ($expression) {
/*            return "<?php \$__TAG_PARAMS__ = {$expression}; \$__TAG_SERVICE__ = app(\App\Services\TagService::class); \$__TAG_LIST__ = \$__TAG_SERVICE__->getMangaList(\$__TAG_PARAMS__); ?>";*/
//        });
//
//        Blade::directive('endmaccmsmanga', function () {
/*            return "<?php unset(\$__TAG_LIST__, \$__TAG_PARAMS__, \$__TAG_SERVICE__); ?>";*/
//        });
//    }
}
