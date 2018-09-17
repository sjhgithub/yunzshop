<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/6/20
 * Time: 上午10:07
 */

namespace app\common\providers;

use app\common\helpers\SettingCache;
use app\common\modules\status\StatusContainer;
use app\frontend\modules\coin\CoinManager;
use app\frontend\modules\deduction\DeductionManager;
use app\frontend\modules\goods\services\GoodsManager;
use app\frontend\modules\payment\managers\PaymentManager;
use Illuminate\Support\ServiceProvider;

class ShopProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('SettingCache',function(){
            return new SettingCache();
        });
        $this->app->singleton('CoinManager',function(){
            return new CoinManager();
        });
        $this->app->singleton('DeductionManager',function(){
            return new DeductionManager();
        });
        $this->app->singleton('PaymentManager',function(){
            return new PaymentManager();
        });
        $this->app->singleton('GoodsManager',function(){
            return new GoodsManager();
        });

        $this->app->singleton('StatusContainer', function (){
            return new StatusContainer();
        });
    }
}