<?php

namespace App\Kernel\Back;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Import
{

    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $file ;
    protected $fields ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {

    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function setFile( $var )
    {
        $this->file = $var ;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getFile()
    {
        return $this->file ;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /* ************************************************** */
    /* ******************  FUNCTIONS  ******************* */
    /* ************************************************** */

    public function upload()
    {
        $name = basename($_FILES['importfile']["name"]);
        $ext = explode( '.' , $name );
        $extension = end( $ext );
        $name = basename( $name , '.' . $extension );
        $name = \App\Kernel\Factory::getInstance()->Url()->encode( $name ) . "_" . time() . '.' . $extension ;

        $this->setFile( UPLOAD_PATH . "/" . $name );

        $rst = move_uploaded_file( $_FILES['importfile']["tmp_name"] , $this->getFile() );

        if ( $rst )
        {
            return $this->parse();
        }
    }

    protected function getFileArray()
    {
        try {
            $fileType = IOFactory::identify( $this->getFile() );
            return IOFactory::createReader($fileType)->load( $this->getFile() )->getActiveSheet()->toArray(null, true, true, true);;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    protected function parse()
    {
        $tab = $this->getFileArray() ;
        $tab = $this->convertArray( $tab ) ;

        dump( $tab );
    }

    protected function convertArray( array $array )
    {
        $newTab     = [];
        $arrayField = $this->getArrayField() ;
        $i          = 0;

        foreach( $array as $line )
        {
            foreach( $line as $letter => $value )
            {
                $newTab[ $i ][ $arrayField[ $letter ] ] = $value ;
            }
        }

        return $newTab ;
    }

    protected function getArrayField()
    {
        $tab = [];

        foreach( $this->getFields() as $name => $field )
        {
            $tab[ $field['letter'] ] = $name ;
        }

        return $tab ;
    }
}