<?php
namespace samson\activerecord;

use samsonframework\orm\QueryInterface;

/**
 * Class for saving external query handlers
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 2.0
 */
class QueryHandler implements QueryInterface
{
	/** Collection of external query handlers(callbacks) */
	private $handlers = array();
	
	/** Collection of external query handlers(callbacks) additional parameters */
	private $params = array();	
	
	/**
	 * Add extenal query handler
	 * @param callable $callable External handler
	 * @return samson\activerecord\QueryHandler Chaining
	 */
	public function handler( $callable )
	{
		// If normal handler is passed
		if( is_callable( $callable ) )
		{
			// Add handler
			$this->handlers[] = $callable;
	
			// Get passed arguments
			$args = func_get_args();
	
			// Remove first argument
			array_shift( $args );
	
			// Add handler parameters stack
			$this->params[] = & $args;
		}
		else e('Cannot set Query handler - function(##) does not exists', E_SAMSON_ACTIVERECORD_ERROR, $callable );
		 
		return $this;
	}
	
	/** Execute all available external query handlers */
	protected function _callHandlers()
	{
		// Iterate handlers and run them
		foreach ( $this->handlers as $i => $handler )
		{
			// Create handler params array with first parameter pointing to this query object
			$params = array( & $this );
		
			// Combine params with existing ones in one array
			$params = array_merge( $params, $this->params[ $i ] );
		
			// Execute handler
			call_user_func_array( $handler, $params );
		}
	}
}