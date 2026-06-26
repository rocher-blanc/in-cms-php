<?php

$app->get('/robots.txt', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = []) {
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
})->setName('robots_txt');
