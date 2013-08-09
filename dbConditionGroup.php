<?php 
namespace samson\activerecord;

/**
 * Условная группа аргументов в условии запроса к БД
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbConditionGroup
{
	/**
	 * Коллекция аргументов условия группы
	 * @var array
	 * @see dbConditionArgument
	 */
	public $arguments = array();
	
	/**
	 * Отношение между аргументами в условной группе
	 * @var string
	 */
	public $relation = 'AND';	
	
	/**
	 * Конструктор
	 * 
	 * @param string $relation Условное отношение данной группы к другим группам	
	 */
	public function __construct( $relation = NULL ){ if( isset($relation) ) $this->relation = $relation; }
}
?>