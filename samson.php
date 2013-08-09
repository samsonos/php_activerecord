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

	/**
	 * Коллекция связей модуля	 
	 * @var array
	 */
	protected $requirements = array();
	
	public $name;
	public $login;
	public $pwd;
	public $host = '127.0.0.1';
	
	/** @see \samson\core\CompressableExternalModule::beforeCompress() */
	public function beforeCompress( & $obj = null, array & $code = null )
	{
		
	}	
	
	/** @see \samson\core\CompressableExternalModule::afterCompress() */
	public function afterCompress( & $obj = null, array & $code = null )
	{	
		// Iterate throug generated php code
		foreach (\samson\core\File::dir( __SAMSON_CWD__, 'dbs', '', $r, 1 ) as $file)
		{	
			// No namespace for global function file
			$ns = strpos( $file, 'func') === false ? __NAMESPACE__ : '';			 
				
			// Compress generated php code
			$obj->compress_php( $file, $this, $code, $ns );
		}		
	}
	
	/** @see \samson\core\ExternalModule::prepare() */
	public function prepare()
	{
		// Connect to database
		db()->connect(array(
			'name' => $this->name,
			'login'=> $this->login,
			'pwd' =>  $this->pwd,
			'host' =>  $this->host
		));		
	}

	/** @see \samson\core\ExternalModule::init() */
	public function init( array $params = array() )
	{	
		parent::init( $params );

		//[PHPCOMPRESSOR(remove,start)]
		db()->generate();
		//[PHPCOMPRESSOR(remove,end)]
		
		// Connect to database
		db()->connect(array(
			'name' => $this->name, 
			'login'=> $this->login, 
			'pwd' =>  $this->pwd,
			'host' =>  $this->host
		));			
	}
}