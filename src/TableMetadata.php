<?php
namespace samson\activerecord;

/**
 * Base table metadata class
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
class TableMetadata
{
	/** Database table name */
	public $table_name;
	
	/** Database table primary field */
	public $primary_field;
	
	/** Collection of database table columns */
	public $columns;
	
	/** Collection of database UNIQUE table columns */
	public $unique_columns;
	
	/** Collection of database INDEXED table columns */
	public $index_columns;
	
	/** Collection of database table columns types */
	public $column_types;

	/** Collection of SELECT statements */
	public $select;
	
	/** Collection of FROM statements */
	public $from;
	
	/** Collection object fields */
	public $fields;
}