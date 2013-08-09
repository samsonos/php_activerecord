<?php
namespace samson\activerecord;

class CacheTable implements iCache
{
	/** @see \samson\activerecord\iCache::get() */
	public static function & get( $key )
	{
		$ret_val = null;
		
		// Если ключ записи задан - попытаемся найти запись по ключу
		$sql_result = db()->simple_query('SELECT `value` FROM `'.self::TABLE_NAME.'` WHERE `key`="'.md5($key).'"');
		
		// Если мы успешно выполнили запрос к БД
		if( ! is_bool( $sql_result ) )
		{			
			$row = mysql_fetch_row( $sql_result );
			
			// If we recieved data from db
			if( is_array($row) ) $ret_val = unserialize(base64_decode($row[ 0 ]));	
		}
		
		return $ret_val;
	}
	
	/** @see \samson\activerecord\iCache::ifget() */
	public static function ifget( $key, & $ret_val )
	{
		$ret_val = self::get( $key );	
	
		if( $ret_val === null ) return false;		
		
		return true;
	}
	
	/** @see \samson\activerecord\iCache::set() */
	public static function set( $key, $value )
	{
		// Prepare value for storing
		$value = base64_encode(serialize( $value ));	

		//trace(strlen($value));
		
		// Perform request to db
		if( strlen($value) < 65400 ) mysql_query( 'INSERT INTO `'.self::TABLE_NAME.'` ( `key`, `value` ) VALUES '.	
		'("'.md5($key).'","'.$value.'") ON DUPLICATE KEY UPDATE `value`="'.$value.'"');
		else e('Cannot cache(##) string too long()', E_SAMSON_ACTIVERECORD_ERROR, array( $key, strlen($value) ));
	}
}