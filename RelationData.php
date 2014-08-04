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


    /** Full table name as stored in database(with prefix) */
    public $realTableName;

    /** Short table name as stored everywhere in code (without prefix) */
    public $virtualTableName;

    /** Alias table name */
    public $aliasTableName;

    /** Class name for creating table instances */
    public $className;

    /** Flag for ignoring this class, and do not create instances of this class */
    public $ignore = false;
	
	/**
	 * Constructor	  
	 * @param string  $base_class	    Base class in relation
	 * @param string  $table_name	    Name/alias of table in relation
	 * @param string  $relation_class   Classname that has to be created on joining
     * @param boolean $ignore           Flag for not creating object instances for this class
	 */
	public function __construct( $base_class, $table_name_simple, $relation_class = null, $ignore = false )
	{				
		// If table name passed without namespace consider it as activerecord namespace
		$table_name = strtolower(ns_classname( $table_name_simple, 'samson\activerecord'));
		
		// If relation class not specified
		if( !isset( $relation_class ) )
		{			
			// if there is no class exists for table name specified
			if( !class_exists($table_name, false) )
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
        $this->ignore = $ignore;
	
		// TODO: fix this problem
		$this->table = str_replace('samson_activerecord_', '', $this->table);	
	}
}