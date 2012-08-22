<?php
/**
 * Description of Currency
 *
 * @author morven
 */
class CommerceCurrency extends DataObject {
    public static $db = array(
        'Title' => 'Varchar',
        'HTMLNotation' => 'Varchar(10)',
        'GatewayCode' => 'Varchar'
    );
    
    public static $summary_fields = array(
        'Title',
        'HTMLNotation',
        'GatewayCode'
    );
    
    public static $field_labels = array(
        'Title' => 'Name of Currency',
        'HTMLNotation'  => 'Symbol to appear in HTML',
        'GatewayCode'  => 'Currency code for payment gateway'
    );
    
    public function requireDefaultRecords() {
        if(!Subsite::get_from_all_subsites('CommerceCurrency')) {
            $gbp = new CommerceCurrency();
            $gbp->Title = "Pounds";
            $gbp->HTMLNotation = "&pound;";
            $gbp->GatewayCode = "GBP";
            
            $eur = new CommerceCurrency();
            $eur->Title = "Euro";
            $eur->HTMLNotation = "&euro;";
            $eur->GatewayCode = "EUR";
            
            $usd = new CommerceCurrency();
            $usd->Title = "Dollars";
            $usd->HTMLNotation = "&#36;";
            $usd->GatewayCode = "USD";
        }
        
        parent::requireDefaultRecords();
    }
}