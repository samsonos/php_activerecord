<?php
namespace samson\activerecord;

/**
 * Universal class for converting Query parameters to database queries 
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 0.1
 */
class Builder 
{	
	/**
	 * Convert Query object to database query statement
	 * @param Query $query Object to convert from
	 * @return string Resulting database query statement
	 */
	public function convert( Query & $query )
	{
		return '';	
	}
	
	public function convertRelations()
	{
	
	}
	
	public function convertConditions()
	{
		
	}
}