Upgrading from the old Commerce Module
======================================

If you have been using the old commerce module, there are a lof of
small changes in 2.0 that need to be considered. Mostly around the
database.

To start with, these will be added here, in future we will try and
write a migration script to simplify this process.

## Table names

These tables will be need to be renamed in order to retain old data:

### Categories
* CommerceCategory > CatalogueCategory
* CommerceCategory_Products > CatalogueCategory_Products

## Products
* CommerceProduct > CatalogueProduct
* CommerceProduct_RelatedProducts > CatalogueProduct_RelatedProducts
* CommerceProduct_Images > CatalogueProduct_Images

## Payment Methods
* CommercePaymentMethod > PaymentMethod 

## Column names

The following column names will need to also be changed

### CatalogueCategory_Products (formerly CommerceCategory_Products)

* CommerceCategoryID > CatalogueCategoryID
* CommerceProductID > CatalogueProductID

### CatalogueProduct (formerly CommerceProduct)

* Description > Content
* SKU > StockID

### CatalogueProduct_Images (formerly CommerceProduct_Images)

* CommerceProductID > CatalogueProductID

### CatalogueProduct_RelatedProducts (formerly CommerceProduct_RelatedProducts)

* CommerceProductID > CatalogueProductID

### OrderItem

* SKU > StockID
