<?php

$app->group('/mapip', function () use ($app)
{
    $app->get('/', function () use ($app)
    {
        $ip = \DB::for_table('ip')
            ->group_by('ip_geoip_longitude')
            ->group_by('ip_geoip_latitude')
            ->where_not_null('ip_geoip_latitude')
            ->where_not_null('ip_geoip_longitude')
            ->find_many();


        $app->render('admin/mapip/index.twig.html' , [
            'list' => $ip
        ]);

    })->name('mapip_index');
});