<?php
/**
 * Flyspray REST Services
 *
 * pdoDB Class
 *
 * Provides a persistant PDO instance across the api service.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray/api
 * @author Steven Tredinnick
 */


class pdoDB
{

    // private statics to hold the connection
    private static $dbConnection = null;

    public static $config = null;

    // make the next 2 functions private to prevent normal
    // class instantiation
    private function __construct()
    {

    }
    private function __clone()
    {
    }

    /**
     * Return DB connection or create initial connection
     * @return object (PDO)
     * @access public
     */
    public static function getConnection()
    {
        //if the config settings havent been loaded then do so now.
        if(self::$config==null)
        {
            self::$config = parse_ini_file('../flyspray.conf.php');
        }


        // if there isn't a connection already then create one
        if ( !self::$dbConnection )
        {
            try
            {
                self::$dbConnection = new PDO( "mysql:host=".self::$config['dbhost'].";dbname=".self::$config['dbname'],
                    self::$config['dbuser'], self::$config['dbpass'] );
                self::$dbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
            catch( PDOException $e )
            {
                // in a production system you would log the error not display it
                echo $e->getMessage();
            }
        }
        // return the connection
        return self::$dbConnection;
    }
}