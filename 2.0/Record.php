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
	public static $restricted = array( 'attached', 'onetoone', 'onetomany', 'class_name' );
	
	/** Record primary field value - identifier */
	public $id;
	
	/** @see \samson\core\iModuleViewable::toView() */
	public function toView( $prefix = NULL, array $restricted = array() )
	{
		// Create resulting view data array, add identifier field
		$values = array( $prefix.'id' => $this->id );
	
		// Учтем поля которые не нужно превращать в массив
		$restricted = array_merge( self::$restricted, $restricted );
	
		// Пробежимся по переменным класса
		foreach( get_object_vars( $this ) as $var => $value )
		{
			// Если это не системное поле записи - запишем его значение
			if( ! in_array( $var, $restricted ) ) $values[ $prefix.$var ] = $value;
		}	
	
		// Вернем массив атрибутов представляющий запись БД
		return $values;
	}	
	
	/** @see ArrayAccess::offsetSet() */
	public function offsetSet( $offset, $value ){ $this->$offset = $value; }
	/** @see ArrayAccess::offsetGet() */
	public function offsetGet($offset){ return $this->$offset;  }
	/** @see ArrayAccess::offsetUnset() */
	public function offsetUnset($offset){ unset( $this->$offset ); }
	/** @see ArrayAccess::offsetExists() */
	public function offsetExists($offset){ return property_exists( $this, $offset ); }
}