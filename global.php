<?php

/**
 * DataBase(База данных) - Получить класс для работы с базой данных
 * @param string $link_id Идентификатор подключения к БД
 * @return samson\activerecord\dbMySQL Класс для работы с базой данных
 */
function & db( $link_id = NULL ){ static $_db; return ( $_db = isset($_db) ? $_db : new \samson\activerecord\dbMySQL()); }

/**
 * Шорткат для создания параметризированного запроса
 *
 * @param string 	$class_name Имя класса для которого создается запрос к БД
 * @param mixed		$link		Указатель на экземпляр подключения к БД
 *
 * @return samson\activerecord\dbQuery Объект для формирования запроса к БД
 */
function dbQuery( $class_name, & $link = null ){ return new \samson\activerecord\dbQuery( $class_name, $link ); }