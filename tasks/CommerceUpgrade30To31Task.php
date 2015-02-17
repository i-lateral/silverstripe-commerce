<?php
/**
 * Perform a complex upgrade from the Legacy Silverstripe 3.0 commerce
 * module to the new 3.1 based commerce module
 *
 * @package commerce
 * @subpackage tasks
 */
class CommerceUpgrade30To31Task extends BuildTask {

    protected $title = 'Upgrade commerce module from SS3.0 to SS3.1';

    protected $description = 'Run an upgrade to a site commerce module running on Silverstripe 3.0 to Silverstripe 3.1';

    public function run($request) {
        echo "Upgrading commerce module...\n\n";

        $orders_table = DB::query("SELECT * FROM `Order`");

        // First deal with postage mapings (we remove this in favour of copying postage details)
        $postage_exists = DB::query("SHOW COLUMNS FROM `Order` LIKE 'PostageID'")->value();

        if($postage_exists) {
            // Check correct coluns exist, create if not
            $col_type = DB::query("SHOW COLUMNS FROM `Order` LIKE 'PostageType'")->value();
            $col_cost = DB::query("SHOW COLUMNS FROM `Order` LIKE 'PostageCost'")->value();
            $col_tax = DB::query("SHOW COLUMNS FROM `Order` LIKE 'PostageTax'")->value();

            if(!$col_type) {
                DB::query("ALTER TABLE `Order` ADD `PostageType` varchar(50)");
                echo "Created 'PostageType' column\n\n";
            }

            if(!$col_cost) {
                DB::query("ALTER TABLE `Order` ADD `PostageCost` decimal");
                echo "Created 'PostageCost' column\n\n";
            }

            if(!$col_tax) {
                DB::query("ALTER TABLE `Order` ADD `PostageTax` decimal");
                echo "Created 'PostageTax' column\n\n";
            }

            // Find all postage areas and setup
            $postage_areas = DB::query("SELECT * FROM `PostageArea`");

            // Loop postage areas and update all orders with RAW SQL
            foreach($postage_areas as $postage) {
                $location = $postage['Location'];
                $cost = $postage['Cost'];
                $id = $postage['ID'];

                $update = "UPDATE  `Order` SET ";
                $update .= "`Order`.`PostageType` = '{$location}', ";
                $update .= "`Order`.`PostageCost` = '{$cost}', ";
                $update .= "`Order`.`PostageTax` = 0 ";
                $update .= "WHERE `Order`.`PostageID` = {$id};";

                DB::query($update);

                echo "Updated orders with postage for {$location}\n\n";
            }

            // Finally remove postage ID col in orders and versions
            DB::query("ALTER TABLE `Order` DROP PostageID");
            DB::query("ALTER TABLE `Order_versions` DROP PostageID");

            echo "Dropped postage ID columns\n\n";
        }

        // Now check if our old billing columns are set, if so, rename
        $altersql = "ALTER TABLE `Order` ";
        $altersql .= "CHANGE BillingFirstnames FirstName varchar(50), ";
        $altersql .= "CHANGE BillingSurname Surname varchar(50), ";
        $altersql .= "CHANGE BillingAddress1 Address1 varchar(50), ";
        $altersql .= "CHANGE BillingAddress2 Address2 varchar(50), ";
        $altersql .= "CHANGE BillingCity City varchar(50), ";
        $altersql .= "CHANGE BillingPostCode PostCode varchar(50), ";
        $altersql .= "CHANGE BillingCountry Country varchar(50), ";
        $altersql .= "CHANGE BillingEmail Email varchar(50), ";
        $altersql .= "CHANGE BillingPhone PhoneNumber varchar(50);";

        DB::query($altersql);

        echo "Renamed order billing columns\n\n";

        echo "Upgrade complete!\n\n";
    }

}
