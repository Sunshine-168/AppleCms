<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'mac_admin', 'mac_group', 'mac_plog', 'mac_type', 'mac_ulog', 'mac_user',
            'mac_art', 'mac_vod', 'mac_actor', 'mac_role', 'mac_topic', 'mac_comment',
            'mac_gbook', 'mac_link', 'mac_card', 'mac_order', 'mac_visit', 'mac_collect',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
    }
};
