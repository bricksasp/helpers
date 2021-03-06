<?php
namespace bricksasp\helpers\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * 默认版本号字段
 */
class VersionBehavior extends AttributeBehavior
{
    public $createdAtAttribute = 'version';

    public $updatedAtAttribute = 'version';

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
            $field = $this->updatedAtAttribute;
            return (int)$this->owner->$field + 1;
        }

        return parent::getValue($event);
    }

    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the version is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}
