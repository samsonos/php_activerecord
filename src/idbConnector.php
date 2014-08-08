<?php 
namespace samson\activerecord;

/**
 * Интерфейс для подключения / отключения к базе данных
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
interface idbConnector
{	
	/**
	 * Подключиться к базе данных
	 * @param 	array 	$params Параметры подключения к БД
	 * @return 	boolean	Результат подключения к БД
	 */
	public function connect( array $params = NULL );
	
	/**
	 * Отключиться от базы данных
	 * @param array $params Параметры подключения к БД
	 * @return 	boolean	Результат отключения от БД
	 */
	public function disconnect( array $params = NULL );
}