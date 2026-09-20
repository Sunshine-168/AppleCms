<?php
$root = dirname(__DIR__);
$dirs = [
    $root.'/resources/views/admin',
    $root.'/plugins',
];
$n = 0;
foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if (! $f->isFile() || ! str_ends_with($f->getFilename(), '.blade.php')) {
            continue;
        }
        $path = $f->getPathname();
        $src = (string) file_get_contents($path);
        $next = preg_replace_callback(
            '/@json\((\$[A-Za-z_][A-Za-z0-9_]*)\)/',
            static function (array $m): string {
                return '@json('.$m[1].', JSON_UNESCAPED_UNICODE)';
            },
            $src
        );
        if (! is_string($next) || $next === $src) {
            continue;
        }
        file_put_contents($path, $next);
        $n++;
        echo substr($path, strlen($root) + 1).PHP_EOL;
    }
}
echo "updated=$n\n";
