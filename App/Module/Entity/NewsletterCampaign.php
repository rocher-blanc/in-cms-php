<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class NewsletterCampaign extends Builder
{
    protected function load()
    {
        $this->setModuleParent( 'NewsletterCampaignGroup' );
        $this->setFieldReference( 'subject' );

        $this->addAction( 'stats' );
        $this->addAction( 'cancel' );

        if ( EL_VERSION == 'v2' )
        {
            $this->addAction('downloadStatistic');
            $this->addAction('downloadStatisticDest');

            $this->addAction('resendNoRead');
            $this->addAction('resendNoReadView');
            $this->addAction('resendNoClick');
            $this->addAction('resendNoClickView');
        }
        else
        {
            $this->addAction('reload');
        }

        $this->addIcon( 'icon-bar-chart' , 'stats' , function($c) {
            return $c->id_easyletter !== NULL && ( ( $c->version == 'v2' && $c->stats['state'] == 10 ) or ( $c->version == 'v3' && $c->stats['state'] == 'sent' ) ) ? true : false ;
        });

        $this->addIcon( 'icon-line-square-cross' , 'cancel' , function($c) {
            return $c->id_easyletter !== NULL && ( ( $c->version == 'v2' && $c->stats['state'] < 9 ) or ( $c->version == 'v3' && $c->stats['state'] == 'queued' ) ) ? true : false ;
        } , 'ajax');

        $this->addIcon( 'icon-reload' , 'reload' , function($c) {
            return $c->id_easyletter !== NULL && ( $c->version == 'v3' && $c->stats['state'] == 'error' ) ? true : false ;
        } , 'ajax');

/*
        $this->addIcon( 'icon-reply' , 'resend' , function($c) {
            return $c->stats === NULL ? true : false ;
        });
*/

        $this->showEdit(function( $c ) {
            return false ;
        });

        $this->showDelete(function( $c ) {
            return ( $c->id_easyletter == '' or ($c->version == 'v2' &&  $c->stats['state'] == 1) or ($c->version == 'v3' && ( $c->stats['state'] != 'suspended' && $c->stats['state'] != 'deleted' && $c->stats['state'] != 'error' ) ) ? false : true ) ;
        });

        $this->build('subject')
            ->column(1, 2)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText('mandatory_subject') )
            ->name(Translate::getInstance()->getText('subject') );

        $this->build('version')
            ->defaut('v2')
            ->isVarchar(2)
            ->noFront()
            ->noBack();

        $this->build('date')
            ->column(1, 2)
            ->isDate(true , false)
            ->notEmpty(Translate::getInstance()->getText('mandatory_dispatch_date') )
            ->name(Translate::getInstance()->getText('dispatch_date') );

        $this->build('sender')
            ->column(1, 3)
            ->isSelect()
            ->ManyToMany( 'NewsletterSender', "name" )
            ->notEmpty(Translate::getInstance()->getText('mandatory_sender_sing') )
            ->name(Translate::getInstance()->getText('sender_sing') );

        $this->build('recipient')
            ->column(1, 3)
            ->isCheckbox()
            ->ManyToMany( 'NewsletterGroup', "name" )
            ->notEmpty(Translate::getInstance()->getText('mandatory_grp_destinataire') )
            ->name(Translate::getInstance()->getText('grp_destinataire') );

        $this->build('template')
            ->column(1, 3)
            ->isSelect()
            ->ManyToMany( 'NewsletterModel', "name" )
            ->notEmpty(Translate::getInstance()->getText('mandatory_gabarit') )
            ->name(Translate::getInstance()->getText('email_gabarit') );

        $this->build('type')
            ->column(1, 2)
            ->isSelect( true )
            ->defaut(1)
            ->option([
                1 => Translate::getInstance()->getText('normal'),
                2 => Translate::getInstance()->getText('renvoi_email_nonlu'),
                3 => Translate::getInstance()->getText('renvoi_nonclic'),
            ])
            ->showIf(function($c) {
                return false ;
            })
            ->notEmpty(Translate::getInstance()->getText('mandatory_newsletter_type') )
            ->name(Translate::getInstance()->getText('type') );

        $this->build('newsletter_parent')
            ->column(1, 2)
            ->isSelect()
            ->ManyToMany( 'NewsletterCampaign' )
            ->showIf(function($c) {
                return false ;
            })
            ->notEmpty(Translate::getInstance()->getText('mandatory_parent_newsletter') )
            ->name(Translate::getInstance()->getText('parent_newsletter') );

        $this->build('statut')
            ->isHidden('INT',11)
            ->noSave()
            ->style(function($c) {
                switch( $c->statut )
                {
                    case 1 : return "" ; break;
                    default : return 'text-center" style="margin: 0 auto; display: block; width: 180px;' ; break;
                }
            })
            ->updateValue(function($c) {
            	$data = $this->parseStat( $c );
            	return '<span class="badge badge-'. $data['color'] .'" style="font-size: 14px;">'. $data['label'] .'</span>';
            })
            ->name( Translate::getInstance()->getText('status') );

        $this->build('id_easyletter')
            ->isInteger()
            ->noFront()
            ->noBack()
            ->name("ID Easyletter");

        $this->build('status_str')
            ->isVarchar(10)
            ->noFront()
            ->noBack()
            ->name("Envoyé");
    }


    public function parseStat( $c ) {
    	$result = [
			'color' => "",
			'label' => ""
		];

		if ( ! empty( $c->status_str ) )
		{
			switch( $c->status_str )
			{
				case 'sent' :
					$result['color'] = "success";
					$result['label'] = "Envoyée";
					break;

				case 'cancel' :
					$result['color'] = "danger";
					$result['label'] = "Annulée";
					break;

				case 'suspend' :
					$result['color'] = "warning";
					$result['label'] = "Suspendu";
					break;
			}
		}
		else
		{
			if ( $c->version == 'v2' )
			{
				if ( is_array( $c->stats ) )
				{
					switch( $c->stats['state'] )
					{
						case 0  : $result['color'] = 'info'   ; break; // pret
						case 1  : $result['color'] = 'primary'; break; // en cours
						case 9  : $result['color'] = 'warning'; break; // Suspendu
						case 10 : $result['color'] = 'success'; break; // envoyée
						case 11 : $result['color'] = 'danger' ; break; // annulee
						default : $result['color'] = 'default'; break; // en attente
					}

					switch( $c->stats['state'] )
					{
						case 0  :
						case 9  :
						case 10 :
						case 11 : $result['label'] = $c->stats['state_str']; break; // annulee
						case 1  : $result['label'] = $c->stats['state_str'] . ' (' . $c->stats['sent'] . "/" . $c->stats['to_send'] . ")"; break; // en cours
						default : $result['label'] = 'En attente'; break; // en attente
					}
				}
				else
				{
					$date = (new \DateTime($c->date_created))->format('U') + 120;
					if ( time() > $date )
					{
						$result['color'] = "danger";
						$result['label'] = Translate::getInstance()->getText('error');
					}
					else
					{
						$result['color'] = "info";
						$result['label'] = Translate::getInstance()->getText('attente');
					}
				}
			}
			else
			{
				switch( $c->stats['state'] )
				{
					case 'queued'      : // programmée
					case 'pending'     : $result['color'] = 'info'   ; break; // pret
					case 'in_progress' :
					case 'doing'       : $result['color'] = 'primary'; break; // en cours
					case 'suspended'   : $result['color'] = 'warning'; break; // Suspendu
					case 'sent'        : $result['color'] = 'success'; break; // envoyée
					case 'error'       :
					case 'deleted'     : $result['color'] = 'danger' ; break; // annulee
					default            : $result['color'] = 'default'; break; // en attente
				}

				switch( $c->stats['state'] )
				{
					case 'doing' : $result['label'] = $c->stats['state_str'] . ' (' . $c->stats['sent'] . "/" . $c->stats['to_send'] . ")"; break; // en cours
					default      : $result['label'] = $c->stats['state_str']; break; // en attente
				}
			}
		}

		return $result;
	}

}