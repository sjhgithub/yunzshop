<?php

/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/6/6
 * Time: 下午4:11
 */

namespace app\common\models;

class OrderCreatedJob extends BaseModel
{
    public $table = 'yz_order_created_job';

    protected $guarded = ['id'];

}
