<?php
namespace bricksasp\helpers\behaviors;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * 默认自定义自增
 */
class IncrementBehavior extends AttributeBehavior
{
    public $attribute = 'id';

    public $value;

    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->attribute],
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            throw new InvalidCallException('未设置主键id.');
        }

        // 自增处理
        // if (empty($this->value)) $this->value = ;

        return parent::getValue($event);
    }
}
