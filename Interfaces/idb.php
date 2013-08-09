<?php
namespace samson\activerecord;

/**
 * Главный интерфейс для работы с Базой Данных(БД)
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 *
 */
interface idb
{		
	/**
	 * Выполнить самый низко-уровневый запрос к БД
	 * Запрос не выполняет фиксирование профайлинга и не анализирует/обрабатывает полученные результаты
	 * 
	 * @param string $sql SQL комманда для запроса к БД
	 * @return mixed Результат выполнения запроса к БД
	 */
	public function & simple_query( $sql );
	
	/**
	 * Выполнить запрос к БД
	 * @param string $sql 		Строка с SQL - кодом запроса
	 * @return array Коллекцию-ответ от сервера
	 */
	public function & query( $sql );
	
	/**
	 * Создание новой записи в БД
	 * Передача объекта предназначена для создание сложных записей
	 * в БД, напр. имеющих уникальные поля или сложные ключи. В таком
	 * случаи создается "пустой" объект без привязки к БД, далее все 
	 * "специфические" поля такого объекта заполняются в ручную,
	 * а только потом вызывается привязка к БД, и тогда при создании
	 * новой записи в БД учитываются эти "сложные" значения для полей
	 * и запись успешно создается.
	 * 
	 * Пока ничего другого не придумали =(
	 * 
	 * @param string 	$class_name Имя класса для запроса
	 * @param idbRecord $object 	Если объект еще не привязан к БД то заполним запись по его значениям
	 * @return string Идентификатор созданной записи / (FALSE) 0
	 */
	public function create( $class_name, idbRecord & $object = NULL );
	
	/**
	 * Выполнить обнолвение существующей записи в БД
	 * 
 	 * @param string 	$class_name Имя класса для запроса
	 * @param idbRecord $object 	Объект запись которого необходимо обновить
	 * @return boolean TRUE / FALSE
	 */
	public function update( $class_name, idbRecord & $object );
	
	/**
	 * Поиск записей в БД	
	 * 
	 * @see idbRecord
	 * @param string 	$class_name Имя класса для запроса
	 * @param dbQuery 	$query 		Универсальный объект - запрос
	 * @return array Коллекцию записей полученых из БД
	 */
	public function & find( $class_name, dbQuery $query );
	
	/**
	 * Найти запись в БД по идентификатору. 
	 * Этот метод по своему назначению полностью может выполнятся
	 * через но он используется чаще всего для негонеобходимо 
	 * отдельное оптимизированное описание в виде прямого запроса 
	 * к БД без всяких "костылей"
	 * 
	 * @param string 	$class_name Имя класса для запроса
	 * @param string 	$id			Идентификатор записи в БД
	 * @return array  Коллекцию записей полученых из БД
	 * @see idbRecord
	 */
	public function & find_by_id( $class_name, $id );
	
	/**
	 * Удалить конкретную запись из БД.
	 * Этот метод вызывается крайне часто объект @see idbRecord
	 * имеет прямое удаление самого себя
	 *  
	 * @param string 	$class_name Имя класса для запроса
	 * @param idbRecord $object 	Объект запись которого необходимо обновить
	 * @return boolean TRUE / FALSE
	 */
	public function delete( $class_name, idbRecord & $object );	
	
	/**
	 * Получить статистические данные по скорости работы и количеству запросов
	 * @return array Массив результатов работы с БД ( к-во запросов, затраченное время )
	 */
	public function profiler();	
}