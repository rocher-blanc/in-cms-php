<?php

namespace App\Kernel\Factory;

class File
{
    public function create( $nameFile , $content )
    {
        $fp = fopen( $nameFile , 'w+' ) ;
        $rst = fwrite( $fp , $content ) ;
        fclose( $fp ) ;

        return $rst ;
    }

    public function read( $nameFile )
    {
        if ( file_exists( $nameFile ) )
        {
            $fp 	 = fopen( $nameFile , 'r' ) ;
            $content = fread( $fp , filesize( $nameFile ) ) ;
            fclose( $fp ) ;
        }
        else
        {
            return false ;
        }

        return $content ;
    }

    public function copie( $dir, $dirDest )
    {
        if ( ! is_dir( $dirDest ) ) mkdir( $dirDest ) ;

        $hdir = opendir( $dir );
        while ( $item = readdir( $hdir ) )
        {
            if( $item == "." | $item == ".." ) continue;
            if ( is_dir( $dir . '/' . $item ) )
            {
                $this->copie( $dir . '/' . $item , $dirDest . '/' . $item ) ;
            }
            else
            {
                copy( $dir . '/' . $item , $dirDest . '/' . $item ) ;
            }
        }
        closedir( $hdir );
    }
}