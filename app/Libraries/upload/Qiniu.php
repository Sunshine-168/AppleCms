<?php
namespace App\Libraries\Upload;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu
{
    public $name = '七牛云存储';
    public $ver = '1.0';
    private $config = [];

    public function __construct($config = []) {
        $this->config = $config;
    }

    public function submit($file_path)
    {
        $uploadConfig = config('maccms.upload.api.qiniu', []);
        $bucket = $uploadConfig['bucket'] ?? '';
        $accessKey = $uploadConfig['accesskey'] ?? '';
        $secretKey = $uploadConfig['secretkey'] ?? '';

        require_once base_path('app/Libraries/Qiniu/qiniu/autoload.php');
        $auth = new Auth($accessKey, $secretKey);
        $return = '{"newName":"$(key)","hash":"$(etag)","fsize":$(fsize),"bucket":"$(bucket)","oldName":"$(fname)","width":"$(imageInfo.width)","height":"$(imageInfo.height)"}';
        $return = array('returnBody' => $return);
        $expires = 3600;
        $token = $auth->uploadToken($bucket,$file_path,$expires,$return);
        $filePath = public_path($file_path);
        $uploadMgr = new UploadManager();
        $a = $uploadMgr->putFile($token, $file_path, $filePath);
        empty($this->config['keep_local']) && @unlink($filePath);
        return ($uploadConfig['url'] ?? '') . '/' . $file_path;
    }
}
