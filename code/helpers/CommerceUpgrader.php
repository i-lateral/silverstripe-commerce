<?php

/**
 * Simple class to allow upgrading of the database using the SS ORM
 * (rather than the included SQL file).
 *
 * The advantage of this is that we can perform a more complex upgrade
 * that an SQL file can provide.
 */
class CommerceUpgrader extends Object {

    /**
     * List of tables to upgrade
     *
     * @var array
     */
    protected static $upgrade_tables = array(
        "ProductCategory" => "CatalogueCategory",
        "ProductCategory_Products" => "CatalogueCategory_Products",
        "Product" => "CatalogueProduct",
        "Product_RelatedProducts" => "CatalogueProduct_RelatedProducts",
        "Product_Images" => "CatalogueProduct_Images",
        "CommercePaymentMethod" => "PaymentMethod"
    );

    /**
     * List of table alterations
     *
     * @var array
     */
    protected static $upgrade_table_columns = array(
        "CatalogueCategory_Products" => array(
            "ProductCategoryID" => "CatalogueCategoryID",
            "ProductID" => "CatalogueProductID"
        ),
        "CatalogueProduct" => array(
            "Description" => "Content",
            "StockID" => "_obsolete_StockID",
            "SKU" => "StockID",
            "Price" => "BasePrice"
        ),
        "CatalogueProduct_Images" => array(
            "ProductID" => "CatalogueProductID"
        ),
        "CatalogueProduct_RelatedProducts" => array(
            "ProductID" => "CatalogueProductID"
        ),
        "OrderItem" => array(
            "SKU" => "StockID"
        )
    );

    /**
     * Cache existing table names to reduce queries
     *
     * @var array
     */
    protected static $curr_tables;

    public static function get_curr_tables() {
        // make sure we cache the current tables to reduce query
        if(!self::$curr_tables) {
            $curr_tables = DB::tableList();
            self::set_curr_tables($curr_tables);
        } else
            $curr_tables = self::$curr_tables;

        return $curr_tables;
    }

    public static function set_curr_tables($tables) {
        self::$curr_tables = $tables;
    }


    /**
     * Check to see if any upgrade tables are in need of upgrading
     *
     * @return Boolean
     */
    public static function check() {
        $tables = self::get_curr_tables();

        foreach(self::$upgrade_tables as $up_key => $up_value) {
            foreach($tables as $ta_key => $ta_value) {
                if($ta_key == "commercepaymentmethod") {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Use the ORM to upgrade the database
     *
     * @return Boolean
     */
    public static function upgrade() {
        $tables = self::get_curr_tables();
        $conn = DB::getConn();
        $conn_type = get_class($conn);

        // First loop through existing tables and rename if needed
        foreach(self::$upgrade_tables as $up_key => $up_value) {
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
        foreach(self::$upgrade_table_columns as $table => $columns) {
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
                    }
                }
            }
        }

        // Stall while we wait for connection to finish
        do {
            $i = 0;
        } while($conn->isSchemaUpdating());

        // Set new tables list so future checks don't fall over
        self::$curr_tables = DB::tableList();

        return true;
    }

}
