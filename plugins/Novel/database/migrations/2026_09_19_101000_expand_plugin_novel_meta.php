<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plugin_novels') && ! Schema::hasColumn('plugin_novels', 'tags')) {
            Schema::table('plugin_novels', function (Blueprint $t) {
                $t->string('tags', 255)->default('')->after('author');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plugin_novels') && Schema::hasColumn('plugin_novels', 'tags')) {
            Schema::table('plugin_novels', function (Blueprint $t) {
                $t->dropColumn('tags');
            });
        }
    }
};
