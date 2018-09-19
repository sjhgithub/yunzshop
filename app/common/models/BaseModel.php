<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 24/02/2017
 * Time: 16:36
 */

namespace app\common\models;


use app\backend\modules\goods\observers\SettingObserver;
use app\common\exceptions\ShopException;
use app\common\traits\ValidatorTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package app\common\models
 * @method static uniacid()
 * @method static insert()
 * @method static get()
 * @method static set()
 * @method static exclude()
 */
class BaseModel extends Model
{
    use ValidatorTrait;
    protected $search_fields;
    static protected $needLog = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }

    /**
     * 模糊查找
     * @param $query
     * @param $params
     * @return mixed
     */
    public function scopeSearchLike(Builder $query, $params)
    {
        $search_fields = $this->search_fields;
        $query->where(function (Builder $query) use ($params, $search_fields) {
            foreach ($search_fields as $search_field) {
                $query->orWhere($search_field, 'like', '%' . $params . '%');
            }
        });
        return $query;
    }

    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 获取当前时间
     *
     * @return int
     */
    public function freshTimestamp()
    {
        return time();
    }

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param \DateTime|int $value
     * @return \DateTime|int
     */
    public function fromDateTime($value)
    {
        return $value;
    }

    /**
     * select的时候避免转换时间为Carbon
     *
     * @param mixed $value
     * @return mixed
     */
//  protected function asDateTime($value) {
//	  return $value;
//  }


    /**
     * 从数据库获取的为获取时间戳格式
     */
    //public function getDateFormat() {
    //     return 'U';
    // }

    /**
     * 后台全局筛选统一账号scope
     * @param Builder $query
     * @return $this|Builder
     */
    public function scopeUniacid(Builder $query)
    {
        if (\YunShop::app()->uniacid === null) {
            return $query;
        }
        return $query->where('uniacid', \YunShop::app()->uniacid);
    }

    /**
     * 递归获取$class 相对路径的 $findClass
     * @param $class
     * @param $findClass
     * @return null|string
     */
    public static function recursiveFindClass($class, $findClass)
    {
        $result = substr($class, 0, strrpos($class, "\\")) . '\\' . $findClass;

        if (class_exists($result)) {
            return $result;
        }

        if (class_exists(get_parent_class($class))) {
            return self::recursiveFindClass(get_parent_class($class), $findClass);
        }
        return null;

    }

    /**
     * 获取与子类 继承关系最近的 $model类
     * @param $model
     * @return null|string
     * @throws ShopException
     */
    public function getNearestModel($model)
    {
        $result = self::recursiveFindClass(static::class, $model);

        if (isset($result)) {
            return $result;
        }
        throw new ShopException('获取关联模型失败');
    }

    /**
     * 用来区分订单属于哪个.当插件需要查询自己的订单时,复写此方法
     * @param $query
     * @param int $pluginId
     * @return mixed
     */
    public function scopePluginId(Builder $query, $pluginId = 0)
    {
        return $query->where('plugin_id', $pluginId);
    }

    /**
     * 用来区分订单属于哪个.当插件需要查询自己的订单时,复写此方法
     * @param $query
     * @param null $uid
     * @return mixed
     */
    public function scopeUid(Builder $query, $uid = null)
    {
        if (!isset($uid)) {
            $uid = \YunShop::app()->getMemberId();
        }
        return $query->where('uid', $uid);
    }

    public function scopeMine(Builder $query)
    {
        return $query->where('uid', \YunShop::app()->getMemberId());
    }

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        if (static::$needLog) {
            static::observe(new SettingObserver);
        }

    }

    private function getCommonModelClass($class)
    {
        if (get_parent_class($class) == self::class) {

            return $class;
        }
        return $this->getCommonModelClass(get_parent_class($class));
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return $this->getCommonModelClass(parent::getMorphClass());

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function columns()
    {
        $cacheKey = 'model_' . $this->getTable() . '_columns';

        if (!\Cache::has($cacheKey)) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($this->getTable());
            cache([$cacheKey => $columns], Carbon::now()->addSeconds(3600));
        }

        return cache($cacheKey);

    }

    /**
     * @param $column
     * @return bool
     * @throws \Exception
     */
    public function hasColumn($column)
    {
        return in_array($column, $this->columns());
    }

    /**
     * @param BaseModel $query
     * @param array $excludeFields
     * @return mixed
     * @throws \Exception
     */
    public function scopeExclude(self $query, $excludeFields)
    {
        if (!is_array($excludeFields)) {
            $excludeFields = explode(',', $excludeFields);
        }
        $fields = array_diff($this->columns(), $excludeFields) ?: [];
        return $query->select($fields);
    }
    public function getRelationValue($key)
    {

        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return $this->getRelationshipFromExpansions($key, static::class);
    }

    private function getRelationshipFromExpansions($key, $class)
    {
        $this->loadExpansions($class);

        if (isset($this->expansions)) {
            foreach ($this->expansions as $expansion) {
                /**
                 * @var GoodsExpansion $expansion
                 */

                if (method_exists($expansion, $key)) {
                    return $expansion->getRelationshipFromExpansion($key, $this);
                }
            }
        }
        // 递归到此类为止避免死循环
        if (get_parent_class($class) !== self::class) {
            return $this->getRelationshipFromExpansions($key, get_parent_class($class));
        }
    }

    private function loadExpansions($className)
    {

        if (app()->bound('ModelExpansionManager') && app('ModelExpansionManager')->has($className)) {
            $this->expansions = collect();

            app('ModelExpansionManager')->get($className)->each(function ($expansion) {

                $this->expansions->push($expansion);
            });
        }
    }
    public function __call($method, $parameters){
        if ($this->hasExpansionsMethod($method)) {
            return $this->expansionsMethod($method);
        }
        return parent::__call($method, $parameters);
    }
    protected static $expansions;

    private function getExpansions(){
        return static::$expansions[static::class];
    }

    private function expansionsMethod($method){
        foreach ( $this->getExpansions() as $expansion) {
            if (method_exists($expansion, $method)) {
                return $expansion->getRelationshipFromExpansion($method, $this);
            }
        }
        return false;
    }
    private function hasExpansionsMethod($method){
        foreach ( $this->getExpansions() as $expansion) {
            if (method_exists($expansion, $method)) {
                return true;
            }
        }
        return false;
    }
}