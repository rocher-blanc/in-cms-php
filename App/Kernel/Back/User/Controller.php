<?php

namespace App\Kernel\Back\User;

class Controller extends \App\Kernel\Back\Controller
{
    protected function User()
    {
        return \App\Kernel\Front\User::getInstance();
    }

    /*  **** ADD **** */
    protected function hookAddCheckBefore()
    {


        return true ;
    }

    /*  **** UPDATE **** */
    protected function hookUpdateCheckBefore()
    {


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
    protected function hookEnableAfter()
    {
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