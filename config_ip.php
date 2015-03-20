<?php
/**
 * Created by PhpStorm.
 * User: theovandersluijs
 * Date: 19/03/15
 * Time: 08:26
 *
 * Please donate a coffee, to keep me coding on this url shortner !!!
 * Bitcoin : 18aJm8qj47iafT5gTgHrBAXzboDS8jEfZM
 * Paypal : http://snurl.eu/coffee
 *
 */
defined('_HIDE_OTHER_SCRIPTS') or die(header('HTTP/1.0 404 Not Found'));

//some mongodb stuff
define('MONGO_DB_NAME', 'ip_addresses');
define('MONGO_DB_COLLECTION', 'trusted_ips');

//some error showing stuff
define('SHOW_ERRORS', 1);

//some test info
define('TEST_IP', "[a outside ip]"); //set to NULL if you don't want to use;

// base location of script (include trailing slash)
define('BASE_HREF', 'http://' . $_SERVER['HTTP_HOST'] . '/'); //I guess no changes needed

//fixed IP list (always access)
$fixed_ip_list_array = array('[IP]', '[IP]', '[IP]'); //list of IP's you always want to grant access

//site stuff
$ip_site_name = "[Websitename]";

//allowed email domains and addresses
$allowed_email_domains = array('[example.com]', '[testme.com]'); //set to NULL if you don't want to use
$allowed_email_addresses = array('[a-valid-email-addess]'); //set to NULL if you don't want to use

//url hasing stuff
$secret_hash = "i:forAd#gKoN/vOp^Wg>Tan>>yaZ=pOk^Gus[vum<>>jws"; // make your own!!!

//url valid time
$times = 3; //number of time_spans, so when time_span is 60 and times is 10, it's 10 minutes
$time_span = 3600; //in Seconds, 60 is a minute, 3600 is an hour
$valid_time = $times*$time_span; //10*600 is 10 minutes 3*3600 is three hours

//ip valid time period
$ip_valid_time_period = 30 * 60 * 60 * 24; //roughly one month, more like 30 days :-)

//mail stuff
$mail_subject = "Your {$ip_site_name} IP activation link!";
$mail_from = "[your from email address]";
$mail_from_name = "[you email name]";

//some alert texts
$email_okay_activation = "IP activation mail send to your mailbox! <br/>Please open your mailbox and press the activation link! <br/> Please check your junk/spam folder when you cannot find the mail!";
$email_fail_activation = "We have a problem sending out the mail! <br/>Please contact your nearest helpdesk!<br/>";
$ip_known = "IP already known! Activation mail send out to known email address!";
$email_not_valid = "is not a valid mail address!";
$email_not_allowed = "is not allowed to get access!";
$ip_not_valid = "This IP address is not valid.";
$incorrect_validate = "This URl is either to old to be validated or it is an incorrect IP! Sorry!";