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

    public $empty = false;

    /** @var bool True to show requests */
    protected $debug = false;

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
    public function own_limit($st, $en = NULL)
    {
        $this->own_limit = array($st, $en);
        return $this;
    }

    /** */
    public function own_group_by($params)
    {
        $this->own_group[] = $params;
        return $this;
    }

    /** */
    public function own_order_by($field, $direction = 'ASC')
    {
        $this->own_order = array($field,$direction);
        return $this;
    }

    /** @see idbQuery::flush() */
    public function flush()
    {
        // Очистим параметры запроса
        $this->condition = new Condition();
        $this->limit = array();
        $this->order = array();
        $this->group = array();
        $this->join = array();

        $this->own_condition = new Condition();
        $this->own_group = array();
        $this->own_virtual_fields = array();
        $this->own_limit = array();
        $this->own_order = array();

        $this->cConditionGroup = &$this->condition;
    }

    /** @see idbQuery::random() */
    public function random(& $return_value = null)
    {
        // Add random ordering
        $this->order_by('', 'RAND()');

        // Correctly perform db request for multiple data
        return func_num_args() ? $this->exec($return_value) : $this->exec();
    }


    /**
     * @see idbQuery::or_()
     * @deprecated
     */
    public function or_($relation = 'OR')
    {
        // Получим либо переданную группу условий, либо создадим новую, потом добавим её в массив групп условий запроса
        $cond_group = new Condition($relation);

        // Установим текущую группу условий с которой работает запрос
        $this->cConditionGroup = &$cond_group;

        // Добавим нову группу условий в коллекцию групп
        $this->condition->arguments[] = $cond_group;

        // Вернем себя для цепирования
        return $this;
    }

    /**
     * Set debug query mode
     * @param bool $value Debug status, true - active
     *
     * @return $this Chaining
     */
    public function debug($value = true)
    {
        db()->debug($this->debug = $value);

        return $this;
    }

    public function isnull($attribute)
    {
        return $this->cond($attribute, '', dbRelation::ISNULL);
    }
    public function notnull($attribute)
    {
        return $this->cond($attribute, '', dbRelation::NOTNULL);
    }
    public function notempty($attribute)
    {
        return $this->cond($attribute, '', dbRelation::NOT_EQUAL);
    }
    public function like($attribute, $value = '')
    {
        return $this->cond($attribute, $value, dbRelation::LIKE);
    }

    /**
     * Add condition by primary field
     *
     * @param string $value Primary field value
     * @return \samson\activerecord\dbQuery Chaining
     */
    public function id($value)
    {
        // PHP 5.2 get primary field
        eval('$_primary = ' . $this->class_name . '::$_primary;');

        // Set primary field value
        return $this->cond($_primary, $value);
    }

    /**	 @see idbQuery::where() */
    public function where($condition)
    {
        return $this->cond($condition, '', dbRelation::OWN);
    }

    /**	 @see idbQuery::cond() */
    public function cond($attribute, $value = null, $relation = dbRelation::EQUAL)
    {
        // Установим общую группу условий
        $destination = &$this->cConditionGroup;

        // Если передана строка как атрибут
        if (is_string($attribute)) {
            // If value is not set or an empty array
            if (!isset($value)) {
                $relation = dbRelation::ISNULL;
                $value = '';
            } elseif (is_array($value) && !sizeof($value)) {
                $this->empty = true;
                return $this;
            }
            // Создадим аргумент условия
            $attribute = new Argument($attribute, $value, $relation);

            // Если это свойство принадлежит главному классу запроса - установим внутреннюю группу условий
            if (property_exists($this->class_name, $attribute->field)) {
                $destination = &$this->own_condition;
            }

            // Добавим аргумент условия в выбранную группу условий
            $destination->arguments[] = $attribute;
            // If condition group is passed
        } elseif (is_a($attribute, ns_classname('Condition', 'samson\activerecord'))) {
            // Iterate condition arguments
            foreach ($attribute->arguments as $arg) {
                // Default destination condition group
                $destination = &$this->cConditionGroup;

                // TODO: add recursion as argument can be an condition group
                if (is_a($arg, ns_classname('Argument', 'samson\activerecord'))) {
                    // If base query table has this attribute - use base table condition collection
                    if (property_exists($this->class_name, $arg->field)) {
                        $destination = &$this->own_condition;
                    }
                }

                // Add condition argument to defined destination
                $destination->arguments[] = $attribute;
            }
        }

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::join() */
    public function join($tableName, $className = null, $ignore = false)
    {
        // Добавим имя класса в коллекцию присоединения
        $this->join[] = new RelationData($this->class_name, $tableName, $className, $ignore);

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::group_by() */
    public function group_by($field)
    {
        // Default grouping array
        $destination = &$this->group;

        // If this field belongs to query main class
        //if (property_exists( $this->class_name, $field )) $destination = & $this->own_group;

        $destination[] = $field;

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::limit() */
    public function limit($st, $en = NULL, $own = false)
    {
        // Select base table or whole query destination
        if ($own) {
            $this->own_limit = array($st, $en);
        } else {
            $this->limit = array($st, $en);
        }

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::order_by() */
    public function order_by($field, $direction = 'ASC')
    {
        $this->order[] = array($field, $direction);

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::add_field() */
    public function add_field($field, $alias = null, $own = true)
    {
        // Если передан псевдоним для поля, то подставим его
        if (isset($alias)) {
            $field = $field . ' as ' . $alias;
        } else {
            $alias = $field;
        }

        // Добавим виртуальное поле
        if ($own) {
            $this->own_virtual_fields[$alias] = $field;
        } else {
            $this->virtual_fields[$alias] = $field;
        }

        // Вернем себя для цепирования
        return $this;
    }

    /** @see idbQuery::count() */
    public function count($field = '*')
    {
        return db()->count($this->class_name, $this);
    }

    /** @see idbQuery::innerCount() */
    public function innerCount($field = '*')
    {
        return db()->innerCount($this->class_name, $this);
    }

    /**	@see idbQuery::parse() */
    public function parse($queryText, array $args = null)
    {
        // Преобразуем текст в нижний регистр
        //$query_text = mb_strtolower( $query_text, 'UTF-8' );

        // Паттерн для определения метода динамического запроса
        $sortingPattern = '
		/(?:^(?<method>find_by|find_all_by|all|first|last)_)
			|_order_by_  (?P<order>[a-zа-я0-9]+) _ (?P<order_dir>(?:asc|desc))
			|_limit_ (?P<limit_start>\d+) (?:_(?P<limit_end>\d+))?
			|_group_by_(?P<group>.+)
			|_join_ (?<join>.+)
		/iux';

        // Это внутренний счетчик для аргументов запроса для того чтобы не сбиться при их подставлении
        // в условие запроса к БД
        $argsCnt = 0;

        // Выполним первоначальный парсинг проверяющий правильность указанного метода
        // выборки данных и поиск возможных модификаторов запроса
        if (preg_match_all($sortingPattern, $queryText, $globalMatches)) {
            // Удалим все пустые группы полученные послке разпознования
            $globalMatches = array_filter_recursive($globalMatches);

            // Получим текст условий самого запроса, убрав из него все возможные модификаторы и параметры
            // и переберем только полученные группы условий запроса
            foreach (explode('_or_', str_ireplace($globalMatches[0], '', $queryText)) as $groupText) {
                // Добавим группу условий к запросу
                $this->or_('AND');

                // Переберем поля которые формируют условия запроса - создание объекты-условия запроса
                foreach (explode('_and_', $groupText) as $conditionText) {
                    $this->cond($conditionText, $args[$argsCnt++]);
                }
            }

            // Получим сортировку запроса
            if (isset($globalMatches['order'])) {
                $this->order = array($globalMatches['order'][0], $globalMatches['order_dir'][0]);
            }
            // Получим ограничения запроса
            if (isset($globalMatches['limit_start'])) {
                $this->limit = array($globalMatches['limit_start'][0], $globalMatches['limit_end'][0]);
            }
            // Получим групировку запроса
            if (isset($globalMatches['group'])) {
                $this->group = explode('_and_', $globalMatches['group'][0]);
            }
            // Получим имена таблиц для "объединения" в запросе
            if (isset($globalMatches['join'])) {
                foreach (explode('_and_', $globalMatches['join'][0]) as $join) {
                    $this->join($join);
                }
            }
        }

        // Вернем полученный объект-запрос
        return $this;
    }

    /**
     * Function to reconfigure dbQuery to work with multiple Entities
     *
     * @param string $className Entity name
     * @return self|string Chaining or current class name if nothing is passed
     */
    public function className($className = null)
    {
        if (!func_num_args()) {
            return $this->class_name;
        } else {
            $this->flush();

            if (isset($className)) {
                // Сформируем правильное имя класса
                $className = ns_classname($className, 'samson\activerecord');
                // Установим имя класса для запроса
                $this->class_name = $className;
            }

            return $this;
        }
    }

    // Magic method after-clonning
    public function __clone()
    {
        // Remove old references
        $condition = $this->condition;
        unset($this->condition);

        // Set new one on copied values
        $this->condition = $condition;
        $this->cConditionGroup = &$this->condition;
    }

    // Магический метод для выполнения не описанных динамических методов класса
    public function __call($methodName, array $arguments)
    {
        /** @var array $matches Prepared statement matches */
        $matches = array();
        // Если этот метод поддерживается - выполним запрос к БД
        if (preg_match('/^(find_by|find_all_by|all)/iu', $methodName, $matches)) {
            return db()->find($this->class_name, $this->parse($methodName, $arguments));
        } elseif (property_exists($this->class_name, $methodName)) { // Проверим существует ли у класса заданное поле

            // Если передан аргумент - расцениваем его как аргумент запроса
            if (sizeof($arguments) > 1) {
                return $this->cond($methodName, $arguments[0], $arguments[1]);
            } elseif (isset($arguments[0])) {
                return $this->cond($methodName, $arguments[0]);
            } else { // Просто игнорируем условие
                return $this;
            }
        } else { // Сообщим об ошибке разпознования метода
            return e(
                'Не возможно определить метод(##) для создания запроса к БД',
                E_SAMSON_ACTIVERECORD_ERROR,
                $methodName
            );
        }
    }

    /**
     * Конструктор
     * @param string|null 	$className  Имя класса для которого создается запрос к БД
     * @param mixed		    $link		Указатель на экземпляр подключения к БД
     */
    public function __construct($className = null, & $link = null)
    {
        /*
        // Проверим класс
        if( !class_exists( $class_name ) )
        {
            e(
                'Не возможно создать запрос БД для класса(##) - Класс не существует',
                E_SAMSON_ACTIVERECORD_ERROR,
                $class_name
            );
        }
        */

        $this->className($className);

        // Сохраним экземпляр соединения
        $this->link = $link;
    }
}
