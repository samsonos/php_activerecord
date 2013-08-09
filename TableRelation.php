<?php
namespace samson\activerecord;

/**
 * Class for defining permanent relations beetween database tables
 * @author Vitaly Iegorov <egorov@samsonos.com>
 *
 */
class TableRelation 
{
	/** Relation type: one-to-many */
	const T_ONE_TO_MANY = 1;
	/** Relation type: one-to-one */
	const T_ONE_TO_ONE = 0;
	
	/**	Collection of permanent instances of class */
	public static $instances = array();
	
	/** Parent table in relation */
	public $parent;
	
	/** Child table in relation */
	public $child;
	
	/** Parent table field name in relation */
	public $parent_field;
	
	/** Child table field name in relation */
	public $child_field;
	
	/** Alias for JOIN in relation */
	public $alias;
	
	/** Relation type */
	public $type;
	
	/**
	 * Constructor 
	 * 
	 * @param string 	$p		Parent table name
	 * @param string 	$c		Child table name
	 * @param string 	$pf		Parent field name
	 * @param integer 	$rt		Relation type 
	 * @param string 	$cf		Child field name
	 * @param string 	$a		Alias	 
	 */
	public function __construct( $p, $c, $pf = null,  $rt = self::T_ONE_TO_ONE, $cf = null, $a = null )
	{
		// Set table relation parameters 
		$this->parent = $p;
		$this->child = $c; 
		$this->alias = $a;
		$this->type = $rt;		
		$this->parent_field = $pf;
		$this->child_field = $cf;
		
		/*
		// Get primary field
		eval('$primary = '.$p.'::$_primary;');
		
		// If no primary field is specified - set parent primary field
		if( !isset($pf) ) $this->parent_field = $p.'.'.$primary;
		
		// If no child field is specified - set parent primary field
		if( !isset($cf) ) $this->child_field = $c.'.'.$primary;		
		*/
		
		// Save instance to static array
		self::$instances[] = & $this;
	}
}