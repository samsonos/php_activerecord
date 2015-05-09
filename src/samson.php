<?php
namespace samson\activerecord;
use samson\core\CompressableExternalModule;

class ActiveRecordConnector extends CompressableExternalModule
{
	/**
	 * Идентификатор модуля
	 * @var string
	 */
	protected $id = 'activerecord';
	
	/** Database table prefix */
	public $prefix;

	/** Database name */
	public $name;

	/** Login */
	public $login = 'root';

	/** Password */
	public $pwd;

	/** Host */
	public $host = '127.0.0.1';

    /** @var string Port number */
    public $port = '';
	
	/* Array of additional relations to set */
	public $relations  = array();
	
	/** @see \samson\core\CompressableExternalModule::beforeCompress() */
	public function beforeCompress( & $obj = null, array & $code = null )
	{
		
	}	
	
	/** @see \samson\core\CompressableExternalModule::afterCompress() */
	public function afterCompress( & $obj = null, array & $code = null )
	{
		// Iterate through generated php code
        $files = array();
		foreach (\samson\core\File::dir($this->cache_path.'metadata', 'php', '', $files, 1 ) as $file) {

			// No namespace for global function file
			$ns = strpos( $file, 'func') === false ? __NAMESPACE__ : '';			 

			// Compress generated php code
			$obj->compress_php( $file, $this, $code, $ns );		
		}
		
		// Iterate through generated php code
        $files = array();
		foreach (\samson\core\File::dir($this->cache_path.'relations', 'php', '', $files, 1 ) as $file) {
			// No namespace for global function file
			$ns = strpos( $file, 'func') === false ? __NAMESPACE__ : '';
		
			// Compress generated php code
			$obj->compress_php( $file, $this, $code, $ns );
		}
	}
	
	/** @see \samson\core\ExternalModule::prepare() */
	public function prepare()
	{		
		// Set table prefix
		dbMySQLConnector::$prefix = $this->prefix;
		
		// Connect to database
		db()->connect($this->name, $this->login, $this->pwd, $this->host, $this->port);
		
		// Create specific relations
		foreach ( $this->relations as $args )
		{
			switch(sizeof($args)) 
			{
				case 2: new TableRelation($args[0], $args[1]); break;
				case 3: new TableRelation($args[0], $args[1], $args[2]); break;
				case 4: new TableRelation($args[0], $args[1], $args[2], $args[3]); break;
				case 5: new TableRelation($args[0], $args[1], $args[2], $args[3], $args[4]); break;
				case 6: new TableRelation($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;				
			}				
		}
		
		// Generate db table classes
		db()->generate(false, $this->cache_path);
	}

	/** @see \samson\core\ExternalModule::init() */
	public function init( array $params = array() )
	{	
		parent::init( $params );

		// Set table prefix
		dbMySQLConnector::$prefix = $this->prefix;

        db()->connect($this->name, $this->login, $this->pwd, $this->host, $this->port);

		//[PHPCOMPRESSOR(remove,start)]
		// Generate table relations
		db()->relations($this->cache_path);
		//[PHPCOMPRESSOR(remove,end)]
	}
}