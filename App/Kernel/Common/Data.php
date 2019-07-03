<?php
/**
 * Created by PhpStorm.
 * User: Jammye
 * Date: 22/08/2018
 * Time: 12:11
 */

namespace App\Kernel\Common;


use App\Kernel\Back\Seo;
use App\Kernel\Container;
use App\Kernel\Lang;

class Data
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $name;
    protected $data = false ;
    protected $lang = [] ;
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

    public function set( $key , $value , $lang = null )
    {
        if ( ( $this->Lang()->count() == 1 or $lang !== null ) && $this->getEntity()->get( $key )->hasLang() )
        {
            if ( $this->Lang()->count() == 1 )
            {
                $lang = $this->Lang()->getDefault()->id ;
            }

            $this->lang[ $lang ]->set( $this->getEntity()->get( $key )->getColumn() , $value );
        }
        else
        {
            $this->data->set( $this->getEntity()->get( $key )->getColumn() , $value );
        }
    }

    /* ************************************************** */
    /* ****************     GETTER    ******************* */
    /* ************************************************** */

    public function getName()
    {
        return $this->name ;
    }

    public function get( $key , $lang = null )
    {
        if ( $this->getEntity()->get( $key )->getType() == 'checkbox' )
        {
            return $this->getRepository()->getAssocSimpleValue( $key , $this->get('id') );
        }
        else
        {
            if ( $this->getEntity()->get( $key )->hasLang() )
            {
                return $this->lang[ $lang ]->get( $this->getEntity()->get( $key )->getColumn() ) ;
            }
            else
            {
                return $this->data->get( $this->getEntity()->get( $key )->getColumn() ) ;
            }
        }
    }

    public function getData()
    {
        return $this->data ;
    }

    public function getEntity()
    {
        return Container::getInstance()->module( $this->getName() )->getEntity() ;
    }

    public function getRepository()
    {
        return Container::getInstance()->module( $this->getName() )->getRepository(true) ;
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
        return Lang::getInstance() ;
    }

    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function save()
    {
        if ( $this->add != true )
        {
            $this->set( "date_last_updated" , date('Y-m-d H:i:s') );
            $this->set( "date_updated" , $this->get( "date_last_updated" ) );
        }

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
            foreach( $this->Lang()->getAll() as $lang )
            {
                $seo = new Seo;
                $seo->setElementId( $this->get('id') );
                $seo->setModuleId( $module->module_id );
                $seo->setTitle( $this->get( $this->getEntity()->getUrlName() , $lang->id ) );
                $seo->setLangId( $lang->id );

                if ( $this->isCreate() ) $seo->save();
                else                     $seo->update() ;
            }
        }

        if ( $this->getEntity()->hasMultilang() == true )
        {
            foreach( $this->Lang()->getAll() as $lang )
            {
                $this->lang[ $lang->id ]->set( \DB::getIdNameInLang( $this->getName() ) , $this->get('id') ) ;
                $this->lang[ $lang->id ]->save() ;
            }
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
            $seo = new Seo;
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
            if ( $this->getEntity()->hasMultilang() == true )
            {
                foreach( $this->Lang()->getAll() as $lang )
                {
                    $this->lang[ $lang->id ] = \DB::for_module_lang( $this->getName() , $this->get('id') , $lang->id )->find_one();
                }
            }

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

        if ( $this->getEntity()->hasMultilang() == true )
        {
            foreach( $this->Lang()->getAll() as $lang )
            {
                $this->lang[ $lang->id ] = $this->getRepository()->createLang();
                $this->lang[ $lang->id ]->set( \DB::getLangIdLangName( $this->getName() ) , $lang->id );
            }
        }

        $this->set( "date_created" , date('Y-m-d H:i:s') );
        $this->set( "date_last_updated" , date('Y-m-d H:i:s') );
        $this->set( "date_updated" , date('Y-m-d H:i:s') );

        if ( is_array( $value ) )
        {
            foreach( $value as $key => $value )
            {
                $this->set( $key , $value );
            }
        }
    }

    public function getDataArray( $prefix = '' )
    {
        $tab = [];

        if ( $this->data !== false )
        {
            foreach( $this->getEntity()->getField() as $field )
            {
                $tab[ $prefix . $field->getName() ] = $this->get( $field->getName() );
            }
        }

        return $tab ;
    }
}