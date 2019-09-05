<?php

/**
 * Simple class to allow upgrading of the database using the SS ORM
 * (rather than the included SQL file).
 *
 * The advantage of this is that we can perform a more complex upgrade
 * that an SQL file can provide.
 */
class CommerceUpgrader extends SS_Object {

    
    /**
     * List of tables to check, we need to check that all old tables 
     * exist, if not, we need to build the database from scratch.
     * 
     * This is pretty basic, but seems to be the simplest way to check. 
     *
     * @var array
     */
    public static $check_tables = array(
        "ProductCategory",
        "ProductCategory_Products",
        "Product_RelatedProducts",
        "Product_Images",
        "CommercePaymentMethod"
    );
    
    /**
     * List of tables to upgrade
     *
     * @var array
     */
    public static $upgrade_tables = array(
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
    public static $upgrade_table_columns = array(
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
     * Check to see if any tables are in need of upgrading
     *
     * @return Boolean
     */
    public static function check() {
        $tables = self::get_curr_tables();
        $tables_to_check = self::$check_tables;
        $checked_tables = 0;

        foreach($tables_to_check as $ch_table) {
            foreach($tables as $ta_key => $ta_value) {                
                if($ta_key == strtolower($ch_table)) {
                    $checked_tables++;
                }
            }
        }
        
        // If we have all tables that we need, we don't need to upgrade
        if($checked_tables == count($tables_to_check))
            return true;
        else
            return false;
    }

}
