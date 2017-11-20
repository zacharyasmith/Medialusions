<?php

class Client {

    public $id, $name, $company, $email, $password, $address, $city, $state, $zip, $phone, $website, $staticInformation;

    function __construct($CID = '', $name = '', $sessionSelect = false) {
        if ($CID != '') {
            $this->id = $CID;
        } else {
            $sql = "INSERT INTO  `medialusions`.`clients` (`id` , `name`) VALUES "
                    . "(NULL, '$name');";
            mysql_query($sql) or die(mysql_error());
            $this->id = mysql_insert_id();
        }
        $this->staticInformation = client_search($this->id, 'staticInformation');
        $this->name = client_search($this->id, 'name');
        $this->company = client_search($this->id, 'company');
        $this->email = client_search($this->id, 'email');
        $this->password = client_search($this->id, 'password');
        $this->address = client_search($this->id, 'address');
        $this->city = client_search($this->id, 'city');
        $this->state = client_search($this->id, 'state');
        $this->zip = client_search($this->id, 'zip');
        $this->phone = client_search($this->id, 'phone');
        $this->website = client_search($this->id, 'website');
        if($sessionSelect)
            $this->select();
    }

    static function outstandingClients() {
      $query = mysql_query("SELECT `dueDate`, `clientId` FROM charges WHERE status='unpaid' GROUP BY(clientId) ORDER BY dueDate ASC");

      $ret_val = array();
      while ($row = mysql_fetch_array($query)) {
        array_push($ret_val, new Client($row['clientId']));
      }

      return $ret_val;
    }

    function findLastLoginDate() {
        $query = mysql_query("SELECT `date` FROM tracker WHERE user_id='$this->id' ORDER BY STR_TO_DATE(date,'%Y-%m-%d') DESC LIMIT 1");
        $numRow = mysql_num_rows($query);
        if ($numRow == 1) {
            $row = mysql_fetch_array($query);
            return date('M jS, Y', strtotime($row['date']));
        } else {
            return "none...";
        }
    }

    function delete() {
        mysql_query("DELETE FROM clients WHERE id=\"$this->id\";") or die(mysql_error());
        unset($_SESSION['adminClient']);
    }

    function select() {
        $_SESSION['adminClient'] = $this->id;
    }

    public function updateDB($what, $value) {
        $sql = "UPDATE clients SET " . $what . "=\"" . $value . "\" WHERE id=" . $this->id . ";";
        mysql_query($sql) or die(mysql_error());
    }

    function displayInformation($admin = false) {
        if ($this->staticInformation != 'true') {
            echo $this->company != '' ? (strlen($this->company) > 23 ? substr($this->company, 0, 24) . '...' : $this->company) . '<br>' : 'Not on file' . '<br>';
            echo $this->email != '' ? $this->email . '<br>' : 'Not on file' . '<br>';
            echo $this->phone != '' ? $this->phone . '<br>' : 'Not on file <br>';
            echo $this->address != '' ? $this->address . '<br>' : 'Not on file' . '<br>';
            echo ($this->city != '' ? $this->city . ', ' . $this->state . ' ' . $this->zip : 'Not on file') . '<br>';
            echo ($admin ? '<a href="?id=' . $this->id . '&sendEmail&client">Send Monthly Update</a><br>' : '<a href="?id=' . $this->id . '&setStaticInformation=false&client">Update Information</a>');
        } else {
            echo '<a href="?id=' . $this->id . '&setStaticInformation=true">Done</a>';
        }
    }

    function sendEmail() {
        $message = '<div align="center">';

        $emailSubject = $this->name . '\'s ' . date('F') . ' Account Summary';
        //sendMail(array('user' => $this->email, 'name' => $this->name), $emailSubject, $message);

        $invoices = $this->getCharges(); //get all invoices that have unpaid status
        $total = 0;
        $numInvoices = 0;
        foreach ($invoices as $invoice) {
            $invoiceNum = $invoice[0]->invoiceNumber;
            $dueDate = date('m/d/Y', $invoice[0]->dueDate);
            if ($numInvoices > 0)
                $message .= '<br/>';
            $message .= '<h4 style="display: block;margin: 0;padding: 0;color: #202020;font-family: Helvetica;font-size: 22px;font-style: normal;font-weight: bold;line-height: 125%;letter-spacing: normal;text-align: left;">'
                    . 'Invoice #' . $invoiceNum . ' - Due within 30 days of ' . $dueDate . '</h4>
                        <div class="boxer" style="display: table;border-collapse: collapse;width: 100%;">
<div class="box-row" style="font-weight: bold;display: table-row;border-top: 1px dashed;">
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;">Units</div>
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;">Description</div>
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;">Price/Unit</div>
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;">Line Total</div>
                        </div>';
            foreach ($invoice as $charge) {
                $total += $charge->units * $charge->unitPrice;
                $message .= '<div class="box-row" style="display: table-row;border-top: 1px dashed;">
                            <div class="box" style="display: table-cell;text-align: left;vertical-align: top;">' . $charge->units . '</div>
                            <div class="box" style="display: table-cell;text-align: left;vertical-align: top;">' . $charge->description . '</div>
                            <div class="box" style="display: table-cell;text-align: left;vertical-align: top;">' . sprintf('%0.2f', $charge->unitPrice) . '</div>
                            <div class="box" style="display: table-cell;text-align: left;vertical-align: top;">' . sprintf('%0.2f', $charge->units * $charge->unitPrice) . '</div>
                            </div>';
            }
            $message .= '<div class="box-row" style="font-weight: bold;display: table-row;border-top: 1px dashed;">
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;"></div>
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;"></div>
<div class="box" style="display: table-cell;text-align: left;vertical-align: top;">Total Price</div>
                        <div class="box" style="display: table-cell;text-align: left;vertical-align: top;">' . sprintf('%0.2f', $total) . '</div>
                        </div>
                        </div>';
            $total = 0;
            $numInvoices++;
        }
        
        if($numInvoices == 0){
            //DO NOTHING FOR NOW...
        }else{
            $message .= '</div>';
            sendMail(array('user' => $this->email, 'name' => $this->name, 'pass' => $this->password), $emailSubject, $message);
        }

    }

    function getCharges($status = 'unpaid') {
        $invoiceSql = "SELECT * FROM charges WHERE clientId='$this->id' AND status='$status' ORDER BY invoiceNumber DESC;";
        $invoiceQueryResult = mysql_query($invoiceSql) or die(mysql_error());
        $toRet = array();
        while ($row = mysql_fetch_array($invoiceQueryResult)) {
            $toRet[$row['invoiceNumber']] = array();
        }

        foreach ($toRet as $key => $value) {
            $sql = 'SELECT id FROM charges WHERE invoiceNumber="' . $key . '" ORDER BY id;';
            $queryResult = mysql_query($sql);
            $i = 0;
            while ($row = mysql_fetch_array($queryResult)) {
                $toRet[$key][$i] = new charge($row['id']);
                $i++;
            }
        }
        return $toRet;
    }

    function adminToString() {
        $toRet = "<a href=\"?id=" . $this->id . "&select&client\"><strong>" . $this->name . "</strong></a>"
                . ' &bull; <a href="?id=' . $this->id . '&delete&client" style="color: maroon;">delete</a><br/>'
                . ($this->company != '' ? $this->company : '');

        return $toRet;
    }

    function displayPreview() {
        $sql = 'SELECT * FROM `grid` WHERE url="' . $this->website . '";';
        $queryResult = mysql_query($sql) or die(mysql_error());
        $result = '';
        if (mysql_num_rows($queryResult) != 1) { //There are no projects
            $result .= '<div class="block rectangle">'
                    . '<div class="no_website full_bg">'
                    . '<h1>We do not have a project preview available for your account</h1>'
                    . '<h2>Check back later for an update on your preview!</h2>'
                    . '</div></div>';
        } else { //There is one project; choose and build
            while ($row = mysql_fetch_array($queryResult)) {
                if ($row['type'] === 'image') { //image
                    $result .= '<div class="block ' . $row['shape'] . ($row['title'] != '' ? ' w_title' : '') . '">';
                    $result .= ($row['url'] != '' ? '<a target="_blank" href="' . $row['url'] . '">' : '');
                    $result .= '<img src="grid/images/' . $row['file_src'] . '" title="' . $row['title'] . '">';
                    if ($row['title'] != '') {
                        $result .= '<div class="title"><h1>Preview</h1><h2>This is the preview for your project</h2></div>';
                    }
                    $result .= ($row['url'] != '' ? '</a>' : '');
                    $result .= '</div>';
                } else if ($row['type'] === 'video') { //video
                    $result .= '<div class="block video">';
                    $result .= '<video height="240" autoplay loop muted>';
                    $videoNames = explode(',', $row['file_src']);
                    foreach ($videoNames as $videoName) {
                        $seperateEntities = explode('.', $videoName);
                        $extension = end($seperateEntities);
                        $result .= '<source src="grid/videos/' . $seperateEntities[0] . '.' . $extension . '" type="video/' . $extension . '">';
                    }
                    $result .= 'Your browser does not support HTML5 video.';
                    $result .= '</video>';
                    $result .= ($row['title'] != '' ? '<div class="title"><h1>Preview</h1><h2>This is the preview for your project</h2></div>' : '');
                    $result .= '</div>';
                } //end video and image dev
            } //while loop
        } //end if
        return $result;
    }

    public function setStaticInformation($set = 'true') {
        $this->updateDB('staticInformation', $set);
        $this->staticInformation = $set;
    }

    public function setName($name) {
        $this->updateDB('name', $name);
        $this->name = $name;
    }

    public function setCompany($company) {
        $this->updateDB('company', $company);
        $this->company = $company;
    }

    public function setEmail($email) {
        $this->updateDB('email', $email);
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->updateDB('password', $password);
        $this->password = $password;
    }

    public function setAddress($address) {
        $this->updateDB('address', $address);
        $this->address = $address;
    }

    public function setCity($city) {
        $this->updateDB('city', $city);
        $this->city = $city;
    }

    public function setState($state) {
        $this->updateDB('state', $state);
        $this->state = $state;
    }

    public function setZip($zip) {
        $this->updateDB('zip', $zip);
        $this->zip = $zip;
    }

    public function setPhone($phone) {
        $this->updateDB('phone', $phone);
        $this->phone = $phone;
    }

    public function setWebsite($website) {
        $this->updateDB('website', $website);
        $this->website = $website;
    }

    public function functionRoute($name, $value = '') {
        $this->$name($value);
    }

    public function thankYou() {
        unset($_SESSION['shoppingCart']);
    }

}
