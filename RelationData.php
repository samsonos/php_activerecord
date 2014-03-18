<?php
namespace samson\activerecord;

/**
 * Class for relation table/class data storage on joining tables
 * 
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 */
class RelationData 
{
	/** Base class in relation */
	public $base;
	
	/** Relative class in relation */
	public $relation;
	
	/** Real table name name or alias table name in relation */
	public $table;	
	
	/**
	 * Constructor	  
	 * @param string $base_class	 Base class in relation
	 * @param string $table_name	 Name/alias of table in relation
	 * @param string $relation_class Classname that has to be created on joining
	 */
	public function __construct( $base_class, $table_name_simple, $relation_class = null )
	{				
		// If table name passed without namespace consider it as activerecord namespace
		$table_name = strtolower(ns_classname( $table_name_simple, 'samson\activerecord'));
		
		// If relation class not specified
		if( !isset( $relation_class ) )
		{			
			// if there is no class exists for table name specified
			if( !class_exists($table_name) )
			{
				// PHP < 5.3 get relation aliases
				eval('$_relation_alias = '.$base_class.'::$_relation_alias;');
					
				// Try to find classname in relation aliases
                if( isset($_relation_alias[ $table_name_simple ])) $relation_class = ns_classname( $_relation_alias[ $table_name_simple ], __NAMESPACE__);
                else if( isset($_relation_alias[ $table_name ])) $relation_class = ns_classname( $_relation_alias[ $table_name ], __NAMESPACE__);
			}
			// Relation class name equals to table name
			else $relation_class = $table_name;
			
			// Try to find closest parent class to dbRecord class
			$parent_class = get_parent_class( $relation_class );
			if( $parent_class != ns_classname( 'dbRecord', 'samson\activerecord')) $table_name = classname($parent_class);
		}			
			
		// Set defined class fields
		$this->base = $base_class;
		$this->relation = $relation_class;
		$this->table = classname( $table_name );
	
		// TODO: fix this problem
		$this->table = str_replace('samson_activerecord_', '', $this->table);	
	}
}