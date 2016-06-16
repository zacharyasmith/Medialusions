<?php
error_reporting(0);

$username = "media";
$password = "CDyPyCzrwQubp2WK";
$database = "medialusions";

mysql_connect("localhost", $username, $password);
mysql_select_db($database) or die("Unable to select database");
