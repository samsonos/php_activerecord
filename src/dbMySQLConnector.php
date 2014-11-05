<?php 
namespace samson\activerecord;

//[PHPCOMPRESSOR(remove,start)]
use samson\core\Generator;
//[PHPCOMPRESSOR(remove,end)]
use samson\core\File;

/**
 * Класс описывающий подключение к серверу MySQL
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbMySQLConnector implements idbConnector
{
	/** Path to cache dir */
	const CACHE_PATH = '/db/';
	
	/** Table name prefix */
	public static $prefix = '';
	
	/**
	 * Коллекция данных описывающих таблицы в БД
	 * @var array
	 */
	protected static $tables = array();
	
	/**
	 * Коллекция данных описывающих связи таблиц в БД
	 * @var array
	 */
	protected static $relations = array();		
	
	/**
	 * Флаг подключения к БД
	 * @var boolean
	 */
	protected $connected = FALSE;
	
	/**
	 * Экземпляр подключения к БД
	 * @var mixed
	 */
	public $link;

	/**
	 * Имя БД
	 * @var mixed
	 */
	protected $base_name;
	
	/**
	 * Коллекция созданных классов
	 * @var array
	 */
	private $created_classes = array();
	
	/**
	 * Build database table column map
	 * @param $table_name  
	 */
	private function __build_columns_map( $table_name, array & $select = array(), array & $map = array(), array & $alias = array(), $alias_table_name = null )
	{	
		$alias_table_name = isset($alias_table_name) ? $alias_table_name : $table_name;
		
		// Iterate table column data
		foreach ( self::$tables[ $table_name ] as $column_data )
		{
			// Get column name
			$column = $column_data['Field'];
		
			// Build alias name of related table column
			$column_alias = $alias_table_name.'_'.$column;
				
			// Build full name of related table column
			$column_full = $alias_table_name.'.'.$column;
		
			// Store column alias
			$alias[ $column ] = $column_alias;
				
			// Save sql "SELECT" statement
			$select[] = $column_full.' as '.$column_alias;
		
			// Save column map
			$map[ $column_alias ] = $column_full;
		}
	}
	
	//[PHPCOMPRESSOR(remove,start)]
	/**
	 * Генератор описания классов и функций для работы с таблицами БД
	 *
	 * @return string Код на PHP для динамического создания классов и функций обращения к ним
	 */
	public function classes( $class_data, $class_name, $table_name = NULL, array $db_relations = NULL )
	{		
		// Сюда соберем код для генерации классов ActiveRecord
		$class_eval = '';	

		$func_eval = '';
		
		// Префикс для функций обращения к классам БД
		if( ! defined( '__ARQ_Prefix__' )) define( '__ARQ_Prefix__', '_' );			

		// Сформируем имя функции для "вызова" класса
		$func_name = __ARQ_Prefix__ . $class_name;			
		
		// If table name prefix is set
		if(isset( self::$prefix{0} )) 
		{
			// Remove prefix from class name
			$class_name = str_replace( self::$prefix, '', $class_name);			
		}
		
		// Если такой класс не был описан вручную до этого
		if( ! class_exists( $class_name, false ) && ! in_array( $class_name, $this->created_classes) )
		{
			// Создадим коллекцию созданных классов
			$this->created_classes[] = $class_name;
			
			// Определим реальную таблицу БД
			$table_name = isset($table_name) ? $table_name : self::$prefix.$class_name;			
			
			// Флаг того что этот класс относительный
			$relational_class = $table_name != $class_name;
			
			// Добавим обдасть имен
			//$class_eval .= 'namespace Samson\ActiveRecord {';
			
			// Заполним комманду создания класса
			$class_eval .= "\n".'/**';  
			
			// Для относительных классов выведем специальный заголовок
			if( $relational_class )	$class_eval .= "\n".' * ОТНОСИТЕЛЬНЫЙ Класс для работы с таблицей БД "'.$table_name.'" через "'.$class_name.'"';
			else $class_eval .= "\n".' * Класс для работы с таблицей БД "'.$table_name.'"';			
			$class_eval .= "\n".' * @package SamsonActiveRecord';
			$class_eval .= "\n".' * @author Vitaly Iegorov <egorov@samsonos.com>';
			$class_eval .= "\n".' * @author Nikita Kotenko <kotenko@samsonos.com>';			
			$class_eval .= "\n".' * @version 2.0';
			$class_eval .= "\n".' */';
			$class_eval .= "\n".'class '.$class_name.' extends \samson\activerecord\dbRecord {';		

			// Запишем реальное имя таблицы в БД  
			$class_eval .= "\n\t".'/** Настоящее имя таблицы в БД к которой привязан данный класс */';			
			$class_eval .= "\n\t".'public static $_table_name = "'.$table_name.'";';	
			
			$class_eval .= "\n\t".'/** Внутрення группировка таблицы */';
			$class_eval .= "\n\t".'public static $_own_group = array();';
				
			// Коллекция уникальных переменных
			$unique_var = array();
			
			// Коллекция ключей переменных
			$index_var = array();
			
			// Коллекция типов переменных
			$var_types = array();
			
			// Primary field name
			$primary_field = '';
			
			// Переберем данные описывающие структуру таблицы, её колонки
			foreach ( $class_data as & $column )
			{
				// Если это главный ключ таблицы запишем его в специальную переменную
				if( $column['Key'] == 'PRI' ) 
				{
					$class_eval .= "\n\t".'/** Название ключевого поля таблицы */';
					$class_eval .= "\n\t".'public static $_primary = "'.$column['Field'].'";';
					$primary_field = $column['Field'];
				}			
				// Уникальные поля
				else if( $column['Key'] == 'UNI') $unique_var[] = $column['Field'];
				// Ключевые поля
				else if( $column['Key'] == 'MUL') $index_var[] = $column['Field'];	
				
				// Запишем тип переменной
				$var_types[ $column['Field'] ] = $column['Type'];
			}
						
			// Коллекция определенных переменных
			$defined_vars = array();				
			
			// Комманда на выборку полей
			$sql_select = array();
			
			// Счетчик не английских переменных
			$utf8_fields_count = 1;
			
			// Переменные
			$vars_eval = '';
				
			// Переберем данные описывающие структуру таблицы, её колонки
			foreach ( $class_data as & $column )
			{					
				// Получим реальное имя колонки в таблице БД
				$field = (isset($column['Column'])) ? $column['Column'] : $column['Field'] ;
				
				// Получим виртуальное имя колонки если оно задано
				$f_name = $column['Field'];			
				
				// Если переменную с такими именем мы еще не создавали
				if( ! in_array( $field, $defined_vars ) ) 
				{											
					// Сформируем SQL комманду для "правильной" выборки данных из БД
					$sql_select[] = $table_name.'.'.$field;
					
					// Если это русская переменная или содержащая не правильное имя переменной то мы называем её по своему
					if( preg_match('/([^a-zA-Z_\s0-9]|[\-])+/ui', $field ) ) 
					{											
						// Сгенерируем имя поля
						$f_name = 'Field_'.($utf8_fields_count++);
							
						// Создадим переменную класса
						$vars_eval .= "\n\t".'/** '.$field.' */';								
					}	
					// Если это не настоящие имя поля - укажем настоящее
					else if( $table_name != $class_name ) $vars_eval .= "\n\t".'/** '.$column['Field'].' */';			
				
					// Создадим саму переменную класса
					$vars_eval .= "\n\t".'public $'.$f_name.' = "";';

					// Добавим имя переменной
					$defined_vars[ $f_name ] = $field;		
				}
				// Создадим специальную переменную с пометкой о дубликате и ссылкой на него
				else e('Ошибка: В таблице ##, найден дубликат колонки ##-##', D_SAMSON_ACTIVERECORD_DEBUG, array( $class_name, $field, $f_name ));	
			}

			// Пропишем поля записи
			$class_eval .= "\n\t".'/** Коллекция полей класса и имен колонок в таблице БД */';
			$class_eval .= "\n\t".'public static $_attributes = array(';
			foreach ( $defined_vars as $name => $db_name ) $class_eval .= "\n\t\t".'"'.$name.'"=>"'.$db_name.'",';
			$class_eval .= "\n\t".');';
			
			// Пропишем колонки таблицы
			$class_eval .= "\n\t".'/** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */';
			$class_eval .= "\n\t".'public static $_table_attributes = array(';
			foreach ( $defined_vars as $name => $db_name ) $class_eval .= "\n\t\t".'"'.$name.'"=>"'.$db_name.'",';
			$class_eval .= "\n\t".');';
			
			// Сформируем SQL комманду для "правильной" выборки данных из БД
			$class_eval .= "\n\t".'/** Коллекция параметров SQL комманды для запроса к таблице */';
			$class_eval .= "\n\t".'public static $_sql_from = array(';
			$class_eval .= "\n\t\t".'"this" => "`'.$table_name.'`",';
			
			// Выборка полей для запроса
			$select_eval = "\n\t".'/** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */';
			$select_eval .= "\n\t".'public static $_sql_select = array(';			
			$select_eval .= "\n\t\t".'"this" => "'.implode( ','."\n", $sql_select).'",';
			
			// Коллекция связей имен полей связанных таблиц
			$relation_eval = "\n\t".'/** Коллекция имен полей связанных таблиц для запроса к таблице БД */';
			$relation_eval .= "\n\t".'public static $_relations = array(';
			
			// Коллекция типов связей имен полей связанных таблиц
			$reltype_eval = "\n\t".'/** Коллекция типов связанных таблиц для запроса к таблице БД */';
			$reltype_eval .= "\n\t".'public static $_relation_type = array(';
			
			// Коллекция типов связей имен полей связанных таблиц
			$relalias_eval = "\n\t".'/** Коллекция алиасов связанных таблиц для запроса к таблице БД */';
			$relalias_eval .= "\n\t".'public static $_relation_alias = array(';
			
			// Коллекция имен полей для мапинга
			$map_vars = array();
			foreach ( $defined_vars as $name => $db_name ) $map_vars[ $name ] = $table_name.'.'.$db_name;
			
			// Permanent relation is specified
			if( isset( $db_relations[ $table_name ] ) )  
			{	
				// Iterate table permanent relations
				foreach ( $db_relations[ $table_name ] as $r_table => $i )
				{
					// Check child table
					if( !isset( self::$tables[ $i->child ] ) ) 
					{
						e('Cannot create relation beetween ## and ## - Table ## does not exists',E_SAMSON_ACTIVERECORD_ERROR, array( $i->parent, $i->child));
						continue;
					}				
					
					// Parent table name 
					$r_table_name = isset($i->alias{0}) ? $i->alias : $i->child;  
															
					// If relation alias is defined
					if( isset($i->alias{0})) $class_eval .= "\n\t\t".'"'.$i->alias.'"=>" LEFT JOIN `'.$i->child.'` AS '.$i->alias;
					else 					 $class_eval .= "\n\t\t".'"'.$i->child.'"=>" LEFT JOIN `'.$i->child.'`';
					
					// Parent table name
					$ptable = $i->parent;
					
					// If parent field not specified - use parent table primary field
					if( !isset($i->parent_field) ) $pfield = $primary_field;
					// Correctly determine parent field name
					else 
					{						
						// Define if parent field name has table name in it
						$tableinpf = strpos( $i->parent_field, '.');
						
						// Get parent table field name
						$pfield = $tableinpf !== false ? substr( $i->parent_field, $tableinpf + 1 ) : $i->parent_field;

						// Parent table field
						$ptable = $tableinpf !== false ? substr( $i->parent_field, 0, $tableinpf ) : $i->parent;
					}
					
					// If no "." symbol in parent field name append parent table name
					$pf = '`'.$ptable.'`.`'.$pfield.'`';
						
					// If child field not specified
					if( !isset($i->child_field{0})) $cf  =  '`'.$i->child.'`.`'.$pfield.'`';					
					// If no "." symbol in child field name append child table name
					else $cf = strpos( $i->child_field, '.') === false ? '`'.(isset($i->alias{0})?$i->alias:$i->child).'`.'.$i->child_field : $i->child_field;
						
					// And joining field
					$class_eval .= ' ON '.$pf.' = '.$cf.'",';
					
					// Установим тип связи
					$reltype_eval .= "\n\t\t".'"'.$r_table_name.'"=>'.$i->type.',';
					
					$relation_eval .= "\n\t\t".'"'.$r_table_name.'" => array(';
					
					$relalias_eval .= "\n\t\t".'"'.$r_table_name.'" => "'.$i->child.'",';
					
					// Array for select block
					$select_eval_array = array();
					
					// Переберем поля связанной таблицы
					foreach ( self::$tables[ $i->child ] as $column_data )
					{
						// Имя поля связанной таблицы
						$r_field_name = $r_table_name.'_'.$column_data['Field'];
							
						// Имя поля связанной таблицы
						$r_real_name = $r_table_name.'.'.$column_data['Field'];
							
						// Добавим связь в мап
						$map_vars[ $r_field_name ] = $r_real_name;
							
						// Сформируем значение массива
						$relation_eval .= "\n\t\t\t".'"'.$column_data['Field'].'" => "'.$r_field_name.'",';
					
						// Часть SQL-комманды
						$select_eval_array[] = "\n\t\t\t".$r_real_name.' as '.$r_field_name.'';
					}
					
					// Сформируем массив имен полей для связи
					$select_eval .= "\n\t\t".'"'.$r_table_name.'" => "'.implode( ',', $select_eval_array).'",';
					
					// Закроем коллекцию связей
					$relation_eval .= "\n\t\t".'),';
				}				
			}			
			
			// Закроем
			$class_eval .= "\n\t".');';
			
			// Закроем
			$select_eval .= "\n\t".');';			
				
			// Закроем
			$relation_eval.= "\n\t".');';
			
			$reltype_eval .= "\n\t".');';
			
			$relalias_eval.= "\n\t".');';
			
			// Слепим все вместе
			$class_eval .= $relation_eval.$relalias_eval.$reltype_eval.$select_eval;			
			
			// Пропишем типы полей записи
			$class_eval .= "\n\t".'/** Коллекция типов полей записи в таблице БД */';
			$class_eval .= "\n\t".'public static $_types = array(';
			foreach ( $var_types as $name => $type ) $class_eval .= "\n\t\t".'"'.$name.'"=>"'.$type.'",';
			$class_eval .= "\n\t".');';
			
			// Пропишем ключевые поля записи
			$class_eval .= "\n\t".'/** Коллекция имен ключей записи в таблице БД */';
			$class_eval .= "\n\t".'public static $_indeces = array(';
			foreach ( $index_var as $name ) $class_eval .= "\n\t\t".'"'.$name.'",';
			$class_eval .= "\n\t".');';
			
			// Пропишем уникальные поля записи
			$class_eval .= "\n\t".'/** Коллекция имен уникальных полей записи в таблице БД */';
			$class_eval .= "\n\t".'public static $_unique = array(';
			foreach ( $unique_var as $name ) $class_eval .= "\n\t\t".'"'.$name.'",';
			$class_eval .= "\n\t".');';
			
			// Пропишем поля записи
			$class_eval .= "\n\t".'/** Коллекция связей между именами полей класса и именами колонок в таблице БД */';
			$class_eval .= "\n\t".'public static $_map = array(';
			foreach ( $map_vars as $name => $db_name ) $class_eval .= "\n\t\t".'"'.$name.'"=>"'.$db_name.'",';
			$class_eval .= "\n\t".');';
			
			// Запишем имена всех полей класса как метаданные
			$class_eval .= $vars_eval;		
			
			// Заполним конструктор класса для установки схемы
			$class_eval .= "\n\t".'/** Создать экземпляр класса '.$class_name.' */';
			$class_eval .= "\n\t".'public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "'.$class_name.'" ; parent::__construct( $id, $class_name ); }';

			// Геттер для получения переменных класса по другому названию
			//$class_eval .= "\n\t".'/** Попытаться получить переменную класса '.$class_name.' */';
			//$class_eval .= "\n\t".'public function __get( $name ){ if( isset( self::$_attributes[ $name ]) ){ $f = self::$_attributes[ $name ]; return $this->$f; } else return parent::__get( $name );}';
			
			// Сеттер для установки переменных класса по другому названию
			//$class_eval .= "\n\t".'/** Попытаться получить переменную класса '.$class_name.' */';
			//$class_eval .= "\n\t".'public function __set( $name, $value ){ if( isset( self::$_attributes[ $name ]) ){ $f = self::$_attributes[ $name ]; return $this->$f = $value; } else return parent::__set( $name, $value );}';
							
			// Закончим описание класса
			$class_eval .= "\n}";
			
			// Создадим подгруппу для хранения экземпляров данного класса
			$class_eval .= "\n".'dbRecord::$instances["'.$class_name.'"] = array();';	

			// Закончим описание области имен
			//$class_eval .= "\n}";
			
			// Если мы еще не объявляли функцию для данного класса
			if( ! function_exists( $func_name ) )
			{
				// Создадим специальную функцию для динамических запросов к АктивРекорду		
				//$class_eval .= "\n".'namespace {';
				$func_eval .= "\n".'/** '."\n".' * @return dbQuery '."\n".' */';
				$func_eval .= "\n".'function ' . $func_name . '(){ return new \samson\activerecord\dbQuery("'.$class_name.'"); }'."\n"."\n";
				//$class_eval .= "\n}";				
			}
		}			
		
		// Установим переменную для оптимизации
		//$this->classes .= $class_eval;		
		
		// Вернем строку с описанием классов для таблиц БД
		return array( $class_eval, $func_eval );
	}
	
	/** Generate database table relations */
	public function relations($cachePath = '')
	{
		// Generate unique file name
		$relations_file = $cachePath.self::CACHE_PATH.'/relations/'.md5(serialize(TableRelation::$instances)).'.php';
				
		// Relations file does not exists - create it
		if( !file_exists($relations_file))
		{		
			// Get directory path
			$dir = pathname( $relations_file );
			
			// Create folder
			if( ! file_exists( $dir )) mkdir( $dir, 0777, TRUE );
			//  Clear folder
			else File::clear( $dir );
			
			// Processed permanent table relations
			$db_relations = array();
			
			// Iterate permanent relations
			foreach( TableRelation::$instances as $row )
			{
				// Create relations data for specific table
				if( ! isset( $db_relations[ $row->parent ] ) ) $db_relations[ $row->parent ] = array();
					
				// Define child relation table name
				$child_relation = !isset($row->alias{0}) ? $row->child : $row->alias;
				
				$row->parent = self::$prefix.$row->parent;
				$row->child = self::$prefix.$row->child;
					
				// Save relation data
				$db_relations[ $row->parent ][ $child_relation ] = $row;
			}
			
			// Create code generator instance
			$g = new Generator('samson\activerecord');
			$g->multicomment(array('Static ActiveRecord generated table relations'));	
			
			// Array of "FROM" sql statements for related tables
			$sql_from = array();
				
			// Array of "SELECT" sql statements for related tables
			$sql_select = array();
				
			// Array related tables columns names and aliases
			$relations = array();			
			
			// Array of table aliases
			$aliases = array();			
			
			// Array of table relation type
			$types = array();
			
			// Array of columns map
			$map = array();					
			
			// Iterate grouped relations
			foreach ( $db_relations as $parent => $relation )
			{				
				// Iterate table permanent relations
				foreach ( $relation as $r_table => $i )
				{			
					// Array of "SELECT" sql statements for this related tables
					$_sql_select = array();
					
					// Array related tables columns names and aliases for this related tables
					$_relations = array();
					
					// Parent table name
					$r_table_name = isset($i->alias{0}) ? $i->alias : $i->child;
						
					// Define start of join sql statement
					$_sql_from = 'LEFT JOIN `'.$i->child.'`';
					// If relation alias is defined
					if( isset($i->alias{0})) $_sql_from = 'LEFT JOIN `'.$i->child.'` AS '.$i->alias;				
												
					// Parent table name
					$ptable = $i->parent;
						
					// If parent field not specified - use parent table primary field
					if( !isset($i->parent_field) ) $pfield = $primary_field;
					// Correctly determine parent field name
					else
					{
						// Define if parent field name has table name in it
						$tableinpf = strpos( $i->parent_field, '.');
			
						// Get parent table field name
						$pfield = $tableinpf !== false ? substr( $i->parent_field, $tableinpf + 1 ) : $i->parent_field;
			
						// Parent table field
						$ptable = $tableinpf !== false ? dbMySQLConnector::$prefix.substr( $i->parent_field, 0, $tableinpf ) : $i->parent;					
					}
						
					// If no "." symbol in parent field name append parent table name
					$pf = '`'.$ptable.'`.`'.$pfield.'`';
			
					// If child field not specified
                    if( !isset($i->child_field{0})) $cf  =  '`'.(isset($i->alias{0})?$i->alias:$i->child).'`.`'.$pfield.'`';
					// If no "." symbol in child field name append child table name
					else $cf = strpos( $i->child_field, '.') === false ? '`'.(isset($i->alias{0})?$i->alias:$i->child).'`.'.$i->child_field : $i->child_field;
			
					// Build columns metadata
					$this->__build_columns_map( $i->child, $_sql_select, $map, $_relations, $i->alias );				
					
					// Array of "SELECT" sql statements to all related tables
					$sql_select[ $r_table_name ] = implode( ',',$_sql_select);
					$relations[ $r_table_name ] = $_relations;		
					$sql_from[ $r_table_name ] = $_sql_from.' ON '.$pf.' = '.$cf;
					$aliases[ $r_table_name ] = $i->child;
					$types[ $r_table_name ] = $i->type;		
				}	
				
				// Remove prefix 
				$class_name = str_replace( self::$prefix, '', $parent);
				
				// Generate code for this table
				$g->newline()
				->comment('Relation data for table "'.$parent.'"')
				->defarraymerge( $class_name.'::$_sql_from', $sql_from )
				->defarraymerge( $class_name.'::$_sql_select', $sql_select )
				->defarraymerge( $class_name.'::$_map', $map )
				->defvar( $class_name.'::$_relation_alias', $aliases )
				->defvar( $class_name.'::$_relation_type', $types )
				->defvar( $class_name.'::$_relations', $relations );
			}
			
			// Save file to wwwrot
			$g->write( $relations_file );	

			// Evaluate relations code
			eval($g->code);
		} 
		// Or just include file
		else include( $relations_file );	
	}
	
	/**
	 * Generate ORM classes 
	 * @param string $force Force class generation
	 */
	public function generate($force = false, $cachePath = '')
	{		
		// Processed permanent table relations
		$db_relations = array();

        // Create virtual entities
        $virtualTable = new \samson\activerecord\VirtualTable(db()->link, dbMySQLConnector::$prefix.'unitable');

        // Create real db table if not exists
        $virtualTable->create();

       /* // Create test table
        $virtualTable->newTable('pricelist', array(
            array('number', 'column0'),
            array('item', 'column1'),
            array('price', 'column2'),
            array('count', 'column3'),
        ));*/

        // Get all virtual tables structure data
        $db_mapper = array();
        $virtualTable->getStructure($db_mapper);

		// Получим информацию о всех таблицах из БД
		$show_query = mysqli_query($this->link,
         'SELECT `TABLES`.`TABLE_NAME` as `TABLE_NAME`, `COLUMNS`.`COLUMN_NAME` as `Field`,`COLUMNS`.`DATA_TYPE` as `Type`,`COLUMNS`.`IS_NULLABLE` as `Null`,`COLUMNS`.`COLUMN_KEY` as `Key`,`COLUMNS`.`COLUMN_DEFAULT` as `Default`,`COLUMNS`.`EXTRA` as `Extra` FROM `information_schema`.`TABLES` as `TABLES` LEFT JOIN `information_schema`.`COLUMNS` as `COLUMNS` ON `TABLES`.`TABLE_NAME`=`COLUMNS`.`TABLE_NAME` WHERE `TABLES`.`TABLE_SCHEMA`="'.$this->base_name.'" AND `COLUMNS`.`TABLE_SCHEMA`="'.$this->base_name.'"'
		 );
		
		while( $row = mysqli_fetch_array( $show_query, MYSQL_ASSOC ) )
		{ 
			// Получим имя таблицы
			$table_name = $row['TABLE_NAME'];
			
			// Создадим коллекцию для описания структуры таблицы
			if(!isset(self::$tables[ $table_name ])) self::$tables[ $table_name ] = array();
			
			// Удалим имя таблицы из масива
			unset($row['TABLE_NAME']);
			
			// Запишем описание каждой колонки таблиц в специальный массив
			self::$tables[ $table_name ][] = $row;	
		}	
		
		$bstr = md5(serialize(self::$tables));

        //TODO: check if virtual table has not changed and add it to hash
		
		// Создадим имя файла содержащего пути к модулям
		$md5_file = $cachePath.self::CACHE_PATH.'metadata/classes_'.$bstr.'.php';
		$md5_file_func = $cachePath.self::CACHE_PATH.'metadata/func_'.$bstr.'.php';

		// Если еще не создан отпечаток базы данных - создадим его
		if ( !file_exists( $md5_file ) || $force )
		{
			// Get directory path
			$dir = pathname( $md5_file );		
			
			// Create folder
			if( ! file_exists( $dir )) mkdir( $dir, 0777, TRUE );
			//  Clear folder
			else File::clear( $dir );
			
			// Удалим все файлы с расширением map
			//foreach ( \samson\core\File::dir( getcwd(), 'dbs' ) as $file ) unlink( $file );		
		
			// Если еще не создан отпечаток базы данных - создадим его
		
			// Сохраним классы БД
			$db_classes = 'namespace samson\activerecord;';
			
			$db_func = '';
			
			// Создадим классы
			foreach ( $db_mapper as $table_name => $table_data ) 
			{
				$file_full = $this->classes( $table_data, $table_name, $virtualTable->table, $db_relations );
				$db_classes .= $file_full[0];
				$db_func .= $file_full[1];
			}
			
			// Создадим классы
			foreach ( self::$tables as $table_name => $table_data )	
			{
				$file_full = $this->classes( self::$tables[ $table_name ], $table_name, $table_name, $db_relations );
				$db_classes .= $file_full[0];
				$db_func .= $file_full[1];
			}
			
			// Запишем файл для IDE в корень проекта
			file_put_contents( $md5_file, '<?php '.$db_classes.'?>' );
			file_put_contents( $md5_file_func, '<?php '.$db_func.'?>' );
					
			// Подключим наш ХУК для АктивРекорда!!!!!
			eval( $db_classes );	
			eval( $db_func );
		}
		// Иначе просто его подключим 
		else 
		{				
			include($md5_file);	
			include($md5_file_func);
		}
		
		//elapsed('end');
	}
	

	//[PHPCOMPRESSOR(remove,end)]
	
	/**
	 * (non-PHPdoc)
	 * @see idbConnector::connect()
	 */
	public function connect( array $params = NULL )
	{
		//elapsed('Подключение к БД');	

		// Получим необходимые параметры
		$host 	= (!isset($params['host'])) ? 'localhost' : $params['host'];
		$login 	= (!isset($params['login'])) ? 'root' : $params['login'];
		$pwd 	= (!isset($params['pwd'])) ? '' : $params['pwd'];
        $port 	= (!isset($params['port'])) ? '' : ':'.$params['port'];
		
		$this->base_name =(!isset($params['pwd'])) ? '' : $params['pwd'];
		
		// Выполним проверку "обязательного" параметра
		if( ! isset($params['name']) ) return e('Не задано имя БД для подключения', E_USER_ERROR );
		else $this->base_name 	= $params['name'];
		
		// Если мы еще не подключились
		if( ! $this->connected )
		{
			// Установим глобальный флаг что бы мы больше не пытались подключиться
			$this->connected = true;
			
			// Выполнить открытие подключения к БД
			$this->link = mysqli_connect( $host, $login, $pwd, $this->base_name ) or e( mysqli_error( $this->link ), E_SAMSON_SQL_ERROR );

			// Выполнить установку языковых параметров работы с БД
			mysqli_query ($this->link, "set character_set_client='utf8'" );
			mysqli_query ($this->link, "set character_set_results='utf8'" );
			mysqli_query ($this->link, "set collation_connection='utf8_general_ci'");
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see idbConnector::disconnect()
	 */
	public function disconnect( array $params = NULL )
	{
		// Выполним отключение от БД
		mysqli_close( $this->link );
	}
	
	/**	 
	 * @see Cacheable::__destruct()
	 */
	public function __destruct()
	{
		// Выполним отключение от БД
		$this->disconnect();
	}
}
?>