(function (global) {
    if (!global.CodeMirror) return;

    var PHRASES = {
        'Search:': '查找：',
        '(Use /re/ syntax for regexp search)': '可用 /正则/',
        'Replace:': '替换：',
        'Replace all:': '全部替换：',
        'Replace with:': '替换为：',
        'With:': '为：',
        'Replace?': '替换此处？',
        'Yes': '是',
        'No': '否',
        'All': '全部',
        'Stop': '停止'
    };
    var proto = global.CodeMirror.prototype;
    var prevPhrase = proto.phrase;
    proto.phrase = function (text) {
        var dict = (this.options && this.options.phrases) || PHRASES;
        if (dict[text]) return dict[text];
        return prevPhrase ? prevPhrase.call(this, text) : text;
    };

    function mount(textarea, opts) {
        opts = opts || {};
        var cm = global.CodeMirror.fromTextArea(textarea, {
            mode: 'laravel-blade',
            theme: 'apple',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            matchBrackets: true,
            autoCloseTags: true,
            styleActiveLine: true,
            viewportMargin: 80,
            phrases: PHRASES,
            search: { bottom: false },
            extraKeys: {
                'Ctrl-S': function () {
                    if (opts.onSave) opts.onSave();
                    return false;
                },
                'Cmd-S': function () {
                    if (opts.onSave) opts.onSave();
                    return false;
                },
                'Ctrl-F': 'findPersistent',
                'Cmd-F': 'findPersistent',
                'Ctrl-G': 'findNext',
                'Shift-Ctrl-G': 'findPrev',
                'Cmd-G': 'findNext',
                'Shift-Cmd-G': 'findPrev',
                'Ctrl-H': 'replace',
                'Cmd-Alt-F': 'replace',
                'Tab': 'indentMore',
                'Shift-Tab': 'indentLess'
            }
        });
        var wrap = cm.getWrapperElement();
        wrap.classList.add('tpl-cm');
        wrap.style.display = 'none';

        function fit() {
            if (wrap.style.display === 'none') return;
            var top = wrap.getBoundingClientRect().top;
            var h = Math.max(320, Math.floor(window.innerHeight - top - 28));
            cm.setSize('100%', h);
            cm.refresh();
        }

        return {
            get: function () { return cm.getValue(); },
            set: function (value) {
                if (global.CodeMirror.commands.clearSearch) {
                    global.CodeMirror.commands.clearSearch(cm);
                }
                cm.setValue(value == null ? '' : String(value));
                cm.clearHistory();
                cm.setCursor(0, 0);
                cm.scrollTo(0, 0);
            },
            insert: function (text) {
                if (!text) return;
                cm.replaceSelection(text);
                cm.focus();
            },
            focus: function () { cm.focus(); },
            refresh: function () { cm.refresh(); },
            size: fit,
            find: function () { global.CodeMirror.commands.findPersistent(cm); },
            show: function (on) {
                wrap.classList.toggle('is-open', !!on);
                wrap.style.display = on ? 'block' : 'none';
                if (on) {
                    requestAnimationFrame(function () {
                        if (opts.onShow) opts.onShow();
                        else fit();
                        cm.focus();
                    });
                }
            },
            cursor: function () { return cm.getCursor(); },
            onCursor: function (fn) { cm.on('cursorActivity', fn); },
            wrap: wrap,
            onChange: function (fn) { cm.on('change', fn); },
            instance: cm
        };
    }

    global.TplCodeEditor = { mount: mount };
})(window);
