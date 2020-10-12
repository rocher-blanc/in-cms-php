<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Api\Easyletter;
use App\Kernel\Front\Data;
use App\Kernel\Front\Translate;

class EdAutomationHistory extends Builder
{
    protected function load()
    {
        $this->disableUpdate();
        $this->disableDelete();
        $this->disableCreate();

        $this->setFieldReference( 'date' );

        $this->build('email')
            ->column(1, 1)
            ->isVarchar("255", "email")
            ->notEmpty(Translate::getInstance()->getText('mandatory_email' ))
            ->name(Translate::getInstance()->getText('email' ));

        $this->build('date')
            ->isDate(true)
            ->name(Translate::getInstance()->getText('date' ));

        $this->build('information')
            ->noFront()
            ->noBack()
            ->isText("LONG")
            ->name("JSON - Informations");

        $this->build('automation')
            ->isSelect()
            ->ManyToMany("EdAutomation")
            ->name(Translate::getInstance()->getText('automation' ));

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");

        $this->build('error')
            ->isVarchar("500")
            ->noFront()
            ->noBack()
            ->name(Translate::getInstance()->getText('error_message' ));

        $this->build('version')
            ->defaut(EL_VERSION)
            ->isVarchar("2")
            ->noFront()
            ->noBack()
            ->name(Translate::getInstance()->getText('error_message' ));

        $this->build('statut')
            ->column(1, 1)
            ->isHidden()
            ->style(function($c) {
				if( defined('EL_TOKEN') )
				{
					switch ( $c->statut )
					{
						case 1 :
							return "";
							break;
						default :
							return 'text-center" style="margin: 0 auto; display: block; width: 180px;';
							break;
					}
				}
				else
				{
					return "";
				}
            })
            ->updateValue(function($c) {
            	if( defined('EL_TOKEN') )
				{
				    if ( EL_VERSION == 'v2' )
                    {
                        $send = ( $c->stats['sent'] == 1 ? true : false );
                        $delivery = ( $c->stats['sent'] == 1 && $c->stats['hard_bounces'] == 0 && $c->stats['soft_bounces'] == 0 ? true : false );
                        $open = ( $c->stats['reads'] > 0 ? true : false );
                        $clic = ( $c->stats['clicks'] > 0 ? true : false );
                        $shield = false;
                        $error = ( $c->stats['sent'] == 1 && ( $c->stats['hard_bounces'] > 0 || $c->stats['soft_bounces'] > 0 ) ? true : false );

                        if ( $c->stats !== NULL && $error == true && $c->error == '' )
                        {
                            $el = new Easyletter();
                            $stats = $el->stats( $c->id_easyletter );
                            $transco = $stats['state_mailing'] ;

                            $c->error = $transco[ $stats['destStats']['records'][0][3] ] ;

                            $data = new Data("EdAutomationHistory");
                            $data->find([
                                'id_easyletter' => $c->id_easyletter
                            ]);
                            $data->set('error' , $c->error );
                            $data->save();
                        }

                        if ( $send ) $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Envoyé" style="color: #007ca2;"></i>';
                        else         $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Non envoyé" style="color: #ddd;"></i>';

                        if ( $delivery ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Délivré" class="icon-check-sign" style="color: #449d44;"></i>' ;
                        else             $str.= '<i data-toggle="tooltip" data-placement="top" title="Non délivré" class="icon-check-sign" style="color: #ddd;"></i>' ;

                        if ( $open ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Ouvert" class="icon-email" style="color: darkorange;"></i>' ;
                        else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Jamais ouvert" class="icon-email" style="color: #ddd;"></i>' ;

                        if ( $clic ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Cliqué" class="icon-link" style="color: darkcyan;"></i>' ;
                        else         $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucun clic" class="icon-link" style="color: #ddd;"></i>' ;

                        if ( $shield ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Email bloqué : XXXXXXXXXXXXXXXX" class="icon-shield" style="color: darkred;"></i>' ;

                        if ( $error ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Erreur : ' . $c->error . '" class="icon-warning-sign" style="color: darkred;"></i>' ;
                        else          $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucune erreur" class="icon-warning-sign" style="color: #ddd;"></i>' ;

                        return $str ;
                    }
                    else
                    {
                        if ( $c->stats['sent'] ) $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Envoyé" style="color: #007ca2;"></i>';
                        else                     $str = '<i class="icon-email2" data-toggle="tooltip" data-placement="top" title="Non envoyé" style="color: #ddd;"></i>';

                        if ( $c->stats['delivered'] ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Délivré" class="icon-check-sign" style="color: #449d44;"></i>' ;
                        else                          $str.= '<i data-toggle="tooltip" data-placement="top" title="Non délivré" class="icon-check-sign" style="color: #ddd;"></i>' ;

                        if ( $c->stats['opened'] ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Ouvert" class="icon-email" style="color: darkorange;"></i>' ;
                        else                       $str.= '<i data-toggle="tooltip" data-placement="top" title="Jamais ouvert" class="icon-email" style="color: #ddd;"></i>' ;

                        if ( $c->stats['clicked'] ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Cliqué" class="icon-link" style="color: darkcyan;"></i>' ;
                        else                        $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucun clic" class="icon-link" style="color: #ddd;"></i>' ;

//                        if ( $shield ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Email bloqué : XXXXXXXXXXXXXXXX" class="icon-shield" style="color: darkred;"></i>' ;

                        if ( $c->stats['error'] ) $str.= '<i data-toggle="tooltip" data-placement="top" title="Erreur : ' . $c->error . '" class="icon-warning-sign" style="color: darkred;"></i>' ;
                        else                      $str.= '<i data-toggle="tooltip" data-placement="top" title="Aucune erreur" class="icon-warning-sign" style="color: #ddd;"></i>' ;

                        return $str ;
                    }
                }
				else
				{
					return "";
				}
            })
            ->name(Translate::getInstance()->getText('status' ));

        if( ! defined('EL_TOKEN') )
		{
			$this->setLast('statut')->noBack();
		}

	}
}