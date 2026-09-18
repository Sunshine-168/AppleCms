<?php
/**
 * 支付接口定义
 */
interface PaymentInterface {
    /**
     * 提交支付订单，返回支付表单或跳转HTML
     * @param array $order 订单信息
     * @return string
     */
    public function submit($order);
    
    /**
     * 验证异步通知签名
     * @param array $data 回调数据
     * @return bool
     */
    public function verifyNotify($data);
    
    /**
     * 查询订单状态
     * @param string $out_trade_no
     * @return array
     */
    public function queryOrder($out_trade_no);
}