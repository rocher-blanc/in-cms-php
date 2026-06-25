<?php

$app->get('/robots.txt', function () use ( $app ) {
    $app->contentType('text/plain');

    $meta = $app->getViewData('meta') ;
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

        echo 'Sitemap: ' . \App\Kernel\Http::getInstance()->getUrl() . '/sitemap.xml' ;
    }
})->name('robots_txt');
