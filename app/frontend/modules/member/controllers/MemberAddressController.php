<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 下午8:40
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\components\BaseController;
use app\common\models\member\Address;
use app\common\models\Street;
use app\frontend\modules\member\models\MemberAddress;

class MemberAddressController extends ApiController
{
    protected $publicAction = ['address'];

    /*
     * 会员收货地址列表
     *
     * */
    public function index()
    {
        $memberId = \YunShop::app()->getMemberId();
        $addressList = MemberAddress::getAddressList($memberId);
        //获取省市ID
        if ($addressList) {
            $address = Address::getAllAddress();
            $addressList = $this->addressServiceForIndex($addressList, $address);
        }
        $msg = "获取列表成功";
        return $this->successJson($msg, $addressList);
    }

    /*
     * 地址JSON数据接口
     *
     * */
    public function address()
    {
        $address = Address::getAllAddress();
        if (!$address) {
            throw new \app\common\exceptions\ShopException('数据收取失败，请联系管理员！');
        }
        $msg = '数据获取成功';
        return $this->successJson($msg, $this->addressService($address));
    }

    /*
     * 修改默认收货地址
     *
     * */
    public function setDefault()
    {
        $memberId = \YunShop::app()->getMemberId();
        $addressModel = MemberAddress::getAddressById(\YunShop::request()->address_id);
        if ($addressModel) {
            if ($addressModel->isdefault) {
                throw new \app\common\exceptions\ShopException('默认地址不支持取消，请编辑或修改其他默认地址');
            }
            $addressModel->isdefault = 1;
            MemberAddress::cancelDefaultAddress($memberId);
            if ($addressModel->save()) {
                return $this->successJson('修改默认地址成功');
            } else {
                throw new \app\common\exceptions\ShopException('修改失败，请刷新重试！');
            }
        }
        throw new \app\common\exceptions\ShopException('未找到数据或已删除，请重试！');
    }

    /*
     * 添加会员收获地址
     *
     * */
    public function store()
    {
        $addressModel = new MemberAddress();
        $requestAddress = \YunShop::request();
        if (!\YunShop::request()->username) {
            throw new \app\common\exceptions\ShopException('收件人不能为空');
        }

        $mobile = \YunShop::request()->mobile;
        if (!$mobile) {
            throw new \app\common\exceptions\ShopException('手机号不能为空');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/",$mobile)) {
            throw new \app\common\exceptions\ShopException('手机号格式不正确');
        }

        if (!\YunShop::request()->province) {
            throw new \app\common\exceptions\ShopException('请选择省份');
        }

        if (!\YunShop::request()->city) {
            throw new \app\common\exceptions\ShopException('请选择城市');
        }

        if (!\YunShop::request()->district) {
            throw new \app\common\exceptions\ShopException('请选择区域');
        }

        if (!\YunShop::request()->address) {
            throw new \app\common\exceptions\ShopException('请输入详细地址');
        }
        if ($requestAddress) {
            $data = array(
                'username'  => \YunShop::request()->username,
                'mobile'    => \YunShop::request()->mobile,
                'zipcode'   => '',
                'isdefault' => \YunShop::request()->isdefault,
                'province'  => \YunShop::request()->province,
                'city'      => \YunShop::request()->city,
                'district'  => \YunShop::request()->district,
                'address'   => \YunShop::request()->address,
            );
            $addressModel->fill($data);
            $memberId = \YunShop::app()->getMemberId();
            //验证默认收货地址状态并修改
            $addressList = MemberAddress::getAddressList($memberId);
            if (empty($addressList)) {
                $addressModel->isdefault = '1';
            } elseif ($addressModel->isdefault) {
                //修改默认收货地址
                MemberAddress::cancelDefaultAddress($memberId);
            }

            $addressModel->uid = $memberId;
            $addressModel->uniacid = \YunShop::app()->uniacid;
            $validator = $addressModel->validator($addressModel->getAttributes());
            if ($validator->fails()) {
                throw new \app\common\exceptions\ShopException($validator->messages());
            }
            if ($addressModel->save()) {
                 return $this->successJson('新增地址成功', $addressModel->toArray());
            } else {
                throw new \app\common\exceptions\ShopException("数据写入出错，请重试！");
            }
        }
        throw new \app\common\exceptions\ShopException("未获取到数据，请重试！");
    }

    /*
     * 修改会员收获地址
     *
     * */
    public function update()
    {
        $addressModel = MemberAddress::getAddressById(\YunShop::request()->address_id);
        if (!$addressModel) {
            throw new \app\common\exceptions\ShopException("未找到数据或已删除");
        }

        if (!\YunShop::request()->username) {
            throw new \app\common\exceptions\ShopException('收件人不能为空');
        }

        $mobile = \YunShop::request()->mobile;
        if (!$mobile) {
            throw new \app\common\exceptions\ShopException('手机号不能为空');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/",$mobile)) {
            throw new \app\common\exceptions\ShopException('手机号格式不正确');
        }

        if (!\YunShop::request()->province) {
            throw new \app\common\exceptions\ShopException('请选择省份');
        }

        if (!\YunShop::request()->city) {
            throw new \app\common\exceptions\ShopException('请选择城市');
        }

        if (!\YunShop::request()->district) {
            throw new \app\common\exceptions\ShopException('请选择区域');
        }

        if (!\YunShop::request()->address) {
            throw new \app\common\exceptions\ShopException('请输入详细地址');
        }

        if (\YunShop::request()->address_id) {
            $requestAddress = array(
                //'uid' => $requestAddress->uid,
                //'uniacid' => \YunShop::app()->uniacid,
                'username'      => \YunShop::request()->username,
                'mobile'        => \YunShop::request()->mobile,
                'zipcode'       => '',
                'isdefault'     => \YunShop::request()->isdefault ?: 0,
                'province'      => \YunShop::request()->province,
                'city'          => \YunShop::request()->city,
                'district'      => \YunShop::request()->district,
                'address'       => \YunShop::request()->address,
            );
            $addressModel->fill($requestAddress);

            $validator = $addressModel->validator($addressModel->getAttributes());
            if ($validator->fails()) {
                throw new \app\common\exceptions\ShopException($validator->message());
            }
            if ($addressModel->isdefault) {
                //todo member_id 未附值
                MemberAddress::cancelDefaultAddress($addressModel->member_id);
            }
            if ($addressModel->save()) {
                return $this->successJson('修改收货地址成功');
            } else {
                throw new \app\common\exceptions\ShopException("写入数据出错，请重试！");
            }
        }


    }

    /*
     * 移除会员收货地址
     *
     * */
    public function destroy()
    {
        $addressId = \YunShop::request()->address_id;
        $addressModel = MemberAddress::getAddressById($addressId);
        if (!$addressModel) {
            throw new \app\common\exceptions\ShopException("未找到数据或已删除");
        }
        //todo 需要考虑删除默认地址选择其他地址改为默认
        $result = MemberAddress::destroyAddress($addressId);
        if ($result) {
            return $this->successJson();
        } else {
            throw new \app\common\exceptions\ShopException("数据写入出错，删除失败！");
        }
    }

    /*
     * 服务列表数据 index() 增加省市区ID值
     * */
    private function addressServiceForIndex($addressList = [], $address)
    {
        $i = 0;
        foreach ($addressList as $list) {
            foreach ($address as $key) {
                if ($list['province'] == $key['areaname']) {
                    //dd('od');
                    $addressList[$i]['province_id'] = $key['id'];
                }
                if ($list['city'] == $key['areaname']) {
                    $addressList[$i]['city_id'] = $key['id'];
                }
                if ($list['district'] == $key['areaname']) {
                    $addressList[$i]['district_id'] = $key['id'];
                }
            }
            $i++;
        }
        return $addressList;
    }

    /*
     * 服务地址接口数据重构
     * */
    private function addressService($address)
    {
        $province = [];
        $city = [];
        $district = [];
        foreach ($address as $key)
        {
            if ($key['parentid'] == 0 && $key['level'] == 1) {
                $province[] = $key;
            } elseif ($key['parentid'] != 0 && $key['level'] == 2 ) {
                $city[] = $key;
            } else {
                $district[] = $key;
            }
        }
        return array(
            'province' => $province,
            'city' => $city,
            'district' => $district,
        );
    }

    public function getStreet()
    {
        //member.member-address.get-street
        $districtId = \YunShop::request()->get('district_id');

        $street = Street::getStreetByParentId($districtId);

        if($street){
            return $this->successJson('获取街道数据成功!', $street);
        }
        return $this->successJson('获取数据失败!', $street);

    }


}
