<?php
namespace samson\activerecord;

/**
 * Интерфейс для работы с таблицами базы данных которые хранятся в оперативной памяти
 * 
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com> 
 * 
 */
interface idbMemoryTable
{
	/**
	 * Создать КЕШ-Таблицу в БД
	 * Если таблица не существует то создадим её
	 * @return TRUE Если все прошло успешно
	 */
	public function create();
	
	/**
	 * Очистить всю КЕШ-таблицу веб-приложения
	 * @return TRUE Если все прошло успешно
	 */
	public function truncate();
	
	/**
	 * Записать значение в кеш-таблицу
	 *
	 * @param string 	$array_id	Идентификатор массива данных которому принадлежат сохраняемые данные
	 * @param string 	$key		Идентификатор записываемых данных
	 * @param mixed 	$value		Значение для записи в КЕШ
	 * @param string 	$cache_id	Уникальный идентификатор записи в КЕШ-таблице	 
	 * @return boolean TRUE Если все прошло успешно
	 */
	public function set( $array_id, $key, $value, $cache_id = NULL );
	
	/**
	 * Получить значение из кеш-таблицы
	 *
	 * @param string 	$array_id		Идентификатор массива данных которому принадлежат сохраняемые данные
	 * @param string 	$key			Идентификатор записываемых данных
	 * @param string 	$cache_id		Уникальный идентификатор записи в КЕШ-таблице	 
	 * @return mixed Значение полученное из кеш-таблицы, если значение не найдено то возвращается FALSE
	 */
	public function get( $array_id, $key, $cache_id = NULL );
	
	/**
	 * Очистить запись в кеш-таблице
	 * 
	 * @param string $array_id	Идентификатор массива данных которому принадлежат сохраняемые данные
	 * @param string $key		Идентификатор записываемых данных
	 * @return boolean TRUE Если все прошло успешно
	 */
	public function clear( $array_id, $key = NULL );
	
	/**
	 * Очистить все записи КЕШ-таблицы в которых идетификатор массива данных включает в себя переданные данные
	 * @param string $array_id Часть идентификатора для поиска в КЕШ-таблицы
	 * @return boolean TRUE Если все прошло успешно
	 */
	public function clear_like( $array_id );
	
	/**
	 * Определить присутствуют ли хоть какие-то данные для массива по его идентификатору
	 *
	 * @param string $array_id Идентификатор массива в КЕШ-Таблице
	 * @return boolean Есть такой массив с данными в КЕШ-Таблице
	 */
	public function exists( $array_id );
	
	/**
	 * Получить коллекцию данных из КЕШ-Таблицы по его идентификатору массива
	 *
	 * @param string 	$array_id 		Идентификатор массива в КЕШ-Таблице	 
	 * @return array Массив вида ключ => значение
	 */
	public function & collection( $array_id );
}
?>