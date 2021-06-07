<?php

// INDEX
use App\Api\Easyletter;
use App\Kernel\Container;

$app->get('/', function () use ( $app ) {
    $json = \App\Kernel\Factory::getInstance()->File()->read( APPLICATION_PATH . '/../composer.json');
    $json = json_decode( $json ) ;

    $data = DB::for_table('param')
        ->where_equal('param_key', 'server_cdn')
        ->find_one();

    if ( $data )    $cdn = $data->param_value ;
    else            $cdn = "" ;

    $seo_page = DB::for_table('page')
        ->select('page.page_id')
        ->select('page.*')
        ->left_outer_join( 'page_lang' , [ 'page.page_id' , '=', 'page_lang.page_lang_page_id' ] )
        ->where_equal('page.page_active', 1)
        ->where_in('page_lang.page_lang_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
        ->where_raw("((page_lang.page_lang_title IS NULL OR page_lang.page_lang_description IS NULL) OR (page_lang.page_lang_title = '' OR page_lang.page_lang_description = ''))",[])
        ->group_by('page.page_id')
        ->find_many();

    $contentRows = \DB::for_table('module')
        ->where_equal('module_active' , 1 )
        ->order_by_asc('module_name')
        ->find_many();

    $module = [] ;
    $tab    = [] ;

    if ( $contentRows )
    {
        foreach( $contentRows as $row )
        {
            $entity = Container::getInstance()->module( $row->module_class_name )->getEntity();
            if ( $entity )
            {
                if ( $entity->hasUrl() == true )
                {
                    $tab[] = $row->module_id;
                    $seo_module_one = null; //\App\Kernel\Container::getInstance()->module( $row->module_class_name )->getRepository( true )->getOnIndex( $row->module_id );

                    if ( $seo_module_one )
                    {
                        $a = [
                            'title' => $entity->get( $entity->getUrlName() )->getTitle(),
                            'name' => $row->module_name,
                            'icon' => $row->module_icon,
                            'class' => $row->module_class_name,
                            'tab' => $seo_module_one,
                        ];

                        $module[] = $a;
                    }
                }
            }
        }
    }

    if ( $tab )
    {
        $seo_module = DB::for_table('module')
            ->select('module.module_id')
            ->select('module.*')
            ->left_outer_join( 'module_lang' , [ 'module.module_id' , '=', 'module_lang.module_lang_module_id' ] )
            ->where_in('module.module_id', $tab)
            ->where_in('module_lang.module_lang_lang_id',\App\Kernel\Lang::getInstance()->getTabLang() )
            ->where_raw("((module_lang.module_lang_title IS NULL OR module_lang.module_lang_description IS NULL) OR (module_lang.module_lang_title = '' OR module_lang.module_lang_description = ''))",[])
            ->group_by('module.module_id')
            ->find_many();

        $tab = [];

        if ( $seo_module )
        {
            foreach( $seo_module as $row )
            {
                $tab[] = $row->module_id ;
            }

            $seo_module = DB::for_table('module')
                ->left_outer_join( 'module_lang' , [ 'module.module_id' , '=', 'module_lang.module_lang_module_id' ] )
                ->where_in('module.module_id', $tab)
                ->find_many();
        }
    }
    else
    {
        $seo_module = [];
    }

    $app->render('index/index.twig' , [
        "version" => $json->version,
        "debug" => DEBUG_CMS,
        "maintenance" => Container::getInstance()->param()->get('maintenance_active'),
        "seo" => [
            "page"       => $seo_page,
            "module"     => $seo_module,
            "elt_module" => $module
        ],
        'campaigns' => index_getCampaigns(),
        "cdn" => $cdn,
        "date_update" => filemtime( VENDOR_PATH . '/autoload.php' ),
    ]) ;
})->name('index');


function index_getCampaigns()
{
	$rst = [
		'enabled'   => true,
		'campaigns' => [],
	];

	$v = [
		'v2' => [],
		'v3' => [],
	];
	$campaigns = [];

	$now = new \DateTime();

	$req = \DB::for_table('mod_newslettercampaign')
		->where_null( 'mod_newslettercampaign_status_str' )
		->where_gte( 'mod_newslettercampaign_date' , $now->format('Y-m-d H:i:s') )
		->order_by_desc( 'mod_newslettercampaign_date' )
		->find_many();

	if( $req )
	{
		foreach( $req as $row )
		{
			if( ! empty( $row->mod_newslettercampaign_version ) )
			{
				$v[$row->mod_newslettercampaign_version][] = $row->mod_newslettercampaign_id_easyletter;
			}
			$campaigns[] = $row;
		}
	}

	$req = \DB::for_table('mod_newslettercampaign')
		->where_null( 'mod_newslettercampaign_status_str' )
		->where_lt( 'mod_newslettercampaign_date' , $now->format('Y-m-d H:i:s') )
		->order_by_asc( 'mod_newslettercampaign_date' )
		->find_many();

	if( $req )
	{
		foreach( $req as $row )
		{
			if( ! empty( $row->mod_newslettercampaign_version ) )
			{
				$v[$row->mod_newslettercampaign_version][] = $row->mod_newslettercampaign_id_easyletter;
			}
			$campaigns[] = $row;
		}
	}

	return $rst;
}

function index_parseCampaign( $row )
{
	$c = (object) [];
	foreach( $row->asArray() as $k => $v )
	{
		$key = str_replace( "mod_newslettercampaign_" , '' , $k );
		$c->$key = $v ;
	}

	if( $c->version == EL_VERSION || $c->version == NULL )
	{
		$el = new Easyletter( EL_VERSION );
		$c->stats = $el->stats( [ $c->id_easyletter ] );
	}

	dump( $c );
	dump( get_class(Container::getInstance()->module("NewsletterCampaign")->getController(true)) );

	$dt = \DateTime::createFromFormat('Y-m-d H:i:s', $c->date);
	return [
		'on_error' => true,
		'subject'  => $c->subject,
		'date'     => $dt,
		'color'    => $dt->format('U') < time()
			? 'danger'
			: ( $dt->format('U') < time() - 3600  ? 'warning' : 'success' ),
		'status'   => Container::getInstance()->module("NewsletterCampaign")->getEntity()->parseStat($c)
	];
}

function index_parseStatus( $status ) {
	switch( $status )
	{

		case 'sent';
			return [
				'color' => 'success',
				'icon'  => 'icon-line2-check',
				'label' => "Envoyé"
			];

		case 'cancel';
			return [
				'color' => 'success',
				'icon'  => 'icon-line2-cross',
				'label' => "Annulé"
			];

		case 'suspend';
			return [
				'color' => 'success',
				'icon'  => 'icon-line2-check',
				'label' => "Envoyé"
			];

		default :
			return [];

	}
}