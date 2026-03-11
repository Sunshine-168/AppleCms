<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Upyun\Upyun as UpyunClient;
use Upyun\Config as UpyunConfig;
use Qiniu\Auth as QiniuAuth;
use Qiniu\Storage\UploadManager as QiniuUploadManager;

class UploadService
{
    /**
     * 上传文件到又拍云
     */
    public function uploadToUpyun($filePath, $config = [])
    {
        $upyunConfig = config('maccms.upload.api.upyun', []);
        
        require_once app_path('Libraries/Upyun/upyun/vendor/autoload.php');
        
        $bucketConfig = new UpyunConfig(
            $upyunConfig['bucket'] ?? '',
            $upyunConfig['username'] ?? '',
            $upyunConfig['pwd'] ?? ''
        );
        
        $client = new UpyunClient($bucketConfig);
        $file = fopen($filePath, 'r');
        $client->write($filePath, $file);
        fclose($file);
        
        // 如果配置不保留本地文件，删除本地文件
        if (empty($config['keep_local'])) {
            @unlink($filePath);
        }
        
        return ($upyunConfig['url'] ?? '') . '/' . $filePath;
    }

    /**
     * 上传文件到七牛云
     */
    public function uploadToQiniu($filePath, $config = [])
    {
        $qiniuConfig = config('maccms.upload.api.qiniu', []);
        
        require_once app_path('Libraries/Qiniu/qiniu/autoload.php');
        
        $auth = new QiniuAuth(
            $qiniuConfig['accesskey'] ?? '',
            $qiniuConfig['secretkey'] ?? ''
        );
        
        $bucket = $qiniuConfig['bucket'] ?? '';
        $returnBody = '{"newName":"$(key)","hash":"$(etag)","fsize":$(fsize),"bucket":"$(bucket)","oldName":"$(fname)","width":"$(imageInfo.width)","height":"$(imageInfo.height)"}';
        $policy = ['returnBody' => $returnBody];
        $expires = 3600;
        $token = $auth->uploadToken($bucket, $filePath, $expires, $policy);
        
        $uploadMgr = new QiniuUploadManager();
        $uploadMgr->putFile($token, $filePath, $filePath);
        
        // 如果配置不保留本地文件，删除本地文件
        if (empty($config['keep_local'])) {
            @unlink($filePath);
        }
        
        return ($qiniuConfig['url'] ?? '') . '/' . $filePath;
    }
}
