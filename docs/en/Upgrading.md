Upgrading from the old Commerce Module
======================================

NOTE: There is now an upgrade script (1.0-2.0.sql) located in the "sql"
directory that makes upgrading the database a lot less painfull!

The module also tries to accomodate auto updating by trying to auto
run the script when dev/build is run.

If you have been using the old commerce module, there are a lof of
small changes in 2.0 that need to be considered. Mostly around the
database.

To start with, these will be added here, in future we will try and
write a migration script to simplify this process.

## Table names

These tables will be need to be renamed in order to retain old data:

### Categories
* ProductCategory > CatalogueCategory
* ProductCategory_Products > CatalogueCategory_Products

## Products
* Product > CatalogueProduct
* Product_RelatedProducts > CatalogueProduct_RelatedProducts
* Product_Images > CatalogueProduct_Images

## Payment Methods
* CommercePaymentMethod > PaymentMethod 

## Column names

The following column names will need to also be changed

### CatalogueCategory_Products (formerly ProductCategory_Products)

* ProductCategoryID > CatalogueCategoryID
* ProductID > CatalogueProductID

### CatalogueProduct (formerly Product)

* Description > Content
* SKU > StockID
* Price > BasePrice

### CatalogueProduct_Images (formerly Product_Images)

* ProductID > CatalogueProductID

### CatalogueProduct_RelatedProducts (formerly Product_RelatedProducts)

* ProductID > CatalogueProductID

### OrderItem

* SKU > StockID
