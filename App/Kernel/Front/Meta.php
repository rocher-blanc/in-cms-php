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

		$rgpd = \DB::for_table('param')
			->select('param_key')
			->select('param_value')
			->where_like('param_key','rgpd_%')
			->find_many();

		$tabrgpd = [];
		if ( $rgpd )
		{
			foreach( $rgpd as $row )
			{
				$tabrgpd[ $row->param_key ] = $row->param_value;
			}
		}

        $content = \DB::for_table('param')
            ->select('param_key')
            ->select('param_value')
            ->where_like('param_key','md_%')
            ->find_many();

        $md = [];
        if ( $content )
        {
            foreach( $content as $row )
            {
                $md[ $row->param_key ] = $row->param_value;
            }
        }

        $sameAs = [];
        if ( ! empty( $md['md_facebook'] ) )  $sameAs[] = $md['md_facebook'];
        if ( ! empty( $md['md_twitter'] ) )   $sameAs[] = $md['md_twitter'];
        if ( ! empty( $md['md_instagram'] ) ) $sameAs[] = $md['md_instagram'];
        if ( ! empty( $md['md_linkedin'] ) )  $sameAs[] = $md['md_linkedin'];
        if ( ! empty( $md['md_pinterest'] ) ) $sameAs[] = $md['md_pinterest'];

        $this->CMS()->view()->appendData([
            'site' => [
                'url'               => $this->Factory()->Url()->getFullUrl(),
                'referer'           => $_SERVER['HTTP_REFERER'],
                'referer_external'  => $_SESSION['referer'],
                'adwords'           => $_SESSION['adwords'],
                'get'               => $_GET,
                'post'              => $_POST
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
			'rgpd' => [
				'enabled'        	=> $tabrgpd['rgpd_enabled'],
				'position'        	=> $tabrgpd['rgpd_position'],
				'popup_background'  => $tabrgpd['rgpd_popup_background'],
				'popup_color'       => $tabrgpd['rgpd_popup_color'],
				'button_background' => $tabrgpd['rgpd_button_background'],
				'button_color'      => $tabrgpd['rgpd_button_color'],
				'text'        		=> $tabrgpd['rgpd_popup_text'],
				'text_button'       => $tabrgpd['rgpd_button_text'],
				'text_link'        	=> $tabrgpd['rgpd_link_text'],
				'link'        		=> $tabrgpd['rgpd_link_href']
			],
            'og' => [
                'type'           => "website",
                'title'          => "",
                'language'       => $this->Lang()->getActive()->url,
                'url' 			 => \App\Kernel\Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl() ,
                'description'    => ""
            ],
            'md' => [
                'name'           => $md['md_name'],
                'alt_name'       => $md['md_alt_name'],
                'description'    => $md['md_description'],
                'logo' 			 => $md['md_logo'],
                'facebook'       => $md['md_facebook'],
                'twitter'        => $md['md_twitter'],
                'instagram'      => $md['md_instagram'],
                'linkedin'       => $md['md_linkedin'],
                'pinterest'      => $md['md_pinterest'],
                'sameAs'         => $sameAs,
                'phone'          => $md['md_phone'],
                'address'        => $md['md_address'],
                'zip'            => $md['md_zip'],
                'town'           => $md['md_town']
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