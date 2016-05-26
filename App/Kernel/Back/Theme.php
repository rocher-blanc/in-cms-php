<?php

namespace App\Kernel\Back;

class Theme
{

	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

    private function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }
	
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */
	
	public function load()
	{
        $data = \DB::for_table('param')
            ->where_like('param_key', 'admin_%')
            ->find_many();

        $tab = [] ;
        if ( $data )
        {
            foreach( $data as $row )
            {
                $tab[ str_replace('admin_', '', $row->param_key ) ] = $row->param_value ;
            }
        }
		
		$this->CMS()->view()->appendData([
            'theme' => $tab
        ]);
	}
}