<?php
/**
 * Created by PhpStorm.
 * User: Jammye
 * Date: 22/08/2018
 * Time: 12:11
 */

namespace App\Kernel\Common;


class Data
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $name;
    protected $data = false ;
    protected $add  = false ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $name )
    {
        $this->name = $name ;
    }

    /* ************************************************** */
    /* ****************     SETTER    ******************* */
    /* ************************************************** */

    public function set( $key , $value )
    {
        $this->data->set( $this->getEntity()->get( $key )->getColumn() , $value );
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getName()
    {
        return $this->name ;
    }

    public function get( $key )
    {
        return $this->data->get( $this->getEntity()->get( $key )->getColumn() ) ;
    }

    public function getData()
    {
        return $this->data ;
    }

    public function getEntity()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function getRepository()
    {
        return \App\Kernel\Container::getInstance()->module( $this->getName() )->getRepository(true) ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    public function isCreate()
    {
        return $this->add ;
    }

    /* ************************************************** */
    /* ****************     TOOLS     ******************* */
    /* ************************************************** */

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function save()
    {
        $rst = $this->data->save();

        $module = \DB::for_table('module')
            ->select('module_id')
            ->where([
                'module_class_name' => $this->getName()
                , 'module_active' => 1
            ])
            ->find_one();

        if ( $this->getEntity()->hasUrl() && $this->add == true )
        {
            $seo = new \App\Kernel\Back\Seo;
            $seo->setElementId( $this->get('id') );
            $seo->setModuleId( $module->module_id );
            $seo->setTitle( $this->get( $this->getEntity()->getUrlName() ) );
            $seo->setLangId( $this->Lang()->getDefault()->id );
            $seo->save();
        }

        return $rst ;
    }

    public function delete()
    {
        $rst = $this->data->save();

        $module = \DB::for_table('module')
            ->select('module_id')
            ->where([
                'module_class_name' => $this->getName()
                , 'module_active' => 1
            ])
            ->find_one();

        if ( $this->getEntity()->hasUrl() )
        {
            $seo = new \App\Kernel\Back\Seo;
            $seo->setElementId( $this->get('id') );
            $seo->setModuleId( $module->module_id );
            $seo->delete();
        }

        return $rst ;
    }

    public function findOrCreate( $value = NULL )
    {
        $rst = $this->find( $value );

        if ( $rst === false )
        {
            $this->create( $value );
        }
    }

    public function find( $value )
    {
        if ( is_array( $value ) )
        {
            $this->data = $this->getRepository()->findWhere( $value );
        }
        else
        {
            $this->data = $this->getRepository()->findOne( $value );
        }

        if ( $this->data === false )
        {
            return false ;
        }
        else
        {
            return true ;
        }
    }

    public function count( array $value )
    {
        return $this->getRepository()->countWhere( $value );
    }

    public function create( $value = NULL )
    {
        $this->data = $this->getRepository()->create() ;
        $this->add  = true ;

        if ( is_array( $value ) )
        {
            foreach( $value as $key => $value )
            {
                $this->set( $key , $value );
            }
        }
    }

    public function getDataArray()
    {
        $tab = [];

        if ( $this->data !== false )
        {
            foreach( $this->getEntity()->getField() as $field )
            {
                $tab[ $field->getName() ] = $this->get( $field->getName() );
            }
        }

        return $tab ;
    }
}