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
	 * Установить специальный режим работы - "Маппинг"
	 * При таком режиме работы БД вся информация о таблицах и все
	 * данные этих таблиц физически размещаются в одной таблице БД,
	 * но для разработчика этот факт остается прозрачным а все подмены
	 * выполняются на уровне ActiveRecord.
	 * Такой подход дает универсальность в обработке данных таких таблиц
	 * и экономит огрмное количество кода и веремени при создании/управлении
	 * различными сущностями.
	 *
	 * @param string $mapper_table Имя таблицы в БД, которая хранит все данные
	 * @param string $mapper_id Идентификатор сущности описывающий структуры таблиц
	 */
	public function mapper( $mapper_table = 'scmstable', $mapper_id='Headers' );
	
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