<?php

ini_set('upload_max_filesize', '10M');
ini_set('default_charset', 'utf-8');
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);
ini_set("session.dd", SESSION_LIFETIME );

if ( DEBUG )
{
    ini_set('error_reporting', error_reporting() & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 'on');
}
else
{
    ini_set('error_reporting', error_reporting() & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 'off');
}