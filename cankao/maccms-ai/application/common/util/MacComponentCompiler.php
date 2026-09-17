<?php
namespace app\common\util;

/**
 * MacComponentCompiler — compiles a Prism page-definition (a JSON tree of component
 * instances) into a MacCMS template: a chain of {include ...} calls wrapped by the
 * base layout. Dependency-free (no ThinkPHP), so it can run standalone or be wired
 * into a console command.
 *
 * The component registry is DISCOVERED by scanning the co-located
 * html/components/**\/*.schema.json files — there is no central manifest. Container
 * vs leaf is detected on disk (<name>_open.html ⇒ container). Each {include} emits
 * exactly the [tokens] its partial uses, filled from the page def + schema defaults,
 * so no literal [prop] ever leaks. `columns` props (cols[]/stack_at) are translated
 * to the CSS-ready cols_template/stack the partial expects.
 *
 *   $c = new MacComponentCompiler('/path/to/template/prism/html');
 *   file_put_contents($dir.'/index/index.html', $c->compile($pageDefArray));
 */
class MacComponentCompiler
{
    private $htmlDir;
    private $compDir;
    private $registry = [];   // name => ['props'=>[k=>default], 'variants'=>[...], 'category'=>'']
    private $partials = [];   // basename => true
    private $tokens   = [];   // basename => [token,...]  (the [name] placeholders in the partial)

    public function __construct($htmlDir)
    {
        $this->htmlDir = rtrim(str_replace('\\', '/', $htmlDir), '/');
        $this->compDir = $this->htmlDir . '/components';
        $this->loadRegistry();
        $this->loadPartials();
    }

    public function components() { return array_keys($this->registry); }

    public function compile(array $page)
    {
        if (!isset($page['blocks']) || !is_array($page['blocks'])) {
            throw new \RuntimeException('page definition requires a blocks[] array');
        }
        $out = "{include file=\"layouts/base_open\" /}\n\n";
        foreach ($page['blocks'] as $b) $out .= $this->emit($b, 0);
        $out .= "\n{include file=\"layouts/base_close\" /}\n";
        return $out;
    }

    // ── emit ──────────────────────────────────────────────────────────────
    private function emit(array $block, $depth)
    {
        $name = isset($block['component']) ? $block['component'] : null;
        if ($name === null) throw new \RuntimeException('block is missing "component"');
        if (!isset($this->registry[$name])) throw new \RuntimeException("unknown component: $name");

        $pad = str_repeat('  ', $depth);
        $dir = $this->dir($name);
        $isContainer = isset($this->partials[$name . '_open']);

        if (!$isContainer) {
            if (!isset($this->partials[$name])) throw new \RuntimeException("no partial for leaf component: $name");
            return $pad . $this->inc("components/$dir/$name", $this->mergeProps($name, $block)) . "\n";
        }
        if ($name === 'columns') return $this->emitColumns($block, $depth, $dir);

        $s  = $pad . $this->inc("components/$dir/{$name}_open", $this->mergeProps($name, $block)) . "\n";
        foreach ($this->slotChildren($block) as $child) $s .= $this->emit($child, $depth + 1);
        $s .= $pad . $this->incBare("components/$dir/{$name}_close") . "\n";
        return $s;
    }

    private function emitColumns(array $block, $depth, $dir)
    {
        $pad   = str_repeat('  ', $depth);
        $given = isset($block['props']) ? $block['props'] : [];
        $cols  = isset($given['cols']) ? $given['cols'] : [8, 4];
        $at    = isset($given['stack_at']) ? (int)$given['stack_at'] : 720;
        $tpl   = is_array($cols) ? implode(' ', array_map(function ($n) { return $n . 'fr'; }, $cols)) : (string)$cols;
        $stack = $at <= 600 ? 'sm' : ($at <= 860 ? 'md' : 'lg');
        $rev   = !empty($given['reverse_stack']) ? '1' : '0';

        $s = $pad . $this->inc("components/$dir/columns_open", ['cols_template' => $tpl, 'stack' => $stack, 'reverse_stack' => $rev]) . "\n";
        $slots = isset($block['slots']) && is_array($block['slots']) ? $block['slots'] : [];
        $keys = array_keys($slots);
        sort($keys, SORT_NATURAL);
        foreach ($keys as $k) {
            $s .= $pad . '  ' . $this->incBare("components/$dir/columns_col_open") . "\n";
            foreach ((array)$slots[$k] as $child) $s .= $this->emit($child, $depth + 2);
            $s .= $pad . '  ' . $this->incBare("components/$dir/columns_col_close") . "\n";
        }
        $s .= $pad . $this->incBare("components/$dir/columns_close") . "\n";
        return $s;
    }

    private function slotChildren(array $block)
    {
        $kids = [];
        if (isset($block['slots']) && is_array($block['slots'])) {
            foreach ($block['slots'] as $children) foreach ((array)$children as $c) $kids[] = $c;
        } elseif (isset($block['children']) && is_array($block['children'])) {
            foreach ($block['children'] as $c) $kids[] = $c;
        }
        return $kids;
    }

    // ── props ─────────────────────────────────────────────────────────────
    private function mergeProps($name, array $block)
    {
        $given  = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
        $merged = array_merge($this->registry[$name]['props'], $given);
        $variants = $this->registry[$name]['variants'];
        if (isset($merged['variant']) && $merged['variant'] !== '' && $variants
            && !in_array($merged['variant'], $variants, true)) {
            throw new \RuntimeException("$name: invalid variant '{$merged['variant']}' (allowed: " . implode(', ', $variants) . ")");
        }
        return $merged;
    }

    private function inc($file, array $props)
    {
        $base   = basename($file);
        $tokens = isset($this->tokens[$base]) ? $this->tokens[$base] : array_keys($props);
        $attrs  = '';
        foreach ($tokens as $k) {
            $v = isset($props[$k]) ? $props[$k] : '';
            if (is_array($v)) continue;
            if (is_bool($v)) $v = $v ? '1' : '0';
            $v = str_replace('"', '', (string)$v);
            $attrs .= ' ' . $k . '="' . $v . '"';
        }
        return '{include file="' . $file . '"' . $attrs . ' /}';
    }

    private function incBare($file) { return '{include file="' . $file . '" /}'; }

    private function dir($name)
    {
        foreach (['atoms', 'layout', 'media', 'data', 'chrome'] as $d) {
            if (is_file($this->compDir . "/$d/$name.html") || is_file($this->compDir . "/$d/{$name}_open.html")) return $d;
        }
        $cat = $this->registry[$name]['category'] ?? '';
        return $cat === 'atom' ? 'atoms' : ($cat ?: 'atoms');
    }

    // ── discovery ─────────────────────────────────────────────────────────
    private function loadRegistry()
    {
        foreach ($this->rglob($this->compDir, '.schema.json') as $f) {
            $s = json_decode(file_get_contents($f), true);
            if (!is_array($s) || empty($s['name'])) continue;
            $defaults = [];
            foreach ((isset($s['props']) ? $s['props'] : []) as $p => $meta) {
                $defaults[$p] = is_array($meta) && array_key_exists('default', $meta) ? $meta['default'] : '';
            }
            $variants = [];
            if (isset($s['props']['variant']['enum'])) $variants = $s['props']['variant']['enum'];
            elseif (isset($s['variants']) && is_array($s['variants'])) $variants = array_keys($s['variants']);
            $this->registry[$s['name']] = ['props' => $defaults, 'variants' => $variants, 'category' => isset($s['category']) ? $s['category'] : ''];
        }
    }

    private function loadPartials()
    {
        foreach ($this->rglob($this->compDir, '.html') as $f) {
            $base = basename($f, '.html');
            $this->partials[$base] = true;
            if (preg_match_all('/\[([a-z_]+)\]/', file_get_contents($f), $m)) {
                $this->tokens[$base] = array_values(array_unique($m[1]));
            } else {
                $this->tokens[$base] = [];
            }
        }
    }

    private function rglob($dir, $ext)
    {
        $out = [];
        if (!is_dir($dir)) return $out;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && substr($f->getFilename(), -strlen($ext)) === $ext) {
                $out[] = str_replace('\\', '/', $f->getPathname());
            }
        }
        return $out;
    }
}
