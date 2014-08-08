<?php
namespace samson\activerecord;

/**
 * Universal class defining database table query parameters 
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 0.1
 */
class QueryParams
{	
	/** Table name */
	protected $classname;
	
	/** Query amount limitation parameter */	
	protected $limit;
	
	/** Collection of query grouping parameters */
	protected $group = array();
	
	/** 
	 * Collection of all query conditions parameters 
	 * @see \samson\activerecord\Condition
	 */	
	protected $conditions  = array() ;
	
	/** 
	 * Current query condition group to work with in chain calls
	 * @var \samson\activerecord\Condition
	 * @see \samson\activerecord\Condition
	 */
	protected $condition;
	
	/** Collection of query relations parameters */
	protected $relations = array();
	
	/** Collection of query virtual fields */
	protected $fields = array();
	

	/**
	 * Reset all query parameters
	 * @return \samson\activerecord\Query Chaining
	 */
	public function flush()
	{
		$this->limit = null;
		$this->group = array();
		$this->conditions = array();
		$this->relations = array();
		$this->fields = array();
		
		// Create new condition for query
		$this->condition = new Condition();
		
		// Add new condition to conditions collection
		$this->conditions[] = & $this->condition;
		
		return $this; 
	}
	
	/**
	 * Set query limitation parameter
	 * @param $count Maximal amount of records in result collection
	 * @param $offset Offset size from result collection start
	 * @return \samson\activerecord\Query Chaining
	 */
	public function limit( $count, $offset = 0 ){ $this->limit = array( $offset, $count ); }
	
	/**
	 * Add query grouping parameter
	 * @param $field Ffield name for grouping
	 * @param $direction Grouping ordering direction
	 * @return \samson\activerecord\Query Chaining
	 */
	public function group( $field, $direction  = 'ASC' ){ $this->group[] = array( $field, $direction ); }
	
	/**
	 * Add query grouping parameter
	 * @param $field Ffield name for grouping
	 * @param $direction Grouping ordering direction
	 * @return \samson\activerecord\Query Chaining
	 */
	public function condition( $argument, $value = '', $relation = dbRelation::EQUAL ){	$this->condition->add( $argument, $value, $relation ); }
	
	public function relation(){}
	
	/**
	 * Constructor
	 * @param string $classname Table name
	 */
	public function __construct( $classname )
	{
		$this->classname = $classname; 
		
		// Reset all query parameters
		$this->flush();
	}
}