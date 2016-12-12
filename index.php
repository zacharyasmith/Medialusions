<?php
include('php/functions.php');

$error_contact = '';
$error_login = isset($_GET['mes']) ? $_GET['mes'] : '';
if (isset($_POST['name'])) {
    if ($_POST['name'] == '' || $_POST['email'] == '' || $_POST['text'] == '' || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error_contact = 'Please fill in all form elements completely';
    } else {
        $error_contact = 'Your message has been sent';
        $from = $_POST['email'];
        sendMail('info@medialusions.com', 'Message from Contact Form - ' . $_POST['name'], $_POST['name'] . '--' . $_POST['text'], $from);
    }##else form submitted
}
if (isset($_POST['user'])) {
    if ($_POST['user'] == '' || $_POST['password'] == '') {
        $error_login = 'Please fill in all form elements completely';
    } else {
        $sql = 'SELECT * FROM clients WHERE email = "' . mysql_real_escape_string($_POST['user']) . '"';
        $result = mysql_query($sql);
        $num_rows = mysql_num_rows($result);
        if ($num_rows == 1) {
            while ($row = mysql_fetch_array($result)) {

                if ($row['password'] === $_POST['password']) {
                    $_SESSION['user'] = $row['id'];
                    $header = isset($_GET['forward']) ? $_GET['forward'] : 'client.php';
                    header('location:' . $header);
                }##check password
                else {
                    $error_login = 'Incorrect user/password combination. Please try again';
                }
            }
        }##check if email exists
        else if ($_POST['user'] == 'zach_admin' && $_POST['password'] == 'bobbYjr1#') {##check for me
            $_SESSION['user'] = 'admin';
            $header = isset($_GET['forward']) ? $_GET['forward'] : 'admin.php';
            header('location:' . $header);
        } else {
            $error_login = 'Incorrect user/password combination. Please try again';
        }
    }##is form submitted and filled?
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

        <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700|Source+Sans+Pro' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
        <!-- font-family: 'Droid Sans', sans-serif; -->
        <title>Local Web Design and Development | Medialusions Interactive | Serving Franktown, Parker and Fort Collins</title>

        <link rel="canonical" href="http://medialusions.com/" />
        <META NAME="Keywords" CONTENT="web design, web development, parker web design, parker web hosting,
              parker email solutions, web design parker co, web hosting parker co, email solutions parker co,
              franktown web design, franktown web hosting, franktown email solutions, 
              web design franktown co, web hosting franktown co, email solutions franktown co" />
        <META NAME="description" CONTENT="Local web development company. Offering complete web marketing solutions.
              Serving Franktown, Parker and Fort Collins." />
        <META NAME="PUBLISHER" CONTENT="www.medialusions.com">
        <META NAME="LANGUAGE" CONTENT="English">
        <META NAME="COPYRIGHT" CONTENT="copyright 2015 Medialusions Interactive, Inc">
        <meta name="norton-safeweb-site-verification" content="mfgps9frbbym2zjlzga2vgs-ve1303q90kad211f1wcv8vzadjhzth5znqb148yi3nyo79qubsaalobnwhbj7nb-8h-u8yvszm3r7e1sjqhavey52n5727q808i4nhfu" />
        <link rel="stylesheet" href="style/Font-Awesome/css/font-awesome.min.css">
        <?php populateHead(); ?>
    </head>
    <body>
        <div style="display: none;">
            Web Design &amp; Development in Franktown, Parker, and Castle Rock, Colorado. Mobile &amp; Responsive websites.
            <h1>Medialusions Interactive</h1>
            <h1>Web Development</h1>
            <h1>Web Design</h1>
            <h1>Email Solutions</h1>
            <h1>eCommerce & Retail</h1>
            <h1>Mobile/Responsive Web</h1>
        </div>
        <div id="contact">
            <div id="form-div">
                <a href="#" class="boxclose" id="boxclose"></a>
                <form  method="post" class="form" id="form1">
                    <?php echo $error_contact; ?>
                    <p class="name">
                        <input name="name" type="text" class="validate[required,custom[onlyLetter],length[0,100]] feedback-input" placeholder="Name" id="name" />
                    </p>

                    <p class="email">
                        <input name="email" type="text" class="validate[required,custom[email]] feedback-input" id="email" placeholder="Email" />
                    </p>

                    <p class="text">
                        <textarea name="text" class="validate[required,length[6,300]] feedback-input" id="comment" placeholder="Comment"></textarea>
                    </p>

                    <div class="submit">
                        <input type="submit" value="SEND" id="button-blue"/>
                        <div class="ease"></div>
                    </div>
                </form>
                <div>
                    <h1>Contact:</h1>
                    <h3>Medialusions Interactive, Inc.</h3>
                    <h3>PO Box 130</h3>
                    <h3>Franktown, CO 80116</h3>
                    <h3>info@medialusions.com</h3>
                    <h3>(303) 549-0491</h3>
                </div>
            </div>
        </div>
        <div id="log-in">
            <div id="form-div">
                <a href="#" class="boxclose" id="boxclose"></a>
                <form method="post" class="form" id="log_in_form">
                    <?php echo $error_login; ?>
                    <p class="user">
                        <input name="user" type="text" class="validate[required,custom[email]] feedback-input" placeholder="Username" id="email" />
                    </p>

                    <p class="password">
                        <input name="password" type="password" class="validate[required,custom[email]] feedback-input" id="comment" placeholder="Password" />
                    </p>

                    <div class="submit">
                        <input type="submit" value="LOG-IN" id="button-blue"/>
                        <div class="ease"></div>
                    </div>
                </form>
                <div>
                    <h1>For Inquiries:</h1>
                    <h3>Medialusions Interactive, Inc.</h3>
                    <h3>PO Box 130</h3>
                    <h3>Franktown, CO 80116</h3>
                    <h3>info@medialusions.com</h3>
                    <h3>(303) 549-0491</h3>
                    <h3>Or <a href="#contact">send us a message</a></h3>
                </div>
            </div>
        </div>
        <div id="header">
            <div id="menu_wrapper">
                <nav>
                    <a href="http://www.medialusions.com">HOME</a>
                    <a href="?s=Website">PORTFOLIO</a>
                    <a href="#contact">CONTACT</a>
                    <a href="<?php echo check_log_in('menu_link'); ?>"><?php echo check_log_in('menu'); ?></a>
                    <?php echo check_log_in('log_out'); ?> 
                    <a href="https://soundcloud.com/medialusions" target="_blank"><i class="fa fa-soundcloud fa-lg"></i></a>
                    <a href="https://www.facebook.com/medialusions" target="_blank"><i class="fa fa-facebook"></i></a>
                    <a href="https://plus.google.com/+Medialusions/posts" target="_blank"><i class="fa fa-google-plus"></i></a>
                    <a href="https://www.youtube.com/c/Medialusions" target="_blank"><i class="fa fa-youtube"></i></a>
                </nav>
            </div>
        </div>
        <div itemscope itemtype="http://schema.org/LocalBusiness" id="grid">
            <div class="block square">
                <a href="http://www.medialusions.com">
                    <img itemprop="image" src="grid/images/logo.png" alt="Medialusions Interactive, Logo" />
                </a>
            </div>
            <?php (isset($_GET['s']) ? populateGrid("WHERE title='" . mysql_real_escape_string($_GET['s']) . "'") : populateGrid()); ?>
        </div>
        <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
            <meta itemprop="streetAddress" content="10418 Tanglewood Rd">
            <meta itemprop="addressLocality" content="Franktown">
            <meta itemprop="addressRegion" content="CO">
            <meta itemprop="addressCountry" content="USA">
            <meta itemprop="postalCode" content="80116"></span>
        <meta itemprop="name" content="Medialusions Interactive">
        <meta itemprop="telephone" content="3035490491">
        <meta itemprop="email" content="info@medialusions.com">
        <script>
            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
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
<?php ob_flush(); ?>
