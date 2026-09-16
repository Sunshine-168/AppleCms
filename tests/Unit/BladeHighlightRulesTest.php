<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class BladeHighlightRulesTest extends TestCase
{
    public function test_all_theme_directives_are_in_the_highlighter(): void
    {
        $js = (string) file_get_contents(base_path('plugins/CodeEditor/assets/blade.js'));
        $this->assertSame(1, preg_match("/var BLADE_WORDS = '([^']+)'/", $js, $m));
        $blade = explode('|', $m[1]);
        $unknown = [];
        $files = [];
        $root = resource_path('views/themes/default');
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $files[] = $file->getPathname();
        }
        $this->assertNotEmpty($files);
        foreach ($files as $path) {
            $src = (string) file_get_contents($path);
            preg_match_all('/@(?!@)[A-Za-z_][A-Za-z0-9_]*/', $src, $found);
            foreach (array_unique($found[0]) as $dir) {
                $name = substr($dir, 1);
                if (preg_match('/^(end)?vod[A-Za-z]*$/', $name) || $name === 'conf') {
                    continue;
                }
                if (! in_array($name, $blade, true)) {
                    $unknown[] = str_replace('\\', '/', substr($path, strlen($root) + 1)).':'.$dir;
                }
            }
        }
        $this->assertSame([], $unknown, 'unhighlighted: '.implode(', ', $unknown));
        $this->assertContains('endif', $blade);
        $this->assertContains('csrf', $blade);
        $this->assertContains('includeIf', $blade);
        $this->assertContains('forelse', $blade);
        $this->assertContains('json', $blade);
        $this->assertContains('php', $blade);
        $this->assertContains('guest', $blade);
        $this->assertContains('extends', $blade);
        $this->assertContains('section', $blade);
        $this->assertContains('for', $blade);
        $this->assertContains('foreach', $blade);
        $this->assertStringContainsString('vodDirRe', $js);
        $this->assertStringContainsString('cutRe', $js);
        $this->assertStringContainsString('htmlMode.token', $js);
        $this->assertStringNotContainsString('overlayMode', $js);
    }

    public function test_requested_directives_match_the_highlighter_regex(): void
    {
        $js = (string) file_get_contents(base_path('plugins/CodeEditor/assets/blade.js'));
        $this->assertSame(1, preg_match("/var BLADE_WORDS = '([^']+)'/", $js, $m));
        $re = '/^@(?:'.$m[1].')\b/';
        foreach ([
            '@php', '@endphp', '@if', '@endif', '@else', '@elseif',
            '@for', '@endfor', '@foreach', '@endforeach',
            '@guest', '@endguest', '@auth', '@endauth',
            '@extends', '@section', '@endsection', '@csrf', '@json',
        ] as $dir) {
            $this->assertSame(1, preg_match($re, $dir), $dir);
        }
        $this->assertSame(1, preg_match('/^@(?:end)?vod[A-Za-z]*\b/', '@vodSource'));
        $this->assertSame(1, preg_match('/^@(?:end)?vod[A-Za-z]*\b/', '@endvodSource'));
    }

    public function test_css_at_rules_are_not_treated_as_blade(): void
    {
        $js = (string) file_get_contents(base_path('plugins/CodeEditor/assets/blade.js'));
        $this->assertSame(1, preg_match("/var BLADE_WORDS = '([^']+)'/", $js, $m));
        $blade = explode('|', $m[1]);
        foreach (['media', 'keyframes', 'import', 'gmail'] as $word) {
            $this->assertNotContains($word, $blade);
        }
    }
}
