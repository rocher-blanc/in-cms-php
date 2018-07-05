<?php

namespace App\Kernel\Back;

class ControllerUser extends Controller
{
    protected function User()
    {
        return \App\Kernel\Front\User::getInstance();
    }

    /*  **** ADD **** */
    protected function hookAddCheckBefore()
    {
        if ( $this->getApp()->request->post('user_action') == 'register' )
        {
            if ( $this->User()->hasError() )
            {
                $this->checkForm();
                return false ;
            }
            else
            {
                return true ;
            }
        }

        return true ;
    }

    /*  **** UPDATE **** */
    protected function hookUpdateCheckBefore()
    {
        if ( $this->getApp()->request->post('user_action') == 'update' )
        {
            if ( $this->User()->hasError() )
            {
                $this->checkForm();
                return false ;
            }
            else
            {
                return true ;
            }
        }

        return true ;
    }

    /*  **** DELETE **** */
    protected function hookDeleteBefore()
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( $content )
        {
            \DB::for_table('user_front')->where_id_is( $content->get( $this->field( $this->getEntity()->getUsertIdName() )->getColumn() ) )->delete();
            return true ;
        }
        else
        {
            return false ;
        }
    }

    /*  **** VALIDATION **** */
    protected function hookEnableAfter() {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( $content )
        {
            $user = \DB::for_table('user_front')->where_id_is( $content->get( $this->field( $this->getEntity()->getUsertIdName() )->getColumn() ) )->find_one();
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
            $user = \DB::for_table('user_front')->where_id_is( $content->get( $this->field( $this->getEntity()->getUsertIdName() )->getColumn() ) )->find_one();
            $user->user_front_active = 0;
            $user->save();

            return true ;
        }
        else
        {
            return false ;
        }
    }
}