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
            $this->display_exception( $e );
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
            $this->display_exception( $e );
        }
        

        try
        {
            $command = escapeshellcmd(
                "nslookup -debug -type=$type $this->domain 8.8.8.8"
            );
            exec( $command, $result );

            $result = $this->sanitize_result( $result );
            $records = null;
        }
        catch( Exception $e )
        {
            $this->display_exception( $e );
        }

        return $result;
    }


    /**
     *  Extracts records for query result
     * @param array $data An array of query results
     * @return array An array of query results records
     */
    protected function extract_records( $data )
    {
        $data_count = count( $data );
        $records = array();
        $type = null;

        for( $i = 1; $i < $data_count -1 ; $i++ )
        {
            /**
             * Previous, Current, Next record in array
             */
            $prev = strtolower( $data[ $i - 1 ] );
            $record = strtolower( $data[ $i ] );
            $next = strtolower( $data[ $i + 1 ] );

            /**
             * Handle A, NS, MX records
             */
            if(
                $this->validate_record( $prev, $next, $this->domain )
            )
            {
                if ( stristr( $record, "nameserver" ) )
                    $type = "ns";
                elseif( stristr( $record, "mx" ) )
                    $type = "mx";
                elseif( stristr( $record, "internet address" ) )
                    $type = "a";
                
                $records[ $type ][] = $this->record_to_array(
                    $type,
                    $record,
                    $next
                );
            }
            /**
             * Handle TXT record
             */
            elseif(
                ( $i + 2 ) < $data_count &&
                $this->validate_record(
                    $prev,
                    strtolower( $data[ $i + 2 ] ),
                    $this->domain
                )
            )
            {
                $records[ "txt" ][] = $this->record_to_array(
                    "txt",
                    $next,
                    strtolower( $data[ $i + 2 ] )
                );
            }
            else continue;
        }

        return $records;
    }

    /**
     * Displays manually generated exception
     * @param array $e The exception that was thrown
     * @return void
     */
    protected function display_exception( $e )
    {
        echo "Exception: ", $e->getMessage(), " on line ",
        $e->getLine(), " of ", $e->getFile(), "\n";
        echo "Trace: ", $e->getTrace(), "\n";
    }

    /**
     * Extracts and formats the ttl to human
     * readable format.
     * @param string $ttl The ttl data
     * @return string $ttl The ttl in human
     * readable format
     */
    protected function format_ttl( $ttl )
    {
        try
        {
            preg_match(
                "/(\w+\s=\s\d+)/",
                $ttl,
                $match
            );
        }
        catch( Exception $e )
        {
            $this->display_exception( $e );
            return;
        }

        $ttl = explode(
            "=",
            str_replace(
                ' ',
                '',
                $match[ 0 ]
            )
        );

        $ttl = $this->ttl_to_readable( $ttl );

        return $ttl;
    }

    /**
     * Converts record to array format
     * @param string $type The type of record
     * @param string $record The record
     * @param string $ttl The ttl of the record
     * @return array The record in array format
     */
    protected function record_to_array( $type, $record, $ttl )
    {
        $record = str_replace(' ', '', $record);

        switch( $type )
        {
            // A Record
            case 'a':
                $record = explode("=", $record);
                return array(
                    "ip"    =>  $record[ 1 ],
                    "ttl"   =>  $this->format_ttl($ttl)
                );
                break;
            // MX Records
            case 'mx':
                $record     = explode( ",", $record );
                $priority   = explode( "=", $record[ 0 ] );
                $host       = explode( "=", $record[ 1 ] );
                return array(
                    "host"      => $host[ 1 ],
                    "priority"  => $priority[ 1 ],
                    "ip"        => $this->resolve_ip( $host[ 1 ] ),
                    "ttl"       => $this->format_ttl( $ttl ) 
                );
                break;
            // NS Records
            case 'ns':
                $record = explode( "=", $record );
                return array(
                    "host"  => $record[ 1 ],
                    "ip"    => $this->resolve_ip( $record[ 1 ] ),
                    "ttl"   => $this->format_ttl( $ttl )
                );
                break;
            // TXT Records
            case 'txt':
                return array(
                    "record"    => $record,
                    "ttl"       => $this->format_ttl( $ttl )
                );
                break;
        }
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
     * Convert TTL to human readable format
     * @param string $ttl The ttl value
     * @return string $readable The ttl value in
     * human readable format
     */
    protected function ttl_to_readable( $ttl )
    {
        $ttl        = intval( $ttl[ 1 ] );
        $readable   = "";

        // Day(s)
        $d = intval( $ttl / ( 3600 * 24 ) );
        // Hour(s)
        $h = ( $ttl / 3600 ) % 24;
        // Minute(s)
        $m = ( $ttl / 60 ) % 60;
        // Seconds(s)
        $s = $ttl % 60;

        if( $d > 0 )
        {
            $readable .= (
                $d == 1?
                "$d day ":
                "$d days "
            );
        }

        if( $h > 0 )
        {
            $readable .= (
                $h == 1?
                "$h hour ":
                "$h hours "
            );
        }

        if( $m > 0 )
        {
            $readable .= (
                $m == 1?
                "$m minute ":
                "$m minutes "
            );
        }

        if( $s > 0 )
        {
            $readable .= (
                $s == 1?
                "$s second":
                "$s seconds"
            );
        }

        return $readable;

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
     * Validates a record based on the previous and
     * next entry in the array
     * @param string $prev The previous data entry
     * @param string $next The next data entry
     * @param string $domain The domain name
     * @return bool The result of the validation
     */
    protected function validate_record( $prev, $next, $domain )
    {
        if( $prev == $domain && stristr( $next, "ttl" ) ) return TRUE;
        else return FALSE;
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