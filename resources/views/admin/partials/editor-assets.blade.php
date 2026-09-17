@once
@push('styles')
<style>
    .tox-tinymce { max-width: 720px; }
    .entry-main .tox-tinymce { max-width: none; }
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.tinymce) return;
    tinymce.init({
        selector: 'textarea.cms-editor',
        height: 420,
        menubar: false,
        plugins: 'link lists image code table autoresize',
        toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image | code',
        branding: false,
        convert_urls: false,
        paste_data_images: true,
        automatic_uploads: true,
        images_file_types: 'jpeg,jpg,png,gif,webp',
        images_upload_handler: function (blobInfo) {
            var blob = blobInfo.blob();
            var name = blobInfo.filename() || 'image.png';
            var file = blob instanceof File ? blob : new File([blob], name, { type: blob.type || 'image/png' });
            return AdminUi.upload(file).then(function (res) {
                if (res && res.code === 0 && res.data && res.data.url) return res.data.url;
                throw new Error((res && res.msg) || '上传失败');
            });
        },
        file_picker_types: 'image',
        file_picker_callback: function (callback) {
            AdminUi.pickFile('image/*').then(function (file) {
                if (!file) return;
                AdminUi.loading(true);
                return AdminUi.upload(file).then(function (res) {
                    AdminUi.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        callback(res.data.url, { alt: '' });
                    } else {
                        AdminUi.toast((res && res.msg) || '上传失败', 'err');
                    }
                });
            });
        }
    });
});
</script>
@endpush
@endonce
