<?php

namespace App\Kernel\Front;

class Meta
{
    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    public function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function load()
    {
        $content = \DB::for_table('param')
            ->select('param_key')
            ->select('param_value')
            ->where_like('param_key','seo_%')
            ->find_many();

        $tab = [];
        if ( $content )
        {
            foreach( $content as $row )
            {
                $tab[ $row->param_key ] = $row->param_value;
            }
        }

        $this->CMS()->view()->appendData([
            'site' => [
                'url'       => $this->Factory()->Url()->getFullUrl(),
                'referer'   => $_SERVER['HTTP_REFERER'],
                'get'       => $_GET,
                'post'      => $_POST
            ],
            'meta' => [
                'title'          => "",
                'language'       => $this->Lang()->getActive()->url,
				'identifier-url' => \App\Kernel\Http::getInstance()->getUrl() . '/',
                'description'    => "",
                'author'         => $tab['seo_author'],
                'robots'         => ( $tab['seo_robots'] == '0' ? 'noindex,nofollow' : 'index,follow' ),
                'robots_value'   => $tab['seo_robots'],
                'geo.region'     => $tab['seo_geo_region'],
                'geo.placename'  => $tab['seo_geo_placename'],
                'geo.position'   => $tab['seo_geo_position'],
                'ICBM'           => $tab['seo_geo_icbm']
            ],
			'og' => [
                'type'           => "website",
                'title'          => "",
                'language'       => $this->Lang()->getActive()->url,
				'url' 			 => \App\Kernel\Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl() ,
                'description'    => ""
            ],
            'webmaster_tools' => [
                'google'    => $tab['seo_google_webmaster_tools'],
                'bing'      => $tab['seo_bing_webmaster_tools']
            ],
            'analytics' => [
                'google'    => $tab['seo_google_analytics']
            ],
            'tracking' => [
                'header'    => $tab['seo_divers_header'],
                'footer'    => $tab['seo_divers_footer']
            ]
        ]);
    }
}