<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 27.08.14 at 17:27
 */
//[PHPCOMPRESSOR(remove,start)]
namespace samson\activerecord;

 /**
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version 
 */
class VirtualTable
{
    /** @var \mysqli Resource for database connection */
    protected $link;

    /** @var string Database table name */
    public $table = 'unitable';

    /** @var string Virtual meta-table name */
    public $metaTable = 'headers';

    /** @var int Maximum amount of available virtual table additional fields */
    public $virtualColumnsCount = 20;

    /** @var string General REAL column name to define entity */
    public $mainEntityColumn = 'entity';

    /**
     * Real db table column name which represents entity in meta-table
     * @var string
     */
    protected $entityColumn = 'Column0';

    /**
     * Real db table column name which represents entity column name in meta-table
     * @var string
     */
    protected $fieldColumn = 'Column1';

    /**
     * Real db table column name which represents entity column REAL name in meta-table
     * @var string
     */
    protected $realColumn = 'Column2';

    /**
     * Real db table column name which represents entity column key type in meta-table
     * @var string
     */
    protected $keyColumn = 'Column3';

    /**
     * Real db table column name which represents entity column type in meta-table
     * @var string
     */
    protected $typeColumn = 'Column4';

    /**
     * Collection of common virtual table fields
     * @var array
     */
    protected $defaultColumns = array(
        array('row_id', 'row_id', 'PRI', 'int(255)'),
        array('entity_id', 'entity_id', 'UNI', 'varchar(64)'),
        array('material_id', 'material_id', '', 'int(255)'),
        array('entity', 'entity', '', 'varchar(64)'),
        array('active', 'active', '', 'int(1)', 'DEFAULT "1"'),
        array('created', 'created', '', 'datetime'),
        array('ts', 'ts', '', 'timestamp', 'DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
    );

    /**
     * Constructor
     *
     * @param resource $link      DB connection link resource
     * @param string   $table     DB real table name
     * @param string   $metaTable Meta-table name
     */
    public function __construct(& $link, $table = 'unitable', $metaTable = 'headers')
    {
        // Save pointer to db connection link
        $this->link = & $link;

        // Add global table prefix to table name
        $this->table = $table;

        // Set main meta-table name
        $this->metaTable = $metaTable;
    }

    /**
     * Generic creation of virtual table metadata
     *
     * @param       $entity     Virtual table name
     * @param array $tableData  Virtual table columns structure
     *
     * @internal param array $columnData
     */
    public function newTable($entity, array $tableData)
    {
        // Build db query to create virtual table metadata
        $createSQL = 'INSERT INTO `'.$this->table.'` (
            `'.$this->mainEntityColumn.'`,
            `'.$this->entityColumn.'`,
            `'.$this->fieldColumn.'`,
            `'.$this->realColumn.'`,
            `'.$this->keyColumn.'`,
            `'.$this->typeColumn.'`
        ) VALUES';

        // Will make single query so will gather all columns insertion in one statement
        $valuesSQL = array();

        // Iterate all columns data int table description
        foreach ($tableData as $columnData) {
            // Define default insert field values
            $values = array(
                '"'.$this->metaTable.'"',   // Meta-table name
                '"'.$entity.'"'             // New virtual table name
            );

            // Iterate all passed column data fields
            for($i = 0; $i < 0; $i++) {
                // Safely get column metadata and add to values collection
                $values[] = isset($columnData[$i]) ? '"'.$columnData[$i].'"' : '""';
            }

            // Build db query to create virtual table column metadata
            $valuesSQL[] = '('.implode(',', $values).')';
        }

        // Perform db request for creating virtual table metadata
        mysqli_query($this->link, $createSQL.implode(',', $valuesSQL));
    }

    /**
     * Create real db table for storing virtual tables
     */
    public function create()
    {
        // Generic table creation script
        $createSQL = "CREATE TABLE IF NOT EXISTS `".$this->table."` (";

        // Add default virtual table columns
        foreach($this->defaultColumns as $columnData) {
            $createSQL .='`'.$columnData[0].'` '.$columnData[3].' NOT NULL ';
            $createSQL .= (isset($columnData[4])? $columnData[4] : '').','."\n";
        }

        // Add virtual table possible virtual fields
        for($i=0; $i<$this->virtualColumnsCount; $i++) {
            $createSQL .= '`Column'.$i.'` varchar(255) NOT NULL,'."\n";
        }

        // Finish table creation
        $createSQL .= "PRIMARY KEY (`row_id`),
          KEY `entity` (`entity`),
          KEY `entity_id` (`entity_id`),
          KEY `material_id` (`material_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

        // Perform db request for creating table
        mysqli_query($this->link, $createSQL);
    }

    /**
     * Get virtual tables structure in ActiveRecord compliant way. All virtual
     * table structure is actually stored in another virtual table @see $metaTable
     * add this function actually reads its rows and build other virtual tables
     * structure.
     *
     * This method is optimized to perform only one query to the real db and
     * build all metadata from it results.
     *
     * @param array $structures Collection where all virtual tables structure
     *                          data will be returned
     *
     * @return bool True if at least one virtual table has been found
     */
    public function getStructure(& $structures = array())
    {
        // Build SQL to get meta-table rows
        $sql_result = mysqli_query($this->link, 'SELECT * FROM `'.$this->table.'` WHERE Entity = "'.$this->metaTable.'" AND Active = "1" ORDER BY RowID ASC');
        if (!is_bool($sql_result)) {
            // Load rows from sql response
            while ($row = mysqli_fetch_array($sql_result, MYSQL_ASSOC)) {
                // Get current entity
                $entity = $row[$this->entityColumn];

                // Set pointer to entity metadata
                $metaData = & $structures[$entity];

                // If this is first time we are creating table meta-data for this table
                if (!isset($metaData)) {
                    // Create new entity metadata entry
                    $metaData = array();
                    // Iterate all default virtual table fields
                    foreach ($this->defaultColumns as $columnData) {
                        // Add this fields to table meta-data
                        $metaData[] = array(
                            'Field' 	=> $columnData[0],
                            'Column' 	=> $columnData[1],
                            'Key' 		=> $columnData[2],
                            'Type'		=> $columnData[3]
                        );
                    }
                }

                // Add current entity column metadata
                $metaData[] = array(
                    'Field' 	=> $row[$this->fieldColumn],
                    'Column' 	=> $row[$this->realColumn],
                    'Key' 		=> $row[$this->keyColumn],
                    'Type'		=> $row[$this->typeColumn]
                );
            }
        }

        // Return count of found virtual tables
        return sizeof($structures);
    }
}
//[PHPCOMPRESSOR(remove,end)]
 