<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('mac_cj_node', function (Blueprint $table) {
            $table->unsignedSmallInteger('nodeid')->autoIncrement();
            $table->string('name', 20);
            $table->unsignedInteger('lastdate')->default(0);
            $table->string('sourcecharset', 8);
            $table->unsignedTinyInteger('sourcetype')->default(0);
            $table->text('urlpage');
            $table->unsignedTinyInteger('pagesize_start')->default(0);
            $table->unsignedMediumInteger('pagesize_end')->default(0);
            $table->char('page_base', 255);
            $table->unsignedTinyInteger('par_num')->default(1);
            $table->char('url_contain', 100);
            $table->char('url_except', 100);
            $table->char('url_start', 100);
            $table->char('url_end', 100);
            $table->char('title_rule', 100);
            $table->text('title_html_rule');
            $table->char('type_rule', 100);
            $table->text('type_html_rule');
            $table->char('content_rule', 100);
            $table->text('content_html_rule');
            $table->char('content_page_start', 100);
            $table->char('content_page_end', 100);
            $table->unsignedTinyInteger('content_page_rule')->default(0);
            $table->unsignedTinyInteger('content_page')->default(0);
            $table->char('content_nextpage', 100);
            $table->unsignedTinyInteger('down_attachment')->default(0);
            $table->unsignedTinyInteger('watermark')->default(0);
            $table->unsignedTinyInteger('coll_order')->default(0);
            $table->text('customize_config');
            $table->text('program_config');
            $table->unsignedTinyInteger('mid')->default(1);
        });

        Schema::create('mac_cj_content', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('nodeid')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->char('url', 255);
            $table->char('title', 100);
            $table->mediumText('data');
            $table->index('nodeid', 'nodeid');
            $table->index('status', 'status');
        });

        Schema::create('mac_cj_history', function (Blueprint $table) {
            $table->char('md5', 32)->primary();
            $table->index('md5', 'md5');
        });

        Schema::create('mac_collect', function (Blueprint $table) {
            $table->unsignedInteger('collect_id')->autoIncrement();
            $table->string('collect_name', 30);
            $table->string('collect_url', 255);
            $table->unsignedTinyInteger('collect_type')->default(1);
            $table->unsignedTinyInteger('collect_mid')->default(1);
            $table->string('collect_appid', 30);
            $table->string('collect_appkey', 30);
            $table->string('collect_param', 100);
            $table->unsignedTinyInteger('collect_filter')->default(0);
            $table->string('collect_filter_from', 255);
            $table->string('collect_filter_year', 255);
            $table->unsignedTinyInteger('collect_opt')->default(0);
            $table->unsignedTinyInteger('collect_sync_pic_opt')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_collect');
        Schema::dropIfExists('mac_cj_history');
        Schema::dropIfExists('mac_cj_content');
        Schema::dropIfExists('mac_cj_node');
    }
};
