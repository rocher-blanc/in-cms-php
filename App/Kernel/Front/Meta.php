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
    
    public function exist( $key , $var , $default = '' )
    {
        if ( ! is_array( $var ) ) return $default;
        return ( array_key_exists($key, $var) ? $var[ $key ] : $default ) ;
    }

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
        if ( ! empty( $md['md_youtube'] ) )   $sameAs[] = $md['md_youtube'];
        if ( ! empty( $md['md_vimeo'] ) )     $sameAs[] = $md['md_vimeo'];

        $this->CMS()->view()->appendData([
            'site' => [
                'full_url'          => \App\Kernel\Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl(),
                'url'               => $this->Factory()->Url()->getFullUrl(),
                'referer'           => $this->exist( "HTTP_REFERER" , $_SERVER ),
                'referer_external'  => $this->exist( "referer" , $_SESSION ),
                'adwords'           => $this->exist( "adwords" , $_SESSION ),
                'get'               => $_GET,
                'post'              => $_POST
            ],
			'meta' => [
				'title'          => "",
				'language'       => $this->Lang()->getActive()->url,
                'identifier-url' => \App\Kernel\Http::getInstance()->getUrl() . '/',
				'description'    => "",
				'author'         => $this->exist( "seo_author" , $tab ),
				'robots'         => ( ($tab['seo_robots'] ?? '1') == '0' ? 'noindex,nofollow' : 'index,follow' ),
				'robots_value'   => $this->exist( "seo_robots" , $tab ),
				'geo.region'     => $this->exist( "seo_geo_region" , $tab ),
				'geo.placename'  => $this->exist( "seo_geo_placename" , $tab ),
				'geo.position'   => $this->exist( "seo_geo_position" , $tab ),
				'ICBM'           => $this->exist( "seo_geo_icbm" , $tab )
			],
			'rgpd' => [
				'enabled'        	=> $this->exist( "rgpd_enabled" , $tabrgpd ),
				'position'        	=> $this->exist( "rgpd_position" , $tabrgpd ),
				'popup_background'  => $this->exist( "rgpd_popup_background" , $tabrgpd ),
				'popup_color'       => $this->exist( "rgpd_popup_color" , $tabrgpd ),
				'button_background' => $this->exist( "rgpd_button_background" , $tabrgpd ),
				'button_color'      => $this->exist( "rgpd_button_color" , $tabrgpd ),
				'text'        		=> $this->exist( "rgpd_popup_text" , $tabrgpd ),
				'text_button'       => $this->exist( "rgpd_button_text" , $tabrgpd ),
				'text_link'        	=> $this->exist( "rgpd_link_text" , $tabrgpd ),
				'link'        		=> $this->exist( "rgpd_link_href" , $tabrgpd )
			],
            'og' => [
                'type'           => "website",
                'title'          => "",
                'language'       => $this->Lang()->getActive()->url,
                'url' 			 => \App\Kernel\Http::getInstance()->getUrl() . $this->Factory()->Url()->getFullUrl() ,
                'description'    => ""
            ],
            'md' => [
                'name'           => $this->exist( "md_name" , $md ),
                'alt_name'       => $this->exist( "md_alt_name" , $md ),
                'description'    => $this->exist( "md_description" , $md ),
                'logo' 			 => $this->exist( "md_logo" , $md ),
                'facebook'       => $this->exist( "md_facebook" , $md ),
                'twitter'        => $this->exist( "md_twitter" , $md ),
                'instagram'      => $this->exist( "md_instagram" , $md ),
                'linkedin'       => $this->exist( "md_linkedin" , $md ),
                'pinterest'      => $this->exist( "md_pinterest" , $md ),
                'youtube'        => $this->exist( "md_youtube" , $md ),
                'vimeo'          => $this->exist( "md_vimeo" , $md ),
                'sameAs'         => $sameAs,
                'email'          => $this->exist( "md_email" , $md ),
                'phone'          => $this->exist( "md_phone" , $md ),
                'address'        => $this->exist( "md_address" , $md ),
                'zip'            => $this->exist( "md_zip" , $md ),
                'town'           => $this->exist( "md_town" , $md )
            ],
            'analytics' => [
                'google'    => $tab['seo_google_analytics'] ?? null
            ],
            'tracking' => [
                'header'    => $tab['seo_divers_header'] ?? null,
                'footer'    => $tab['seo_divers_footer'] ?? null,
                'gtm'       => $tab['seo_gtm'] ?? null
            ],
            'matomo' => $tab['seo_matomo'] ?? null
        ]);
    }
}