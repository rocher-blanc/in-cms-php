<?php

namespace App\Kernel;

class Debug
{
    ########################################################################################
    /* ****************************      VARIABLES      ********************************* */
    ########################################################################################

    public static $sql = [];
    public static $value = [];
    public static $logORM = [
        'time'    => 0,
        'count'   => 0,
        'queries' => []
    ];

    ########################################################################################
    /* ****************************        ORM          ********************************* */
    ########################################################################################

    public static function logORM( $query , $time )
    {
        self::$logORM['queries'][] = [
            "time" => $time,
            "query" => $query
        ];
        self::$logORM['count']++;
        self::$logORM['time'] += $time;
    }

    public static function printLogORM()
    {
        self::dump( self::$logORM );
    }

    public static function getTimeORM()
    {
        return round( self::$logORM['time'] , 4 ) ;
    }

    public static function getCountQuery()
    {
        return self::$logORM['count'] ;
    }

    public static function getQueries()
    {
        return self::$logORM['queries'] ;
    }

    ########################################################################################
    /* ****************************       MYSQL         ********************************* */
    ########################################################################################

    public static function sql()
    {
        self::dump( \SqlFormatter::format( \DB::get_last_query() ) );
    }

    public static function saveSql()
    {
        self::$sql[] = \SqlFormatter::format( \DB::get_last_query() ) ;
    }

    ########################################################################################
    /* ****************************        DUMP         ********************************* */
    ########################################################################################

    public static function dumpMulti()
    {
        // get variables to dump
        $args = func_get_args();

        // loop through all items to output
        $i = 1;
        foreach ($args as $arg) {
            self::dump($arg , '' , ( $i == count( $args ) ? true : false ) , ( $i == 1 ? true : false ) );
            $i++;
        }
    }

    public static function save()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            self::$value[] = $arg ;
        }
    }

    public static function view( $var = NULL )
    {
        if ( $var !== NULL ) self::save( $var );
        if ( !empty( self::$sql ) ) self::dump( self::$sql , "SQL" , false , true ) ;
        if ( !empty( self::$value ) )
        {
            $ct = count( self::$value ) ;
            $index = 1;
            foreach( self::$value as $row )
            {
                self::dump( $row , null , ( $index == $ct ? true : false ) , false ) ;
                $index++;
            }
        }
    }

    /**
     * Dump information about a variable
     *
     * @param mixed $variable Variable to dump
     * @param string $caption Caption of the dump
     * @return void
     */
    public static function dump($variable, $caption = null, $stop = true, $viewcall = true)
    {
        // don't dump anything in non-development environments
        if (DEBUG !== true) {
            return;
        }

        // prepare the output string
        $html = '';

        // start the output buffering
        ob_start();

        // generate the output
        if ( $caption == 'SQL' )
        {
            foreach( $variable as $rqt )
            {
                echo $rqt ;
            }
        }
        else
        {
            var_dump( $variable );
        }

        // get the output
        $output = ob_get_clean();

        $maps = array(
            'string'    => '/(string\((?P<length>\d+)\)) (?P<value>\"(?<!\\\).*\")/i',
            'array'     => '/\[\"(?P<key>.+)\"(?:\:\"(?P<class>[a-z0-9_\\\]+)\")?(?:\:(?P<scope>public|protected|private))?\]=>/Ui',
            'countable' => '/(?P<type>array|int|string)\((?P<count>\d+)\)/',
            'resource'  => '/resource\((?P<count>\d+)\) of type \((?P<class>[a-z0-9_\\\]+)\)/',
            'bool'      => '/bool\((?P<value>true|false)\)/',
            'float'     => '/float\((?P<value>[0-9\.]+)\)/',
            'object'    => '/object\((?P<class>[a-z_\\\]+)\)\#(?P<id>\d+) \((?P<count>\d+)\)/i',
        );

        foreach ($maps as $function => $pattern) {
            $output = preg_replace_callback($pattern, array('self', '_process' . ucfirst($function)), $output);
        }
        $output = str_replace( "]=>\n " , "] =>" , $output ) ;

        $header = '';
        if (!empty($caption)) {
            $header = '<h2 style="' . self::_getHeaderCss() . '">' . $caption . '</h2>';
        }

        if ( $viewcall )
        {
            $header.= '<h2 style="' . self::_getHeaderCss() . '">Calltrace</h2>';
            $calltrace = self::generateCallTrace() . "\n\n";
            $calltrace.= '<h2 style="' . self::_getHeaderCss() . '">Variable</h2>';
        }

        print '<pre style="' . self::_getContainerCss() . '">' . $header . $calltrace . $output . '</pre>';
        if ( $stop ) die;
    }

    private static function generateCallTrace()
    {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++)
        {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "\t" . implode("\n\t", $result);
    }

    private static function _processString(array $matches)
    {
        $matches['value'] = htmlspecialchars($matches['value']);
        return '<span style="color: #0000FF;">string</span>(<span style="color: #1287DB;">' . $matches['length'] . ')</span> <span style="color: #6B6E6E;">' . $matches['value'] . '</span>';
    }

    private static function _processArray(array $matches)
    {
        // prepare the key name
        $key = '<span style="color: #008000;">"' . $matches['key'] . '"</span>';
        $class = '';
        $scope = '';

        // prepare the parent class name
        if (isset($matches['class']) && !empty($matches['class'])) {
            $class = ':<span style="color: #4D5D94;">"' . $matches['class'] . '"</span>';
        }

        // prepare the scope indicator
        if (isset($matches['scope']) && !empty($matches['scope'])) {
            $scope = ':<span style="color: #666666;">' . $matches['scope'] . '</span>';
        }

        // return the final string
        return '[' . $key . $class . $scope . ']=>';
    }

    private static function _processCountable(array $matches)
    {
        $type = '<span style="color: #0000FF;">' . $matches['type'] . '</span>';
        $count = '(<span style="color: #1287DB;">' . $matches['count'] . '</span>)';

        return $type . $count;
    }

    private static function _processBool(array $matches)
    {
        return '<span style="color: #0000FF;">bool</span>(<span style="color: #0000FF;">' . $matches['value'] . '</span>)';
    }

    private static function _processFloat(array $matches)
    {
        return '<span style="color: #0000FF;">float</span>(<span style="color: #1287DB;">' . $matches['value'] . '</span>)';
    }

    private static function _processResource(array $matches)
    {
        return '<span style="color: #0000FF;">resource</span>(<span style="color: #1287DB;">' . $matches['count'] . '</span>) of type (<span style="color: #4D5D94;">' . $matches['class'] . '</span>)';
    }

    private static function _processObject(array $matches)
    {
        return '<span style="color: #0000FF;">object</span>(<span style="color: #4D5D94;">' . $matches['class'] . '</span>)#' . $matches['id'] . ' (<span style="color: #1287DB;">' . $matches['count'] . '</span>)';
    }

    private static function _getContainerCss()
    {
        return self::_arrayToCss(array(
            'background-color'      => '#fffce6',
            'border'                => '1px solid #bbb',
            'border-radius'         => '4px',
            '-moz-border-radius'    => '4px',
            '-webkit-border-radius' => '4px',
            'font-size'             => '12px',
            'line-height'           => '1.4em',
            'margin'                => '30px',
            'padding'               => '7px',
        ));
    }

    private static function _getHeaderCss()
    {

        return self::_arrayToCss(array(
            'border-bottom' => '1px solid #bbb',
            'font-size'     => '18px',
            'font-weight'   => 'bold',
            'margin'        => '0 0 10px 0',
            'padding'       => '3px 0 10px 0',
        ));
    }

    private static function _arrayToCss(array $rules)
    {
        $strings = array();

        foreach ($rules as $key => $value) {
            $strings[] = $key . ': ' . $value;
        }

        return join('; ', $strings);
    }

}
