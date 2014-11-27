<?php
namespace samson\activerecord;

/**
 * Universal class representing database record 
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 2.0
 */
class Record implements \samson\core\iModuleViewable, \ArrayAccess
{
    /** Collection of class fields that would not be passed to module view */
    public static $restricted = array('attached', 'onetoone', 'onetomany', 'class_name');

    /** Record primary field value - identifier */
    public $id;

    /** @see \samson\core\iModuleViewable::toView() */
    public function toView($prefix = null, array $restricted = array())
    {
        // Create resulting view data array, add identifier field
        $values = array($prefix.'id' => $this->id);

        // Учтем поля которые не нужно превращать в массив
        $restricted = array_merge(self::$restricted, $restricted);

        // Пробежимся по переменным класса
        foreach (get_object_vars($this) as $var => $value) {
            // Если это не системное поле записи - запишем его значение
            if (!in_array($var, $restricted)) {
                $values[ $prefix.$var ] = is_string($value) ? trim($value) : $value;
            }
        }

        // Вернем массив атрибутов представляющий запись БД
        return $values;
    }

    /**
     * Create full entity copy from
     * @param mixed $object Variable to return copied object
     * @return Record New copied object
     */
    public function & copy(&$object = null)
    {
        // Get current entity class
        $entity = get_class($this);

        // Create object instance
        $object = new $entity(false);

        // PHP 5.2 compliant get attributes
        $attributes = array();
        eval('$attributes = '.$entity.'::$_attributes;');


        // Iterate all object attributes
        foreach ($attributes as $attribute) {
            // If we have this attribute set
            if (isset($this[$attribute])) {
                // Store it in copied object
                $object[$attribute] = $this[$attribute];
            }
        }

        // Save object in database
        $object->save();

        // Return created copied object
        return $object;
    }

    /** @see ArrayAccess::offsetSet() */
    public function offsetSet($offset, $value)
    {

        $this->$offset = $value;
    }
    /** @see ArrayAccess::offsetGet() */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
    /** @see ArrayAccess::offsetUnset() */
    public function offsetUnset($offset)
    {
        unset( $this->$offset );
    }
    /** @see ArrayAccess::offsetExists() */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }
}
