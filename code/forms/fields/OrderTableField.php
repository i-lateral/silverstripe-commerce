<?php
/**
 * OrderTableField lists all orders and allows you to export them in various
 * formats.
 *
 * @author morven
 */
class OrderTableField extends TableListField {
    protected $template = "OrderTableField";
    
    public function __construct($name, $sourceClass, $fieldList = null, $sourceFilter = null, $sourceSort = null, $sourceJoin = null) {
        $this->Markable = true;
        $this->setPageSize(75);
		
        // Note: These keys have special behaviour associated through TableListField.js
        $this->selectOptions = array(
            'all' => _t('OrderTableField.SELECTALL', 'All'),
            'none' => _t('OrderTableField.SELECTNONE', 'None')
        );
        
        parent::__construct($name, $sourceClass, $fieldList, $sourceFilter, $sourceSort, $sourceJoin);
    }
    
    public function FieldHolder() {
        return parent::FieldHolder();
    }


    private function TagPrinterLink() {
        $exportLink = Controller::join_links($this->Link(), 'tagprinter');
        return $exportLink;
        
    }
    
    private function DispatchedLink() {
        $exportLink = Controller::join_links($this->Link(), 'mark_dispatched');
        return $exportLink;
        
    }
    
    public function mark_dispatched() {
        $record_ids = explode(',', $_GET['records']);
        
        foreach($record_ids as $ID) {
            if($ID) {
                $record = DataObject::get_by_id('Order', $ID);
                $record->Status = 'dispatched';
                $record->write();
            }
        }
        
        return new SS_HTTPResponse(
            Convert::array2json(array(
                'html' => "Records Updated",
                'message' => _t('ModelAdmin.UNPUBLISHED','Marked as dispatched')
            )),
            200
        );
        
        
    }
    
    /**
     * Try and generate a printable file from the list of orders, or if not
     * possible, generate an error.
     * 
     * @return file
     */
    public function tagprinter() {
        $now = Date("d-m-Y-H-i");
        $fileName = "tags-$now.txt";

        if($fileData = $this->generateTagPrinterFileData())
            return SS_HTTPRequest::send_file($fileData, $fileName);
        else
            user_error("No records found", E_USER_ERROR);
    }
    
    /**
     * Method used to do the ground work of generating the contents of the tag
     * printer file.
     * 
     * @return string 
     */
    private function generateTagPrinterFileData() {
        $record_ids = explode(',', $_GET['records']);
        $tags = '';
        
        foreach($record_ids as $ID) {
            if($ID) {
                $record = DataObject::get_by_id('Order', $ID);
                
                $tags .= "\n---------------------------- Order {$record->OrderNumber} --------------------------------\n";
                $tags .= "Order Details:\n";
                $tags .= "{$record->BillingFirstnames} {$record->BillingSurname},\n";
                $tags .= $record->DeliveryAddress1 . ",\n";
                $tags .= $record->DeliveryAddress2 . ",\n";
                $tags .= $record->DeliveryCity . ",\n";
                $tags .= $record->DeliveryPostCode . ",\n";
                $tags .= $record->DeliveryCountry . "\n";
                $tags .= "Postage: {$record->Postage()->Location} ({$record->Postage()->Cost})\n\n";
                
                foreach($record->Items() as $item) {
                    $i = 0;
                    
                    $tags .= "\n---------------------------- Order Item --------------------------------\n\n";
                    $tags .= "Colour: {$item->Colour}\n\n";
                    
                    while($i < $item->Quantity) {
                        $tags .= "<" . $this->rebuildTag($item->TagOne) . ">\n";
                        $tags .= "<" . $this->rebuildTag($item->TagTwo) . ">\n";
                        
                        $i++;
                    }
                }
            }
        }
        
        return $tags;
    }
    
    /**
     * Add additional line spaces to export file by exploding the tag and adding
     * a line break to any empty lines.
     * 
     * @param type $tag
     * @return type string
     */
    private function rebuildTag($tag) {
        $string_found = false;
        $blank_lines = 0;
        
        $tag_lines = "";
        
        foreach(explode("\n", $tag) as $line) {

            if(trim($line) == '')
                $blank_lines++;
            
            if($blank_lines > 0 && trim($line) != '') {
                $tag_lines .= "\n";
                $blank_lines = 0;
            }
            
            $tag_lines .= $line . "\n";
        }
        
        return $tag_lines;
    }


    public function Utility() {
        $links = new DataObjectSet();
        
        $links->push(new ArrayData(array(
            'Title' => _t('TableListField.CSVEXPORT', 'Export to CSV'),
            'ID'    => $this->name . '_utility_csvexport',
            'Link' => $this->ExportLink()
        )));
        
        $links->push(new ArrayData(array(
            'Title' => 'Export to Tag Printer',
            'ID'    => $this->name . '_utility_tagprinter',
            'Link'  => $this->TagPrinterLink()
        )));
        
        $links->push(new ArrayData(array(
            'Title' => 'Mark as dispatched',
            'ID'    => $this->name . '_utility_dispatched',
            'Link'  => $this->DispatchedLink()
        )));
        
        return $links;
    }
}