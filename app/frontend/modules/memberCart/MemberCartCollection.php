<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/6/1
 * Time: 下午5:31
 */

namespace app\frontend\modules\memberCart;

use app\framework\Database\Eloquent\Collection;
use app\frontend\models\MemberCart;

class MemberCartCollection extends Collection
{
    /**
     * 验证商品有效性
     */
    public function validate()
    {

        $this->unique('goods_id')->each(function (MemberCart $memberCart) {

            if (isset($memberCart->goods->hasOnePrivilege)) {
                // 合并规格商品数量,并校验
                $total = $this->where('goods_id', $memberCart->goods_id)->sum('total');

                $memberCart->goods->hasOnePrivilege->validate($total);
            }
        });
        $this->each(function (Membercart $memberCart) {
            $memberCart->validate();
        });
    }

    /**
     * 载入管理模型
     * @return $this
     */
    public function loadRelations()
    {
        $with = ['goods' => function ($query) {
            $query->exclude('content,description');
        }, 'goods.hasOnePrivilege', 'goods.hasOneOptions', 'goods.hasManyGoodsDiscount', 'goods.hasOneGoodsDispatch', 'goods.hasOneSale', 'goodsOption'];
        $with = array_merge($with, config('shop-foundation.member-cart.with'));
        $this->expansionLoad($with);
        $this->each(function (MemberCart $memberCart) {
            if (isset($memberCart->goodsOption)) {
                $memberCart->goodsOption->setRelation('goods', $memberCart->goods);
            }
        });
        return $this;
    }

    /**
     * 按插件分类
     */
    public function sortByPlugin()
    {

    }
}