<?php
/* CaryYe 06/07/2017 9:04 AM */
namespace app\models;
use yii\db\ActiveRecord;

class CActiveRecord extends ActiveRecord
{
    const SCENARIO_CREATE = "create";

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // If currently scenario is self::SCENARIO_CREATE, allow all columns.
        $schema = self::getTableSchema();
        $defaults = $columns = array_keys($schema->columns);

        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = $columns;

        unset($defaults[array_search("id", $defaults)]);
        $scenarios[self::SCENARIO_DEFAULT] = $defaults;

        return $scenarios;
    }

    /**
     * @param string $attribute
     * @param array $params the
     * @return boolean
     */
    public function wasExists($attribute, $params = [])
    {
        if (isset($this->$attribute)
            && trim($this->$attribute) !== "")
        {
            $m = isset($params['m']) ? "\app\models\\".$params['m'] : "self";
            $c = isset($params['c']) ? $params['c'] : $attribute;
            $r = call_user_func($m."::findOne", [$c => $this->$attribute]);

            if (is_null($r)) {
                $this->addError(
                    $attribute,
                    "Column " .$attribute." "
                        . "reference " .end(explode("\\", $m)).".$c "
                        ."was not existed!"
                );
                return false;
            }

            return true;
        }
        $this->addError($attribute, "$attribute can not empty!");
        return false;
    }

    /**
     * @param array $properties
     * @return void
     */
    public function setProperties($properties)
    {
        $sets = [];
        $attributes = $this->getAttributes();

        foreach($properties as $k => $v) {
            if (array_key_exists($k, $attributes)) {
                $sets[$k] = $v;
            } else {
                method_exists($this, "set".ucfirst($k)) && $this->$k = $v;
            }
        }

        $this->setAttributes($sets);
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        return $this->assignVars()
            ? parent::afterValidate()
            : false;
    }

    /**
     * After validate, if still need to assigns any columns, use this.
     * @return boolean
     */
    public function assignVars()
    {
        if ($this->hasErrors()) return false;
        if ($this->scenario === self::SCENARIO_DEFAULT) return true;

        $sets = [];
        $data = $this->assignValuesAfterValidate();

        foreach($data as $k => $v)
            ($this->hasAttribute($v[0])
                && trim($this->getAttribute($v[0])) === '')
            && $sets[$v[0]] = $v["value"];

        $this->setAttributes($sets);
        return true;
    }

    /**
     * If you want assignment any attributes afterValidate. use this.
     * @return array
     */
    public function assignValuesAfterValidate()
    {
        return [];
    }

    /**
     * @param array $defaults
     * @return array
     */
    public static function onCreate(array $defaults)
    {
        foreach ($defaults as $k => $v)
            (!isset($v["on"])
             || is_null($v["on"]))
                 && $defaults[$k]["on"] = self::SCENARIO_CREATE;

        return $defaults;
    }
}