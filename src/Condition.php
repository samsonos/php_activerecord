<?php
namespace samson\activerecord;

/**
 * Universal class for storing query condition groups and arguments
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 2.0
 */
class Condition
{
	/** AND(conjunction) - Condition relation type */
	const REL_AND = 'AND';
	
	/** OR(disjunction) - Condition relation type */
	const REL_OR = 'OR';
	
	/** 
	 * Arguments collection 
	 * @see \samson\activerecord\Argument
	 */
	public $arguments = array();
	
	/** Arguments relation */
	public $relation = self::REL_AND;	
	
	/**
	 * Generic condition addiction function
	 * @param \samson\activerecord\Condition|\samson\activerecord\Argument|string 	$argument Entity for adding to arguments collection	 
	 * @param string 	$value		Argument value 
	 * @param string 	$relation	Relation between argument and value
     * @return $this Chaining
	 */
	public function add( $argument, $value = '', $relation = dbRelation::EQUAL )
	{
		// If query Condition object is passed
		if( is_a( $argument, ns_classname('Condition','samson\activerecord')))
		{
			// Add condition as current condition argument
			$this->arguments[] = $argument;			
		}
		// If query Argument object is passed
		else if( is_a( $argument, ns_classname('Argument','samson\activerecord')))
		{
			// Add argument to arguments collection
			$this->arguments[] = $argument;	
		}
		// If string - consider it as argument field name
		else if( is_string($argument) )
		{
			// Add new argument to arguments collection
			$this->arguments[] = new Argument( $argument, $value, $relation );
		}

        return $this;
	}
	
	/**
	 * Construcor
	 * @param string $relation Relation type beetween arguments
	 */
	public function __construct( $relation = NULL ){ if( isset($relation) ) $this->relation = $relation; }
		
}