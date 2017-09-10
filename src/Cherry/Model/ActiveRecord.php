<?php

namespace Cherry\Model;

use Cherry\Application;

abstract class ActiveRecord implements \JsonSerializable
{
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    abstract public static function getTable();

    /**
     * Id can be array like ['id' => 3] or just a simple int\string
     *
     * @param $id
     * @return object|null
     */
    public static function find($id)
    {
        $id = is_array($id) ? $id : ['id' => $id];

        $parameters = implode(' AND ', array_map(function ($id) {
            return $id.' = ?';
        }, array_keys($id)));

        $sql = sprintf("SELECT * FROM `%s` WHERE %s", static::getTable(), $parameters);
        $result = Application::$db->fetchAssoc($sql, array_values($id));

        return $result ? static::map($result) : null;
    }

    public static function findOneBy(string $field, string $value)
    {
        $sql = sprintf("SELECT * FROM `%s` WHERE %s = ?", static::getTable(), $field);

        return static::map(Application::$db->fetchAssoc($sql, [$value]));
    }

    public static function findAll()
    {
        $order = defined('static::ORDER_BY') ? ' ORDER BY '.static::ORDER_BY : '';

        $sql = sprintf('SELECT * FROM `%s`%s', static::getTable(), $order);

        return array_map(['static', 'map'], Application::$db->fetchAll($sql));
    }

    public static function map(array $values)
    {
        $object = new static();

        static::beforeMap($values);

        $setter = function ($field) {
            $normalizedField = implode('', array_map('ucfirst', explode('_', $field)));
            return 'set'.$normalizedField;
        };

        foreach ($values as $field => $value) {
            $object->{$setter($field)}($value);
        }

        return $object;
    }

    public function save()
    {
        $this->beforeSave();
        $object = static::find($this->id);

        if (!$object) {
            $this->saveNew($this);
        } else {
            $this->update($this);
        }
    }

    protected function saveNew(ActiveRecord $object)
    {
        Application::$db->insert($object::getTable(), $object->jsonSerialize());
    }

    /**
     * @param ActiveRecord $object
     * @return integer The number of affected rows.
     */
    protected function update(ActiveRecord $object)
    {
//        var_dump($object->jsonSerialize()); exit;
        return Application::$db->update($object::getTable(), $object->jsonSerialize(), ['id' => $object->getId()]);
    }

    protected function beforeSave()
    {
    }

    public static function beforeMap(array &$values) {}

    public static function __callStatic($name, $arguments)
    {
        if (0 === strpos($name, 'findOneBy')) {
            return static::findOneBy(substr($name, 9), $arguments[0]);
        }

        throw new \Exception(sprintf('Method "%s" not found', $name));
    }
}
