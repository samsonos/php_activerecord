<?php
namespace samson\activerecord;

/**
 * Интерфейс для выполнения "удобных" запросов к базе данных
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */ 
interface idbQuerier
{	
	/**
	 * Выполнить разпознование выражения для выполнения запроса к БД
	 * @param 	mixed $expression Выражение для разпознования
	 * @return 	array Коллекцию QueryGroup для запроса к БД
	 * @see dbQuery
	 */
	public function parse( $expression );
	
	/**
	 * Выполнить "прямой" запрос к БД
	 * @param string $sql Текст "прямого" запроса к БД 
	 * @return dbResult Результат выполнения запроса к БД
	 * @see dbResult 
	 */
	public function plain( $sql );
	
	/**
	 * Получить все записи из БД
	 * @return dbResult Результат выполнения запроса к БД
	 * @see dbResult
	 */
	public function all();
	
	/**
	 * Получить первую запись из БД
	 * @return dbResult Результат выполнения запроса к БД
	 * @see dbResult 
	 */
	public function first();
	
	/**
	 * Получить последнюю запись из БД	 
	 * @return dbResult Результат выполнения запроса к БД
	 * @see dbResult 
	 */
	public function last();
	
	// Следующие функции find(), delete(), update() - Являються "цепированными" и выполнятся
	// только если после них следует функция exec(); Внутри т.н. "цепи запроса" можно использовать
	// допольнительные методы для уточнения( select(), where() ), объединения( join() ), и 
	// упорядочивания( order(), group(), limit() )
	
	/**
	 * Выполнить начало формирования поискового запроса записей в БД
	 * @param dbQuery $query_params Коллекция параметров для поиска
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function find( dbQuery & $query_params );
	
	/**
	 * Выполнить начало формирования запроса на удаление записей в БД
	 * @param array $query_params Коллекция параметров для удаления
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function delete( dbQuery & $query_params );
	
	/**
	 * Выполнить начало формирования запроса на обновление записей в БД
	 * @param array $query_params Коллекция параметров для обновления
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function update( dbQuery & $query_params );	
	
	/**
	 * Выполнить установку параметров ВЫБОРКИ(SELECT) при запросе к БД
	 * @param string $sql Текст "прямого" запроса к БД
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function select( $sql );
	
	/**
	 * Выполнить установку параметров УСЛОВИЯ(WHERE) при запросе к БД
	 * @param string $sql Текст "прямого" запроса к БД
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function where( $sql );

	/**
	 * Выполнить установку параметров ОБЪЕДИНЕНИЯ(JOIN) при запросе к БД
	 * @param array $query_params Коллекция параметров для объединения в запросе
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function join( & $query_params  );	
	
	/**
	 * Выполнить установку параметров СОРТИРОВАНИЯ(ORDER BY) результатов при запросе к БД
	 * @param array $query_params Коллекция параметров для СОРТИРОВАНИЯ в запросе
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function order( & $query_params );
	
	/**
	 * Выполнить установку параметров ГРУППИРОВАНИЯ(GROUP BY) результатов при запросе к БД
	 * @param array $query_params Коллекция параметров для ГРУППИРОВАНИЯ в запросе
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function group( & $query_params );	
	
	/**
	 * Выполнить установку параметров ОГРАНИЧЕНИЯ(LIMIT) результатов при запросе к БД
	 * @param array $query_params Коллекция параметров для ОГРАНИЧЕНИЯ в запросе
	 * @return idbQuerier Данный интерфейс для продолжения "ЦЕПИРОВАНИЯ" запроса к БД
	 */
	public function limit( & $query_params );

	/**
	 * Выполнить завершение формирования запроса к БД
	 * @return dbResult Результат выполнения запроса к БД
	 * @see dbResult
	 */
	public function exec();
}
?>