<?php
/**
 * NSLookup - PHP class query internet name servers
 *
 * NSLookup is a class to query internet domain servers. NSLookup returns
 * an array containing the results of the query. An instance of the class
 * requires the domain for which to query.  The NSLookup method requires the
 * type of query to perform.  Options are: all, any, a, mx, ns, txt
 * 
 * Example Usage:
 * $lookup = new NSLookup("example.com");
 * $lookup->nslookup("all");
 *
 * @package     NSLookup
 * @author      Al Zuniga <al.zuniga@alzuniga.com>
 * @version     0.1 alpha
 * @copyright   Al Zuniga 2019
 * @license     https://mit-license.org/
 */
class NSLookup
{
    /**
     * @var string $domain Domain name to lookup.
     */
    public $domain;

    /**
     * Sanitize and set domain
     * 
     * @param string $domain
     */
    public function __construct( $domain )
    {
        try
        {
            $this->validate_domain( $domain );
        }
        catch ( Exception $e )
        {
            $this->display_exception($e);
        }

        $this->domain = escapeshellarg( $domain );
    }

    /**
     * Perform NSLookup
     * 
     * @param string $type
     * @return string[] An array of string data
     * @method string[] nslookup( $string )
     */
    public function nslookup( $type = "any" )
    {
        try
        {
            if( $this->validate_type( $type ) )
            {
                $type = escapeshellarg( $type );
            }
        }
        catch ( Exception $e )
        {
            $this->display_exception($e);
        }
        

        try
        {
            $command = escapeshellcmd(
                "nslookup -debug -type=$type $this->domain 8.8.8.8"
            );
            exec( $command, $result);

            $result = $this->sanitize_result( $result );
            $records = null;
        }
        catch( Exception $e )
        {
            $this->display_exception($e);
        }

        return $result;
    }

    /**
     * Displays manually generated exception
     */
    protected function display_exception($e)
    {
        echo "Exception: ", $e->getMessage(), " on line ",
        $e->getLine(), " of ", $e->getFile(), "\n";
        echo "Trace: ", $e->getTrace(), "\n";
    }

    /**
     * Sanitizes the input
     * 
     * @param string $data
     * @return string $data Sanitized input
     * @deprecated
     */
    protected function sanitize_input( $data )
    {
        $data = trim( $data );
        $data = stripslashes( $data );
        $data = htmlspecialchars( $data );

        return $data;
    }

    /**
     * Sanitizes NSLookup results
     * 
     * @param string[] An array of string results
     * @return string[] An array of sanitized string data
     */
    protected function sanitize_result( $data )
    {
        /**
         * Remove dashed line elements and blank elements
         * @var $key=>$value $data[]
         */
        foreach( $data as $key=>$value )
        {
            if(
                $value == "------------" ||
                empty( $value )
            )
            {
                unset( $data[$key] );
                continue;
            }
        }

        /**
         * IMPORTANT!
         * Reset array after unset
         */
        $data = array_values( $data );

        return $data;
    }

    /**
     * Validates domain format
     * 
     * @param string A domain string
     * @return string Validated domain string
     */
    protected function validate_domain($domain){
        /**
         * Domain regex pattern
         * 
         * Examples of matches:
         * example.com
         * example.co.uk
         * example.website
         */
        $regex =
        '/^(?:[a-z0-9-]+\.([a-z]{2,16}|[a-z]{2,6}\.[a-z]{2}))$/';

        if( !preg_match( $regex, $domain) )
        {
            throw new Exception( "Invalid domain format." );
        }
        return $domain;
    }

    /**
     * Validate record type
     * @param string A record type
     * @return string A valid record type
     */
    protected function validate_type($type)
    {
        if( !in_array(
            $type,
            [ 'all', 'any', 'a', 'mx', 'ns', 'txt' ] 
        ) )
        {
            throw new Exception("Not a valid record type.");   
        }

        return true; 
    }

}