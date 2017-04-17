<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2017/3/24
 * Time: 下午12:42
 */

namespace app\common\services;

use app\common\services\finance\Balance;

class CreditPay extends Pay
{
    public function __construct()
    {
    }

    public function doPay($params = [])
    {
        $data = [
            'member_id' => $params['member_id'],
            'change_money' => $params['amount'],
            'serial_number' => $params['order_no'],
            'operator' => $params['operator'],
            'operator_id' => $params['operator_id'],
            'remark' => $params['remark'],
            'service_type' => $params['service_type']
        ];

        $this->log($params);

        $result = (new Balance())->changeBalance($data);

        if ($result === true) {
            return true;
        } else {
            return false;
        }


    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        // TODO: Implement doRefund() method.
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {
        // TODO: Implement doWithdraw() method.
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

    /**
     * 响应日志
     *
     * @param $post
     */
    public function log($post)
    {
        //访问记录
        self::payAccessLog();
        //保存请求数据
        self::payRequestDataLog($post['order_no'], '支付宝支付', json_encode($post));
    }
}