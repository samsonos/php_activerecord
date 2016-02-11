<?php 
namespace samson\activerecord;

//TODO: Написать метод ALL()
//TODO: Поддержка нескольких подключений
/**
 * Запрос для БД
 * Класс собирает в себя все необходимые параметры для 
 * формирования "правильного" запроса на самом низком уровне
 * работы с БД
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com> 
 *
 */
class dbQuery extends \samsonframework\orm\Query
{
    /**
     * Указатель на текущую группу условий с которой работает запрос
     *
     * @var Condition
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
     * @var Condition
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



    /** @deprecated Use self::fields() */
    public function fieldsNew($fieldName, & $return = null)
    {
        return call_user_func_array(array($this, 'fields'), func_get_args());
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
     * @deprecated @see \samsonframework\orm\QueryInterface::entity(), full class name with namespace
     *                 should be passed.
     * @return self|string Chaining or current class name if nothing is passed
     */
    public function className($className = null)
    {
        // Old support for not full class names
        if (strpos($className, '\\') === false) {
            // Add generic namespace
            $className = '\samson\activerecord\\'.$className;
        }

        return func_num_args() > 0 ? $this->entity($className) : $this->class_name;
    }

    /**
     * Add condition by primary field
     *
     * @param string $value Primary field value
     * @return self Chaining
     * @deprecated Use direct query with where('PRIMARY_FIELD',...)
     */
    public function id($value)
    {
        // PHP 5.2 get primary field
        $_primary = null;
        eval('$_primary = ' . $this->class_name . '::$_primary;');

        // Set primary field value
        return $this->where($_primary, $value);
    }

    /**
     * Add condition to current query.
     * This method supports receives three possible types for $fieldName,
     * this is deprecated logic and this should be changed to use separate methods
     * for each argument type.
     *
     * @param string|ConditionInterface|ArgumentInterface $fieldName Entity field name
     * @param string $fieldValue Value
     * @param string $relation Relation between field name and its value
     * @deprecated @see self::where()
     * @return self Chaining
     */
    public function cond($fieldName, $fieldValue = null, $relation = '=')
    {
        // If empty array is passed
        if (is_string($fieldName)) {
            return $this->where($fieldName, $fieldValue, $relation);
        } elseif (is_array($fieldValue) && !sizeof($fieldValue)) {
            $this->empty = true;
            return $this;
        } elseif (is_a($fieldName, '\samsonframework\orm\ConditionInterface')) {
            $this->whereCondition($fieldName);
        } elseif (is_a($fieldName, '\samsonframework\orm\ArgumentInterface')) {
            $this->getConditionGroup($fieldName->field)->addArgument($fieldName);
        }

        return $this;
    }

    /**
     * Query constructor.
     * @param string|null $entity Entity identifier
     * @throws EntityNotFound
     */
    public function __construct($entity = 'material')
    {
        // Old support for not full class names
        if (strpos($entity, '\\') === false) {
            // Add generic namespace
            $entity = '\samson\activerecord\\'.$entity;
        }

        $this->order = &$this->sorting;
        $this->group = &$this->grouping;

        // Call parent constructor
        parent::__construct(db());
        $this->entity($entity);
    }

    /**
     * Magic method for setting beautiful conditions.
     *
     * @deprecated Use ->where(..)->
     * @param $methodName
     * @param array $arguments
     * @return $this|array|bool|dbQuery
     */
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
}
