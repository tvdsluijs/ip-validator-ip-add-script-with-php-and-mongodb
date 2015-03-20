<?php
/**
 * Created by PhpStorm.
 * User: theovandersluijs
 * Date: 19/03/15
 * Time: 08:28
 *
 * Please donate a coffee, to keep me coding on this url shortner !!!
 * Bitcoin : 18aJm8qj47iafT5gTgHrBAXzboDS8jEfZM
 * Paypal : http://snurl.eu/coffee
 *
 */
defined('_HIDE_OTHER_SCRIPTS') or die(header('HTTP/1.0 404 Not Found'));


/**
 * get real the users IP!
 * @return mixed
 */
function GetIP()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
    {
        if (array_key_exists($key, $_SERVER) === true)
        {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
            {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                {
                    return $ip;
                }
            }
        }
    }

   // return TEST_IP;

}


/**
 * a mail function to send the hash and url to the user
 * @param $email
 * @param $ip
 * @return bool
 */
function sendMail($email, $ip){
    global $mail_subject,$mail_from,$ip_site_name;

    try {
        $hash = createMailHash(GetIP());

        if (!isset($hash['hash'])) {
            die('Houston, we have a hash creating problem!');
        }

        $url = BASE_HREF . "?validate_url={$hash['hash']}";

        if (!isset($email)) {
            die('Houston, we have a email address problem!');
        }

        $to = $email;

        $subject = $mail_subject;

        $headers = "From: " . strip_tags($mail_from) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($mail_from) . "\r\n";

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


        $message = "<html>";
        $message .= "<body>";
        $message .= "<p>Thank you for requesting to Add your IP.</p>";
        $message .= "<p>Please click (or copy/paste in browser) the below link to activate your IP address.</p>";
        $message .= "<p><a href='{$url}'>{$url}</a></p>";
        $message .= "<p>This link will be valid until : {$hash['human_readable_time']}</p>";
        $message .= "<p>This link has to be opened from the ip address you want to add : {$ip}</p>";
        $message .= "<p>With kind Retards</p>";
        $message .= "<p>Your {$ip_site_name} Team</p>";
        $message .= "</body>";
        $message .= "</html>";

        return mail($to, $subject, $message, $headers);

    }catch(Exception $e) {
        echo 'Message: ' .$e->getMessage();
    }

}

/**
 * a great and genius hashing function for the url that is send by mail to the user
 * @param $ip
 * @return mixed
 */
function createMailHash($ip){
    global $secret_hash,$valid_time,$time_span;
    $timestamp = time();
    $ramdomhash = randomHash(7);

    $nearest = round($timestamp/$time_span)*$time_span;

    $newTimestamp = ($nearest + $valid_time);

    $human_readable_time = date("Y-M-d H:i:s", $newTimestamp);

    $hash = sha1($newTimestamp.$ip.$secret_hash.$ramdomhash);

    $outcome['hash'] = $hash.":".$ramdomhash;
    $outcome['$newTimestamp'] = $newTimestamp;
    $outcome['human_readable_time'] = $human_readable_time;

    return $outcome;

}

/**
 * create a nice little salt!
 * @param int $length
 * @return string
 */
function randomHash( $length = 16 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $size = strlen( $chars );
    $str = '';
    for( $i = 0; $i < $length; $i++ ) {
        $str .= $chars[ rand( 0, $size - 1 ) ];
    }
    return $str;
}

/**
 * a great and genius deHashing function
 * @param $hash
 * @param $ip
 * @return bool
 */
function deHash($hash,$ip){
    global $secret_hash,$valid_time,$time_span,$times;
    $timestamp = time();

    //first split hash
    $hash_parts = explode(':', $hash);
    $user_hash = $hash_parts[0]; //real user hash
    $ramdomhash = $hash_parts[1]; //so called random hash

    //let's create the current closed to the time span time (could be hour, 10 minutes what ever)
    $nearest = round($timestamp/$time_span)*$time_span;

    //lets get all the hashes for the next 3 hours or ten minutes or what ever you put in the config!)
    //I need to rebuild to turn it up side down... it will be faster then, because you start with the most equasion that will most likely match.
    for($i=0; $i <= $times; $i++ ) {
        $newTimestamp = ($nearest + ($i * $time_span));

        $hash = sha1($newTimestamp . $ip . $secret_hash . $ramdomhash);
        if ($user_hash == $hash) { //if hash is correct lets give access!!!
            return true;
        }
    }

    return false;

}

/**
 * Like a var dump but better for arrays
 * @param $arg
 * @param string $title
 */
function print_pre($arg, $title = '')
{
    $bt = debug_backtrace();
    $file = $bt[0]['file'];
    $line = $bt[0]['line'];
    echo "<pre>[$file:$line]\n";
    if ($title) {
        echo "$title:";
    }
    if (is_array($arg)) {
        $n = count($arg);
        echo "[$n elements] ";
    }
    print_r($arg);
    echo "</pre>";
}