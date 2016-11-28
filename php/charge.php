<?php
class charge {

    public $id, $invoiceNumber, $clientId, $description, $unitPrice, $units, $recurring, $recurPeriod, $status, $dueDate, $interestPercentage;

    function __construct($id = '', $invoiceNumber = '', $clientId = '', $description = '', $unitPrice = '', $units = '', $recurring = '', $recurPeriod = '', $status = '', $dueDate = '', $interestPercentage = '', $sessionSet = false) {
        if ($id != '') {
            $this->id = $id;
        } else {
            if ($invoiceNumber == '') {
                $invoiceNumber = getNextInvoice();
            }
            $sql = "INSERT INTO  `medialusions`.`charges` (
                `id` , `invoiceNumber` , `clientId` , `description` , `unitPrice` ,
                `units` , `recurring` , `recurPeriod` , `status` , `dueDate` , `interestPercentage`
                )
                VALUES (
                NULL ,
                '" . $invoiceNumber . "',
                '" . $clientId . "',
                '" . addslashes($description) . "',
                '" . $unitPrice . "',
                '" . $units . "',
                '" . $recurring . "',
                '" . $recurPeriod . "',
                '" . $status . "',
                '" . strtotime($dueDate) . "',
                '" . ((double) $interestPercentage / 100) . "'
            );";
            mysql_query($sql) or die(mysql_error());
            $this->id = mysql_insert_id();
        }
        $this->invoiceNumber = charge_search($this->id, 'invoiceNumber');
        $this->clientId = charge_search($this->id, 'clientId');
        $this->description = charge_search($this->id, 'description');
        $this->unitPrice = charge_search($this->id, 'unitPrice');
        $this->units = charge_search($this->id, 'units');
        $this->recurring = charge_search($this->id, 'recurring');
        $this->recurPeriod = charge_search($this->id, 'recurPeriod');
        $this->status = charge_search($this->id, 'status');
        $this->dueDate = charge_search($this->id, 'dueDate');
        $this->interestPercentage = charge_search($this->id, 'interestPercentage');
        if($sessionSet)
            $this->select ();
    }
    
    function autoRecur(){
        //due date
        $nextDueDate = $this->dueDate + (24*60*60*$this->recurPeriod);
        
        //invoice
        $nextInvoiceQuery = mysql_query("SELECT invoiceNumber FROM charges ORDER BY invoiceNumber DESC LIMIT 1;") or die(mysql_error());
        $nextInvoiceArray = mysql_fetch_array($nextInvoiceQuery);
        $nextInvoice = $nextInvoiceArray[0]+1;
        
        new charge('', $nextInvoice, $this->clientId, $this->description, $this->unitPrice, $this->units, $this->recurring, $this->recurPeriod, "unpaid", date("m/d/Y",$nextDueDate), $this->interestPercentage);
        
        return;
    }

    //compares this client id with incoming client id
    function clientCheck($id) {
        return $this->clientId == $id;
    }

    function select() {
        if($this->cartCheck())
            $this->removeFromCart();
        $_SESSION['adminCharge'] = $this->id;
    }
    
    function isSelected() {
        if(isset($_SESSION['adminCharge'])){
            return $_SESSION['adminCharge'] == $this->id;
        }else{
            return false;
        }
    }
    
    function addToCart() {
        if(!isset($_SESSION['shoppingCart']) || empty($_SESSION['shoppingCart'])){
            $_SESSION['shoppingCart'][0] = $this->id;
        }else{
            array_push($_SESSION['shoppingCart'], $this->id);
        }
    }
    
    function removeFromCart() {
        if (!isset($_SESSION['shoppingCart'])) {
            return false;
        }
        $index = array_search($this->id, $_SESSION['shoppingCart']);
        if ($index !== false) {
            unset($_SESSION['shoppingCart'][$index]);
        }
        return true;
    }

    function cartCheck() {
        if(!isset($_SESSION['shoppingCart']))
            return false;
        return in_array($this->id, $_SESSION['shoppingCart']);
    }
    
    //compares this status with incoming status
    function statusCheck($status = 'paid') {
        return $this->status == $status;
    }

    function deselect() {
        unset($_SESSION["adminCharge"]);
    }

    function getUnitPrice($formatted = true) {
        if ($formatted) {
            return '$' . sprintf('%0.2f', $this->unitPrice);
        } else {
            return $this->unitPrice;
        }
    }

    function getTotalPrice($formatted = true) {
        $totalCost = ($this->units / 1.00) * ($this->unitPrice / 1.00);
        if ($formatted) {
            return '$' . sprintf('%0.2f', $totalCost);
        } else {
            return $totalCost;
        }
    }

    function getDescription($charLimit = -1, $html = true) {
        if ($charLimit < 0) {
            if(!$html){
                return strip_tags(stripslashes($this->description));
            }else{
                return stripslashes($this->description);
            }    
        } else {
            if(!$html){
                return truncate(strip_tags(stripslashes($this->description)), $charLimit, '...', false, true);
            }else{
                return truncate(stripslashes($this->description), $charLimit, '...', false, true);
            }
        }
    }

    function toString($admin = false, $clientOpts = true, $descLength = 125) {
        if($this->isSelected() && !$admin){
            return $this->clientEditView ();
        }
        $date = date('m/d/Y', $this->dueDate). '<br>';
        $toRet = '<div class="chargeContainer">'
                . '<div class="chargeNum floatl">' . ($clientOpts ? $date : '')
                . '<strong>' . $this->status . '</strong><br>';
        if($admin){
            $toRet .= '<a href="?id=' . $this->id . '&delete&charge">delete</a> &bull;'
                . ($this->status != 'paid' ? '<a href="?id=' . $this->id . '&setStatus=paid&charge">mark paid</a> &bull;' : '' )
                . ' <a href="?id=' . $this->id . '&select&charge">select</a>';
        }else if($this->cartCheck() && !$this->statusCheck('paid') && $clientOpts){
            $toRet .= '<a href="?id=' . $this->id . '&removeFromCart&charge">Remove</a> '.($this->recurring == 'true' ? '&bull; <a href="?id=' . $this->id . '&select&charge">Edit</a>' : '' );
        }else if(!$this->cartCheck() && !$this->statusCheck('paid') && $clientOpts){
            $toRet .= '<a href="?id=' . $this->id . '&addToCart&charge">Add to Cart</a> '.($this->recurring == 'true' ? '&bull; <a href="?id=' . $this->id . '&select&charge">Edit</a>' : '' );
        }else{
            $toRet .= '';
        }
        
        $toRet .= '</div><div class="chargeNum floatl">' . $this->units . '</div>'
                . '<div class="chargeDescription floatl">';
        
        if($this->recurring == 'true' && $this->status != 'paid'){
            $time = date("m/d/Y", charge_search($this->id, 'dueDate')+(24*3600*charge_search($this->id, 'recurPeriod')));
            $toRet .= '<em>&#10227; Recurrence set for '.$time.'</em>';
        }
        
        $toRet .=  $this->getDescription($descLength) . '</div>'
                . '<div class="chargeNum floatl">' . $this->getUnitPrice() . '</div>'
                . '<div class="chargeNum floatl">' . $this->getTotalPrice() . '</div>'
                . '</div>';

        return $toRet;
    }

    function toQuoteString($admin = false, $clientOpts = true, $descLength = 125) {
        if($this->isSelected() && !$admin){
            //return $this->clientEditView ();
        }
        $toRet = '<div class="chargeContainer">';
        
        $toRet .= '<div class="chargeNum floatl">' . $this->units . '</div>'
                . '<div class="chargeWideDescription floatl">';
        
        if($this->recurring == 'true'){
            $time = date("m/d/Y", charge_search($this->id, 'dueDate')+(24*3600*charge_search($this->id, 'recurPeriod')));
            $toRet .= '<em>&#10227; Recurrence set for '.$time.'</em>';
        }
        
        $toRet .= $this->getDescription($descLength) . '</div>'
                . '<div class="chargeNum floatl">' . $this->getUnitPrice() . '</div>'
                . '<div class="chargeNum floatl">' . $this->getTotalPrice() . '</div>'
                . '</div>';

        return $toRet;
    }
    
    function clientEditView() {
        if($this->recurring != 'true')
            return '<div class="chargeContainer">There are no editable fields on this charge. <a href="?id=' . $this->id . '&deselect&charge">Cancel</a></div>';
        //set due date to next cycle
        $nextDueDate = strtotime("+".$this->recurPeriod." days", $this->dueDate);
        //open file of template and replace placeholders with values
        $file = file_get_contents("php/clientEditTemplate.html");
        $toReplace = array("%SERVER_SIDE_INPUT_DATE%", "%SERVER_SIDE_INPUT_PRICE%", "%SERVER_SIDE_INPUT_PERIOD%", "%SERVER_SIDE_INPUT_CHID%");
        $replaceWith = array(date("m/d/Y", $nextDueDate), $this->getTotalPrice(true), $this->recurPeriod, $this->id);
        $file = str_replace($toReplace, $replaceWith, $file);
        //return the HTML from the file
        return $file;
    }
    
    function paypalFormElements($number){
        $toRet = '<input type="hidden" name="item_name_'.$number.'" value="'.$this->getDescription(45, false).'">'
                . '<input type="hidden" name="amount_'.$number.'" value="'.round($this->unitPrice+($this->unitPrice*0.029),2).'">'
                . '<input type="hidden" name="item_number_'.$number.'" value="'.$this->id.'">'
                . '<input type="hidden" name="quantity_'.$number.'" value="'.$this->units.'">';
        
        return $toRet;
    }
    
    function cartView() {
        $toRet = '<div class="chargeCartContainer">'
                . '<div class="chargeNum floatl">'
                . '<a href="?id=' . $this->id . '&removeFromCart&charge">Remove</a></div>'
                . '<div class="chargeDescription floatl">' . $this->getDescription(50) . '</div>'
                . '<div class="chargeNum floatl">' . $this->getTotalPrice() . '</div>'
                . '</div>';

        if($this->cartCheck())
            return $toRet;
        else
            return null;
    }

    function delete() {
        $sql = 'DELETE FROM charges WHERE id="' . $this->id . '";';
        mysql_query($sql) or die(mysql_error());
        $this->deselect();
    }

    function updateDB($what, $value) {
        $sql = "UPDATE charges SET " . $what . "=\"" . $value . "\" WHERE id=" . $this->id . ";";
        mysql_query($sql) or die(mysql_error());
    }

    function setInvoiceNumber($invoiceNumber) {
        $this->updateDB('invoiceNumber', $invoiceNumber);
        $this->invoiceNumber = $invoiceNumber;
    }

    function setClientId($clientId) {
        $this->updateDB('clientId', $clientId);
        $this->clientId = $clientId;
    }

    function setDescription($description) {
        $this->updateDB('description', addslashes($description));
        $this->description = addslashes($description);
    }

    function setUnitPrice($unitPrice) {
        $this->updateDB('unitPrice', $unitPrice);
        $this->unitPrice = $unitPrice;
    }

    function setUnits($units) {
        $this->updateDB('units', $units);
        $this->units = $units;
    }

    function setRecurring($recurring) {
        $this->updateDB('recurring', $recurring);
        $this->recurring = $recurring;
    }

    function setRecurPeriod($recurPeriod) {
        if ($recurPeriod != $this->recurPeriod) {
            $ratio = $this->units / ($this->recurPeriod / 30);
            $newUnits = $ratio * ($recurPeriod / 30);
            if (floor($ratio) == $ratio) {
                $this->units = $newUnits;
                $this->updateDB('units', $newUnits);

                $this->updateDB('recurPeriod', $recurPeriod);
                $this->recurPeriod = $recurPeriod;
            } else {
                throw new Exception('Recurrance period is incorrect. Fractions not allowed.');
            }
        }
    }

    /**
     * 
     * @param type $status paid || unpaid || late || overdue
     */
    function setStatus($status) {
        $this->updateDB('status', $status);
        $this->status = $status;
        if($status == 'paid' && $this->recurring == 'true'){
            $this->autoRecur();
        }
    }

    function setDueDate($dueDate) {
        $this->updateDB('dueDate', strtotime($dueDate));
        $this->dueDate = strtotime($dueDate);
    }

    function setInterestPercentage($interestPercentage) {
        $this->updateDB('interestPercentage', $interestPercentage);
        $this->interestPercentage = $interestPercentage;
    }

    function functionRoute($name, $value = '') {
        $this->$name($value);
    }

}
