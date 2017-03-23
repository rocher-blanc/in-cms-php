<?php

namespace App\Kernel\Factory;

class Date
{
    public function convertUs( $date )
    {
        if ( !empty( $date ) )
        {
            list( $day , $month , $year ) = explode( "/" , $date ) ;

            return "$year-$month-$day" ;
        }
    }
}