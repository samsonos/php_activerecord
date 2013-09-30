<?php
namespace samson\activerecord;

/**
 * Виды отношения между аргументами условных груп условия запроса к БД
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 *
 */
class dbRelation
{
	const EQUAL 		= '=';
	const NOT_EQUAL 	= '!=';
	const GREATER 		= '>';
	const LOWER 		= '<';
	const GREATER_EQ 	= '>=';
	const LOWER_EQ 		= '<=';
	const LIKE 			= ' LIKE ';
	const NOTNULL 		= ' IS NOT NULL ';
	const ISNULL 		= ' IS NULL ';
	const OWN 			= ' !!! ';
}