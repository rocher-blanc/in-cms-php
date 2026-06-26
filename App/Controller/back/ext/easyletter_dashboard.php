<?php

use App\Kernel\Container;
use App\Kernel\Front\Translate;
use App\Kernel\Back\Data;

function easyletterDashboardGetSender( $id )
{
	$data = new Data( "NewsletterSender" );
	$data->find( $id );
	return $data->getDataArray();
}

function easyletterDashboardGetRecipient( $id )
{
	$data = new Data( "NewsletterGroup" );
	$data->find( $id );
	return $data->getDataArray();
}

function easyletterDashboardGetTemplate( $id )
{
	$data = new Data( "NewsletterModel" );
	$data->find( $id );
	return $data->getDataArray();
}

function easyletterDashboardGetCampaign( $id )
{
	$data = new Data( "NewsletterCampaign" );
	$data->find( $id );
	return $data->getDataArray();
}

function easyletterDashboardGetType( $id )
{
	$mod = Container::getInstance()->module("NewsletterCampaign");
	$options = $mod->getEntity()->get('type')->getData('option');
	if( array_key_exists( $id , $options ) )
	{
		return $options[$id];
	}
	else
	{
		return $id ;
	}
}

function easyletterDashboardGetStatus( $id )
{
	$mod = Container::getInstance()->module("NewsletterCampaign");
	$cb = $mod->getEntity()->get('statut')->getData('updateValue');
	if ( is_callable( $cb ) )
	{
		return $cb( $id ) ;
	}
	else
	{
		return $id ;
	}
}


$app->group('/easyletter_dashboard', function (\Slim\Routing\RouteCollectorProxy $app)
{

    $app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
    {
		$Compaign = new Data( "NewsletterCampaign" );
		$rst      = [];

		$req = \DB::for_table( "mod_newslettercampaign" )
			->select( "mod_newslettercampaign_id"  , 'id' )
			->order_by_desc( "mod_newslettercampaign_date_created" )
			->find_many();

    	foreach( $req as $row )
		{
			$result = easyletterDashboardGetCampaign( $row->id ) ;

			$recipients = [];
			foreach( $result['recipient'] as $i )
			{
				$recipients[] = easyletterDashboardGetRecipient( $i );
			}

			$result['sender']            = easyletterDashboardGetSender( $result['sender'] );
			$result['recipient']         = $recipients;
			$result['template']          = easyletterDashboardGetTemplate( $result['template'] );
			$result['type']              = easyletterDashboardGetType( $result['type'] );
			$result['newsletter_parent'] = easyletterDashboardGetCampaign( $result['newsletter_parent'] );
			$result['statut']            = easyletterDashboardGetStatus( $result['statut'] );

			$rst[] = $result;
		}

        return \App\Kernel\AppContext::twig()->render($res, 'ext/easyletter_dashboard/index.twig', [ "contentRows" => $rst ]);
    })->name('easyletter_dashboard_index');

});