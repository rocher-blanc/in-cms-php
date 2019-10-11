<?php

namespace App\Kernel\Back\User;

use App\Kernel\Front\User;

class Controller extends \App\Kernel\Back\Controller
{
    protected function User()
    {
        return User::getInstance();
    }

    /*  **** ADD **** */
    protected function hookAddCheckAfter()
    {
        if ( ! $this->isUnique( true ) )
        {
            return $this->returnArrayError() ;
        }
        else
        {
            return true ;
        }
    }

    protected function returnArrayError()
    {
        $result['msg']    = ( $this->getApp()->config('config') == 'front' ? \App\Kernel\Front\Translate::getInstance()->getText( "user_login_is_uniq" ) : "Cette adresse email est déja utilisée" ) ;
        $result['field']  = 'user_login' ;
        $result['tab']    = $this->field('user_login')->getTab() ;
        $result['result'] = false;

        return $result;
    }

    protected function hookAddSaveAfter( $c )
    {
        $date = new \DateTime();

        $user = \DB::for_table('user_front')->create();
        $user->user_front_token                 = $this->User()->getNewToken();
        $user->user_front_login                 = $this->post('user_login');
        $user->user_front_password              = $this->User()->hashPassword( $this->post('user_password') );
        $user->user_front_date_created          = $date->format('Y-m-d H:i:s');
        $user->user_front_active                = $this->post( $this->field( $this->getEntity()->getValidationName() )->getColumn() );
        $user->user_front_user_front_group_id   = $this->post('user_front_user_front_group_id');
        $user->save();

        $c->set( $this->field( $this->getEntity()->getUserIdName() )->getColumn() , $user->user_front_id );
        $c->save();
    }

    /*  **** UPDATE **** */
    protected function hookUpdateCheckAfter()
    {
        if ( ! $this->isUnique() )
        {
            return $this->returnArrayError() ;
        }
        else
        {
            return true ;
        }
    }

    protected function hookUpdateSaveAfter( $c )
    {
        $user = \DB::for_table('user_front')
            ->where_id_is( $this->post( $this->field( $this->getEntity()->getUserIdName() )->getColumn() ) )
            ->find_one();

        $user->user_front_login               = $this->post('user_login');
        $user->user_front_active              = $this->post( $this->field( $this->getEntity()->getValidationName() )->getColumn() );
        $user->user_front_user_front_group_id = $this->post('user_front_user_front_group_id');

        if ( $this->post('user_password') != '' ) $user->user_front_password = $this->User()->hashPassword( $this->post('user_password') );

        $user->save();
    }

    /*  **** DELETE **** */
    protected function hookDeleteBefore()
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( $content )
        {
            $rst = \DB::for_table('user_front')
                ->where_equal( $this->getEntity()->getUserIdName() , $content->get( $this->field( $this->getEntity()->getUserIdName() )->getColumn() ) )
                ->find_one();

            if ( $rst )
            {
                $rst->delete();
            }

            return true ;
        }
        else
        {
            return false ;
        }
    }

    /*  **** VALIDATION **** */
    protected function hookEnableAfter()
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( $content )
        {
            $user = \DB::for_table('user_front')->where_id_is( $content->get( $this->field( $this->getEntity()->getUserIdName() )->getColumn() ) )->find_one();
            $user->user_front_active = 1;
            $user->save();

            return true ;
        }
        else
        {
            return false ;
        }
    }

    protected function hookDisableAfter()
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( $content )
        {
            $user = \DB::for_table('user_front')->where_id_is( $content->get( $this->field( $this->getEntity()->getUserIdName() )->getColumn() ) )->find_one();
            $user->user_front_active = 0;
            $user->save();

            return true ;
        }
        else
        {
            return false ;
        }
    }

    public function isUnique( $add = false )
    {
        $ct = \DB::for_table('user_front');

        if ( $add == false )
        {
            $ct = $ct->where_not_equal('user_front_id', $this->post($this->field( $this->getEntity()->getUserIdName() )->getColumn() ) );
        }

        $ct = $ct->where_equal('user_front_login', $this->post('user_login') )->count();

        if ( $ct == 0 ) return true ;
        else            return false ;
    }
}