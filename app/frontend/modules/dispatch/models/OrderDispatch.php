<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/9
 * Time: 上午9:25
 */

namespace app\frontend\modules\dispatch\models;

use app\common\events\dispatch\OrderDispatchWasCalculated;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\services\OrderService;

class OrderDispatch
{
    private $preGeneratedOrder;

    public function __construct(PreOrder $preGeneratedOrder)
    {
        $this->preGeneratedOrder = $preGeneratedOrder;
    }

    /**
     * 订单运费
     * @return float|int
     */
    public function getDispatchPrice()
    {
        if (!isset($this->preGeneratedOrder->hasOneDispatchType) || !$this->preGeneratedOrder->hasOneDispatchType->needSend()) {
            // 没选配送方式 或者 不需要配送配送
            return 0;
        }
        $event = new OrderDispatchWasCalculated($this->preGeneratedOrder);
        event($event);
        $data = $event->getData();
        return $result = array_sum(array_column($data, 'price'));
    }

}