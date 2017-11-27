<?php
include_once ('constants.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

ob_start();
session_start();
//$site_base = 'http://localhost:8888/medialusions';
$site_base = 'http://www.medialusions.com';

include_once ('mysqlConnect.php');
include_once ('resize.php');
include_once ('ip2locationlite.class.php');

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    if (!isset($_GET['header'])) {
        header('Location: '.$site_base.'?logout&header');
    } else {
        header('location: '.$site_base.'');
    }
}

date_default_timezone_set('America/Denver');

//define("UPLOAD_DIR", "/webroot/medialusions.com/grid/images/");
define("IP_LOCATION_API_KEY", "6bdffd2414061ffd9a5122070e642a4d29373584f0a0fc249bde5c27e29ae2ca");

function is_bot() {
    $botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
        "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
        "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
        "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
        "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
        "Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
        "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler", "TweetmemeBot",
        "Butterfly", "Twitturls", "Me.dium", "Twiceler", "Baiduspider", "spbot",
        "Bot", "bot", "Spider", "spider");
    foreach ($botlist as $bot) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
            return true;
    }
    return false;
}

function tracker() {
    $http_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $user_id = isset($_SESSION['user']) ? $_SESSION['user'] : '';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    $http_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    //Load the class
    //$ipLite = new ip2location_lite;
    //$ipLite->setKey(IP_LOCATION_API_KEY);
    //Get errors and locations
    //$locations = $ipLite->getCity($ip);
    //$errors = $ipLite->getError();
    //Getting the result
//    if (!empty($locations) && is_array($locations)) {
//      foreach ($locations as $field => $val) {
//        if ($field == 'countryName'){
//            $country = $val;
//        }
//        if ($field == 'cityName'){
//            $city = $val;
//        }
//      }
//    }
    $country = "N/A";
    $city = "N/A";
    if (is_bot()) {
        $isbot = 1;
    } else {
        $isbot = 0;
    }
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $query = "INSERT INTO `tracker` (`user_id`,`country`,`city`,`date`, `time`, `ip`, `query_string`, `http_url`, `http_referer`, `http_user_agent`, `isbot`)
    VALUES ('$user_id','$country','$city','$date', '$time', '$ip', '$query_string', '$http_url', '$http_referer' ,'$http_user_agent' , $isbot)";
    mysql_query($query);
}

function populateHead() {
    tracker();
    echo '<link rel="stylesheet" type="text/css" href="style/home.css"/>
    <link rel="stylesheet" type="text/css" href="style/form.css"/>
    <link rel="stylesheet" type="text/css" href="style/jquery-ui.min.css"/>
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="style/scripts/jquery-ui.min.js"></script>
    <script src="style/ckeditor/ckeditor.js"></script>
    <link rel="icon" type="image/png" href="style/images/favicon.png" />';
}

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars), 0, $length);
}

function urlCheck($file) {
    $file_headers = @get_headers($file);
    if ($file_headers[0] == 'HTTP/1.1 404 Not Found' && $file != '') {
        return false;
    } else {
        return true;
    }
}

function getExtension($str) {
    $i = strrpos($str, ".");
    if (!$i) {
        return "";
    }
    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    return $ext;
}

function fileUpload($shape) {
    $filename = $_FILES['gridImage']['name'];
    $extension = getExtension($filename);
    $extension = '.' . strtolower($extension);
    $newname = rand_string(10) . $extension;
    move_uploaded_file($_FILES['gridImage']['tmp_name'], "grid/images/$newname");
    // *** 1) Initialize / load image
    $resizeObj = new resize('grid/images/' . $newname);
    // *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
    if ($shape == 'square' || $shape == 'Square') {
        $resizeObj->resizeImage(310, 240, 'exact');
    } else {
        $resizeObj->resizeImage(630, 240, 'exact');
    }
    // *** 3) Save image
    $resizeObj->saveImage('grid/images/' . $newname);
    return "$newname";
}

function invoiceUpload($invoiceID) {
    $filename = $_FILES['invoice']['name'];
    $extension = getExtension($filename);
    $extension = '.' . strtolower($extension);
    $newname = $invoiceID . $extension;
    move_uploaded_file($_FILES['invoice']['tmp_name'], "files/invoices/$newname");
    return "$newname";
}

function newGridItem($shape, $url, $title, $text, $file_src) {
    $link = (urlCheck($url) ? $url : 'false');

    $sql = ("INSERT INTO  `medialusions`.`grid` (`url`, `title`, `sub_title`, `file_src`, `shape`) VALUES ('$link',  '$title',  '$text',  '$file_src',  '$shape');");
    mysql_query($sql) or die('Could not import grid item');
}

function populateGrid($select = '') {
    $sql = "SELECT * FROM `grid` " . $select . "  ORDER BY rand();";
    $queryResult = mysql_query($sql) or die(mysql_error());
    $result = '';
    while ($row = mysql_fetch_array($queryResult)) {
        if ($row['type'] === 'image') {
            $result .= '<div class="block ' . $row['shape'] . ($row['title'] != '' ? ' w_title' : '') . '">';
            $result .= ($row['url'] != '' ? '<a target="_blank" href="' . $row['url'] . '">' : '');
            $result .= '<img src="grid/images/' . $row['file_src'] . '" alt="' . $row['title'] . ' | ' . $row['sub_title'] . '">';
            if ($row['title'] != '') {
                $result .= '<div class="title">'
                        . '<h1>' . $row['title'] . ' </h1>'
                        . '<h2>' . $row['sub_title'] . ' </h2>'
                        . '</div>';
            }
            $result .= ($row['url'] != '' ? '</a>' : '');
            $result .= '</div>';
        } else if ($row['type'] === 'video') {
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
            $result .= ($row['title'] != '' ? '<div class="title"><h1>' . $row['title'] . '</h1><h2>' . $row['sub_title'] . '</h2></div>' : '');
            $result .= '</div>';
        }
    }
    echo $result;
}

function insertLog($text) {

    $log = date("j.n.Y-G:i:s") . " - $text"
            . PHP_EOL;
    //Save string to log, use FILE_APPEND to append.
    echo file_put_contents('./general_log.txt', $log, FILE_APPEND);
}

function sendMail($to, $subject = "Your Monthly Account Summary", $message = "", $from = "info@medialusions.com") {

    insertLog("sendEmail initiated");
    insertLog("email - " . $to['user']);

    $htmlTemplate = file_get_contents('php/MonthlyUpdateTemplate.html');
    $htmlTemplate = str_replace('{NAME}', $to['name'], $htmlTemplate);
    $htmlTemplate = str_replace('{UNAME}', $to['user'] . ' (Pwd: ' . $to['pass'] . ')', $htmlTemplate);
    $htmlTemplate = str_replace('{CONTENT}', $message, $htmlTemplate);
    $htmlTemplate = str_replace('*|CURRENT_YEAR|*', date('Y'), $htmlTemplate);
    $htmlTemplate = str_replace('*|ADDRESS|*', ADDRESS1 . ' ' . ADDRESS2, $htmlTemplate);
    $htmlTemplate = str_replace('*|LIST:COMPANY|*', 'Medialusions Interactive, Inc', $htmlTemplate);
    //echo $htmlTemplate;
    //die;
    // To send HTML mail, the Content-type header must be set

    $header = "From: Medialusions Interactive <" . $from . "> \r\n"
            . "Reply-To: Medialusions Interactive <" . $from . "> \r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $success = mail($to['name'] . ' <' . $to['user'] . '>', $subject, $htmlTemplate, $header);
    if ($success) {
        insertLog("Mail sent succesfully");
    } else {
        insertLog("Mail send failed");
    }
}

function check_log_in($what = 'client', $type = 'default', $return_url = false) {
    if ($what == 'admin') {
        if ($type == 'boolean') {
            return $_SESSION['user'] == 'admin';
        }
        ##admin check
        if ($_SESSION['user'] != 'admin') {
            unset($_SESSION['user']);
        }
        if (is_null($_SESSION['user'])) {
            $full_url = $return_url ? '&forward=http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'] : '';
            header('Location: '.$site_base.'?mes=Login first to view this page.' . $full_url . '#log-in');
            die;
        }
    } else if ($what == 'client') {
        if ($type == 'boolean') {
            return isset($_SESSION['user']) && is_numeric($_SESSION['user']);
        }
        ##client check
        if (!is_numeric($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
        if (is_null($_SESSION['user'])) {
            $full_url = $return_url ? '&forward=http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'] : '';
            header('Location: '.$site_base.'?mes=Login first to view this page.' . $full_url . '#log-in');
            die;
        }
    } else if ($what == 'menu') {
        #menu text return
        if (!isset($_SESSION['user'])) {
            return 'CLIENT LOG-IN';
        } else if ($_SESSION['user'] == 'admin') {
            return 'Admin';
        } else if (is_numeric($_SESSION['user'])) {
            $toReturn = 'Your account';
            return $toReturn;
        } else {
            return $_SESSION['user'] . 'Your account';
        }
    } else if ($what == 'menu_link') {
        ##menu link return
        if (!isset($_SESSION['user'])) {
            return '#log-in';
        } else if ($_SESSION['user'] == 'admin') {
            return 'admin.php';
        } else {
            return 'client.php';
        }
    } else if ($what == 'log_out') {
        ##log out link return
        if (!isset($_SESSION['user'])) {
            return '';
        } else {
            return '<a href="?logout">Log-out</a>';
        }
    }
}

function client_search($id, $what = 'name') {
    $sql = 'SELECT ';
    $error = false;
    switch ($what) {
        case 'name':
        case 'email':
        case 'company':
        case 'address':
        case 'city':
        case 'state':
        case 'zip':
        case 'phone';
        case 'password':
        case 'website':
            $sql .= $what . ' ';
            break;
        default:
            $error = true;
            break;
    }
    if ($error) {
        return false;
    }
    $sql .= 'FROM clients WHERE id="' . $id . '";';
    $result = mysql_query($sql) or die(mysql_error());
    $arr = mysql_fetch_array($result);
    $numRows = mysql_num_rows($result);
    $error = $numRows != 1;
    if (!$error) {
        return $arr[0];
    }
    return false;
}

function grid_search($id, $what = 'file_src') {
    $sql = 'SELECT ';
    $error = false;
    switch ($what) {
        case 'file_src':
        case 'shape':
        case 'sub_title':
        case 'title':
        case 'url':
        case 'type':
            $sql .= $what . ' ';
            break;
        default:
            $error = true;
            break;
    }
    if ($error) {
        return false;
    }
    $sql .= 'FROM grid WHERE id="' . $id . '";';
    $result = mysql_query($sql) or die(mysql_error());
    $arr = mysql_fetch_array($result);
    $numRows = mysql_num_rows($result);
    $error = $numRows != 1;
    if (!$error) {
        return $arr[0];
    }
    return false;
}

function charge_search($id, $what = 'invoiceNumber', $where = 'id', $limit = -1) {
    $sql = 'SELECT ';
    $error = false;
    switch ($what) {
        case 'invoiceNumber':
        case 'clientId':
        case 'description':
        case 'unitPrice':
        case 'units':
        case 'recurring':
        case 'recurPeriod':
        case 'status':
        case 'dueDate':
        case 'interestPercentage':
            $sql .= $what . ' ';
            break;
        default:
            $error = true;
            break;
    }
    if ($error) {
        return false;
    }
    $sql .= 'FROM charges WHERE ' . $where . '="' . $id . '"' . ($limit > 0 ? ' LIMIT ' . $limit : '') . ';';
    //die($sql);
    $result = mysql_query($sql) or die(mysql_error());
    $arr = mysql_fetch_array($result);
    $numRows = mysql_num_rows($result);
    $error = $numRows != 1;
    if (!$error) {
        return $arr[0];
    }
    return false;
}

function payment_search($id, $what = 'method', $where = 'id', $limit = -1) {
    $sql = 'SELECT ';
    $error = false;
    switch ($what) {
        case 'date_posted':
        case 'method':
        case 'identifier':
        case 'date_added':
            $sql .= $what . ' ';
            break;
        default:
            $error = true;
            break;
    }
    if ($error) {
        return false;
    }
    $sql .= 'FROM payments WHERE ' . $where . '="' . $id . '"' . ($limit > 0 ? ' LIMIT ' . $limit : '') . ';';
    //die($sql);
    $result = mysql_query($sql) or die(mysql_error());
    $arr = mysql_fetch_array($result);
    $numRows = mysql_num_rows($result);
    $error = $numRows != 1;
    if (!$error) {
        return $arr[0];
    }
    return false;
}

function invoice_search($invoiceId) {
    $sql = 'SELECT * FROM charges WHERE invoiceNumber="' . $invoiceId . '";';
    $result = mysql_query($sql) or die(mysql_error());
    $numRows = mysql_num_rows($result);
    if ($numRows >= 1) {
        $toRet = array();
        $index = 0;
        while ($row = mysql_fetch_array($result)) {
            $toRet[$index] = new charge($row['id']);
            $index++;
        }
        return $toRet;
    } else {
        return false;
    }
}

function getGridItems() {
    $sql = "SELECT * FROM `grid` ORDER BY id DESC;";
    $queryResult = mysql_query($sql) or die(mysql_error());
    $toRet = array();
    $index = 0;
    while ($row = mysql_fetch_array($queryResult)) {
        $toRet[$index] = new grid($row['id']);
        $index++;
    }
    return $toRet;
}

function getClients() {
    $sql = "SELECT * FROM `clients` ORDER BY name;";
    $queryResult = mysql_query($sql) or die(mysql_error());
    $toRet = array();
    $index = 0;
    while ($row = mysql_fetch_array($queryResult)) {
        $toRet[$index] = new client($row['id']);
        $index++;
    }
    return $toRet;
}

function getCharges($clientId) {
    $invoiceSql = "SELECT DISTINCT invoiceNumber FROM charges WHERE clientId=\"" . $clientId . "\" ORDER BY dueDate DESC;";
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

function getNextInvoice() {
    $result = mysql_query("SELECT invoiceNumber FROM charges ORDER BY invoiceNumber DESC LIMIT 1;") or die(mysql_error());
    $array = mysql_fetch_array($result);
    return $array[0] + 1;
}

/**
 * Truncates text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 * @return string Trimmed string.
 */
function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
    if ($considerHtml) {
        // if the plain text is shorter than the maximum length, return the whole text
        if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }

        // splits all html-tags to scanable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

        $total_length = strlen($ending);
        $open_tags = array();
        $truncate = '';

        foreach ($lines as $line_matchings) {
            // if there is any html-tag in this line, handle it and add it (uncounted) to the output
            if (!empty($line_matchings[1])) {
                // if it’s an “empty element” with or without xhtml-conform closing slash (f.e.)
                if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                    // do nothing
                    // if tag is a closing tag (f.e.)
                } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                    // delete tag from $open_tags list
                    $pos = array_search($tag_matchings[1], $open_tags);
                    if ($pos !== false) {
                        unset($open_tags[$pos]);
                    }
                    // if tag is an opening tag (f.e. )
                } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                    // add tag to the beginning of $open_tags list
                    array_unshift($open_tags, strtolower($tag_matchings[1]));
                }
                // add html-tag to $truncate’d text
                $truncate .= $line_matchings[1];
            }

            // calculate the length of the plain text part of the line; handle entities as one character
            $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
            if ($total_length + $content_length > $length) {
                // the number of characters which are left
                $left = $length - $total_length;
                $entities_length = 0;
                // search for html entities
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                    // calculate the real length of all entities in the legal range
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entities_length <= $left) {
                            $left--;
                            $entities_length += strlen($entity[0]);
                        } else {
                            // no more characters left
                            break;
                        }
                    }
                }
                $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                // maximum lenght is reached, so get off the loop
                break;
            } else {
                $truncate .= $line_matchings[2];
                $total_length += $content_length;
            }

            // if the maximum length is reached, get off the loop
            if ($total_length >= $length) {
                break;
            }
        }
    } else {
        if (strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = substr($text, 0, $length - strlen($ending));
        }
    }

    // if the words shouldn't be cut in the middle...
    if (!$exact) {
        // ...search the last occurance of a space...
        $spacepos = strrpos($truncate, ' ');
        if (isset($spacepos)) {
            // ...and cut the text in this position
            $truncate = substr($truncate, 0, $spacepos);
        }
    }

    // add the defined ending to the text
    $truncate .= $ending;

    if ($considerHtml) {
        // close all unclosed html-tags
        foreach ($open_tags as $tag) {
            $truncate .= '';
        }
    }

    return $truncate;
}
