<?php

function converteDateFr( $datecomplete )
{
    if ( ! empty( $datecomplete ) )
    {
        list($date, $hour) = explode(' ', $datecomplete);
        list($y, $m, $d) = explode('-', $date);
        return "$d/$m/$y";
    }
    return false;
}

function converteDateDb( $date )
{
    if ( ! empty( $date ) )
    {
        list( $d , $m , $y ) = explode('/' , $date );
        return "$y-$m-$d" ;
    }
    return false ;
}

function converteDateToTime( $datecomplete )
{
    list($date, $hour) = explode(' ', $datecomplete);
    list($y, $m, $d) = explode('-', $date);
    list($h, $i, $s) = explode(':', $hour);

    return mktime ($h, $i, $s, $m, $d, $y);
}

$app->group('/log', function (\Slim\Routing\RouteCollectorProxy $app)
{
    $app->get('/', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res, array $args = [])
    {
        $info = true ;
        $danger = true ;
        $alerte = true ;

        $tabType = [1,2,3];

        $date_start = date("Y-m-d" , ( time() - ( 86400 * 5  ) ) ) . " 00:00:00";
        $date_end   = date("Y-m-d") . " 23:59:59";

        if ( strtoupper($req->getMethod()) === 'POST' )
        {
            $tabType = [];

            if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['info'] ?? '') : '') == NULL )   $info = false ;
            else                                         $tabType[] = 1;

            if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['danger'] ?? '') : '') == NULL ) $danger = false ;
            else                                         $tabType[] = 2;

            if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['alerte'] ?? '') : '') == NULL ) $alerte = false ;
            else                                         $tabType[] = 3;

            if ( (is_array($req->getParsedBody()) ? ($req->getParsedBody()['date'] ?? '') : '') != '' )
            {
            	$dates = (is_array($req->getParsedBody()) ? ($req->getParsedBody()['date'] ?? '') : '');
                $date_start = \App\Kernel\Factory::getInstance()->Date()->convertUs( $dates['start'] ) . " 00:00:00";
                $date_end   = \App\Kernel\Factory::getInstance()->Date()->convertUs( $dates['end'] ) . " 23:59:59";
            }
        }

        $logRows = \DB::for_table('log')
            ->where_date_lte('log_date' , $date_end )
            ->where_date_gte('log_date' , $date_start )
            ->where_in('log_type' , $tabType )
            ->order_by_desc('log_date')
            ->find_many() ;

        $rows = array() ;
        if ( $logRows )
        {
            $log = \App\Kernel\Back\Log::getInstance() ;
            foreach( $logRows as $row ) {
                $log->setData( $row ) ;
                $rows[] = $log->parse() ;
            }
        }

        return \App\Kernel\AppContext::twig()->render($res, 'admin/log/index.twig.html', [
            "logRows" => $rows,
            "info"    => $info,
            "alerte"  => $alerte,
            "danger"  => $danger,
            "date"    => (is_array($req->getParsedBody()) ? ($req->getParsedBody()['date'] ?? '') : ''),
        ]);

    });
});