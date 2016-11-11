# Commerce Module stock keeping
Silverstripe Commerce has stock keeping enabled by default which
allows tracking of the stock levels of products and automatically
decreasing these levels when items are purchased.

## Changing the stock levels
To change stock levels log into the admin interface (http://www.yourwebsite.com/admin)
then visit:

Catalogue > Products > Product to change stock levels > Settings

Change the "Stock" field and then save the product.

## Disabling Stock keeping
Stock levels can be disabled via Silverstripe configuration, this has
to be done in two places, the Commerce config class and the ShoppingCart
class.

You can do this in the config.yml by adding:

```
Commerce:
  allow_negative_stock: false
  add_out_of_stock: true
ShoppingCart:
  check_stock_levels: false
```

This disables reduction of stock below zero automatically and also
stops the shopping cart checking stock levels before adding. 