<?php

/**
 * Perform a database upgrade from the old (v1.0) commerce module to the
 * new (v2.0) module.
 *
 * @author ilateral (http://www.ilateral.co.uk)
 * @package commerce
 * @subpackage tasks
 */
class CommerceUpgrade1To2Task extends BuildTask {

    protected $title = 'Upgrade commerce module from v1.0 to v2.0';

    protected $description = 'Run an upgrade to the database to allow the new version of the commerce module (2.0) to work.';
    
    /**
     * Run an upgrade and go through any tables that need upgrading
     *
     */
    public function run($request) {
        
        if(CommerceUpgrader::check()) {
            echo "<p>Upgrading...\n\n</p>";

            $tables = CommerceUpgrader::get_curr_tables();
            $conn = DB::getConn();
            $conn_type = get_class($conn);

            // First loop through existing tables and rename if needed
            foreach(CommerceUpgrader::$upgrade_tables as $up_key => $up_value) {
                foreach($tables as $ta_key => $ta_value) {
                    // If we have an old table, rename
                    if(strtolower($up_key) == $ta_key) {
                        $conn->renameTable($up_key, $up_value);
                    }
                }
            }

            // Stall while we wait for connection to finish
            do {
                $i = 0;
            } while($conn->isSchemaUpdating());


            // Re connect to make changes
            $conn = DB::getConn();

            // Check if there are any columns that need altering
            foreach(CommerceUpgrader::$upgrade_table_columns as $table => $columns) {
                foreach($conn->fieldList($table) as $field => $options) {
                    foreach($columns as $old_col => $new_col) {
                        // Check if there is a column that needs upgrading
                        // and generate upgrade statement.
                        if(strtolower($field) == strtolower($old_col)) {
                            $sql = "";

                            switch ($conn_type) {
                                case "MySQLDatabase":
                                    $sql = "ALTER TABLE `{$table}` CHANGE `{$old_col}` `{$new_col}` {$options}";
                                    break;
                                case "PostgreSQLDatabase":
                                    $pos_table = strtolower($table);
                                    $sql = "ALTER TABLE `{$pos_table}` RENAME COLUMN `{$old_col}` `{$new_col}`";
                                    break;
                                case "MSSQLDatabase":
                                    $sql = "EXEC sp_RENAME '{$table}.{$old_col}' , '{$new_col}', 'COLUMN'";
                                    break;
                            }

                            if($sql) $conn->query($sql);
                            
                            echo "<p>Updated table {$field}</p>\n\n";
                        }
                    }
                }
            }

            // Stall while we wait for connection to finish
            do {
                $i = 0;
            } while($conn->isSchemaUpdating());

            // Set new tables list so future checks don't fall over
            CommerceUpgrader::set_curr_tables(DB::tableList());

            echo "<p>Upgrade complete!</p>\n\n";
        } else
            echo "<p>No upgrade needed</p>";
    }

}
