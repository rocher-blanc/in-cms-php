<?php

use App\Kernel\Factory;
use App\Kernel\Container as ContainerAlias;

$app->get('/sitemap.xml', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
{
    $data = \DB::for_table('param')
        ->where_equal('param_key', 'seo_robots')
        ->find_one();

    if ( $data->param_value == '0' )
    {
        Factory::getInstance()->Response()->show404();
    }

    $app->contentType('text/xml');

    $langArray = [] ;
    $langObj = \App\Kernel\Lang::getInstance() ;

    foreach( $langObj->getAll() as $lang )
    {
        $langArray[ $lang->id ] = $lang->url ;
    }

    $content = \DB::for_table('module')
        ->select('module.module_id')
        ->select('module.module_class_name')
        ->select('module.module_default')
        ->select('module.module_priority')
        ->select('module.module_index')
        ->select('module.module_index_elmt')
        ->select('module_lang.module_lang_url')
        ->select('module_lang.module_lang_lang_id')
        ->left_outer_join('module_lang', [ 'module_lang.module_lang_module_id', '=', 'module.module_id' ])
        ->where_equal('module.module_active',1)
        ->where_in('module_lang.module_lang_lang_id', $langObj->getTabLang() )
        ->find_many();

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?xml-stylesheet type="text/xsl" href="' . \App\Kernel\Http::getInstance()->vendor( VENDOR_CMS . '/xsl/stylesheet.xsl' ) . '"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n\t" . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"> ' . "\n";

    if ( $content )
    {
        foreach( $content as $module )
        {
            if ( ( $module->module_index == 1 or $module->module_index_elmt == 1 ) && $module->module_lang_url != '' )
            {
                $url = ((string)$req->getUri()->getScheme() . '://' . (string)$req->getUri()->getHost()) ;
                if ( $langObj->count() > 1 ) $url.= '/' . $langArray[ $module->module_lang_lang_id ] ;

                $getAllExist = false ;

                if ( $module->module_index == 1 )
                {
                    $file = '/module/' . $module->module_class_name . '/getall.twig' ;
                    $_twigLoader1 = \App\Kernel\CMS::getInstance()->getApp()->view()?->getEnvironment()->getLoader();
                    $_twigDirs1   = ($_twigLoader1 instanceof \Twig\Loader\FilesystemLoader) ? $_twigLoader1->getPaths() : [];
                    foreach( $_twigDirs1 as $folder )
                    {
                        if ( file_exists( $folder . $file ) )
                        {
                            $getAllExist = true ;
                        }
                    }
                }

                if ( $module->module_index == 1 && $getAllExist == true )
                {
                    echo "\t" . '<url>' . "\n" ;
                    echo "\t\t" . '<loc>' . $url . '/' . $module->module_lang_url . '</loc>' . "\n";
                    if ( $module->module_priority != 0 )
                    {
                        echo "\t\t" . '<priority>' . $module->module_priority . '</priority>' . "\n";
                    }
                    echo "\t" . '</url>' . "\n" ;
                }

                if ( $module->module_default == 0 )
                {
                    $url .= '/' . $module->module_lang_url . '/';
                }
                else
                {
                    $url.= '/' ;
                }

                $getOneExist = false ;

                if ( $module->module_index_elmt == 1 )
                {
                    $file = '/module/' . $module->module_class_name . '/getone.twig' ;
                    $_twigLoader2 = \App\Kernel\CMS::getInstance()->getApp()->view()?->getEnvironment()->getLoader();
                    $_twigDirs2   = ($_twigLoader2 instanceof \Twig\Loader\FilesystemLoader) ? $_twigLoader2->getPaths() : [];
                    foreach( $_twigDirs2 as $folder )
                    {
                        if ( file_exists( $folder . $file ) )
                        {
                            $getOneExist = true ;
                        }
                    }
                }

                if ( $module->module_index_elmt == 1 && $getOneExist == true )
                {
                    $Controller = ContainerAlias::getInstance()->module( $module->module_class_name )->getController();
                    $result = $Controller->getSiteMap() ;

                    if ( $result )
                    {
                        foreach( $result['content'] as $row )
                        {
                            if ( $module->module_lang_lang_id == $row->seo_lang_id )
                            {
                                $date_updated = new \DateTime( $row->date_updated ) ;
                                $date_last_updated = new \DateTime( $row->date_last_updated ) ;
                                $interval = $date_last_updated->diff($date_updated);
                                $delta = intval( $interval->format('%a') );

                                if ( $delta <= 1 )          $fred = 'daily' ;
                                else if ( $delta <= 7 )     $fred = 'weekly' ;
                                else if ( $delta <= 30 )    $fred = 'monthly' ;
                                else                        $fred = 'yearly' ;

                                echo "\t" . '<url>' . "\n" ;
                                echo "\t\t" . '<loc>' . $url . $row->seo_url . '</loc>' . "\n";
                                echo "\t\t" . '<lastmod>' . $date_updated->format('Y-m-d') . '</lastmod>' . "\n";
                                echo "\t\t" . '<changefreq>' . $fred . '</changefreq>' . "\n";
                                echo "\t\t" . '<priority>' . $module->module_priority . '</priority>' . "\n";

                                if ( !empty( $result['fieldImage'] ) )
                                {
                                    foreach( $result['fieldImage'] as $field )
                                    {
                                        $image = $row->get( $field->getColumn() ) ;

                                        if ( !empty( $image ) )
                                        {
                                            $media = new \App\Kernel\Front\Media;
                                            $media->setImageId( $image );
                                            $media->getNameById();

                                            $urlImage = \App\Kernel\Http::getInstance()->getCdn() . $result['pathImage'] . '/' . $media->getImageName();
                                            echo "\t\t" . '<image:image>' . "\n";
                                            echo "\t\t\t" . '<image:loc>' . $urlImage . '</image:loc>' . "\n";
//                                            echo "\t\t\t" . '<image:title>' . $media->getImageName() . '</image:title>' . "\n";
                                            echo "\t\t" . '</image:image>' . "\n";
                                        }
                                    }
                                }

                                if ( !empty( $result['fieldGallery'] ) )
                                {
                                    foreach( $result['fieldGallery'] as $gallery )
                                    {
                                        $Gal = new \App\Kernel\Front\Gallery;
                                        $Gal->setElementId( $row->id );
                                        $Gal->setModuleId( $module->module_id );
                                        $Gal->setModuleName( $module->module_class_name );
                                        $Gal->setField( $gallery->getName() );
                                        $Gal->setFolder( $Controller->getEntity()->getFolder() );
                                        $tab = $Gal->getAllByField();

                                        if ( !empty( $tab ) )
                                        {
                                            foreach( $tab as $img )
                                            {
                                                if ( count( $img ) == 3 )
                                                {
                                                    unset( $img['100x100'] );
                                                    unset( $img['source'] );

                                                    foreach( $img as $url_img )
                                                    {
                                                        $urlImage = $url_img ;
                                                    }
                                                }
                                                else
                                                {
                                                    $urlImage = $img['source'] ;
                                                }

                                                echo "\t\t" . '<image:image>' . "\n";
                                                echo "\t\t\t" . '<image:loc>' . $urlImage . '</image:loc>' . "\n";
                                                echo "\t\t" . '</image:image>' . "\n";
                                            }
                                        }
                                    }
                                }
                                echo "\t" . '</url>' . "\n" ;
                            }
                        }
                    }
                }
            }
        }
    }

    $pages = \DB::for_table('page')
        ->select('page.page_id')
        ->select('page.page_default')
        ->select('page.page_priority')
        ->select('page_lang.page_lang_url')
        ->select('page_lang.page_lang_lang_id')
        ->left_outer_join('page_lang', [ 'page_lang.page_lang_page_id', '=', 'page.page_id' ])
        ->where_equal('page.page_active',1)
        ->where_equal('page.page_index',1)
        ->where_in('page_lang.page_lang_lang_id', $langObj->getTabLang() )
        ->find_many();

    if ( $pages )
    {
        foreach( $pages as $page )
        {
            $url = ((string)$req->getUri()->getScheme() . '://' . (string)$req->getUri()->getHost()) ;

            if ( $page->page_default == 0 )
            {
                if ( $langObj->count() > 1 ) $url.= '/' . $langArray[ $page->page_lang_lang_id ] ;
                $url.= '/' . $page->page_lang_url ;
            }
            else
            {
                if ( $page->page_lang_lang_id == $langObj->getDefault()->id )   $url.= '/';
                else                                                            $url.= '/' . $langArray[ $page->page_lang_lang_id ] ;
            }

            echo "\t" . '<url>' . "\n" ;
            echo "\t\t" . '<loc>' . $url . '</loc>' . "\n";
            echo "\t\t" . '<priority>' . $page->page_priority . '</priority>' . "\n";
            echo "\t" . '</url>' . "\n" ;
        }
    }

    echo '</urlset>' . "\n" ;
})->setName('robots_txt');
