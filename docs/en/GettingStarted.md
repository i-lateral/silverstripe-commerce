# Getting Started with Silverstripe Commerce
Silverstripe Commerce is a container for a collection of packages
that allows the construction of complex ecommerce stores using the
Silverstripe Framework and CMS.

## Project structure
Beyond the core Framework and CMS modules, Silverstripe Commerce also
adds several more modules that allows for the running of an ecommerce
store, these are:

- catalogue (adds objects for managing products and categories)
- checkout (adds a shoppingcart, checkout process and payment pages)
- commerce (sudo-dummy package that ties the other modules together and adds new features)
- orders (Adds order and estimate management to the admin and allows generation of invoices and quotes)

The philosphy behind disconnecting these components into seperate modules
(rather than having one "Commerce" modules that does everything) is that
any one of these modules could be used independantly of the others
to allow the development of more bespoke sites (for example you could have a
simpler catalogue site with no onlline payments or a simple invoicing and estimating
system).

## Integration with Silverstripe
Silverstripe Commerce has been built using the core philosophy behind Silverstripe
itself. The module allows interaction with other modules or your own code using
extensions or configuration values and should work with most other modules out
of the box.