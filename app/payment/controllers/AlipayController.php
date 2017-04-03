<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24/03/2017
 * Time: 01:07
 */

namespace app\payment\controllers;


use app\common\facades\Setting;
use app\payment\PaymentController;

class AlipayController extends PaymentController
{
    public function notifyUrl()
    {
        $this->pay($_POST);

        $verify_result = $this->getSignResult();

        if($verify_result) {
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            $total_fee = $_POST['total_fee'];

            if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $pay_log = [];
                if (bccomp($pay_log['price'], $total_fee, 2) == 0) {
                     // TODO 更新支付单状态
                     // TODO 更新订单状态
                }
            }

            echo "success";

        } else {
            echo "fail";
        }
    }

    public function returnUrl()
    {
        file_put_contents('../../../../addons/sz_yi/data/r.log', print_r($_GET,1));
        // TODO 访问记录
        // TODO 保存响应数据

        $verify_result = $this->getSignResult();

        if($verify_result) {
            if($_GET['trade_status'] == 'TRADE_SUCCESS') {
                echo 'ok';exit;
                redirect()->send();
            }
        } else {
            echo "您提交的订单验证失败";
        }
    }

    /**
     * 签名验证
     *
     * @return bool
     */
    public function getSignResult()
    {
        $key = Setting::get('alipay-web.key');

        $alipay = app('alipay.web');
        $alipay->setSignType('MD5');
        $alipay->setKey($key);

        return $alipay->verify();
    }

    public function log($post)
    {
        $pay = new WechatPay();

        //访问记录
        $pay->payAccessLog();
        //保存响应数据
        $pay_order_info = PayOrder::getPayOrderInfo($post['out_trade_no'])->first()->toArray();
        $pay->payResponseDataLog($pay_order_info['id'], $pay_order_info['out_order_no'], '微信支付', json_encode($post));
    }
}