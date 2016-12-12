<?php

include ('php/functions.php');
include ('php/grid.php');
include ('php/client.php');
include ('php/charge.php');
include ('php/invoice.php');

global $site_base;

check_log_in('client');
$client = new Client($_SESSION['user']);

if (isset($_POST['editCharge'])) {
    $obj = new charge($_POST['id']);
    $explode = explode(' ', $_POST['recurPeriod']);
    $recurPeriod = $explode[0];
    $obj->setRecurPeriod($recurPeriod);
    $obj->deselect();
}

if (isset($_GET['thankyou'])) {
    $client->thankYou();
} else if (isset($_GET['id'])) {
    $getKeys = array_keys($_GET);
    $arrClasses = array('client', 'charge', 'grid');
    foreach ($getKeys as $key) {
        if (in_array($key, $arrClasses)) {
            $className = $key;
            break;
        }
    }
    if (isset($className)) {
        $obj = new $className(filter_input(INPUT_GET, 'id'));
        for ($i = 1; $i < count($_GET) && !in_array($getKeys[$i], $arrClasses); $i++) {
            $getKeys = array_keys($_GET);
            $obj->functionRoute($getKeys[$i], filter_input(INPUT_GET, $getKeys[$i]));
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
        <!-- font-family: 'Droid Sans', sans-serif; -->
        <title>Client Center - <?php echo $client->name; ?> | Medialusions Interactive, Inc.</title>
        <?php populateHead(); ?>
    </head>
    <body>
        <div id="header">
            <div id="menu_wrapper">
                <nav>
                    <a href="http://medialusions.com">HOME</a>
                    <a href="<?= $site_base ?>?logout">LOGOUT</a>
                </nav>
            </div>
        </div>
        <div id="grid">
            <div id="logo">
                <a href="http://medialusions.com">
                    <img src="grid/images/logo.png" title="Medialusions Interactive" />
                </a>
            </div>
            <div class="block square">
                <div class="client_information full_bg">
                    <h1>Welcome, <?php
                        $name = explode(' ', $client->name);
                        echo $name[0];
                        ?></h1>
                    <h2>Review your stored information:</h2>
                    <div class="floatl thirty" align="right">
                        <div style="padding-right: 5px; font-weight: bold;">
                            company<br>
                            email<br>
                            phone<br>
                            address<br>
                            city, state zip<br>
                        </div>
                    </div>
                    <div class="floatl sixty">
                        <?php
                        echo $client->company != '' ? (strlen($client->company) > 23 ? substr($client->company, 0, 24) . '...' : $client->company) . '<br>' : 'Not on file' . '<br>';
                        echo $client->email != '' ? $client->email . '<br>' : 'Not on file' . '<br>';
                        echo $client->phone != '' ? $client->phone . '<br>' : 'Not on file <br>';
                        echo $client->address != '' ? $client->address . '<br>' : 'Not on file' . '<br>';
                        echo $client->city != '' ? $client->city . ', ' . $client->state . ' ' . $client->zip : 'Not on file';
                        //</br><a href="#">Update Information</a>
                        ?>
                    </div>
                </div>
            </div>
            <?php echo $client->displayPreview(); ?>
            <div class="block big_rectangle">
                <div class="billing full_bg" style="overflow-y: scroll;">
                    <h1>The following is your billing report and history</h1>
                    <?php
                    $invoiceArray = getCharges($client->id);
                    foreach ($invoiceArray as $invoiceId => $chargeArray) {
                        $invoice = new invoice($invoiceId);
                        echo $invoice->toString(false);
                    }
                    ?>
                </div>
            </div>

            <div class="block tall_rectangle">
                <div class="cart full_bg" style="overflow-y: scroll;">
                    <h1>Your Cart</h1>
                    <?php if (isset($_SESSION['shoppingCart']) && !empty($_SESSION['shoppingCart'])) { ?>
                        <div id="cartContainer">
                            <div class="chargeCartContainer">
                                <div class="chargeNum floatl">
                                    Opts.
                                </div>
                                <div class="chargeDescription floatl">
                                    Description
                                </div>
                                <div class="chargeNum floatl">
                                    Price
                                </div>
                            </div>
                            <?php
                            $totalCartCost = 0;
                            foreach ($_SESSION['shoppingCart'] as $chargeId) {
                                $currShopCharge = new charge($chargeId);
                                $totalCartCost += $currShopCharge->getTotalPrice(false);
                                echo $currShopCharge->cartView();
                            }
                            ?>
                            <div class="chargeCartContainer">
                                <div class="chargeNum floatl">
                                    &nbsp;
                                </div>
                                <div class="chargeDescription floatl" align="right">
                                    <strong>Total </strong>&nbsp;
                                </div>
                                <div class="chargeNum floatl">
                                    $<?php echo $totalCartCost; ?>
                                </div>
                            </div>
                            <div class="chargeCartContainer">
                                <form action="https://www.paypal.com/cgi-bin/webscr" id="paypalform" method="post">
                                    <input type="hidden" name="cmd" value="_cart">
                                    <input type="hidden" name="upload" value="1">
                                    <input type="hidden" name="business" value="info@medialusions.com">
                                    <input type="hidden" name="currency_code" value="US">

                                    <?php
                                    $currPaypalElement = 1;
                                    foreach ($_SESSION['shoppingCart'] as $chargeId) {
                                        $currShopCharge = new charge($chargeId);
                                        echo $currShopCharge->paypalFormElements($currPaypalElement);
                                        $currPaypalElement++;
                                    }
                                    ?>

                                </form>
                                <a href="#" onclick="javascript: $('#paypalform').submit();">Pay now with PayPal*</a>
                            </div>
                        </div>
                        <hr>
                        <h2>--Or pay with check written to Medialusions**:</h2>
                        <div style="padding: 0 5px;">
                            Medialusions Interactive, Inc.<br>
                            1606 Birmingham Dr<br>
                            Fort Collins, CO 80526<br><br>
                            <span style="font-size: 10px;">
                            *Friendly 2.9% applied for all PayPal/Credit Card transactions.<br>
                            </span>
                        </div>
                        <?php
                    } else if(isset($_GET['thankyou'])){
                        echo '<h2>Thank you for your payment!</h2>'
                        . '<div style="margin: 0 5px;">If the items you have paid for do not reflect '
                        . 'the purchase, please give time for the system to '
                        . 'complete its course. If you think there is a sort '
                        . 'of error, please go "HOME" and use the "CONTACT" '
                        . 'tab.</h2>';
                    }else{
                        echo '<h2>Your cart is empty...</h2>'
                        . '<br><br><br>'
                        . '<div align="center"><img style="width:30%;" src="style/images/redbubble.png">'
                        . '<br>While you\'re here, go on and check out my t-shirt designs at redbubble. Thank you for your continued business!<br>'
                        . '<a target="_blank" href="http://www.redbubble.com/people/medialusions/shop">
                            <button>Shop Now</button></a></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <script>
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-64039467-1', 'auto');
            ga('send', 'pageview');

        </script>
    </body>
</html>
<?php ob_flush();