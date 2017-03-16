<?php

namespace App\Kernel\Front;

class Language
{
    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    private function parseFile()
    {

    }

    public function load()
    {
        dump( $this->Lang() );
        $this->CMS()->view()->appendData([
            'lang' => [
                'default' => $this->Lang()->getDefault(),
                'active' => $this->Lang()->getActive(),
                'all' => $this->Lang()->getAll(),
                'count' => $this->Lang()->count()
            ]
        ]);
    }
}