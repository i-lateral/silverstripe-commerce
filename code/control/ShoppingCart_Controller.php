<?php
/**
 * Description of ShoppingCart_Controller
 *
 * @author morven
 */
class ShoppingCart_Controller extends Commerce_Controller {
    public static $url_segment = 'commerce/cart';

    private static $allowed_actions = array(
        'add',
        'remove',
        'empty',
        'clear',
        'update',
        "CartForm"
    );

    public function init() {
        parent::init();
    }

    public function index() {
        $cart_copy = (SiteConfig::current_site_config()->CartCopy) ? SiteConfig::current_site_config()->CartCopy : '';

        $this->customise(array(
            'ClassName' => "ShoppingCart",
            'Title'     => _t('Commerce.CARTNAME', 'Shopping Cart'),
            'MetaTitle' => _t('Commerce.CARTNAME', 'Shopping Cart'),
            'Content'   => $cart_copy
        ));

        return $this->renderWith(array(
            'Commerce_shoppingcart',
            'Commerce',
            'Page'
        ));
    }

    /**
     * Remove a product from ShoppingCart Via its ID.
     *
     * @param ID product ID
     */
    public function remove() {
        $key = $this->request->param('ID');

        if(!empty($key)) {
            $cart = ShoppingCart::get();
            $cart->remove($key);
            $cart->save();
        }

        return $this->redirectBack();
    }

    public function CartForm() {
        return Commerce_ShoppingCartForm::create($this, 'CartForm')
            ->addExtraClass('forms');
    }
}
