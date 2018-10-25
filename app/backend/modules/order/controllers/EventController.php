<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/10/12
 * Time: 下午4:54
 */

namespace app\backend\modules\order\controllers;


use app\backend\modules\order\models\Order;
use app\common\components\BaseController;

class EventController extends BaseController
{
    public function created()
    {
        $order = Order::find(request('id'));
        $order->fireCreatedEvent();
    }

    public function paid()
    {
        $order = Order::find(request('id'));
        $order->firePaidEvent();
    }

    public function received()
    {
        $order = Order::find(request('id'));
        $order->fireReceivedEvent();
    }
}