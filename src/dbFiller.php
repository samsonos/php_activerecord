<?php
namespace samson\activerecord;

/**
 * Класс для автоматического наполнения содержимым базы данных
 * и его валидации
 * 
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @package SamsonActiveRecord
 * @version 0.2
 * 
 */
class dbFiller
{
	/**
	 * Заполнить и проверить на личие таблицу данных, переданной эталонной
	 * коллекцией строк
	 * 
	 * @param string 	$class_name 	Имя класса для работы с данными
	 * @param array 	$standart_rows 	Эталонная коллекция строк данных
	 */
	public static function fill( $class_name, array & $standart_rows )
	{		
		// Получим параметры таблицы данных
		$class_data = db()->__get_table_data( $class_name );
			
		// Получим все строки данных из БД
		$db_rows = dbQuery( $class_name )->all();	
			
		// Флаг обновления БД
		$db_needs_update = false;
			
		// Переберем эталонные строки данных
		foreach ( $standart_rows as $standart_row )
		{
			// СФормируем уникальное представление эталонной строки
			$key = implode( '', $standart_row );
		
			// Флаг нахождения необходимой строки
			$found = false;
		
			// Переберем строки полученные из БД
			foreach ( $db_rows as $db_row )
			{
				// Сюда соберем все значения колонок таблицы для формирования уникального ключа
				$u_key = '';
					
				// Переберем аттрибуты строки, сформируем уникальный ключ - ключевое поле пропускаем
				foreach ( $class_data['_attributes'] as $a ) if( $a != $class_data['_primary'] ) $u_key .= $db_row[ $a ];
		
				// Попытаемся найти строку в эталонной коллекции
				if( $key === $u_key ){$found = true; break;	}
			}
		
			// Если мы не нашли эталонную строку - добавим её
			if( ! $found )
			{
				// Создадим новую строку в БД
				$r = new $class_name();
					
				// Счетчик номера поля
				$idx = 0;
					
				// Флаг обновления БД
				$db_needs_update = true;
					
				// Переберем аттрибуты строки, Ключевое поле пропускаем, заполняем атрибут
				foreach ( $class_data['_attributes'] as $a ) if( $a != $class_data['_primary'] ) $r->$a = $standart_row[ $idx++ ];
		
				// Запишем строку в БД
				$r->save();
			}
		
			// Удалим файл
			//if( $db_needs_update ) die('Структура таблицы БД была изменена, удалите _db_classes.php');
		}
	}
}
?>