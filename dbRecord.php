<?php
namespace samson\activerecord;

use samson\core\iModuleViewable;

/**
 * Отражение записи БД в PHP
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbRecord implements idbRecord, iModuleViewable, \ArrayAccess
{	
	/**
	 * Коллекция экземпляров данного класса
	 * @var array
	 */
	public static $instances = array();
	
	/**
	 * Коллекция системых полей которые не учавствуют в преобразовании записи в массив
	 * @var array
	 */
	public static $restricted = array( '_data', 'attached', 'onetoone', 'onetomany', 'class_name' );
	
	/**
	 * Уникальный идентификатор объекта
	 * @var string
	 */
	public $id = 0;	
		
	/**
	 * Имя текущего класса
	 * @var string
	 */
	public $class_name;
	
	/**
	 * Коллекция атрибутов объекта 
	 * Она соответствует значениям ячеек записи из БД
	 * @var array
	 */
	//public $_data = array();	
	
	/**
	 * Коллекция связанных объектов один к одному
	 * @var array
	 */
	public $onetoone = array();
	
	/**
	 * Коллекция связанных объектов один ко многим
	 * @var array
	 */
	public $onetomany = array();
	
	/**
	 * Флаг привязанности данного объекта к конкретной записи в БД
	 * @var boolean
	 */
	public $attached = FALSE;	
	
	
	/**
	 * Конструктор
	 * 
	 * Если идентификатор не передан - выполняется создание новой записи в БД
	 * Если идентификатор = FALSE - выполняеся создание объекта без его привязки к БД
	 * Если идентификатор > 0 - выполняется поиск записи в БД и привязка к ней в случае нахождения
	 * 
	 * @param mixed 	$id 		Идентификатор объекта в БД
	 * @param string 	$class_name Имя класса
	 */
	public function __construct( $id = NULL, $class_name = NULL )	
	{				
		// Запишем имя текущего класса
		if(!isset($this->class_name))$this->class_name = get_class($this); //$class_name; 
		
		//if( get_class($this) == 'Order') elapsed('ЩКВУК!!!');

		// Если установлен флаг создания объекта без привязки к записи в БД
		if( $id === FALSE ){ /* Пустое условие для оптимизации */ }
		else
		{
			// Если идентификатор записи в БД НЕ передан
			if( ! isset( $id ) ) $this->create();
			// Мы получили положительный идентификатор и нашли запись в БД с ним - Выполним привязку данного объекта к записи БД
			else if( NULL !== ($db_record = db()->find_by_id( $class_name, $id )))
			{
				// Если по переданному ID запись была успешно получена из БД
				// установим его как основной идентификатор объекта
				$this->id = $id;

				// Пробежимся по переменным класса
				foreach( $db_record as $var => $value ) $this->$var = $value;
					
				// Установим флаг привязки к БД
				$this->attached = TRUE;
			}
			
			// Зафиксируем данный класс в локальном кеше
			self::$instances[ $class_name ][ $this->id ] = $this;
		}	
	}
	
	/**	 
	 * @see idbRecord::create()
	 */
	public function create()
	{
		// Если запись уже привязана к БД - ничего не делаем
		if( !$this->attached )
		{
			// Получим имя класса
			$class_name = $this->class_name;
			
			// Получим переменные для запроса
			extract(db()->__get_table_data( $class_name ));			
				
			// Выполним создание записи в БД 
			// и сразу заполним её значениями атрибутов объекта
			$this->id = db()->create( $class_name, $this );			
			
			// Получим созданную запись из БД
			$db_record = db()->find_by_id( $class_name, $this->id );	
			
			// Запишем все аттрибуты которые БД выставила новой записи
			foreach ( $_attributes as $name => $r_name) $this->$name = $db_record->$name;
			
			// Установим флаг что мы привязались к БД
			$this->attached = TRUE;
		}
	}	
	
	/**	 
	 * @see idbRecord::save()
	 */
	public function save()
	{			
		
		// Если данный объект еще привязан к записи в БД - выполним обновление записи в БД		
		if( $this->attached ) db()->update( $this->class_name, $this );	
		// Иначе создадим новую запись с привязкой к данному объекту
		else $this->create();
		
		// Обновим указатель на текущую запись в локальном кеше АктивРекорд
		self::$instances[ $this->class_name ][ $this->id ] = & $this;
	}
	
	/**	 
	 * @see idbRecord::delete()
	 */
	public function delete()
	{
		// Если запись привязана к БД то удалим её оттуда
		if( $this->attached ) db()->delete( $this->class_name, $this );		
	}	

	/**
	 * Создаваемый массив формируется из всех внутренних полей объекта-записи БД
	 * с учетом переданного префикса:
	 * 	<code>array( $prefix.FIELD => FIELD_VALUE</code>
	 *
	 * Если объект-запись БД имеет связи с другими объектами-записями БД
	 * то они также будут преобразованы и добавлены в создаваемый массив но с использованием
	 * специальных ключей:
	 * 	<code>array( $prefix.SUB_CLASS.$prefix.SUB_CLASS_FIELD => SUB_CLASS_FIELD_VALUE</code>
	 * @see iModuleViewable::toView()
	 */
	public function toView( $prefix = NULL, array $restricted = array() )
	{
		// Результирующая коллекция значенией атрибутов записи
		// Добавим в неё универсальное поле - идентификатор
		$values = array( $prefix.'id' => $this->id );	

		// Учтем поля которые не нужно превращать в массив
		$restricted = array_merge( self::$restricted, $restricted );		
		
		// Пробежимся по переменным класса
		foreach( get_object_vars( $this ) as $var => $value ) 
		{			
			// Если это не системное поле записи - запишем его значение
			if( ! in_array( $var, $restricted ) ) $values[ $prefix.$var ] = $value;	
		}
		
		// Переберем связанные 1-1 классы
		foreach ( $this->onetoone as $name => $obj ) $values = array_merge( $values, $obj->toView( $prefix.classname( get_class($obj)).'_' ));		
		
		// Переберем значение атрибутов записи - запишем ключом массиву реальное имя колонки в БД
		//foreach ( $this->_data as $attribute => $value )  $values[ $prefix.$attribute ] = $value;			
		
		// Вернем массив атрибутов представляющий запись БД
		return $values;
	}
	
	/**
	 * Обработчик клонирования записи
	 * Этот метод выполняется при системном вызове функции clone 
	 * и выполняет создание записи в БД и привязку клонированного объекта к ней
	 */
	public function __clone()
	{		
		// Выполним создание записи в БД
		$this->id = db()->create( $this->class_name, $this );
		
		// Установим флаг что мы привязались к БД
		$this->attached = TRUE;
	
		// Сохраним запись в БД
		$this->save();
	}
	
	/** ArrayAccess methods */
	public function offsetSet( $offset, $value ){ $this->$offset = $value; }
	public function offsetGet($offset)			{ return $this->$offset;  }
	public function offsetUnset($offset){ unset( $this->$offset ); }
	public function offsetExists($offset){ return property_exists( $this, $offset ); }	
	
	//public function __sleep(){	return array( 'id', '_data', 'attached' );}
	
	/*
	
	public function __get( $key )
	{	
		// Попытаемся найти запрашиваемый аттрибут записи
		if( isset( $this->_data[ $key ] ) ) return $this->_data[ $key ];
		// Если требуется вернуть идентификатор записи
		else if( $key == 'id' ) return $this->id;
	}
	
	
	public function __set( $key, $value )
	{
		//elapsed('Устанавливаем '.get_class($this).'-'.$key.'-'.$value);	
		
		// Проверим есть ли установливаемый атрибут у данной записи, т.к. ключ регистро-незавимимый - уменьшим его
		if( isset( $this->_data[ $key ] )) $this->_data[ $key ] = $value;
		
		// Вернем пустышку
		return null;
	}*/
}