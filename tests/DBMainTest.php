<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 04.08.14 at 16:42
 */
class DBMainTest extends PHPUnit_Framework_TestCase
{
    /** @var array Collection of parameters to connect to db */
    public $connection = array(
        'name'  => 'landscape-test',
        'login' => 'root',
        'pwd'   => 'vovan123',
        'host'  => '192.168.88.99',
    );

    /** @var dbMysql Pointer to db interface object */
    public $db;

    public function testSpeed()
    {
        // Pointer to database interface
        $this->db = & db();

        // Connect to database
        $this->db->connect($this->connection);

        $this->assertEquals(0, 0);
    }
}
