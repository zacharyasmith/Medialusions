<?php

class invoice {

    public $id, $charges, $userId;

    function __construct($id) {
        $this->id = $id;
        $this->userId = charge_search($this->id, 'clientId', 'invoiceNumber', 1);
        $this->charges = invoice_search($this->id);
    }
    
    function userCheck($UID){
        return $UID == $this->userId;
    }

    function linkToInvoice($arg) {
        if($arg == 'quote'){
            return "http://www.medialusions.com/quote.php?id=" . $this->id; 
        }
        $path = "files/invoices/";
        $files = scandir($path);
        foreach ($files as $file) {
            $path_parts = pathinfo($file);
            //$path_parts['extension'];
            //$path_parts['filename'];
            if ($path_parts['filename'] == $this->id) {
                return "http://www.medialusions.com/" . $path . $path_parts['filename'] . '.' . $path_parts['extension'];
            }
        }
        return "http://www.medialusions.com/invoice.php?id=" . $this->id;
    }

    function toString($admin = false, $clientOpts = true, $descLength = 125) {
        if($this->charges[0]->status=="quote"){
            $link = "quote";
            $title = "Quote";
        }else{
            $link = '';
            $title = "Invoice";
        }
        
        $toRet = '<fieldset><legend>'.$title.' #' . $this->id . ' - <a target="_blank" href="' . $this->linkToInvoice($link) . '">View '.$title.'</a></legend>'
        . '<div class="chargeContainer">'
        . '<div class="chargeNum floatl"><strong>'.($clientOpts ? 'Info/Opts.' : 'Status').'</strong></div>'
        . '<div class="chargeNum floatl"><strong>Units</strong></div>'
        . '<div class="chargeDescription floatl"><strong>Description</strong></div>'
        . '<div class="chargeNum floatl"><strong>Price/Unit</strong></div>'
        . '<div class="chargeNum floatl"><strong>Line Total</strong></div>'
        . '</div>';
        $totalInvoicePrice = 0;
        foreach ($this->charges as $charge) {
            $toRet .= $charge->toString($admin, $clientOpts, $descLength);
            $totalInvoicePrice += $charge->getTotalPrice(false);
        }
        $toRet .= '<div class="chargeContainer">'
        . '<div class="chargeNum floatl">&nbsp;</div>'
        . '<div class="chargeNum floatl"><strong>&nbsp;</strong></div>'
        . '<div class="chargeDescription floatl">&nbsp;</div>'
        . '<div class="chargeNum floatl"><strong>Total Price</strong></div>'
        . '<div class="chargeNum floatl"><strong>$' . sprintf('%0.2f', $totalInvoicePrice) . '</strong></div>'
        . '</div>';
        $toRet .= '</fieldset>';
        return $toRet;
    }

}
