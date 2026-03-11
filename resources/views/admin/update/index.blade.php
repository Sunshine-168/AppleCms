@include('admin.public.head')
<div class="page-container p10">
    <blockquote class="layui-elem-quote layui-quote-nm">
        当前版本：<span class="layui-badge">{{ $version }}</span><br>
        在线升级会下载远程升级包并覆盖当前项目文件，请先自行备份代码和数据库。
    </blockquote>

    <div class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">升级包标识</label>
            <div class="layui-input-inline w300">
                <input type="text" id="update-file" class="layui-input" placeholder="例如：2025.1001.0001">
            </div>
            <button type="button" class="layui-btn layui-btn-normal" id="check-update-btn">检查更新</button>
            <button type="button" class="layui-btn" id="run-update-btn">开始升级</button>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-header">检查结果</div>
        <div class="layui-card-body">
            <pre id="update-result" style="white-space: pre-wrap;">点击“检查更新”后会在这里显示结果。</pre>
        </div>
    </div>
</div>
@include('admin.public.foot')
<script type="text/javascript">
    document.getElementById('check-update-btn').addEventListener('click', function () {
        var result = document.getElementById('update-result');
        result.textContent = '检查中...';
        $.get("{{ route('admin.update.check') }}", function (resp) {
            if (resp.code === 1) {
                var text = '消息：' + resp.msg + "\n";
                text += '当前版本：{{ $version }}' + "\n";
                text += '远端版本：' + (resp.version || '未知') + "\n";
                text += '升级包：' + (resp.file || '未返回') + "\n";
                text += '可升级：' + (resp.has_update ? '是' : '否');
                result.textContent = text;
                if (resp.file) {
                    document.getElementById('update-file').value = resp.file;
                }
            } else {
                result.textContent = resp.msg || '检查失败';
            }
        }).fail(function () {
            result.textContent = '检查失败';
        });
    });

    document.getElementById('run-update-btn').addEventListener('click', function () {
        var file = document.getElementById('update-file').value.trim();
        if (!file) {
            layer.msg('请先填写升级包标识');
            return;
        }
        location.href = "{{ route('admin.update.step1', ['file' => '__FILE__']) }}".replace('__FILE__', encodeURIComponent(file));
    });
</script>
