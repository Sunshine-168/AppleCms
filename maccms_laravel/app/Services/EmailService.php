<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class EmailService
{
    /**
     * 使用 PHPMailer 发送邮件
     */
    public function sendWithPhpmailer($to, $title, $body, $config = [])
    {
        if (empty($config)) {
            $config = config('maccms.email.phpmailer', []);
            $config['nick'] = config('maccms.email.nick', '');
        }
        
        require_once app_path('Libraries/Phpmailer/phpmailer/src/PHPMailer.php');
        require_once app_path('Libraries/Phpmailer/phpmailer/src/SMTP.php');
        require_once app_path('Libraries/Phpmailer/phpmailer/src/Exception.php');
        
        $mail = new \phpmailer\src\PHPMailer();
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->CharSet = "UTF-8";
        $mail->Host = $config['host'] ?? '';
        $mail->SMTPSecure = $config['secure'] ?? 'tls';
        $mail->Port = $config['port'] ?? 587;
        $mail->Username = $config['username'] ?? '';
        $mail->Password = $config['password'] ?? '';
        $mail->setFrom($config['username'] ?? '', $config['nick'] ?? '');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body = $body;
        
        $res = $mail->send();
        
        if ($res === true) {
            return ['code' => 1, 'msg' => '发送成功'];
        } else {
            return ['code' => 102, 'msg' => '发生错误：' . $mail->ErrorInfo];
        }
    }
}
