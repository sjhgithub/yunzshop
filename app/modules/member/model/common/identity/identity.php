<?php
namespace mobile\order\demo\model;
class Identity
{
    public function getMoneyOff(){

    }
    public function getLevel($member_model){
        return m("member")->getLevel($member_model->getOpenid());
    }
    public function getDiscount($order_model){
        //todo
        $level = $this->getLevel($this->getOpenid());
        $discounts = $order_model->getDiscounts();
        if (is_array($discounts)) {
            if (!empty($level["id"])) {
                if (floatval($discounts["level" . $level["id"]]) > 0 && floatval($discounts["level" . $level["id"]]) < 10) {
                    $level["discount"] = floatval($discounts["level" . $level["id"]]);
                } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                    $level["discount"] = floatval($level["discount"]);
                } else {
                    $level["discount"] = 0;
                }
            } else {
                if (floatval($discounts["default"]) > 0 && floatval($discounts["default"]) < 10) {
                    $level["discount"] = floatval($discounts["default"]);
                } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                    $level["discount"] = floatval($level["discount"]);
                } else {
                    $level["discount"] = 0;
                }
            }
        }
        return $level;
    }
}