<?php

//Variables for connecting to your database.
//These variable values come from your hosting account.
$hostname = "localhost";
$username = "medialusions";
$dbname = "medialusions";

//These variable values need to be changed by you before deploying
$password = "bobbYjr1#";


//Connecting to your database
mysql_connect($hostname, $username, $password) OR DIE("Unable to 
            connect to database! Please try again later.");
mysql_select_db($dbname) OR DIE('Unable to connect to DataBase, please try later!');
