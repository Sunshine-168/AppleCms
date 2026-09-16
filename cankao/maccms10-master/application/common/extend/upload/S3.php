<?php
namespace app\common\extend\upload;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3
{
    public $name = 'S3';
    public $ver = '1.0';
    private $config = [];

    public function __construct($config = []) {
        $this->config = $config;
    }

    public function submit($file_path)
    {
        $bucket = $GLOBALS['config']['upload']['api']['s3']['bucket'];
        $accessKey = $GLOBALS['config']['upload']['api']['s3']['accesskey'];
        $secretKey = $GLOBALS['config']['upload']['api']['s3']['secretkey'];
        $region = $GLOBALS['config']['upload']['api']['s3']['region'];
        $endpoint = !empty($GLOBALS['config']['upload']['api']['s3']['endpoint']) ? $GLOBALS['config']['upload']['api']['s3']['endpoint'] : '';
        $basepath = !empty($GLOBALS['config']['upload']['api']['s3']['basepath']) ? $GLOBALS['config']['upload']['api']['s3']['basepath'] : '';
        $domain = !empty($GLOBALS['config']['upload']['api']['s3']['domain']) ? $GLOBALS['config']['upload']['api']['s3']['domain'] : '';

        require_once ROOT_PATH . 'extend/aws/autoload.php';
        $options = [
            'region'  => $region,
            'version' => '2006-03-01',
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey
            ]
        ];
        if (!empty($endpoint)) {
            $options['endpoint'] = $endpoint;
            $options['use_path_style_endpoint'] = true;
        }
        $s3 = new S3Client($options);
        $filePath = ROOT_PATH . $file_path;
        if (!is_file($filePath)) {
            return $file_path;
        }
        try {
            $key = !empty($basepath) ? rtrim($basepath, '/') . '/' . ltrim($file_path, '/') : $file_path;
            $put = [
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => fopen($filePath, 'r'),
            ];
            // acl=false/'' 时不写 ACL（适合备份私有桶 / MinIO）；未传时保持图片场景 public-read
            if (array_key_exists('acl', $this->config)) {
                if ($this->config['acl'] !== false && $this->config['acl'] !== null && $this->config['acl'] !== '') {
                    $put['ACL'] = $this->config['acl'];
                }
            } else {
                $put['ACL'] = 'public-read';
            }
            $result = $s3->putObject($put);
        } catch (AwsException $e) {
            return $file_path;
        }

        empty($this->config['keep_local']) && @unlink($filePath);
        if (!empty($domain)) {
            return rtrim($domain, '/') . '/' . $bucket . '/' . $key;
        }
        return isset($result['ObjectURL']) ? $result['ObjectURL'] : $file_path;
    }
}
