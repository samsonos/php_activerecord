<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 04.08.14 at 16:42
 */
class DBMainTest extends PHPUnit_Framework_TestCase
{
    /** @var Database Pointer to db interface object */
    public $db;

    public function setUp()
    {
        \samson\core\Error::$OUTPUT = false;
    }

    public function testSpeed()
    {

    }
}
