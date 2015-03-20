<?php
/**
 * Created by PhpStorm.
 * User: theovandersluijs
 * Date: 05/02/15
 * Time: 09:08
 *
 * Please donate a coffee, to keep me coding on this url shortner !!!
 * Bitcoin : 18aJm8qj47iafT5gTgHrBAXzboDS8jEfZM
 * Paypal : http://snurl.eu/coffee
 *
 */
defined('_HIDE_OTHER_SCRIPTS') or die(header('HTTP/1.0 404 Not Found'));
if (SHOW_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

class M_DB_IP
{
    private $_connection;
    public $_db_name;
    public $_collection_name;

    private $_db;
    private $_collection;
    public function __construct($mongodb_db_name, $mongodb_collection)
    {

        try {
            if (class_exists('MongoClient')) {
                $this->_connection = new MongoClient();
            } else {
                die('No Mongo installed?');
            }

            // Save the database name for later use
            $this->_db_name = $mongodb_db_name;

            // Set the collection class name
            $this->_collection_name = $mongodb_collection;

            $this->connect();

            // Create the two collections;
            $this->collection();

        } catch (MongoConnectionException $e) {
            die('Error connecting to MongoDB server');
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Create the connection to be established.
     *
     * @return boolean
     * @throws MongoException
     */
    private function connect()
    {
        try {
            if (!isset($this->_connected) || !$this->_connected) {
                $this->_connected = $this->_connection->connect();
                $this->_db = $this->_connection->selectDB("$this->_db_name");
            }
            return $this->_connected;
        } catch (MongoConnectionException $e) {
            die('Error connecting to MongoDB server');
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Load collection.
     *
     * @return MongoCollection
     * @throws MongoException
     */
    private function collection()
    {
        try {
            if (!$this->_collection) {
                $this->_collection = $this->_db->selectCollection("{$this->_collection_name}");
            }
            return $this->_collection;
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }


    /**
     * destroy connection
     */
    public function __destruct()
    {
        try {
            $this->close();
            $this->_connection = NULL;
            $this->_connected = FALSE;
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Close the connection to Mongo
     *
     * @return  boolean  if the connection was successfully closed
     */
    public function close()
    {
        try {
            if ($this->_connected) {
                $this->_connected = $this->_connection->close();
                $this->_db = "$this->_db";
            }
            return $this->_connected;
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }


    /**
     * its the initial script to give access to a user (or not) and check if he wants to add the ip he's working from.
     */
    function validator()
    {
        global $incorrect_validate,$fixed_ip_list_array;
        $users_ip = GetIP();

        if(!isset($fixed_ip_list_array) || !in_array($users_ip,$fixed_ip_list_array)) {

            //lets first do some cleaning up!
            self::removeOldIPs();

            //no email, no request to validate ip and no valid IP, that's a Bye Bye!
            if (!isset($_REQUEST['validate_url']) && !isset($_REQUEST['add_ip']) && !isset($_REQUEST['email']) && !self::validIP($users_ip)) {
                header("HTTP/1.0 404 Not Found");
                die();
            }

            //email or add_ip request and no valid IP, I guess the user want to add his/her IP
            if (!isset($_REQUEST['validate_url']) && (isset($_REQUEST['add_ip']) || isset($_REQUEST['email'])) && !self::validIP($users_ip)) {
                if (isset($_REQUEST['add_ip'])) {
                    // show email form!
                    include_once('email.form.php');
                    die();
                }

                if (isset($_REQUEST['email'])) {
                    // add email address and reload page!
                    self::insertIP($users_ip, $_REQUEST['email']);
                    die(); // just to be sure!
                }
            }

            //lets dehash
            if (isset($_REQUEST['validate_url'])) {
                if (deHash($_REQUEST['validate_url'], GetIP())) {
                    self::validateIP($users_ip);
                } else {
                    die($incorrect_validate);
                }
            }

            //when all fails and no valid IP! Bye bye!
            if (!self::validIP($users_ip)) {
                header("HTTP/1.0 404 Not Found");
                die();
            }
        }
    }


    /**
     * Function to get database stats
     * @return mixed
     */
    function mongoDbStats()
    {
        /* @var _collection MongoDB */
        return $this->_db->command(array('dbStats' => 1));
    }


    /**
     * this function will do some cleaning when there is a ip valid time period var
     */
    function removeOldIPs()
    {
        global $ip_valid_time_period;

        if (isset($ip_valid_time_period)) {
            $now = time();

            $prev_period = $now - $ip_valid_time_period;

            //lets remove ip's older then the $ip_valid_time_period!
            $ops[]['$match'] = array('created' => array('$lte' => $prev_period));

            $g = $this->_collection->aggregate($ops);

            foreach ($g['result'] as $result) {
                $id = $result['_id']['$id'];
                $this->_collection->remove(array('_id' => $id));
            }
        }
    }

    /**
     * check if ip is valid
     * @param $ip
     * @return bool
     */
    function validIP($ip)
    {
        $ops[]['$match'] = array('ip' => $ip);

        $g = $this->_collection->aggregate($ops);
        if (isset($g['result'][0]) && isset($g['result'][0]['ip']) && $g['result'][0]['valid'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * check if the ip exists within the system
     * @param $ip
     * @return bool or result!
     */
    function knownIP($ip)
    {
        $ops[]['$match'] = array('ip' => $ip);

        $g = $this->_collection->aggregate($ops);

        if (isset($g['result'][0])) {
            return $g['result'][0];
        }

        return false;
    }

    /**
     * Function to ADD Long URL and create short URL
     * @param $long_url
     * @param null $short_url
     * @return bool|null|string
     */
    function insertIP($ip, $email)
    {
        global $ip_known, $email_okay_activation, $allowed_email_domains, $allowed_email_addresses, $email_not_allowed, $email_not_valid, $ip_not_valid, $email_fail_activation;

        try {
            //Cleanup email address
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            // Make sure the email address is valid
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $domain = explode('@', $email);

                //make sure email is within allowed domains of within allowed email address list
                if ((isset($allowed_email_domains) && !in_array($domain[1], $allowed_email_domains)) && (isset($allowed_email_addresses) && !in_array($email, $allowed_email_addresses))) {
                    die("{$email} {$email_not_allowed}");
                }

            } else {
                die("{$email} {$email_not_valid}");
            }

            //check if it is a valid IP!
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                die("{$ip} {$ip_not_valid}");
            }


            $timestamp = time();
            //search for IP in MongoDB
            $foundIPs = self::knownIP($ip);

            if (isset($foundIPs) && isset($foundIPs['ip']) && $foundIPs['valid'] == 0) {
                sendMail($foundIPs['email'], $ip);
                die($ip_known);
            }


            //not found, then insert
            $newIP['ip'] = (string)$ip;
            $newIP['email'] = (string)$email;
            $newIP['valid'] = 0;
            $newIP['created'] = $timestamp;

            //save stuff in MongoDB
            $this->_collection->insert($newIP);

            //ip address added, lets send an activation mail !
            if (sendMail($email, $ip)) {
                die($email_okay_activation);
            } else {
                die($email_fail_activation);
            }

        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * search for the user and change valid = 0 to 1 and give him access!
     * @param $ip
     */
    function validateIP($ip)
    {
        try {
            $row = self::knownIP($ip);
            $this->_collection->update(array("ip" => $row['ip']), array('$set' => array("valid" => 1)));
        } catch (MongoException $e) {
            die('Error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log($e->getMessage());
            if (SHOW_ERRORS) {
                echo $e->getMessage();
            }
        }
    }


}