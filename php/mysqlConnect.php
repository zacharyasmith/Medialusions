<?php
error_reporting(0);

$username = "media";
$password = "RGdTOLOKre@wm5b";
$database = "medialusions";

mysql_connect("localhost", $username, $password);
mysql_select_db($database) or die("Unable to select database");
