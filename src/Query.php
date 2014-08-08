<?php
namespace samson\activerecord;
use samson\core\Module;

/**
 * Universal class for creating database queries 
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 2.0
 */
class Query extends QueryHandler
{	
	/** Class name for interacting with database */
	protected $class_name;		
	
	/** 
	 * Collection of query parameters objects
	 * @see \samson\activerecord\QueryParams 
	 */
	protected $parameters = array();

    public function joins($targetClass, $parentField = null, $childField = null, $relationType = TableRelation::T_ONE_TO_ONE, $alias = null)
    {
        return $this;
    }
	
	
	/**
	 * Reset all query parameters
	 * @return \samson\activerecord\Query Chaining
	 */
	public function flush(){ foreach ($this->parameters as $param) $param->flush(); return $this; }
		
	/**
	 * Perform database request and get collection of database record objects 
	 * @see \samson\activerecord\Query::execute()
	 * @param mixed $return External variable to store query results
	 * @return mixed If no arguments passed returns query results collection, otherwise query success status
	 */
	public function exec( & $return = null){ $args = func_num_args(); return $this->execute( $return, $args ); }	
	
	/**
	 * Perform database request and get first record from results collection 
	 * @see \samson\activerecord\Query::execute()
	 * @param mixed $return External variable to store query results	 
	 * @return mixed If no arguments passed returns query results first database record object, otherwise query success status
	 */
	public function first( & $return = null ){ $args = func_num_args(); return $this->execute( $return, $args, 1 ); }	
	
	/**
	 * Perform database request and get array of record field values
	 * @see \samson\activerecord\Query::execute()
	 * @param string $field_name 	Record field name to get value from 
	 * @param string $return		External variable to store query results	
	 * @return Ambigous <boolean, NULL, mixed>
	 */
	public function fields( $field_name, & $return = null ){ $args = func_num_args() - 1; return $this->execute( $return, $args, null, array( $this, '_toFieldArray'), array($field_name) );  }

    // TODO: Comment? WTF?
    public function fieldsNew( $field_name, & $return = null ){
        $args = func_num_args() - 1;
        // Perform DB request
        $return = db()->findFields( $this->class_name, $this,  $field_name);

        $success = is_array( $return ) && sizeof($return);

        // If parent function has arguments - consider them as return value and return request status
        if( $args > 0 ) return $success;
        // Parent function has no arguments, return request result
        else return $return;
    }

    /**
	 * Perform database request and return different results depending on function arguments.	 
	 * @see \samson\activerecord\Record
	 * @param array 	$result 		External variable to store dabatase request results collection
	 * @param integer 	$r_type			Amount of arguments passed to parent function
	 * @param integer 	$limit			Quantity of records to return
	 * @param callable	$handler 		External callable handler for results modification
	 * @param array		$handler_ags 	External callable handler arguments
	 * @return boolean/array Boolean if $r_type > 0, otherwise array of request results 
	 */
	protected function & execute( & $result = null, $r_type = false, $limit = null, $handler = null, $handler_args = array() )
	{
		// Call handlers stack
		$this->_callHandlers();

		// Perform DB request
		$result = db()->find( $this->class_name, $this );

		// If external result handler is passed - use it
		if( isset( $handler ) )
		{ 
			// Add results collection to array
			array_unshift( $handler_args, $result );
			
			// Call external handler with parameters
			$result = call_user_func_array( $handler, $handler_args );
		}
		
		// Clear this query
		$this->flush();
		
		// Count records
		$count = sizeof( $result );

		// Define is request was successful
		$success = is_array( $result ) && $count;

		// Is amount of records is specified
		if( isset( $limit ) )
		{	
			// If we have not enought records - return null
			if( $count < $limit ) $result = null;				
			// If we need first record
			else if( $limit === 1 ) $result = array_shift($result);
			// Slice array for nessesar amount
			else if( $limit > 1 ) $result = array_slice( $result, 0, $limit );			
		}	
		
		// If parent function has arguments - consider them as return value and return request status
		if( $r_type > 0 ) return $success;
		// Parent function has no arguments, return request result
		else return $result;
	}
	
	/**
	 * Convert records array to array of record field values
	 * @param array 	$records	Records collection
	 * @param string 	$field_name	Record field name to collect value from
	 * @return array Collection of record field values
	 */
	protected function _toFieldArray( array $records, $field_name )
	{
		$return = array();
		foreach ( $records as $record ) $return[] =  $record->$field_name;
		return $return;
	}
}