<?php
/**
 * Order objects track all the details of an order and if they were completed or
 * not.
 *
 * @author morven
 */
class Order extends DataObject {
    public static $db = array(
        'OrderNumber'       => 'Varchar',
        'BillingFirstnames' => 'Varchar',
        'BillingSurname'    => 'Varchar',
        'BillingAddress1'   => 'Varchar',
        'BillingAddress2'   => 'Varchar',
        'BillingCity'       => 'Varchar',
        'BillingPostCode'   => 'Varchar',
        'BillingCountry'    => 'Varchar',
        'BillingEmail'      => 'Varchar',
        'BillingPhone'      => 'Varchar',
        'DeliveryFirstnames'=> 'Varchar',
        'DeliverySurname'   => 'Varchar',
        'DeliveryAddress1'  => 'Varchar',
        'DeliveryAddress2'  => 'Varchar',
        'DeliveryCity'      => 'Varchar',
        'DeliveryPostCode'  => 'Varchar',
        'DeliveryCountry'   => 'Varchar',
        'EmailDispatchSent' => 'Boolean',
        'Status'            => "Enum('incomplete,failed,paid,processing,dispatched','incomplete')"
    );
    
    public static $has_one = array(
        'Postage' => 'PostageArea'
    );
    
    public static $has_many = array(
        'Items' => 'OrderItem'
    );
    
    // Cast method calls nicely 
    public static $casting = array(
        'BillingAddress'    => 'Text',
        'DeliveryAddress'   => 'Text',
        'PostageCost'       => 'Decimal',
        'SubTotal'          => 'Currency',
        'OrderTotal'        => 'Currency'
    );
    
    public static $defaults = array(
        'EmailDispatchSent' => 0
    );
    
    public static $summary_fields = array(
        'OrderNumber'       => 'Order #',
        'BillingFirstnames' => 'First Name',
        'BillingSurname'    => 'Surname',
        'BillingAddress'    => 'Billing Address',
        'DeliveryAddress'   => 'Delivery Address',
        'BillingEmail'      => 'Email',
        'Status'            => 'Status'
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        // Structure order details
        $fields->addFieldToTab('Root.Billing', new TextField('BillingFirstnames', 'First Name(s)'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingSurname', 'Surname'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingAddress1', 'Address 1'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingAddress2', 'Address 2'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingCity', 'City'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingPostCode', 'Post Code'));
        $fields->addFieldToTab('Root.Billing', new TextField('BillingCountry', 'Country'));
        
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryFirstnames', 'First Name(s)'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliverySurname', 'Surname'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryAddress1', 'Address 1'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryAddress2', 'Address 2'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryCity', 'City'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryPostCode', 'Post Code'));
        $fields->addFieldToTab('Root.Delivery', new TextField('DeliveryCountry', 'Country'));
        
        return $fields;
    }

    public function canCreate($member = null) {
        return false;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
    
    public function getPostageCost() {
        return $this->Postage()->Cost;
    }
    
    public function getBillingAddress() {
        $address = ($this->BillingAddress1) ? $this->BillingAddress1 . ",\n" : '';
        $address .= ($this->BillingAddress2) ? $this->BillingAddress2 . ",\n" : '';
        $address .= ($this->BillingCity) ? $this->BillingCity . ",\n" : '';
        $address .= ($this->BillingPostCode) ? $this->BillingPostCode . ",\n" : '';
        $address .= ($this->BillingCountry) ? $this->BillingCountry : '';
        
        return $address;
    }
    
    public function getDeliveryAddress() {
        $address = ($this->DeliveryAddress1) ? $this->DeliveryAddress1 . ",\n" : '';
        $address .= ($this->DeliveryAddress2) ? $this->DeliveryAddress2 . ",\n" : '';
        $address .= ($this->DeliveryCity) ? $this->DeliveryCity . ",\n" : '';
        $address .= ($this->DeliveryPostCode) ? $this->DeliveryPostCode . ",\n" : '';
        $address .= ($this->DeliveryCountry) ? $this->DeliveryCountry : '';
        
        return $address;
    }
    
    public function getOrderTotal() {
        $total = $this->SubTotal;
        
        // Add postage
        if(is_int((int)Session::get('PostageID')) && (int)Session::get('PostageID') > 0)
            $total += DataObject::get_by_id('PostageArea', Session::get('PostageID'))->Cost;
        
        return number_format($total,2);
    }
    
    public function getSubTotal() {
        $total = 0;
        
        // Calculate total from items in the list
        foreach($this->Items() as $order_item) {
            $total += $order_item->getTotal();
        }
        
        return $total;
    }
    
    public function onAfterWrite() {
        parent::onAfterWrite();
        
        // Deal with sending the status email
        if(($this->Status == 'dispatched') && !($this->EmailDispatchSent)) {
            $siteconfig = SiteConfig::current_site_config();
            
            $body = "
Thank you for ordering from armydogtags.co.uk. Your order ({$this->OrderNumber})
has been received and will be despatched to the following address:

{$this->DeliveryAddress1},
{$this->DeliveryAddress2},
{$this->DeliveryCity},
{$this->DeliveryPostCode},
{$this->DeliveryCountry}

If you have any queries, please contact us by:

Phone: {$siteconfig->ContactPhone}
Email: {$siteconfig->ContactEmail}

Please check your tags carefully when they arrive, and contact us as soon as
possible if there are any problems.

Many Thanks,

Rhodri
{$siteconfig->Title}

Visit our facebook page: www.facebook.com/armydogtags
            ";
            
            $email = new Email(
                $siteconfig->EmailFrom,
                $this->BillingEmail,
                "Order {$this->OrderNumber} dispatched",
                $body);
                
            $email->sendPlain();
            
            $this->EmailDispatchSent = 1;
            $this->write();
        }
    }
    
    protected function onAfterDelete() {
        parent::onAfterDelete();
        
        foreach ($this->Items() as $item) {
            $item->delete();
        }
    }
}
