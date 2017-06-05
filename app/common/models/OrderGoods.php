<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 上午11:24
 */

namespace app\common\models;

use app\common\models\goods\GoodsDispatch;
use app\common\models\order\OrderGoodsChangePriceLog;
use Illuminate\Database\Eloquent\Builder;

class OrderGoods extends BaseModel
{
    public $table = 'yz_order_goods';
    protected $hidden = ['order_id'];
    protected $appends = ['buttons'];
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $attributes = [
        'goods_option_id' => 0,
        'goods_option_title' => ''
    ];
    protected $search_fields = ['goods_sn', 'title'];

    //public function
    public function hasOneGoods()
    {
        return $this->hasOne('\app\common\models\Goods', 'id', 'goods_id');
    }

    public function goods()
    {
        //todo ??需调查为什么 $this->goods 调取不到这个函数
        return $this->belongsTo(self::getNearestModel('Goods'));
    }

    public function scopeOrderGoods(Builder $query)
    {
        return $query->select(['id', 'order_id', 'goods_id', 'goods_price', 'total', 'goods_option_title', 'price', 'thumb', 'title', 'goods_sn'])->with('goods',function ($query){
            return $query->goods();
        });
    }

    public function getButtonsAttribute()
    {
        if($this->comment_status == 0){
            $result[] = [
                'name' => '评价',
                'api' => '',
                'value' => ''
            ];
        }else if ($this->comment_status == 1) {
            $result[] = [
                'name' => '追评',
                'api' => '',
                'value' => '1'
            ];
            $result[] = [
                'name' => '查看评价',
                'api' => '',
                'value' => '2'
            ];
        } else {
            $result[] = [
                'name' => '查看评价',
                'api' => '',
                'value' => '2'
            ];
        }
        return $result;
    }

    public function hasOneGoodsDispatch()
    {
        return $this->hasOne(GoodsDispatch::class, 'goods_id', 'goods_id');
    }

    public function belongsToGood()
    {
        return $this->belongsTo(self::getNearestModel('Goods'), 'goods_id', 'id');
    }

    public function goodsOption()
    {
        return $this->hasOne('\app\common\models\GoodsOption', 'id', 'goods_option_id');

    }

    public function hasOneComment()
    {
        return $this->hasOne(\app\frontend\modules\goods\models\Comment::class, 'id', 'comment_id');
    }

    public function orderGoodsChangePriceLogs()
    {
        return $this->hasMany(OrderGoodsChangePriceLog::class, 'order_id', 'id');

    }

    public function isOption()
    {
        return !empty($this->goods_option_id);
    }
}