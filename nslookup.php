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
        $this->domain = $this->sanitize_input( $domain );
    }

    /**
     * Perform NSLookup
     * 
     * @todo Add error check for valid type
     * e.g. all, any, a, mx, ns, txt
     * 
     * @method string[] nslookup( $string ) 
     * @param string $type
     * @return string[] An array of string data
     */
    public function nslookup( $type )
    {
        if( empty( $type ) )
        {
            $type = "all";
        }
        else
        {
            $type = $this->sanitize_input( $type );
        }

        try
        {
            exec(
                "nslookup -debug -type=$type $this->domain 8.8.8.8",
                $result
            );

            $result = $this->sanitize_result( $result );
            $records = null;
        }
        catch( Exception $e )
        {
            $records = null;
        }

        return null;
    }

    /**
     * Sanitizes the input
     * 
     * @param string $data
     * @return string $data Sanitized input
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

}