<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/3/12
 * Time: 下午5:42
 */

namespace app\common\middleware;


use app\common\helpers\Url;
use app\platform\modules\application\models\AppUser;

class ShopBootstrap
{
    private $authRole = ['operator'];

    public function handle($request, \Closure $next, $guard = null)
    {
        if (\Auth::guard('admin')->user()->uid !== 1) {
            $cfg = \config::get('app.global');
            $account = AppUser::getAccount(\Auth::guard('admin')->user()->uid);

            if (!is_null($account) && in_array($account->role, $this->authRole)) {

                $cfg['uniacid'] = $account->uniacid;
                setcookie('uniacid', $account->uniacid);
                \config::set('app.global', $cfg);

                return redirect()->guest(Url::absoluteWeb('index.index'));
            }
        }

        return $next($request);
    }
}