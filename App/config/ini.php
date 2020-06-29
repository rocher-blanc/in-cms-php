<?php

ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('default_charset', 'utf-8');
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);

ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);

session_start() ;

if ( DEBUG_CMS )
{
    // ini_set('error_reporting', error_reporting());
    ini_set('error_reporting', error_reporting() & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 'on');
}
else
{
    ini_set('error_reporting', error_reporting() & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 'off');
}