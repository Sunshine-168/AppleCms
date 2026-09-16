@php
    $assetVer = static function (string $rel): string {
        $path = base_path('plugins/CodeEditor/assets/'.$rel);
        $t = is_file($path) ? (int) filemtime($path) : 1;

        return (string) $t;
    };
@endphp
<link rel="stylesheet" href="/plugin-assets/code-editor/vendor/codemirror.css?v={{ $assetVer('vendor/codemirror.css') }}">
<link rel="stylesheet" href="/plugin-assets/code-editor/vendor/dialog.css?v={{ $assetVer('vendor/dialog.css') }}">
<link rel="stylesheet" href="/plugin-assets/code-editor/theme-apple.css?v={{ $assetVer('theme-apple.css') }}">
