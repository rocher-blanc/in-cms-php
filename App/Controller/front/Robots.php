<?php

$app->get('/robots.txt', function () use ( $app ) {
    $app->contentType('text/plain');

    $meta = $app->view()->get('meta') ;
    echo 'User-agent: *' . "\n";

    if ( $meta['robots_value'] == 0 )
    {
        echo 'Disallow: /' ;
    }
    else
    {
        echo 'Allow: /' . "\n" ;
        echo 'Allow: /*.js' . "\n" ;
        echo 'Allow: /*.css' . "\n" ;

        echo 'Sitemap: ' . \Slim\Slim::getInstance()->request()->getUrl() . '/sitemap.xml' ;
    }
})->name('robots_txt');
