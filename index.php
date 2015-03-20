<?php
/**
 * Created by PhpStorm.
 * User: theovandersluijs
 * Date: 19/03/15
 * Time: 08:25
 *
 * Please donate a coffee, to keep me coding on this url shortner !!!
 * Bitcoin : 18aJm8qj47iafT5gTgHrBAXzboDS8jEfZM
 * Paypal : http://snurl.eu/coffee
 *
 */
define('_HIDE_OTHER_SCRIPTS', 1);

date_default_timezone_set('Europe/Amsterdam'); //very important!!
include_once('config_ip.php');

if (SHOW_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

include_once('functions_ip.php');
include_once('mongodb_ip.php');

$M_IP_DB = new M_DB_IP(MONGO_DB_NAME,MONGO_DB_COLLECTION);
$M_IP_DB->validator();

//All good!! Let's show this page!
echo '<p>Hello Valid User!</p>';