/* Laravel Blade + 苹果v12 @vod* on htmlmixed (not overlay: HTML must not swallow @if / {{) */
(function (mod) {
    if (typeof CodeMirror === 'undefined') return;
    mod(CodeMirror);
})(function (CodeMirror) {
    /* Keep in sync with tests/Unit/BladeHighlightRulesTest.php */
    var BLADE_WORDS = 'if|elseif|else|endif|unless|endunless|isset|endisset|empty|endempty|foreach|endforeach|forelse|endforelse|for|endfor|while|endwhile|continue|break|php|endphp|includeIf|includeWhen|includeUnless|includeFirst|include|each|once|endonce|pushOnce|push|endpush|prependOnce|prepend|endprepend|stack|inject|yield|extends|section|endsection|show|parent|overwrite|stop|append|hasSection|sectionMissing|production|endproduction|env|endenv|auth|endauth|guest|endguest|canany|endcanany|cannot|endcannot|can|endcan|session|endsession|error|enderror|selected|checked|disabled|readonly|required|old|class|style|csrf|method|json|js|vite|props|aware|slot|endslot|component|endcomponent|verbatim|endverbatim|switch|case|default|endswitch|lang|dump|dd|true|false|use|example';
    var bladeDirRe = new RegExp('^@(?:' + BLADE_WORDS + ')\\b');
    var vodDirRe = /^@(?:end)?vod[A-Za-z]*\b/;
    var cutRe = new RegExp('\\{\\{--|\\{!!|\\{\\{|@@|@\\{\\{|@(?:end)?vod[A-Za-z]*\\b|@conf\\b|@(?:' + BLADE_WORDS + ')\\b');

    function exprToken(stream, skipParen) {
        if (stream.eatSpace()) return null;
        if (stream.match('//')) {
            stream.skipToEnd();
            return 'comment';
        }
        var q = stream.peek();
        if (q === "'" || q === '"') {
            stream.next();
            var escaped = false;
            while (!stream.eol()) {
                var ch = stream.next();
                if (escaped) {
                    escaped = false;
                } else if (ch === '\\') {
                    escaped = true;
                } else if (ch === q) {
                    break;
                }
            }
            return 'string';
        }
        if (stream.match(/^\$[A-Za-z_][A-Za-z0-9_]*/)) return 'variable-2';
        if (stream.match(/^->[A-Za-z_][A-Za-z0-9_]*/)) return 'property';
        if (stream.match(/^\d+(?:\.\d+)?/)) return 'number';
        if (stream.match(/^(?:=>|\?\?|\?:|::|\|\||&&|===|!==|==|!=|<=|>=)/)) return 'operator';
        if (stream.match(/^[+\-*\/%=<>!&|^~?:.]/)) return 'operator';
        if (stream.match(skipParen ? /^[\[\]{},;]/ : /^[()[\]{},;]/)) return 'punctuation';
        if (stream.match(/^(?:true|false|null|as|and|or|xor|new|function|return|echo|clone|instanceof|isset|empty)\b/)) {
            return 'atom';
        }
        if (stream.match(/^[A-Za-z_][A-Za-z0-9_]*/)) {
            return stream.peek() === '(' ? 'builtin' : 'variable';
        }
        stream.next();
        return 'operator';
    }

    function argsToken(stream, state) {
        if (stream.eatSpace()) return null;
        if (stream.peek() === '(') {
            stream.next();
            state.argDepth++;
            return 'punctuation';
        }
        if (stream.peek() === ')') {
            stream.next();
            state.argDepth--;
            if (state.argDepth <= 0) {
                state.kind = null;
                state.argDepth = 0;
            }
            return 'punctuation';
        }
        return exprToken(stream, true);
    }

    CodeMirror.defineMode('laravel-blade', function (config) {
        var htmlMode = CodeMirror.getMode(config, 'htmlmixed');

        function bladeToken(stream, state) {
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
                return exprToken(stream);
            }
            if (state.kind === 'echo') {
                if (stream.match('}}')) {
                    state.kind = null;
                    return 'tag';
                }
                return exprToken(stream);
            }
            if (state.kind === 'php') {
                if (stream.match(/^@endphp\b/)) {
                    state.kind = null;
                    return 'def';
                }
                return exprToken(stream);
            }
            if (state.kind === 'args') {
                return argsToken(stream, state);
            }
            if (state.kind === 'after-dir') {
                if (stream.eatSpace()) return null;
                if (stream.peek() === '(') {
                    state.kind = 'args';
                    state.argDepth = 0;
                    return argsToken(stream, state);
                }
                state.kind = null;
                return bladeToken(stream, state);
            }
            if (state.kind === 'after-php') {
                if (stream.eatSpace()) return null;
                if (stream.peek() === '(') {
                    state.kind = 'args';
                    state.argDepth = 0;
                    return argsToken(stream, state);
                }
                state.kind = 'php';
                return bladeToken(stream, state);
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
                state.kind = 'after-php';
                return 'def';
            }
            if (stream.match(/^@endphp\b/)) {
                return 'def';
            }
            if (stream.match(vodDirRe) || stream.match(/^@conf\b/)) {
                state.kind = 'after-dir';
                return 'atom';
            }
            if (stream.match(bladeDirRe)) {
                state.kind = 'after-dir';
                return 'keyword';
            }
            return null;
        }

        return {
            startState: function () {
                return { html: CodeMirror.startState(htmlMode), kind: null, argDepth: 0 };
            },
            copyState: function (state) {
                return {
                    html: CodeMirror.copyState(htmlMode, state.html),
                    kind: state.kind,
                    argDepth: state.argDepth
                };
            },
            token: function (stream, state) {
                if (state.kind) {
                    var inner = bladeToken(stream, state);
                    if (inner || state.kind) {
                        return inner;
                    }
                }
                var style = bladeToken(stream, state);
                if (style || state.kind) {
                    return style;
                }
                var rest = stream.string.slice(stream.pos);
                var cut = rest.search(cutRe);
                if (cut === 0) {
                    stream.next();
                    return null;
                }
                if (cut > 0) {
                    var end = stream.pos + cut;
                    var old = stream.string;
                    stream.string = old.slice(0, end);
                    var htmlStyle = htmlMode.token(stream, state.html);
                    stream.string = old;
                    if (stream.pos > end) {
                        stream.pos = end;
                    }
                    return htmlStyle;
                }
                return htmlMode.token(stream, state.html);
            },
            indent: function (state, textAfter) {
                if (state.kind || ! htmlMode.indent) {
                    return CodeMirror.Pass;
                }
                return htmlMode.indent(state.html, textAfter);
            },
            innerMode: function (state) {
                if (state.kind) {
                    return null;
                }
                return { state: state.html, mode: htmlMode };
            }
        };
    });

    CodeMirror.defineMIME('text/x-laravel-blade', 'laravel-blade');
});
