<?php 
namespace samson\activerecord;

/**
 * Интерфейс для работы с отражение записи БД в PHP
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
interface idbRecord
{		
	/**
	 * Выполнить создание записи в БД с привязкой к данному объекту
	 */
	public function create();
	
	/**
	 * Сохранить данные записи в БД
	 */
	public function save();
	
	/**
	 * Удалить запись из БД
	 */
	public function delete();			
}
?>