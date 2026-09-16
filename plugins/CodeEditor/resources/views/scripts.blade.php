@php
    $assetVer = static function (string $rel): string {
        $path = base_path('plugins/CodeEditor/assets/'.$rel);
        $t = is_file($path) ? (int) filemtime($path) : 1;

        return (string) $t;
    };
@endphp
<script src="/plugin-assets/code-editor/vendor/codemirror.min.js?v={{ $assetVer('vendor/codemirror.min.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/overlay.js?v={{ $assetVer('vendor/overlay.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/xml.js?v={{ $assetVer('vendor/xml.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/javascript.js?v={{ $assetVer('vendor/javascript.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/css.js?v={{ $assetVer('vendor/css.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/htmlmixed.js?v={{ $assetVer('vendor/htmlmixed.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/xml-fold.js?v={{ $assetVer('vendor/xml-fold.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/matchbrackets.js?v={{ $assetVer('vendor/matchbrackets.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/closetag.js?v={{ $assetVer('vendor/closetag.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/active-line.js?v={{ $assetVer('vendor/active-line.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/dialog.js?v={{ $assetVer('vendor/dialog.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/searchcursor.js?v={{ $assetVer('vendor/searchcursor.js') }}"></script>
<script src="/plugin-assets/code-editor/vendor/search.js?v={{ $assetVer('vendor/search.js') }}"></script>
<script src="/plugin-assets/code-editor/blade.js?v={{ $assetVer('blade.js') }}"></script>
<script src="/plugin-assets/code-editor/boot.js?v={{ $assetVer('boot.js') }}"></script>
