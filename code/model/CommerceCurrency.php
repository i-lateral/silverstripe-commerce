<?php
/**
 * Description of Currency
 *
 * @author morven
 */
class CommerceCurrency extends DataObject {
    private static $db = array(
        'Title' => 'Varchar',
        'HTMLNotation' => 'Varchar(10)',
        'GatewayCode' => 'Varchar'
    );

    private static $summary_fields = array(
        'Title',
        'HTMLNotation',
        'GatewayCode'
    );

    private static $field_labels = array(
        'Title' => 'Name of Currency',
        'HTMLNotation'  => 'Symbol to appear in HTML',
        'GatewayCode'  => 'Currency code for payment gateway'
    );

    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        if(!CommerceCurrency::get()->exists()) {
            $gbp = new CommerceCurrency();
            $gbp->Title = "UK Pounds";
            $gbp->HTMLNotation = "&pound;";
            $gbp->GatewayCode = "GBP";
            $gbp->write();
            $gbp->flushCache();
            DB::alteration_message('UK Pounds created', 'created');

            $eur = new CommerceCurrency();
            $eur->Title = "Euro";
            $eur->HTMLNotation = "&euro;";
            $eur->GatewayCode = "EUR";
            $eur->write();
            $eur->flushCache();
            DB::alteration_message('Euro created', 'created');

            $usd = new CommerceCurrency();
            $usd->Title = "US Dollars";
            $usd->HTMLNotation = "&#36;";
            $usd->GatewayCode = "USD";
            $usd->write();
            $usd->flushCache();
            DB::alteration_message('US Dollars created', 'created');
        }
    }

    public function canCreate($member = null) {
        return true;
    }

    public function canEdit($member = null) {
        return true;
    }

    public function canDelete($member = null) {
        return true;
    }
}
