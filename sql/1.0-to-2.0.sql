-- Silverstripe Commerce
-- Database upgrade script
-- Version 1.0 to 2.0
--
-- This is intended for MYSQL 5.0+ and has not been tested in any other
-- language
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


--
-- Rename tables
--

RENAME TABLE
ProductCategory TO CatalogueCategory,
ProductCategory_Products TO CatalogueCategory_Products,
Product TO CatalogueProduct,
Product_RelatedProducts TO CatalogueProduct_RelatedProducts,
Product_Images TO CatalogueProduct_Images,
CommercePaymentMethod TO PaymentMethod;

--
-- Run a test to ensure no REALLY old columns still exist
--

DROP PROCEDURE IF EXISTS schema_change;
  
delimiter ;;
CREATE PROCEDURE schema_change() BEGIN
  IF EXISTS (SELECT * FROM information_schema.columns WHERE table_name = 'CatalogueProduct' AND column_name = 'StockID') THEN
    ALTER TABLE CatalogueProduct DROP COLUMN `StockID`;
  END IF;
END;;
delimiter ;

CALL schema_change();

DROP PROCEDURE IF EXISTS schema_change;

--
-- Rename ProductCategory_Products Columns
--

ALTER TABLE `CatalogueCategory_Products`
CHANGE `ProductCategoryID` `CatalogueCategoryID`
INT(11) NOT NULL DEFAULT '0',
CHANGE `ProductID` `CatalogueProductID`
INT(11) NOT NULL DEFAULT '0';


--
-- Rename CatalogueProduct Columns
--

ALTER TABLE `CatalogueProduct`
CHANGE `Description` `Content`
MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `SKU` `StockID`
VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `Price` `BasePrice`
DECIMAL(9,3) NOT NULL DEFAULT '0.00';


--
-- Rename CatalogueProduct_Images Columns
--

ALTER TABLE `CatalogueProduct_Images`
CHANGE `ProductID` `CatalogueProductID`
INT(11) NOT NULL DEFAULT '0';


--
-- Rename CatalogueProduct_RelatedProducts Columns
--

ALTER TABLE `CatalogueProduct_RelatedProducts`
CHANGE `ProductID` `CatalogueProductID`
INT(11) NOT NULL DEFAULT '0';


--
-- Rename OrderItem Columns
--

ALTER TABLE `OrderItem`
CHANGE `SKU` `StockID`
VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
