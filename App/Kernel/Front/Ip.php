<?php

namespace App\Kernel\Front;

class Ip
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */


    /* ************************************************** */
    /* ****************     ISER      ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************    SETTER     ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************    GETTER     ******************* */
    /* ************************************************** */



    /* ************************************************** */
    /* ****************    TOOLS      ******************* */
    /* ************************************************** */


    /* ************************************************** */
    /* ****************   FUNCTIONS   ******************* */
    /* ************************************************** */

    public function observe()
    {
        if ( ! $this->exist() )
        {
            $this->insert();
        }
        else
        {
            $this->update();
        }
    }

    private function exist()
    {
        $ct = \DB::for_table('ip')
            ->where_equal('ip_address', $this->app->request()->getIp() )
            ->count();

        if ( $ct == 0 ) return false ;
        else            return true ;
    }

    private function insert()
    {
        $ip = \DB::for_table('ip')->create();

        $date = new \DateTime;
        $ip->ip_address             = $this->app->request()->getIp() ;
        $ip->ip_host                = gethostbyaddr( $this->app->request()->getIp() ) ;
        $ip->ip_date                = $date->format('Y-m-d H:i:s') ;
        $ip->ip_geoip_country_code  = $this->geo('COUNTRY_CODE') ;
        $ip->ip_geoip_country_name  = $this->geo('COUNTRY_NAME') ;
        $ip->ip_geoip_region        = $this->geo('REGION') ;
        $ip->ip_geoip_city          = $this->geo('CITY') ;
        $ip->ip_geoip_dma_code      = $this->geo('DMA_CODE') ;
        $ip->ip_geoip_area_code     = $this->geo('AREA_CODE') ;
        $ip->ip_geoip_latitude      = $this->geo('LATITUDE') ;
        $ip->ip_geoip_longitude     = $this->geo('LONGITUDE') ;
        $ip->save();
    }

    private function geo( $key )
    {
        return array_key_exists( 'GEOIP_' . $key , $_SERVER ) ? $_SERVER[ 'GEOIP_' . $key ] : NULL ;
    }

    private function update()
    {
        $ip = \DB::for_table('ip')
            ->where_equal('ip_address', $this->app->request()->getIp() )
            ->find_one();

        $ip->ip_geoip_country_code  = $this->geo('COUNTRY_CODE') ;
        $ip->ip_geoip_country_name  = $this->geo('COUNTRY_NAME') ;
        $ip->ip_geoip_region        = $this->geo('REGION') ;
        $ip->ip_geoip_city          = $this->geo('CITY') ;
        $ip->ip_geoip_dma_code      = $this->geo('DMA_CODE') ;
        $ip->ip_geoip_area_code     = $this->geo('AREA_CODE') ;
        $ip->ip_geoip_latitude      = $this->geo('LATITUDE') ;
        $ip->ip_geoip_longitude     = $this->geo('LONGITUDE') ;
        $ip->save();
    }
}