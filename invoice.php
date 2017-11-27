<?php
include ('php/functions.php');
include ('php/grid.php');
include ('php/client.php');
include ('php/charge.php');
include ('php/invoice.php');
if (!check_log_in('client', 'boolean') && !check_log_in('admin', 'boolean')) {
    header('Location: http://www.medialusions.com');
}
if (!check_log_in('client', 'boolean')) {
    $clientId = charge_search($_GET['id'], 'clientId', 'invoiceNumber', 1);
    $client = new Client($clientId);
} else {
    $client = new Client($_SESSION['user']);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<h2>Error! Url is incorrect.</h2>';
    die();
}
$invoice = new invoice($_GET['id']);

if (!$invoice->userCheck($client->id) && !check_log_in('admin', 'boolean')) {
    echo '<h2>Error! Url is incorrect. You do not have access to this invoice.</h2>';
    die();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
        <!-- font-family: 'Droid Sans', sans-serif; -->
        <title>Invoice #<?= $_GET['id'] ?> - <?= $client->company ?></title>
        <?php populateHead(); ?>
    </head>
    <body style="background: url('style/images/word_invoice_bg.png') no-repeat; background-size: cover;">
        <div id="invoice_wrapper">
            <div class="noPrint">
            </div>
            <div id="logo" class="floatl" style="background: none;"><img src="http://www.medialusions.com/style/images/LOGO.png" alt="logo"></div>
            <div id="invoice_info" class="fifty floatr" align="right">
                <h1 style="margin: 0; padding: 0 0 10px 0;">Invoice</h1>
                <?php
                $dateCharges = $invoice->charges;

                foreach ($dateCharges as $dateCharge) {
                    $dateStr = $dateCharge->dueDate;
                    break;
                }
                ?>
                Date: <?= date("F d, Y", $dateStr) ?><br>
                Invoice # <?= $invoice->id ?>
            </div>
            <div id="client_info">
                <div class="floatr thirty" align="right">
                    <div class="floatl"><strong>Bill To</strong></div>
                    <?php
                    echo $client->name != '' ? $client->name . '<br>' : '';
                    echo $client->company != '' ? $client->company . '<br>' : '';
                    echo $client->address != '' ? $client->address . '<br>' : '';
                    echo $client->city != '' ? $client->city . ', ' . $client->state . ' ' . $client->zip . '<br>' : '';
                    echo $client->phone != '' ? $client->phone . '<br>' : '';
                    ?>
                </div>
                <div class="floatr thirty" align="right" style="padding-right: 15px">
                    <div class="floatl"><strong>From</strong></div>
                    Zach Smith<br>
                    Medialusions Interactive<br>
                    <?= ADDRESS1 ?><br>
                    <?= ADDRESS2 ?><br>
                    (303) 549-0491<br>
                </div>
            </div>
            <br><br>
            <div class="chargeContainer">
                <div class="chargeNum floatl"><strong>Units</strong></div>
                <div class="chargeWideDescription floatl"><strong>Description</strong></div>
                <div class="chargeNum floatl"><strong>Price/Unit</strong></div>
                <div class="chargeNum floatl"><strong>Line Total</strong></div>
            </div>

            <?php
            $charges = $invoice->charges;

            $totalInvoicePrice = 0;
            foreach ($charges as $charge) {
                echo $charge->toQuoteString(false, false, -1);
                $totalInvoicePrice += $charge->getTotalPrice(false);
            }
            ?>
            <div class="chargeContainer">
                <div class="chargeNum floatl"><strong>&nbsp;</strong></div>
                <div class="chargeWideDescription floatl">&nbsp;</div>
                <div class="chargeNum floatl"><strong>Total Price</strong></div>
                <div class="chargeNum floatl"><strong>$<?= sprintf('%0.2f', $totalInvoicePrice) ?></strong></div>
            </div>
            <br><br>
            <div style="width: 100%;" align="center">
                <h2>Make all checks payable to <strong>Medialusions Interactive</strong></h2>
                <strong><i>Thank you for your business!</i></strong>
                <br><br>
                <font color="#792c8c">Medialusions Interactive, Inc.  <?= ADDRESS1 . ', ' . ADDRESS2 ?>.  (303) 549-0491  zach@medialusions.com</font>
            </div>
        </div>
    </body>
</html>
