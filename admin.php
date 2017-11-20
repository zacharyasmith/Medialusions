<?php
include ('php/functions.php');
include ('php/grid.php');
include ('php/client.php');
include ('php/charge.php');
include ('php/invoice.php');

global $site_base;

check_log_in('admin');
if (isset($_POST['shape'])) {
  $file_src = fileUpload($_POST['shape']);
  newGridItem($_POST['shape'], $_POST['url'], $_POST['title'], $_POST['sub_title'], $file_src);
}
if (isset($_POST['newCharge'])) {
  if (is_uploaded_file($_FILES['invoice']['tmp_name'])) {
    invoiceUpload($_POST['invoiceNumber']);
  }
  new charge('', $_POST['invoiceNumber'], $_POST['clientId'], $_POST['description'], $_POST['unitPrice'], $_POST['units'], $_POST['recurring'], $_POST['recurPeriod'], $_POST['status'], $_POST['dueDate'], $_POST['interestPercentage'], true);

  //avoiding resubmit form check
  header("Refresh:0");
  die;
}
if (isset($_POST['updateClient'])) {
  $obj = new Client(filter_input(INPUT_POST, 'id'));
  $obj->setName(filter_input(INPUT_POST, 'name'));
  $obj->setCompany(filter_input(INPUT_POST, 'company'));
  $obj->setWebsite(filter_input(INPUT_POST, 'website'));
  $obj->setEmail(filter_input(INPUT_POST, 'email'));
  $obj->setPhone(filter_input(INPUT_POST, 'phone'));
  $obj->setAddress(filter_input(INPUT_POST, 'address'));
  $obj->setCity(filter_input(INPUT_POST, 'city'));
  $obj->setState(filter_input(INPUT_POST, 'state'));
  $obj->setZip(filter_input(INPUT_POST, 'zip'));
  $obj->setPassword(filter_input(INPUT_POST, 'password'));
  //avoiding resubmit form check
  header("Refresh:0");
  die;
}
if (isset($_POST['editCharge'])) {
  if (is_uploaded_file($_FILES['invoice']['tmp_name'])) {
    invoiceUpload($_POST['invoiceNumber']);
  }
  $obj = new charge(filter_input(INPUT_POST, 'id'));
  $obj->setInvoiceNumber(filter_input(INPUT_POST, 'invoiceNumber'));
  $obj->setDescription(filter_input(INPUT_POST, 'description'));
  $obj->setDueDate(filter_input(INPUT_POST, 'dueDate'));
  $obj->setInterestPercentage(filter_input(INPUT_POST, 'interestPercentage'));
  $obj->setRecurring(filter_input(INPUT_POST, 'recurring'));
  $obj->setStatus(filter_input(INPUT_POST, 'status'));
  $obj->setUnitPrice(filter_input(INPUT_POST, 'unitPrice'));
  $obj->setUnits(filter_input(INPUT_POST, 'units'));
  $obj->setRecurPeriod(filter_input(INPUT_POST, 'recurPeriod'));
  //avoiding resubmit form check
  header("Refresh:0");
  die;
}
if (isset($_POST['newClient'])) {
  new Client('', $_POST['name'], true);
  //avoiding resubmit form check
  header("Refresh:0");
  die;
}

if (isset($_GET['id'])) {
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
$sessionClientPreset = false;
if (isset($_SESSION['adminClient'])) {
  $currentClient = new Client($_SESSION['adminClient']);
  $sessionClientPreset = true;
}
$sessionChargePreset = false;
if (isset($_SESSION['adminCharge'])) {
  $currentCharge = new Charge($_SESSION['adminCharge']);
  $sessionChargePreset = true;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
  <!-- font-family: 'Droid Sans', sans-serif; -->
  <title>Admin Center | Medialusions Interactive, Inc.</title>
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
        <h1>New Grid Item</h1>
        <form action="admin.php" style="padding: 5px;" method="post" enctype="multipart/form-data">
          <input type="file" name="gridImage"><br/>
          <input type="text" placeholder="Web Link URL" name="url"><br/>
          <input type="text" placeholder="Image Title" name="title"><br/>
          <input type="text" placeholder="Image Sub-Title" name="sub_title"><br/>
          <select name="shape">
            <optgroup label="Shape">
              <option value="square">Square</option>
              <option value="rectangle">Rectangle</option>
            </optgroup>
          </select><br/>
          <input type="submit">
        </form>
      </div>
    </div>
    <div class="block rectangle">
      <div class="no_website full_bg admin_grid_center" style="overflow-y: scroll">
        <?php
        $gridItems = getGridItems();
        ?>
        <h1 style="font-size: 1.5em;">Grid Center</h1>
        <div style="margin: 5px;">
          <ul>
            <?php
            foreach ($gridItems as $gridItem) {
              echo $gridItem->toString();
            }
            ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="block rectangle">
      <div class="client_information full_bg" style="background: #6f408a;">

        <h1 style="background: none;">Client Information
          <?php if ($sessionClientPreset) { ?>
            - Last Login: <?= $currentClient->findLastLoginDate() ?>
          <?php } ?>
        </h1>
        <?php
        if ($sessionClientPreset) {
          echo '<div class="floatl fifty">';
          echo $currentClient->name . '<br>';
          $currentClient->displayInformation(true);
          echo '</div>';
          ?>
          <div class="floatl fifty">
            <form method="post" action="admin.php">
              <input type="hidden" name="updateClient" value="true">
              <input type="hidden" name="id" value="<?= $currentClient->id ?>">
              <input type="text" name="name" placeholder="Name" value="<?= $currentClient->name ?>"><br>
              <input type="text" class="fifty" name="company" placeholder="Company" value="<?= $currentClient->company ?>"><input type="text" class="fifty" name="website" placeholder="Website" value="<?= $currentClient->website ?>"><br>
              <input class="fifty" type="text" name="email" placeholder="Email" value="<?= $currentClient->email ?>"><input class="fifty" type="text" name="phone" placeholder="Phone" value="<?= $currentClient->phone ?>"><br>
              <input type="text" name="address" placeholder="Address" value="<?= $currentClient->address ?>"><br>
              <input class="thirty" type="text" name="city" placeholder="City" value="<?= $currentClient->city ?>"><input class="thirty" type="text" name="state" placeholder="State" value="<?= $currentClient->state ?>"><input class="thirty" type="text" name="zip" placeholder="Zip" value="<?= $currentClient->zip ?>"><br>
              <input class="fifty" type="text" name="password" placeholder="Password" value="<?= $currentClient->password ?>">
              <input type="submit" value="Save" class="thirty">
            </form>
          </div>
          <?php
        } else {
          echo '<h2 style="background: none;">You need to select a client...</h2>';
        }
        ?>
      </div>
    </div>
    <div class="block rectangle">
      <div class="no_website full_bg" style="overflow-y: scroll;">
        <h1 style="font-size: 1.5em;">Edit Charge</h1>
        <?php
        if ($sessionClientPreset) {
          if ($sessionChargePreset && $currentCharge->clientCheck($currentClient->id)) {
            ?>
            <form action="admin.php" style="margin: 15px;" method="post" enctype="multipart/form-data">
              <input type="hidden" name="editCharge" value="">
              <input type="hidden" value="<?= $currentClient->id ?>" name="clientId">
              <input type="hidden" value="<?= $currentCharge->id ?>" name="id">
              <input type="hidden" value="15" name="interestPercentage">
              <a href="?id=<?= $currentCharge->id ?>&autoRecur=true&charge=true" >&#10227; Generate Next Recurrence</a>
              <label for="statusOpt">Payment Status</label>
              <select class="sixty" id="statusOpt" name="status">
                <option value="<?= $currentCharge->status ?>">current - <?= $currentCharge->status ?></option>
                <optgroup label="Good Standing">
                  <option value="unpaid">Unpaid</option>
                  <option value="paid">Paid</option>
                  <option value="quote">Quote</option>
                </optgroup>
                <optgroup label="Poor Standing">
                  <option value="late">Late</option>
                  <option value="overdue">Overdue</option>
                </optgroup>
              </select>
              <?php /* <div id="payment_container">
              <select class="thirty" name="p_init_select">
              <option value="UNDEFINED">Select for Payment</option>
              <option value="new">New...</option>
              <optgroup label="Recent">
              <?php //FILL IN DYNAMIC DROPDOWN ?>
              </optgroup>
              <option value="manual">Manual</option>
              </select>
              <br>
              <!-- new container -->
              <div id="p_new_container">
              <hr>
              <form id="p_new">
              <div class="floatl thirty">
              <label for="p_date_posted">Date Posted</label>
              <input type="text" class="datePicker" id="p_date_posted" name="p_date_posted">
              </div>
              <div class="floatl thirty">
              <label for="p_method">Method</label>
              <select name="p_method" id="p_method">
              <option value="Check">Check</option>
              <option value="Cash">Cash</option>
              <option value="PayPal">PayPal</option>
              </select>
              </div>
              <div class="floatl thirty">
              <label for="p_id">Identifier</label>
              <input type="text" id="p_id" name="p_id">
              </div>
              <br/>
              <input type="submit" value="Save">
              </form>
              <hr>
              </div>
              <!-- manual container -->
              <div id="p_manual_container">
              <hr>
              <form id="p_manual">
              <label for="p_man_id">Payment ID</label>
              <input type="text" class="sixty" id="p_man_id" name="p_man_id">
              <input type="submit" value="Save">
              </form>
              <hr>
              </div>
              </div>
              */ ?>
              <!-- <input type="file" name="invoice"><br/> -->
              <input class="fifty" type="number" value="<?= $currentCharge->invoiceNumber ?>" name="invoiceNumber"><input class="fifty datePicker" type="text" value="<?= date('m/d/Y', $currentCharge->dueDate) ?>" placeholder="Due Date" name="dueDate">
              <input class="fifty" type="text" value="<?= $currentCharge->unitPrice ?>" placeholder="Unit Price - Price per Unit/Recur" name="unitPrice"><input class="fifty" type="number" value="<?= $currentCharge->units ?>" placeholder="Number of Units - If Recurring, Units/Period" name="units">
              <br/>
              <div class="floatl fifty">
                <label for="recurringOpt">Recurring?</label>
                <select class="sixty" id="recurringOpt" name="recurring">
                  <optgroup label="Recurring">
                    <option value="<?= $currentCharge->recurring ?>">current - <?= $currentCharge->recurring ?></option>
                    <option value="false">No</option>
                    <option value="true">Yes</option>
                  </optgroup>
                </select>
              </div>
              <div class="floatl fifty">
                <label for="amount">Recur Period (days)</label>
                <div id="editSlider"></div>
                <input type="text" name="recurPeriod" id="editAmount" readonly>
              </div>
              <div style="clear: both;">
                <textarea class="ckeditor" name="description"><?= $currentCharge->getDescription() ?></textarea>
              </div>
              <input type="submit" value="Save">
            </form>
            <?php
          } else {//new charge form
            echo '<h2 style="background: none;">You need to select a charge...</h2>';
          }
        } else {
          echo '<h2 style="background: none;">You need to select a client...</h2>';
        }
        ?>
      </div>
    </div>
    <div class="block big_rectangle">
      <div class="billing full_bg" style="overflow-y: scroll;">
        <h1>Billing Information</h1>
        <h2>Next Invoice Number is <?= getNextInvoice() ?> - <a href="#NewInvoice">New Invoice</a></h2>
        <?php
        if ($sessionClientPreset) {
          $invoiceArray = getCharges($_SESSION['adminClient']);
          foreach ($invoiceArray as $invoiceId => $chargeArray) {
            $invoice = new invoice($invoiceId);
            echo $invoice->toString(true);
          }
          ?>
          <a name="NewInvoice"></a>
          <fieldset><legend>New Invoice</legend>
            <form action="admin.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="newCharge" value="">
              <input type="hidden" value="<?= $currentClient->id ?>" name="clientId">
              <input type="hidden" value="unpaid" name="status">
              <input type="hidden" value="15" name="interestPercentage">

              <input type="file" name="invoice"><br/>
              <input class="fifty" type="number" value="<?= getNextInvoice() ?>" name="invoiceNumber"><input class="fifty datePicker" type="text" class="" placeholder="Due Date" name="dueDate">
              <input class="fifty" type="text" placeholder="Unit Price - Price per Unit/Recur" name="unitPrice"><input class="fifty" type="number" placeholder="Number of Units - If Recurring, Units/Period" name="units">
              <br/>
              <div class="floatl fifty">
                <label for="recurringOpt">Recurring?</label>
                <select class="sixty" id="recurringOpt" name="recurring">
                  <optgroup label="Recurring">
                    <option value="false">No</option>
                    <option value="true">Yes</option>
                  </optgroup>
                </select>
              </div>
              <div class="floatl fifty">
                <label for="amount">Recur Period (days)</label>
                <div id="slider"></div>
                <input type="text" name="recurPeriod" id="amount" readonly>
              </div>
              <div style="clear: both;">
                <textarea class="ckeditor" name="description">Description of the charge...</textarea>
              </div>
              <input type="submit">
            </form>
          </fieldset>
          <?php
        } else {
          echo '<h2 style="background: none;">You need to select a client...</h2>';
        }
        ?>
      </div>
    </div>
    <div class="block tall_rectangle">
      <div class="cart full_bg" style="overflow-y: scroll">
        <h1>SUMMARY</h1>
        <h2>Today: <?= date('M jS, Y') ?></h2>
        <?php
        $openBalanceQuery = mysql_query("SELECT `units`, `unitPrice`, `status` FROM charges");
        $openBalance = 0;
        $totPaid = 0;
        while ($openBalanceRow = mysql_fetch_array($openBalanceQuery)) {
          if ($openBalanceRow['status'] == 'unpaid')
          $openBalance += $openBalanceRow['units'] * $openBalanceRow['unitPrice'];
          else
          $totPaid += $openBalanceRow['units'] * $openBalanceRow['unitPrice'];
        }
        $trackerTotQuery = mysql_query("SELECT `id` FROM tracker WHERE `isbot` = '0'");
        $trackerTot = 0;
        while ($trackerTotRow = mysql_fetch_array($trackerTotQuery)) {
          $trackerTot ++;
        }
        ?>
        <ul>
          <li>Logs: <a target="_blank" href="php/paypal_log.txt">PayPal</a> &bull; <a target="_blank" href="general_log.txt">Email</a></li>
          <li>Sum of open balances: $<?= number_format($openBalance, 2, '.', ',') ?></li>
          <li>Total paid: $<?= number_format($totPaid, 2, '.', ',') ?></li>
          <li>Total page visits: <?= number_format($trackerTot, 0, '.', ','); ?></li>
        </ul>
        <?php
        $clients = getClients();
        $outstandingClients = Client::outstandingClients(true);
        ?>
        <h1>Client List</h1>
        <div style="margin: 5px;" id="adminClientList">
          <ul>
            <li>
              <form action="admin.php" method="post">
                <input type="hidden" name="newClient" value="true">
                <input name="name" placeholder="Name - Return when done..." type="text" class="sixty">
              </form>
            </li>
            <?php
            foreach ($clients as $client) {
              echo '<li>' . $client->adminToString() . (array_search($client->id, $outstandingClients) === false ? '' : ' !') . '</li>';
            }
            ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
  $(function() {
    $(".datePicker").datepicker();

    $("#slider").slider({
      value: 30,
      min: 30,
      max: 360,
      step: 30,
      slide: function(event, ui) {
        $("#amount").val(ui.value);
      }
    });
    $("#amount").val($("#slider").slider("value"));

    $("#editSlider").slider({
      value: <?= ($sessionChargePreset ? $currentCharge->recurPeriod : '30') ?>,
      min: 30,
      max: 360,
      step: 30,
      slide: function(event, ui) {
        $("#editAmount").val(ui.value);
      }
    });
    $("#editAmount").val($("#editSlider").slider("value"));
  });
  </script>
</body>
</html>
<?php ob_flush(); ?>
