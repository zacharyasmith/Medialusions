<?php
include ('php/functions.php');
include ('php/grid.php');
include ('php/client.php');
include ('php/charge.php');
include ('php/invoice.php');

if (!isset($_GET['pass'])) {
  die();
}
if ($_GET['pass'] != 'F!8$YKrqd6gd'){
  die();
}

$clients = Client::outstandingClients();
foreach ($clients as $client) {
    $client->sendEmail();
    echo 'Sent email.</br>';
}
