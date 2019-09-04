<?php

namespace App\Kernel\Front\User;

use App\Kernel\Front\User;

class Controller extends \App\Kernel\Front\Controller
{
    protected function User()
    {
        return User::getInstance();
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

    public function listenForm( $add = true )
    {
        $rst = parent::listenForm( $add );

        if ( $rst['result'] == true )
        {
            if ( $this->_post('moduleCustom') != 1 ) $rst['msg'] = $this->User()->getError()['msg'] ;
            else                                          return $rst ;
        }
        else
        {
            return $this->User()->getError() ;
        }

        return $rst ;
    }
}