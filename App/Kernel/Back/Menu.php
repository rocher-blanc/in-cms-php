<?php

namespace App\Kernel\Back;

class Menu
{
	/*
	 * @array
	 * Contient toute la decoupe de l'URL
	 * On l'obtient grace au Router de App
	 */
	private $_url = [] ;
	
	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}
	
	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */

	private function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }
	
	private function Factory()
	{
		return \App\Kernel\Factory::getInstance() ;
	}
	
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */
	
	public function load()
	{
		$this->_url = $this->Factory()->Url()->cutUrl() ;

		$rst = \DB::for_table('module_group')
			->where_equal('module_group_active',1)
			->order_by_asc('module_group_order')
			->find_many();

        // For each groups
        $groups  = [];
        $columns = [];
        $blocks  = [];

        $groups_id  = [];
        $columns_id = [];
        $blocks_id  = [];

        // GROUPS
        $req_group = \DB::for_table( "module_group" )
            ->where_equal( "module_group_active" , 1 )
            ->order_by_asc( "module_group_order" )
            ->find_many();
        foreach( $req_group as $group ) {

            $groups_id[] = $group->module_group_id;
            $groups[ $group->module_group_id ] = [
                'name'    => $group->module_group_name,
                'columns' => []
            ];

        }

        // COLUMNS
        if( ! empty( $groups_id ) )
        {
            $req_column = \DB::for_table( "module_column" )
                             ->where_in( "module_column_module_group_id" , $groups_id )
                             ->find_many();
            foreach ( $req_column as $column )
            {

                $columns_id[] = $column->module_column_id;
                $columns[ $column->module_column_id ] = [
                    'id'     => $column->module_column_id ,
                    'group'  => $column->module_column_module_group_id ,
                    'blocks' => []
                ];

            }

            // BLOCKS
            $req_blocks = \DB::for_table( "module_column_block" )
                             ->where_in( "module_column_block_module_column_id" , $columns_id )
                             ->order_by_asc( "module_column_block_order" )
                             ->find_many();
            foreach ( $req_blocks as $block )
            {

                $blocks_id[] = $block->module_column_block_id;
                $blocks[ $block->module_column_block_id ] = [
                    'id' => $block->module_column_block_id ,
                    'column' => $block->module_column_block_module_column_id ,
                    'title' => $block->module_column_block_title ,
                    'modules' => [] ,
                ];

            }

            // MODULES
            $req_modules = \DB::for_table( "module" )
                              ->where_in( "module_module_column_block_id" , $blocks_id )
                              ->order_by_asc( "module_order" )
                              ->find_many();
            foreach ( $req_modules as $module )
            {

                $blocks[ $module->module_module_column_block_id ][ 'modules' ][ $module->module_id ] = [
                    'title' => $module->module_name ,
                    'url' => $module->module_class_name
                ];

            }

            // Blocks
            foreach ( $blocks as $block )
            {
                $columns[ $block[ 'column' ] ][ 'blocks' ][ $block[ 'id' ] ] = $block;
            }

            // Columns
            foreach ( $columns as $column )
            {
                $groups[ $column[ 'group' ] ][ 'columns' ][ $column[ 'id' ] ] = $column;
            }
        }


        $this->Container()->newClass('App\Kernel\View')->appendData([
            'adminFolder'       => trim( $this->getApp()->config('admin.url') , "/"),
            'cat'               => ( array_key_exists( 0 , $this->_url ) == true ? $this->_url[0] : '' ),
            'menu'              => ( array_key_exists( 1 , $this->_url ) == true ? $this->_url[1] : '' ),
            'submenu'           => ( array_key_exists( 2 , $this->_url ) == true ? $this->_url[2] : '' ),
            'menuTree'          => $groups,
            'active_user'       => ACTIVE_USER,
            'active_newsletter' => NEWSLETTER_ACTIVE,
            'color'             => COLOR,
            'techno'            => TECHNO
        ]);
	}
}