<?php
include 'mysqlConnect.php';
include 'charge.php';
/**
 * @author Zach-DT
 */
class Paypal {
    public $id, $keys, $values;
    
    public function __construct($arr) {
        $values = array();
        $keys = array();
        //$idArr = array();
        foreach($arr as $key=>$value){
            array_push($values, mysql_real_escape_string($value));
            array_push($keys, $key);
            $col = mysql_query("SELECT `$key` FROM `paypal`");
            if(!$col){
                mysql_query("ALTER TABLE `paypal` ADD `$key` varchar(128) NOT NULL") or die(mysql_error());
            }
        }
        $this->keys = $keys;
        $this->values = $values;
        for($i = 0; $i < count($values); $i++){
            $values[$i] = mysql_real_escape_string($values[$i]);
        }
        $sql = "INSERT INTO `paypal` (".implode(", ", $keys).") VALUES ('".implode("', '", $values)."');";
        mysql_query($sql) or die(mysql_error());
        $this->id = mysql_insert_id();
    }
    
    function returnItemKeysSold(){
        $toRet = array(); //to be returned
        
        $good = true;
        $i = 1;
        while($good){
            //string for indexing
            $string = "item_number".$i;
            $i++;
            //index of $i item
            $index = array_search($string, $this->keys);
            
            //if no key, return
            if(!$index){return $toRet;}
            
            $this->log("Index of $string - ".$index);
            
            $currItemId = $this->values[$index];
            
            $this->log("CurrId of $string - ".$currItemId);
            array_push($toRet, $currItemId);
        }
        return $toRet;
    }
    
    function log($text){
        $log  = date("j.n.Y-G:i:s")." - $text"
                . PHP_EOL;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./paypal_log.txt', $log, FILE_APPEND);
    }
    
    function checkFields(){
        $itemIds = $this->returnItemKeysSold();
        foreach($itemIds as $itemId){
            $this->log("Item ID - ".$itemId);
            $sql = "UPDATE charges SET status='paid' WHERE id=$itemId;";
            $this->log("SQL - ".$sql);
            mysql_query($sql);
        }
    }
}

if(isset($_POST)){
    $obj = new Paypal($_POST);
    $obj->checkFields();
}
