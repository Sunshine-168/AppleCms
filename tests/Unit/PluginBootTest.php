<?php

namespace Tests\Unit;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PluginBootTest extends TestCase
{
    public function test_migrate_file_skips_when_sqlite_file_is_missing(): void
    {
        $missing = database_path('missing-install-'.uniqid('', true).'.sqlite');
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $missing,
        ]);
        DB::purge('sqlite');
        app()->forgetInstance('plugin.boot.database');

        $file = $this->migrationFile(<<<'PHP'
<?php

return new class {
    public function up(): void
    {
        throw new \RuntimeException('should not run');
    }
};
PHP);

        try {
            PluginBoot::migrateFile($file);
            $this->assertFileDoesNotExist($missing);
        } finally {
            @unlink($file);
        }
    }

    public function test_migrate_file_runs_when_database_is_available(): void
    {
        $file = $this->migrationFile(<<<'PHP'
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        if (! Schema::hasTable('plugin_boot_probe')) {
            Schema::create('plugin_boot_probe', function (Blueprint $table) {
                $table->increments('id');
            });
        }
    }
};
PHP);

        try {
            PluginBoot::migrateFile($file);
            $this->assertTrue(Schema::hasTable('plugin_boot_probe'));
        } finally {
            @unlink($file);
        }
    }

    private function migrationFile(string $contents): string
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'plugin-boot-'.uniqid('', true).'.php';
        file_put_contents($file, $contents);

        return $file;
    }
}
