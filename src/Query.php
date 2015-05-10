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

    public function joins(
        $targetClass,
        $parentField = null,
        $childField = null,
        $relationType = TableRelation::T_ONE_TO_ONE,
        $alias = null
    ) {
        return $this;
    }


    /**
     * Reset all query parameters
     * @return \samson\activerecord\Query Chaining
     */
    public function flush()
    {
        foreach ($this->parameters as $param) {
            $param->flush();
        }
        return $this;
    }

    /**
     * Perform database request and get collection of database record objects
     * @see \samson\activerecord\Query::execute()
     * @param mixed $return External variable to store query results
     * @return mixed If no arguments passed returns query results collection, otherwise query success status
     */
    public function exec(& $return = null)
    {
        $args = func_num_args();
        return $this->execute($return, $args);
    }

    /**
     * Perform database request and get first record from results collection
     * @see \samson\activerecord\Query::execute()
     * @param mixed $return External variable to store query results
     * @return mixed If no arguments passed returns query results first database record object,
     * otherwise query success status
     */
    public function first(& $return = null)
    {
        $args = func_num_args();
        return $this->execute($return, $args, 1);
    }

    /**
     * Perform database request and get array of record field values
     * @see \samson\activerecord\Query::execute()
     * @param string $fieldName Record field name to get value from
     * @param string $return External variable to store query results
     * @return Ambigous <boolean, NULL, mixed>
     */
    public function fields($fieldName, & $return = null)
    {
        // Call handlers stack
        $this->_callHandlers();

        // Perform DB request
        $return = db()->fetchColumn($this->class_name, $this, $fieldName);

        $success = is_array($return) && sizeof($return);

        // If parent function has arguments - consider them as return value and return request status
        if (func_num_args() - 1 > 0) {
            return $success;
        } else { // Parent function has no arguments, return request result
            return $return;
        }
    }

    /** @deprecated Use self::fields() */
    public function fieldsNew($fieldName, & $return = null)
    {
        return call_user_func_array(array($this, 'fields'), func_get_args());
    }

    /**
     * Perform database request and return different results depending on function arguments.
     * @see \samson\activerecord\Record
     * @param array $result External variable to store dabatase request results collection
     * @param integer|bool $rType Amount of arguments passed to parent function
     * @param integer $limit Quantity of records to return
     * @param callable $handler External callable handler for results modification
     * @param array $handlerArgs External callable handler arguments
     * @return boolean/array Boolean if $r_type > 0, otherwise array of request results
     */
    protected function & execute(
        & $result = null,
        $rType = false,
        $limit = null,
        $handler = null,
        $handlerArgs = array()
    ) {
        // Call handlers stack
        $this->_callHandlers();

        // Perform DB request
        $result = db()->find($this->class_name, $this);

        // If external result handler is passed - use it
        if (isset($handler)) {
            // Add results collection to array
            array_unshift($handlerArgs, $result);

            // Call external handler with parameters
            $result = call_user_func_array($handler, $handlerArgs);
        }

        // Clear this query
        $this->flush();

        // Count records
        $count = sizeof($result);

        // Define is request was successful
        $success = is_array($result) && $count;

        // Is amount of records is specified
        if (isset($limit)) {
            // If we have not enought records - return null
            if ($count < $limit) {
                $result = null;
            } elseif ($limit === 1) { // If we need first record
                $result = array_shift($result);
            } elseif ($limit > 1) { // Slice array for nessesar amount
                $result = array_slice($result, 0, $limit);
            }
        }

        // If parent function has arguments - consider them as return value and return request status
        if ($rType > 0) {
            return $success;
        } else { // Parent function has no arguments, return request result
            return $result;
        }
    }
}
