<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/23
 * Time: 下午5:40
 */

namespace app\frontend\models;


use app\common\models\Coupon;
use app\common\models\MemberCoupon;
use app\frontend\modules\member\models\MemberAddress;
use app\frontend\models\OrderGoods;

class Member extends \app\common\models\Member
{
    /**
     * 会员－会员优惠券1:多关系
     * @param null $backType
     * @return mixed
     */
    public function hasManyMemberCoupon($backType = null)
    {
        return $this->hasMany(MemberCoupon::class, 'uid', 'uid')
            ->where('used', 0)->with('belongsToCoupon', function ($query) use ($backType) {
                if (isset($backType)) {
                    $query->where('coupon_method', $backType);
                }
            });
    }

    public function defaultAddress()
    {
        return $this->hasOne(MemberAddress::class, 'uid', 'uid')->where('isdefault', 1);
    }

    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class,'uid','uid');
    }
    public function yzMember()
    {
        return $this->hasOne(self::getNearestModel('MemberShopInfo'), 'member_id', 'uid');
    }
}