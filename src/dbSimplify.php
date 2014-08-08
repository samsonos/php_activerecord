<?php
namespace samson\activerecord;

/**
 * Класс для упрощения работы с Базой Данных(БД)
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 *
 */
class dbSimplify
{	
	/**
	 * Выполнить удобный запрос к БД с проверкой результата его выполнения.
	 * Удобство заключается в возможности использывания данного метода сразу
	 * в условном операторе IF, с получением значения в установлденную переменную
	 * 
	 * Для удобство было принято решение возвращать в переданную переменную
	 * первый полученный элемент из БД.
	 * 
	 * Аргументом для $query_result принято использовать динамический запрос
	 * вида:
	 * 	ИМЯ_КЛАССА()->find[_all]_by_FIELDS...( NAMES... )
	 * 
	 * А это функция помогает определить результат выполнение такого запроса
	 * и вернуть в определенную переменную $r_object нужный объект
	 * 
	 * Если необходимо получить весь массив полученных от БД элементов то
	 * для это существует специальный параметр $return_array
	 * 
	 * @param array $query_result 	Результат запроса к БД
	 * @param mixed $r_object 		Переменная для возврата полученного от БД значения
	 * @param boolean $return_array Флаг принудительного возвращения всего массива результатов 
	 * 								запроса к БД а не первого элемента
	 * @return boolean Результат выполнения запроса к БД, если получен хотя бы один элемент
	 * 					TRUE / FALSE
	 */
	public static function query( $query_result, & $r_object = NULL, $return_array = FALSE )
	{					
		// Проверим полученный результат запроса к БД 
		// В идеале он должен вернуть массив полученных объектов		
		if( is_array( $query_result ) && sizeof( $query_result ) > 0 )
		{		
			// Если необходимо принудительно вернуть весь массив результатов
			if( $return_array ) $r_object = $query_result;  	
			// Иначе вернем только первый элемент из выборки
			else $r_object = $query_result[0];
			// Скажем что все прошло ОК
			return TRUE;
		}	
		
		// Принудительно опустошим возвращаемый объект
		$r_object = NULL;
		
		// Дошли сюда значит запрос не выполнился
		return FALSE;
	}
	
	/**
	 * Выполнить преобразование массива записей полученных из БД
	 * в коллекцию значений одно из полей каждой из полученых записей
	 * например необходимо получить все ID полученных записей в отдельную
	 * коллекцию для подстановки этой коллекции в другой запрос
	 *
	 * @param array 	$collection	Коллекция записей полученых из БД
	 * @param string 	$field_name	Имя поля по котороу заполнится новая коллекция
	 * @return array Коллекция значений заданого поля из каждой записи полученной из БД
	 */
	public static function implode( array $collection, $field_name )
	{
		// Результирующая коллекция
		$result = array();
		
		// Если нам есть что перебирать
		if( sizeof( $collection )  > 0 )
		{				
			// Переберем полученную коллекциб записей		
			foreach ( $collection as & $record ) 
			{			
				// Добавим значение поля записи в новую коллекцию
				$result[] = $record[ $field_name ];
				/*if( isset( $record[ $field_name ] ) )
				else e('Object ## doesn\'t have field ##', E_SAMSON_ACTIVERECORD_ERROR, array( get_class($record), $field_name));
				*/
			}
		}
		
		// Вернем результат
		return $result;
	}
	
	/**
	 * Функция для упрощения разпознования аргумента
	 * и попытки его получения из указанной сущности БД.
	 * 
	 * Аргументом функции может быть значение любого поля
	 * сущности из БД либо сама запись для её проверки
	 * 
	 * @param string 	$entity_name 	Имя сущности в БД
	 * @param mixed 	$argument		Аргумент для разпазнавания сущности
	 * @param mixed 	$r_object		Возвращаемое значение куда попадет результат работы функции
	 * @param string 	$field_name		Имя поля сущности для поиска в БД
	 * @return boolean TRUE / FALSE - Удалось ли получить объект из указанной сущности БД
	 * @deprecated
	 */
	public static function parse( $entity_name, $argument, & $r_object = NULL, $field_name = 'id' )
	{		
		// Если аргумент для разпознования это строка или число
		if( is_string( $argument ) || is_numeric( $argument ) )
		{					
			// Сформируем правильное имя класса
			$entity_name = ns_classname( $entity_name,'samson\activerecord');

			// Если мы успешно получили схему данных
			if( class_exists( $entity_name ) )
			{ 			
				// Получим данные таблицы
				$vars = db()->__get_table_data( $entity_name );				
				
				// Подставим имя ключевого поля сущности
				$field_name = ( $field_name == 'id' ) ? $vars['_primary'] : $field_name;
				
				// Создаем группу условий запроса
				$query = dbQuery( $entity_name )->cond( $field_name, $argument );	
			
				// Если в схеме есть поле Active, то будем искать только актуальные записи
				if ( isset( $vars['_attributes'][ 'Active' ] ) ) $query->cond( 'Active', 1 );	
				
				// Выполняем запрос к БД, и сохраняем результат в возвращаемый объект
				$r_object = db()->find( $entity_name, $query );	
				
				// Сохраняем для возврата первый полученный объект от БД
				if( ($r_object !== FALSE) && ( sizeof( $r_object ) > 0 ) ) 
				{
					// Установим указатель на первый объект
					$r_object = $r_object[ 0 ];
					
					// Скажем что все ок =)
					return TRUE;
				}	
			}	
			// Ничего не вышло
			else return FALSE;			
		}
		// Если нам передана ГОТОВАЯ запись из БД
		else if( is_object( $argument ) )
		{	
			/*
			// Проверим класс проверяемого объекта
			if( ('\\'.strtolower(get_class( $argument ))) != strtolower($entity_name) ) 
			{
				return e( 'Разпознования аргумента для БД: Объект::## не совпадает с требуемым(##)', E_SAMSON_ACTIVERECORD_ERROR, array( get_class( $argument ), $entity_name ) );			
			}*/
			
			// Подставим эту запись в возвращаемое значение
			$r_object = $argument;		
	
			// Скажем что все ок =)
			return TRUE;
		}
		
		// Принудительно опустошим возвращаемый объект
		$r_object = NULL;
		
		// Ничего не вышло =(
		return FALSE;		
	}
	
	/**
	 * Сохранить объект в БД использую данные из переданной коллекции	 
	 * 
	 * @param string 	$class_name	Класс сохраняемого объекта
	 * @param mixed 	$db_object	ИД объекта или сам объект
	 * @param mixed 	$_data		Коллекция значений полей объекта
	 * @return boolean	Результат выполнения сохранения
	 */
	public static function save( $class_name, & $db_object = NULL, array $_data = NULL )
	{	
		//trace($db_object->id);		
		// Сформируем правильное имя класса
		$class_name = ns_classname($class_name,'samson\activerecord');		
		
		// Получим переменные для запроса
		extract(db()->__get_table_data( $class_name ));		
		
		// Если переда объект - то проверим его тип
		if( is_object( $db_object ) && strtolower(get_class( $db_object )) == strtolower($class_name) ) ;
		// Если передан идентификатор сущности в БД - попытаемся получить саму запись из БД
		else if( isset($_data[ $_primary ]))
		{
			// Получим идентификатор объекта
			$id = $_data[ $_primary ];
			
			// Если этот объект уже есть в локальном кеше
			if( isset(dbRecord::$instances[ $class_name ][ $id ] ) ) $db_object = dbRecord::$instances[ $class_name ][ $id ];		
			// Иначе создадим экземпляр
			else $db_object = new $class_name( $id );		
		}
		// Если мы не получили объект-запись из БД
		else $db_object = new $class_name( FALSE );

		// Переберем коллекцию полученных атрибутов записи
		// Если в схемы данных существует атрибут с указанным ключом - запишем 
		// его значение в объект, убрав крайние пробелы из него
		if( isset($_data) ) foreach ( $_data as $k => $v )
		{			
			// Ключевое поле не установлено
			if (( $k != $_primary )&&(!is_array($v)) ) $db_object->$k = trim( $v );
		}
		
		// Cохраним объект в БД
		$db_object->save();		

		// Все прошло ОК
		return TRUE;
	}
	
	/**
	 * Универсальный метод для удаления записи в БД
	 * Удаления происходит помечанием записи как не активная ("Active" = 0)
	 * Если передан произвольный обработчик сохранения - он будет выполнен в случаи 
	 * успешного удаления записи с переданным указаетелем на эту запись
	 * 
	 * @param string 	$class_name Имя класса БД для выполнения удаления
	 * @param mixed 	$db_prt		Указатель на запись БД для удаления
	 * @param mixed 	$_data		Коллекция значений полей объекта
	 * @return boolean Результат удаления записи
	 */
	public static function delete( $class_name, $db_object = NULL, & $_data = NULL )
	{	
		// Безопасно получим запись из БД
		if ( dbSimplify::parse( $class_name, $db_object, $db_object ) )
		{
			// Пометим запись как удаленную
			$db_object->Active = 0;
		
			// Сохраним изменения в БД
			$db_object->save();
				
			// Все прошло ок
			return TRUE;
		}
		
		// Дошли сюда - были ошибки
		return FALSE;
	}
	
	/**
	* Универсальный метод для получения массива из записей БД
	* Ключем массива является поля Primary в таблице
	*
	* @param string $class_name 	Имя класса БД для построения массива
	* @param mixed 	$name_attr		Имя поля объекта БД отвечающего за его представление
	* @return array Результирующий массив
	*/
	public static function toarray($class_name, $name_attr = 'Name', $select_param = array())
	{
		// Сформируем список данных
		$data = array( );
		
		if ( class_exists( $class_name ) )
		{
			// Работаем с активаными записями(не удаленными)
			$query = dbQuery( $value )->cond( 'Active', 1 );
		
			// Добавим дополнительные параметры выборки
			if (sizeof($select_param) > 0) foreach ($select_param as $k=>$v) $query->cond($k, $v);
		
			// Выполним запрос на получение записей из БД
			if( dbSimplify::query( $query->exec(), $db_objs, TRUE ) )
			{
				// Переберем полученный объекты из БД
				foreach ($db_objs as $item) $data[$item->id] = $item->$name_attr;
			}
		}
		
		return $data;
	}
}
?>