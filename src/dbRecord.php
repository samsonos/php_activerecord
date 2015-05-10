<?php
namespace samson\activerecord;

use samsonframework\orm\Record;

/**
 * Отражение записи БД в PHP
 * @author Vitaly Iegorov <vitalyiegorov@gmail.com>
 * @author Nikita Kotenko <nick.w2r@gmail.com>
 *
 */
class dbRecord extends Record
{
    public function __construct($id = false, $className = null, $database = null)
    {
        parent::__construct($database);
    }
}
