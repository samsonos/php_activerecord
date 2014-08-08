<?php
namespace samson\activerecord;

/**
 * Interface for working with cacheable data
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 */
interface iCache
{	
	/** Table name */
	const TABLE_NAME = 'cache';
	
	/**
	 * Retrieve cache entry
	 * 
	 * @param string $key Hash key for searching
	 * @return Object Retrieved object from cache
	 */
	public static function & get( $key );
	
	/**
	 * Retrieve cache entry and places it in ret_val
	 *
	 * @param string $key Hash key for searching
	 * @return boolean Key exists in cache
	 */
	public static function ifget( $key, & $ret_val );
	
	/**
	 * Save cache entry
	 * 
	 * @param string 	$key Hash key 
	 * @param mixed 	$value Object to be cached 	 
	 */
	public static function set( $key, $value );	
}