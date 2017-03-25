<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 17/3/9
 * Time: 上午11:40
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\BaseController;
use app\common\services\CreditPay;
use app\common\services\WechatPay;
use app\frontend\modules\member\services\MemberService;
use app\common\services\AliPay;


class TestController extends BaseController
{
   public function index()
   {
       $pay = new WechatPay();
       $data = $pay->doPay(['order_no'=>time(),'amount'=>0.2, 'subject'=>'微信支付', 'body'=>'测试:2', 'extra'=>'']);

echo '<pre>';print_r($data);exit;
       $this->render('shop/wx',[
           'config' => $data['config'],
           'js' => $data['js']
       ]);
       exit;
       $pay = new AliPay();

      //\\ $p = $pay->doRefund('2017032421001004920213140182', '1', '0.1');

       //$p = $pay->doPay(['order_no'=>time(),'amount'=>0.2, 'subject'=>'支付宝支付', 'body'=>'测试:2', 'extra'=>'']);
       $p = $pay->doWithdraw(4,time(),'0.1','提现');
       redirect($p)->send();
   }

   public function add()
   {
       echo MemberService::$name;
   }
}
