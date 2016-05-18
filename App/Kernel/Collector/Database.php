<?php

namespace App\Kernel\Collector;

class Database extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{

    public function __construct()
    {

    }

    public function collect()
    {
        $array = [];
        $tab   = \App\Kernel\Debug::getQueries() ;

        if ( $tab )
        {
            $i = 1;
            foreach( $tab as $row )
            {
                $array[ $i . ". " . round( $row['time'] , 6 ) . "s" ] = $row['query'] ;
                $i++;
            }
        }

        return [
            'name' => "Queries: " . \App\Kernel\Debug::getCountQuery() . " | Time: " . \App\Kernel\Debug::getTimeORM() . "s",
            'queries' => $array
        ];
    }

    public function getName()
    {
        return 'mysql';
    }

    public function getWidgets()
    {
        return [
            'mysqltime' => [
                'icon' => 'database',
                'tooltip' => "" ,
                'map' => 'mysql.name',
                'default' => '',
            ],
            "mysql" => array(
                "icon" => "database",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "mysql.queries",
                "default" => "{}"
            )
        ];
    }
}
