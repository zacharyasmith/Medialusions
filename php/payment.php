<?php

class payment {

    public $id, $date_posted, $method, $identifier;

    function __construct($id = '', $date_posted = '', $method = '', $identifier = '') {
        if ($id != '') {
            $this->id = $id;
        } else {
            $sql = "INSERT INTO  `medialusions`.`payments` (
                `id` , `date_posted` , `method` , `identifier` , `date_added`
                )
                VALUES (
                NULL ,
                '" . $date_posted . "',
                '" . $method . "',
                '" . $identifier . "',
                null
            );";
            mysql_query($sql) or die(mysql_error());
            $this->id = mysql_insert_id();
        }

        $this->date_posted = payment_search($this->id, 'date_posted');
        $this->method = payment_search($this->id, 'method');
        $this->identifier = payment_search($this->id, 'identifier');
    }

    // PRE: Val is timestamp
    function setDatePosted($val) {
        $this->identifier = $this->updateDB('identifier', $val);
    }

    // PRE: Val is text
    function setMethod($val) {
        $this->identifier = $this->updateDB('identifier', $val);
    }

    // PRE: Val is text
    function setIdentifier($val) {
        $this->identifier = $this->updateDB('identifier', $val);
    }

    function updateDB($what, $value) {
        $what_escaped = mysql_real_escape_string($what);
        $value_escaped = mysql_real_escape_string($value);
        $sql = "UPDATE payments SET " . $what_escaped . "=\"" . $value_escaped . "\" WHERE id=" . $this->id . ";";
        mysql_query($sql) or die(mysql_error());
        return $value_escaped;
    }

}
