<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/1
 * Time: 下午2:53
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

class OrderMemberOperation extends BaseModel
{
    public $table = 'yz_order_member_operation';
    protected $fillable = [];
    protected $guarded = ['id'];

}