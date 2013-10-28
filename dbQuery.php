<?php 
namespace samson\activerecord;

//TODO: Написать метод ALL()
//TODO: Поддержка нескольких подключений
/**
 * Запрос для БД
 * Класс собирает в себя все необходимые параметры для 
 * формирования "правильного" запроса на самом низком уровне
 * работы с БД
 * @see idb
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com> 
 *
 */
class dbQuery extends Query //implements idbQuery 
{	
	/**
	 * Указатель на текущую группу условий с которой работает запрос
	 *
	 * @var dbConditionGroup
	 */
	public $cConditionGroup;
	
	/**
	 * Указатель на соединение с БД
	 * @var resource
	 */
	public $link;
	
	/**
	 * Указатель на группу условий для текущего объекта с которой работает запрос
	 *
	 * @var dbConditionGroup
	 */
	public $own_condition;
	
	/** Limiting filter for base table */
	public $own_limit;
	
	/** Grouping filter for base table */	
	public $own_group;	

	/** Sorting filter for base table */
	public $own_order;
	
	/** Virtual field for base table */
	public $own_virtual_fields = array();
	
	/** Virtual fields */
	public $virtual_fields = array();
	
	/**
	 * Коллекция условных групп для запроса 
	 * @var dbConditionGroup
	 */
	public $condition;	
	
	/**
	 * Параметры ограничения результатов запроса к БД
	 * @var array
	 */
	public $limit  = array();
	
	/**
	 * Параметры сортировки результатов запроса к БД
	 * @var array
	 */
	public $order  = array();
	
	/**
	 * Параметры группировки результатов запроса к БД
	 * @var array
	 */
	public $group  = array();
	
	/**
	 * Коллекция параметров для запроса к связанным объектам
	 * @var array
	 */
	public $join = array();	
	

	
	/** Query handlers stack */
	protected $stack = array();
	
	/** Query parameters stack */
	protected $params = array();
	
// 	/**
// 	 * Universal handler to pass to CMSMaterial::get()	 *
// 	 * @param samson\activerecord\dbQuery $db_query Original db query object for modifiyng
// 	 *
// 	 */
// 	protected function __handler()
// 	{
// 		// Iterate handlers and run them
// 		foreach ( $this->stack as $i => $handler )
// 		{
// 			// Create handler params array with first parameter pointing to this query object			
// 			$params = array( &$this );		

// 			// Combine params with existing ones in one array
// 			$params = array_merge( $params, $this->params[ $i ] ); 			
			
// 			// Append this query object as first handler parameter
// 			//array_unshift( $this->params[ $i ] , & $this );			
			
// 			//trace($this->params[ $i ]);
// 			call_user_func_array( $handler, $params );
// 		}
// 	}
	
// 	/**
// 	 * Add query handler
// 	 * @param callable $callable External handler
// 	 * @return samson\activerecord\dbQuery
// 	 */
// 	public function handler( $callable )
// 	{
// 		// If normal handler is passed
// 		if( is_callable( $callable ) )
// 		{
// 			// Add handler
// 			$this->stack[] = $callable;
	
// 			// Get passed arguments
// 			$args = func_get_args();
	
// 			// Remove first argument
// 			array_shift( $args );
	
// 			// Add handler parameters stack
// 			$this->params[] = & $args;
// 		}
// 		else e('Cannot set CMS Query handler - function(##) does not exists', E_SAMSON_CMS_ERROR, $callable );
		 
// 		return $this;
// 	}

	// 	/** @see idbQuery::fields() */
	// 	public function fields( $field_name, & $return_value = null )
	// 	{
	// 		// Call handlers stack
	// 		$this->_callHandlers();
		
	// 		// Iterate records and gather specified field
	// 		$return_value = array();
	// 		foreach ( db()->find( $this->class_name, $this ) as $record ) $return_value[] =  $record->$field_name;
		
	// 		// Clear this query
	// 		$this->flush();
	
	// 		// Method return value
	// 		$return = null;
	
	// 		// If return value is passed - return boolean about request results
	// 		if( func_num_args() > 1 ) $return = (is_array( $return_value ) && sizeof( $return_value ));
	// 		// Set request results as return value
	// 		else $return = & $return_value;
	
	// 		// Otherwise just return request results
	// 		return $return;
	// 	}
	
	// 	/** @see idbQuery::get() */
	// 	public function & exec( & $return_value = null)
	// 	{
	// 		// Call handlers stack
	// 		$this->_callHandlers();
	
	// 		// Perform DB request
	// 		$return_value = db()->find( $this->class_name, $this );
	
	// 		// Clear this query
	// 		$this->flush();
	
	// 		// Method return value
	// 		$return = null;
	
	// 		// If return value is passed - return boolean about request results
	// 		if( func_num_args() ) $return = (is_array( $return_value ) && sizeof( $return_value ));
	// 		// Set request results as return value
	// 		else $return = & $return_value;
	
	// 		// Otherwise just return request results
	// 		return $return;
	// 	}
	
	// 	/** @see idbQuery::first() */
	// 	public function & first( & $return_value = null)
	// 	{
	// 		// Call handlers stack
	// 		$this->_callHandlers();
	
	// 		// Выполним запрос к БД
	// 		$return_value = db()->find( $this->class_name, $this );
	
	// 		// Получим первую запись из полученного массива, если она есть
	// 		$return_value = isset( $return_value[0] ) ? $return_value[0] : null;
		
	// 		// Очистим запрос
	// 		$this->flush();
	
	// 		// Локальная переменная для возврата правильного результата
	// 		$return = null;
	
	// 		// Если хоть что-то передано в функцию - запишем в локальную переменную boolean значение
	// 		// которое покажет результат выполнения запроса к БД
	// 		if( func_num_args() ) $return = isset( $return_value );
	// 		// Сделаем копию полученных данных в локальную переменную
	// 		else $return = & $return_value;
	
	// 		// Вернем значение из локальной переменной
	// 		return $return;
	// 	}
	 
	/** */
	public function own_limit( $st, $en = NULL ){ $this->own_limit = array($st, $en ); return $this; }
	
	/** */
	public function own_group_by( $params ){ $this->own_group[] = $params; return $this; }
	
	/** */
	public function own_order_by( $field, $direction = 'ASC' ){ $this->own_order = array($field,$direction); return $this; }
	
	/** @see idbQuery::flush() */
	public function flush()
	{
		// Очистим параметры запроса
		$this->condition 	= array();
		$this->limit 		= array();
		$this->order 		= array();
		$this->group 		= array();
		$this->join 		= array();
		
		$this->own_condition = new dbConditionGroup();
		
		$this->condition = new dbConditionGroup();
		
		$this->cConditionGroup = & $this->condition; 	
	}
	
	/** @see idbQuery::random() */
	public function random( & $return_value = null)
	{			
		// Add random ordering
		$this->order_by( '', 'RAND()' );	
	
		// Correctly perform db request for multiple data
		return func_num_args() ? $this->exec( $return_value ) : $this->exec();		
	}

		
	/**
	 * @see idbQuery::or_() 
	 * @deprecated
	 */
	public function or_( $relation = 'OR' )
	{ 
		// Получим либо переданную группу условий, либо создадим новую, потом добавим её в массив групп условий запроса
		$cond_group = new dbConditionGroup( $relation );
		
		// Установим текущую группу условий с которой работает запрос
		$this->cConditionGroup = & $cond_group;
		
		// Добавим нову группу условий в коллекцию групп
		$this->condition->arguments[] = $cond_group;
		
		// Вернем себя для цепирования
		return $this;	
	}	
	
	public function isnull( $attribute ){ return $this->cond($attribute,'',dbRelation::ISNULL); }
	public function notnull( $attribute ){ return $this->cond($attribute,'',dbRelation::NOTNULL); }
	public function notempty( $attribute ){ return $this->cond($attribute,'',dbRelation::NOT_EQUAL); }
	public function like( $attribute, $value = '' ){ return $this->cond($attribute, $value,dbRelation::LIKE); }
	
	/**
	 * Add condition by primary field
	 * 
	 * @param string $value Primary field value
	 * @return \samson\activerecord\dbQuery Chaining
	 */
	public function id( $value )
	{
		// PHP 5.2 get primary field
		eval( '$_primary = '.$this->class_name.'::$_primary;' );	
		
		// Set primary field value
		return $this->cond( $_primary, $value );
	}
	
	/**	 @see idbQuery::where() */
	public function where( $condition ){ return $this->cond($condition, '', dbRelation::OWN ); }
	
	/**	 @see idbQuery::cond() */
	public function cond( $attribute, $value = '', $relation = dbRelation::EQUAL )
	{			
		// Установим общую группу условий
		$destination = & $this->cConditionGroup;	
		 
		// Если передана строка как атрибут
		if( is_string( $attribute ) ) 	
		{
			// Создадим аргумент условия
			$attribute = new dbConditionArgument( $attribute, $value, $relation );
			
			// Если это свойство принадлежит главному классу запроса - установим внутреннюю группу условий
			if (property_exists( $this->class_name, $attribute->field )) 
			{
				$destination = & $this->own_condition;
			}			

			// Добавим аргумент условия в выбранную группу условий
			$destination->arguments[] = $attribute;
		}				
		// If condition group is passed
		else if( is_a( $attribute, ns_classname('dbConditionGroup')) )
		{			
			// Iterate condition arguments
			foreach ( $attribute->arguments as $arg ) 
			{				
				// TODO: add recursion as argument can be an condition group
				// If base query table has this attribute - use base table condition collection
				if( property_exists( $this->class_name, $arg->field )) $destination = & $this->own_condition;
				// Else use query conditions collection			
				else $destination = & $this->cConditionGroup;	
				
				// Add condition argument to defined destination
				$destination->arguments[] = $attribute;
			}
		}		
		
		// Вернем себя для цепирования
		return $this;
	}
	
	/** @see idbQuery::join() */
	public function join( $table_name, $class_name = null )
	{			
		// Добавим имя класса в коллекцию присоединения
		$this->join[] = new RelationData( $this->class_name, $table_name, $class_name );
	
		// Вернем себя для цепирования
		return $this;
	}
	
	/** @see idbQuery::group_by() */
	public function group_by( $field )
	{
		// Default grouping array
		$destination = & $this->group;
		
		// If this field belongs to query main class
		if (property_exists( $this->class_name, $field )) $destination = & $this->own_group;
		
		$destination[] = $field;
	
		// Вернем себя для цепирования
		return $this;
	}
		
	/** @see idbQuery::limit() */
	public function limit( $st, $en = NULL, $own = false )
	{
		// Select base table or whole query destination
		if($own) $this->own_limit = array( $st, $en );
		else $this->limit = array( $st, $en );	
	
		// Вернем себя для цепирования
		return $this;
	}
	
	/** @see idbQuery::order_by() */
	public function order_by( $field, $direction = 'ASC' )
	{
		$this->order = array( $field, $direction );
	
		// Вернем себя для цепирования
		return $this;
	}
	
	/** @see idbQuery::add_field() */
	public function add_field( $field, $alias = NULL, $own = true )
	{
		// Если передан псевдоним для поля, то подставим его
		if ( isset($alias) ) $field = $field.' as '.$alias;
		else $alias = $field;
		
		// Добавим виртуальное поле
		if($own) $this->own_virtual_fields[ $alias ] = $field;
		else $this->virtual_fields[ $alias ] = $field;
	
		// Вернем себя для цепирования
		return $this;
	}	
	
	/** @see idbQuery::count() */
	public function count( $field = '*' ){return db()->count( $this->class_name, $this ); }
	
	/** @see idbQuery::innerCount() */
	public function innerCount( $field = '*' ){return db()->innerCount( $this->class_name, $this ); }
	
	/**	@see idbQuery::parse() */
	public function parse( $query_text, array $args = NULL )
	{		
		// Преобразуем текст в нижний регистр
		//$query_text = mb_strtolower( $query_text, 'UTF-8' );
		
		// Паттерн для определения метода динамического запроса
		$sorting_pattern = '
		/(?:^(?<method>find_by|find_all_by|all|first|last)_)
			|_order_by_  (?P<order>[a-zа-я0-9]+) _ (?P<order_dir>(?:asc|desc))
			|_limit_ (?P<limit_start>\d+) (?:_(?P<limit_end>\d+))?
			|_group_by_(?P<group>.+)
			|_join_ (?<join>.+)
		/iux';		
			 
		// Это внутренний счетчик для аргументов запроса для того чтобы не сбиться при их подставлении
		// в условие запроса к БД
		$args_cnt = 0;
		
		// Выполним первоначальный парсинг проверяющий правильность указанного метода
		// выборки данных и поиск возможных модификаторов запроса
		if( preg_match_all( $sorting_pattern, $query_text, $global_matches ) )
		{		
			// Удалим все пустые группы полученные послке разпознования
			$global_matches = array_filter_recursive( $global_matches );			

			// Получим текст условий самого запроса, убрав из него все возможные модификаторы и параметры		
			// и переберем только полученные группы условий запроса
			foreach (explode( '_or_', str_ireplace( $global_matches[0], '', $query_text) ) as $group_text ) 
			{	
				// Добавим группу условий к запросу	
				$this->or_('AND');
				
				// Переберем поля которые формируют условия запроса - создание объекты-условия запроса
				foreach (explode( '_and_', $group_text ) as $condition_text )$this->cond( $condition_text, $args[$args_cnt++] );	
			}
			
			// Получим сортировку запроса
			if(isset($global_matches['order'])) $this->order = array( $global_matches['order'][0], $global_matches['order_dir'][0]);
			// Получим ограничения запроса
			if(isset($global_matches['limit_start'])) $this->limit = array( $global_matches['limit_start'][0], $global_matches['limit_end'][0] );
			// Получим групировку запроса
			if(isset($global_matches['group'])) $this->group = explode( '_and_', $global_matches['group'][0] );
			// Получим имена таблиц для "объединения" в запросе
			if(isset($global_matches['join'])) foreach (explode( '_and_', $global_matches['join'][0] ) as $join) $this->join($join);
		}		
		
		// Вернем полученный объект-запрос
		return $this;	
	}	
	
	// Magic method after-clonning 
	public function __clone()
	{
		// Remove old references
		$p = $this->condition;
		unset($this->condition);
		
		// Set new onces on copied values
		$this->condition = $p;
		$this->cConditionGroup = & $this->condition;
	}
	
	// Магический метод для выполнения не описанных динамических методов класса
	public function __call( $method_name, array $arguments )
	{				
		// Если этот метод поддерживается - выполним запрос к БД
		if( preg_match( '/^(find_by|find_all_by|all)/iu', $method_name, $matches ))
		{
			return db()->find( $this->class_name, $this->parse( $method_name, $arguments) );
		}
		// Проверим существует ли у класса заданное поле
		else if( property_exists( $this->class_name, $method_name) ) 
		{			
			
			// Если передан аргумент - расцениваем его как аргумент запроса
			if( sizeof($arguments) > 1 ) 	return $this->cond( $method_name, $arguments[0], $arguments[1]);
			else if( isset($arguments[0]) ) return $this->cond( $method_name, $arguments[0] );
			// Просто игнорируем условие
			else return $this;
		}
		// Сообщим об ошибке разпознования метода
		else return e('Не возможно определить метод(##) для создания запроса к БД', E_SAMSON_ACTIVERECORD_ERROR, $method_name );		
	}
	
	/**
	 * Конструктор
	 * @param string 	$class_name Имя класса для которого создается запрос к БД
	 * @param mixed		$link		Указатель на экземпляр подключения к БД
	 */
	public function __construct( $class_name, & $link = null )
	{				
		// Сформируем правильное имя класса
		$class_name = ns_classname( $class_name, 'samson\activerecord' );		
		
		/*
		// Проверим класс		
		if( !class_exists( $class_name ) ) 
		{
			e('Не возможно создать запрос БД для класса(##) - Класс не существует', E_SAMSON_ACTIVERECORD_ERROR, $class_name);
		}
		*/
		
		// Создадим общую условную группу
		$this->condition = new dbConditionGroup();
		
		// Установим указатель на текущую группу условий
		$this->cConditionGroup = & $this->condition;
		
		// Создадим собственную условную группу
		$this->own_condition = new dbConditionGroup();	
		
		// Установим имя класса для запроса		
		$this->class_name = $class_name;	

		// Сохраним экземпляр соединения
		$this->link = $link;
	}

}