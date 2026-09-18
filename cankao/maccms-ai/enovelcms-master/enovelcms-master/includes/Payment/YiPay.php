<?php
/**
 * 易支付实现类
 */
require_once 'PaymentInterface.php';

class YiPay implements PaymentInterface {
    private $pid;
    private $key;
    private $apiUrl;

    public function __construct($apiUrl, $pid, $key) {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        $this->pid = $pid;
        $this->key = $key;
    }

    /**
     * 生成签名
     * @param array $data
     * @return string
     */
    private function sign($data) {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($v !== '' && $k != 'sign' && $k != 'sign_type') {
                $str .= $k . '=' . $v . '&';
            }
        }
        $str = rtrim($str, '&');
        return md5($str . $this->key);
    }

    /**
     * 提交支付
     * @param array $order
     * @return string
     */
    public function submit($order) {
        $params = [
            'pid' => $this->pid,
            'type' => $order['type'],
            'out_trade_no' => $order['out_trade_no'],
            'notify_url' => $order['notify_url'],
            'return_url' => $order['return_url'],
            'name' => $order['name'],
            'money' => $order['money'],
            'sitename' => $order['sitename'] ?? 'Novel Site'
        ];
        $params['sign'] = $this->sign($params);
        $params['sign_type'] = 'MD5';

        $html = '<form id="payform" action="' . $this->apiUrl . 'submit.php" method="post">';
        foreach ($params as $k => $v) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '">';
        }
        $html .= '<input type="submit" value="......"></form>';
        $html .= '<script>document.getElementById("payform").submit();</script>';
        return $html;
    }

    /**
     * 验证异步通知签名
     * @param array $data
     * @return bool
     */
    public function verifyNotify($data) {
        if (!isset($data['sign'])) {
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign'], $data['sign_type']);
        return $this->sign($data) === $sign;
    }

    /**
     * 查询订单
     * @param string $out_trade_no
     * @return array
     */
    public function queryOrder($out_trade_no) {
        $params = [
            'act' => 'order',
            'pid' => $this->pid,
            'key' => $this->key,
            'out_trade_no' => $out_trade_no
        ];
        $url = $this->apiUrl . 'api.php?' . http_build_query($params);
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
}