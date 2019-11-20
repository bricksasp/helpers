<?php
namespace bricksasp\helpers\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * 默认用户字段
 */
class UidBehavior extends AttributeBehavior
{
    public $createdAtAttribute = 'user_id';

    public $updatedAtAttribute = 'user_id';

    public $value;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdAtAttribute, $this->updatedAtAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedAtAttribute,
            ];
        }
    }

    protected function getValue($event)
    {
        if ($this->value === null) {
            $user = Yii::$app->getUser();
            return $user->getId();
        }
    }
}
