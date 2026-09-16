/* Laravel Blade + 苹果v12 @vod* overlay on htmlmixed */
(function (mod) {
    if (typeof CodeMirror === 'undefined') return;
    mod(CodeMirror);
})(function (CodeMirror) {
    /* Keep in sync with tests/Unit/BladeHighlightRulesTest.php */
    var BLADE_WORDS = 'if|elseif|else|endif|unless|endunless|isset|endisset|empty|endempty|for|endfor|foreach|endforeach|forelse|endforelse|while|endwhile|continue|break|php|endphp|includeIf|includeWhen|includeUnless|includeFirst|include|each|once|endonce|pushOnce|push|endpush|prependOnce|prepend|endprepend|stack|inject|yield|extends|section|endsection|show|parent|overwrite|stop|append|hasSection|sectionMissing|production|endproduction|env|endenv|auth|endauth|guest|endguest|canany|endcanany|can|endcan|cannot|endcannot|session|endsession|error|enderror|selected|checked|disabled|readonly|required|old|class|style|csrf|method|json|js|vite|props|aware|slot|endslot|component|endcomponent|verbatim|endverbatim|switch|case|default|endswitch|lang|dump|dd|true|false|use';
    var bladeDirRe = new RegExp('^@(?:' + BLADE_WORDS + ')\\b');
    var vodDirRe = /^@(?:end)?vod[A-Za-z]*\b/;
    var bladeStart = /^(?:\{\{--|\{!!|\{\{|@@|@\{\{|@)/;

    function eatParenArgs(stream) {
        if (stream.peek() !== '(') return;
        var depth = 0;
        while (!stream.eol()) {
            var ch = stream.next();
            if (ch === '(') depth += 1;
            else if (ch === ')') {
                depth -= 1;
                if (depth === 0) break;
            }
        }
    }

    CodeMirror.defineMode('laravel-blade', function (config) {
        var htmlMode = CodeMirror.getMode(config, 'htmlmixed');
        var overlay = {
            startState: function () {
                return { kind: null };
            },
            token: function (stream, state) {
                if (state.kind === 'comment') {
                    if (stream.match('--}}')) {
                        state.kind = null;
                        return 'comment';
                    }
                    stream.next();
                    return 'comment';
                }
                if (state.kind === 'raw') {
                    if (stream.match('!!}')) {
                        state.kind = null;
                        return 'tag';
                    }
                    stream.next();
                    return 'string';
                }
                if (state.kind === 'echo') {
                    if (stream.match('}}')) {
                        state.kind = null;
                        return 'tag';
                    }
                    stream.next();
                    return 'string';
                }
                if (state.kind === 'php') {
                    if (stream.match(/^@endphp\b/)) {
                        state.kind = null;
                        return 'keyword';
                    }
                    stream.next();
                    return 'meta';
                }

                if (stream.match('{{--')) {
                    state.kind = 'comment';
                    return 'comment';
                }
                if (stream.match('{!!')) {
                    state.kind = 'raw';
                    return 'tag';
                }
                if (stream.match('@{{')) {
                    state.kind = 'echo';
                    return 'atom';
                }
                if (stream.match('{{')) {
                    state.kind = 'echo';
                    return 'tag';
                }
                if (stream.match('@@')) {
                    return 'atom';
                }
                if (stream.match(/^@php\b/)) {
                    eatParenArgs(stream);
                    state.kind = 'php';
                    return 'keyword';
                }
                if (stream.match(vodDirRe) || stream.match(/^@conf\b/)) {
                    eatParenArgs(stream);
                    return 'atom';
                }
                if (stream.match(bladeDirRe)) {
                    eatParenArgs(stream);
                    return 'keyword';
                }
                if (stream.peek() === '@') {
                    stream.next();
                    return null;
                }
                while (!stream.eol()) {
                    if (stream.match(bladeStart, false)) break;
                    stream.next();
                }
                return null;
            }
        };
        return CodeMirror.overlayMode(htmlMode, overlay);
    });

    CodeMirror.defineMIME('text/x-laravel-blade', 'laravel-blade');
});
