<?php
namespace samson\activerecord;

use samson\core\iModuleViewable;

/**
 * Отражение записи БД в PHP
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbRecord extends Record implements idbRecord
{	
	/**
	 * Коллекция экземпляров данного класса
	 * @var array
	 */
	public static $instances = array();
	
	
	
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
	 * Коллекция связанных объектов один к одному
	 * @var array
	 */
	// TODO: Избавиться от этой коллекции в пользу названия связанной таблицы\алиаса
	public $onetoone = array();
	
	/**
	 * Коллекция связанных объектов один ко многим
	 * @var array
	 */
	// TODO: Избавиться от этой коллекции в пользу названия связанной таблицы\алиаса
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
		if(!isset($this->class_name)) $this->class_name = get_class($this); //$class_name; 
		
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
				
		//elapsed('saving to cache:'.get_class($this).'-'.$this->id);		
		
		// Обновим указатель на текущую запись в локальном кеше АктивРекорд
		self::$instances[ ns_classname(get_class($this),'') ][ $this->id ] = & $this;
	}
	
	/**	@see idbRecord::delete() */
	public function delete()
	{
		// Если запись привязана к БД то удалим её оттуда
		if( $this->attached ) db()->delete( $this->class_name, $this );		
	}	
	
	/** Special method called when object has been filled with data */
	public function filled()
	{
		
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
	
		

}