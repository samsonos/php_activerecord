<?php
namespace samson\activerecord;

/**
 * Интерфейс для формирования универсального запроса к БД
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com> 
 *
 */
interface idbQuery
{	
	/**
	 * Распознать и выполнить запрос к БД
	 * @param mixed 	$argument 	Текст для разпознования запроса или готовй объект-запрос
	 * @param array 	$params 	Коллекция параметров запроса
	 * @return idbQuery Созданный объект-запрос
	 */
	public function parse( $argument, array $params = NULL );
	
	/**
	 * Добавить новую группу условий в запрос и установить её текущей
	 * для добавления условий
	 *
	 * @param string 	$relation 	Условное отношение данной группы к другим группам
	 * @return idbQuery Указатель на самого себя для цепирования	 
	 */
	public function or_( $relation = 'OR' );
	
	/**
	 * Добавить новое условие в текущую группу условний запроса
	 *
	 * @param string 	$attribute 	Имя атрибута в условии запроса
	 * @param mixed 	$value		Значение атрибута в условии запроса
	 * @return idbQuery Указатель на самого себя для цепирования	 
	 */
	public function cond( $attribute, $value = null, $relation = dbRelation::EQUAL );

    /**
     * Join table to query
     * @param string $table_name    Real table name from DB without prefix
     * @param string $class_name    Class name for creating instances
     * @param bool  $ignore         Flag for NOT creating instances of joining class
     *
     * @return dbQuery Chaining
     */
    public function join( $table_name, $class_name = null, $ignore = false );
	
	/**
	 * Добавить новое виртуальное поле в запрос
	 *
	 * @param string 	$field 	Содержание виртуального поля
	 * @param mixed 		$alias	Значение псевдонима для виртульного поля
	 * @return idbQuery Указатель на самого себя для цепирования
	 */
	public function add_field( $field, $alias = NULL );
	
	/**
	 * Добавить счетчик количества записей и выполнить запрос на его получение
	 * @return integer Количество записей в запросе
	 */
	public function count();
	
	/**
	 * Добавить счетчик количества записей и выполнить запрос на его получение
	 * @return integer Количество записей в запросе
	 */
	public function innerCount();
	
	/**
	 * Сбросить текущие параметры запроса для экземпляра класса 
	 * и перевести объект запроса в исходное состояние
	 */
	public function flush();
	
	/**
	 * Execute database request with random result ordering
	 * 
	 * @param mixed $return_value Variable for returning request results
	 * @return boolean|array If $return_value is passed then method return boolean otherwise results array
	 */
	public function random( & $return_value = null);
	
	/**
	 * Execute database request  and gather array of specified field values
	 * @param string $field_name Field name to gather data
	 * @return boolean|array If $return_value is passed then method return boolean otherwise results array
	 */
	public function fields( $field_name, & $return_value = null );
	
	
	/**
	 * Execute database request
	 * 
	 * @param mixed $return_value Variable for returning request results
	 * @return boolean|array If $return_value is passed then method return boolean otherwise results array
	 */
	public function & exec( & $return_value = null);
	
	/**
	 * Execute database request and get first record from results 
	 * 
	 * @param mixed $return_value Variable for returning request results
	 * @return boolean|samson\activerecord\dbRecord If $return_value is passed then method 
	 * return boolean otherwise first record from request results
	 */
	public function & first( & $return_value = null );
}