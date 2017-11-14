<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/31
 * Time: 上午11:28
 */

namespace app\backend\modules\finance\controllers;


use app\backend\modules\finance\models\Withdraw;
use app\backend\modules\finance\services\WithdrawService;
use app\common\components\BaseController;
use app\common\events\finance\AfterIncomeWithdrawCheckEvent;
use app\common\events\finance\AfterIncomeWithdrawPayEvent;
use app\common\facades\Setting;
use app\common\models\Income;
use Illuminate\Support\Facades\Log;

class WithdrawController extends BaseController
{




    public function dealt()
    {
        $resultData = \YunShop::request();
        if (isset($resultData['submit_check'])) {
            //提交审核
            //dd($resultData);
            $result = $this->submitCheck($resultData['id'], $resultData['audit']);
            return $this->message($result['msg'], yzWebUrl("finance.withdraw.info", ['id' => $resultData['id']]));

        } elseif (isset($resultData['submit_pay'])) {
            //打款
            $result = $this->submitPay($resultData['id'], $resultData['pay_way']);
            return $this->message($result['msg'], yzWebUrl("finance.withdraw.info", ['id' => $resultData['id']]));

        } elseif (isset($resultData['submit_cancel'])) {
            //重新审核
            $result = $this->submitCancel($resultData['id'], $resultData['audit']);
            return $this->message($result['msg'], yzWebUrl("finance.withdraw.info", ['id' => $resultData['id']]));

        }

    }

    public function submitCheck($withdrawId, $incomeData)
    {
        $incomeSet = \Setting::get('withdraw.income');
        $withdraw = Withdraw::getWithdrawById($withdrawId)->first();
        if ($withdraw->status != '0') {
            return ['msg' => '审核失败,数据不符合提现规则!'];
        }
        $withdrawStatus = "-1";
        $actual_amounts = 0;

        // 修改 yz_member_income 表
        foreach ($incomeData as $key => $income) {
            if ($income == 1) {
                $withdrawStatus = "1";
                $actual_amounts += Income::getIncomeById($key)->get()->sum('amount');
                Income::updatedIncomePayStatus($key, ['pay_status' => '1']);

            } elseif ($income == -1) {
                $withdrawStatus = "1";
                Income::updatedIncomePayStatus($key, ['pay_status' => '3','status'=> '0']);
            } else {
                Income::updatedIncomePayStatus($key, ['pay_status' => '-1']);
            }
        }
        $actual_poundage = sprintf("%.2f", $actual_amounts / 100 * $withdraw['poundage_rate']);
        $actual_servicetax = sprintf("%.2f", ($actual_amounts - $actual_poundage) / 100 * $withdraw['servicetax_rate']);
        $updatedData = [
            'status' => $withdrawStatus,
            'actual_amounts' => $actual_amounts - $actual_poundage - $actual_servicetax,
            'actual_poundage' => $actual_poundage,
            'actual_servicetax' => $actual_servicetax,
            'audit_at' => time(),
        ];
        $result = Withdraw::updatedWithdrawStatus($withdrawId, $updatedData);

        if ($result) {
            $noticeData = $withdraw;
            $noticeData->status = $withdrawStatus;
            $noticeData->actual_amounts = $updatedData['actual_amounts'];
            $noticeData->actual_poundage = $updatedData['actual_poundage'];
            $noticeData->audit_at = $updatedData['audit_at'];
            //审核通知事件
            event(new AfterIncomeWithdrawCheckEvent($noticeData));
            return ['msg' => '审核成功!'];
        }
        return ['msg' => '审核失败!'];
    }

    public function submitCancel($withdrawId, $incomeData)
    {
        $withdraw = Withdraw::getWithdrawById($withdrawId)->first();
        if ($withdraw->status != '-1') {
            return ['msg' => '审核失败,数据不符合提现规则!'];
        }
        $withdrawStatus = "-1";
        $actual_amounts = 0;
        foreach ($incomeData as $key => $income) {
            if ($income == 1) {
                $actual_amounts += Income::getIncomeById($key)->get()->sum('amount');
                $withdrawStatus = "1";
                Income::updatedIncomePayStatus($key, ['pay_status' => '1']);

            } elseif ($income == -1) {
                $withdrawStatus = "1";
                Income::updatedIncomePayStatus($key, ['pay_status' => '3','status'=> '0']);

            } else {
                Income::updatedIncomePayStatus($key, ['pay_status' => '-1']);
            }
        }
        $actual_poundage = sprintf("%.2f", $actual_amounts / 100 * $withdraw['poundage_rate']);
        $actual_servicetax = sprintf("%.2f", ($actual_amounts - $actual_poundage) / 100 * $withdraw['servicetax_rate']);
        $updatedData = [
            'status' => $withdrawStatus,
            'actual_amounts' => $actual_amounts - $actual_poundage - $actual_servicetax,
            'actual_poundage' => $actual_poundage,
            'actual_servicetax' => $actual_servicetax,
            'audit_at' => time(),
        ];


        $result = Withdraw::updatedWithdrawStatus($withdrawId, $updatedData);

        if ($result) {
            $noticeData = $withdraw;
            $noticeData->status = $withdrawStatus;
            $noticeData->actual_amounts = $updatedData['actual_amounts'];
            $noticeData->actual_poundage = $updatedData['actual_poundage'];
            $noticeData->audit_at = $updatedData['audit_at'];
            //重新审核通知事件
            event(new AfterIncomeWithdrawCheckEvent($noticeData));
            return ['msg' => '审核成功!'];
        }
        return ['msg' => '审核失败!'];
    }


    public function submitPay($withdrawId, $payWay)
    {
        if (!is_array($withdrawId)) {
            $withdraw = Withdraw::getWithdrawById($withdrawId)->first();

            if ($withdraw->status != '1') {
                return ['msg' => '打款失败,数据不存在或不符合打款规则!'];
            }

            $remark = '提现打款-' . $withdraw->type_name . '-金额:' . $withdraw->actual_amounts . '元,' .
                '手续费:' . $withdraw->actual_poundage;

        } else {
            //支付宝批量打款
            $withdraw = [];
            if ($payWay == '2' && !empty($withdrawId)) {
                foreach ($withdrawId as $id) {
                    $withdraw_modle = Withdraw::getWithdrawById($id)->first();

                    if (!is_null($withdraw_modle)) {
                        if ($withdraw_modle->status != '1') {
                            return ['msg' => '打款失败,数据不存在或不符合打款规则!'];
                        }

                        $withdraw[] = $withdraw_modle;

                        $remark[] = '提现打款-' . $withdraw_modle->type_name . '-金额:' . $withdraw_modle->actual_amounts . '元,' .
                            '手续费:' . $withdraw_modle->actual_poundage;
                    }
                }

                $remark = json_encode($remark);
            }
        }


        if ($payWay == '3') {
            //余额打款

            $resultPay = WithdrawService::balanceWithdrawPay($withdraw, $remark);
            Log::info('MemberId:' . $withdraw->member_id . ', ' . $remark . "打款到余额中!");

        } elseif ($payWay == '2') {
            //支付宝打款

            $resultPay = WithdrawService::alipayWithdrawPay($withdraw, $remark);
            Log::info('MemberId:' . $withdraw->member_id . ', ' . $remark . "支付宝打款中!");

        } elseif ($payWay == '1') {
            //微信打款

            $resultPay = WithdrawService::wechatWithdrawPay($withdraw, $remark);
            Log::info('resultPay:' . $resultPay);
            Log::info('MemberId:' . $withdraw->member_id . ', ' . $remark . "微信打款中!");

        } elseif ($payWay == '4') {
            //手动打款
            $resultPay = true;
            Log::info('MemberId:' . $withdraw->member_id . ', ' . $remark . "手动打款!");

        }

        if ($resultPay && $payWay != '2') {

            $withdraw->pay_status = 1;
            //审核通知事件
            event(new AfterIncomeWithdrawPayEvent($withdraw));

            $updatedData = ['pay_at' => time()];
            Withdraw::updatedWithdrawStatus($withdrawId, $updatedData);
            $result = WithdrawService::otherWithdrawSuccess($withdrawId);
            return ['msg' => '提现打款成功!'];
        } elseif ($resultPay && $payWay == '2') {
            //修改提现记录状态
            $updatedData = [
                'status' => 4,
                'arrival_at' => time(),
            ];
            \Log::info('修改提现记录状态',print_r($updatedData,true));
            return Withdraw::updatedWithdrawStatus($withdrawId, $updatedData);
        }
    }





    public function batchAlipay()
    {
        $ids = \YunShop::request()->ids;

        $ids = explode(',', $ids);

        $result = $this->submitPay($ids, 2);
    }

    public function getAllWithdraw()
    {
        $type = request('type');

        $res = Withdraw::getAllWithdraw($type);

        return json_encode($res);
    }

    public function updateWidthdrawOrderStatus()
    {
        $ids = \YunShop::request()->ids;
        $status = 0;

        if (empty($ids)) {
            return json_encode(['status' => $status]);
        }

        $withdrawId = explode(',', $ids);

        if (Withdraw::updateWidthdrawOrderStatus($withdrawId)) {
            $status = 1;
        }

        return json_encode(['status' => $status]);
    }
}