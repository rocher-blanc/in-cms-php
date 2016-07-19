<?php

namespace App\Kernel;

class Document
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $document_id = NULL ;
    protected $document_name = NULL ;
    protected $folder_name = NULL ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct() {}

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setDocumentId( $var )
    {
        $this->document_id = $var ;
    }

    public function setDocumentName( $var )
    {
        $this->document_name = $var ;
    }

    public function setFolder( $var )
    {
        $this->folder_name = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getDocumentId()
    {
        return $this->document_id ;
    }

    public function getDocumentName()
    {
        return $this->document_name ;
    }

    public function getFolder()
    {
        return $this->folder_name ;
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function getIcon( $name )
    {
        $exp 	= explode( "." , $name ) ;
        $ext 	= end( $exp ) ;

        $my = new \stdClass;

        switch( $ext )
        {
            case "ods" :
            case "doc" :
            case "docx" :
                $my->class = '-word';
                $my->color = '#2B579A';
            break;

            case "xls" :
            case "xlsx" :
            case "csv" :
                $my->class = '-excel';
                $my->color = '#217346';
            break;

            case "zip" :
            case "rar" :
            case "tar" :
            case "gz" :
                $my->class = '-archive';
                $my->color = '#F3DD00';
            break;

            case "wav" :
            case "mp3" :
            case "wma" :
                $my->class = '-audio';
                $my->color = '#FF2DFF';
            break;

            case "ppt" :
            case "pptx" :
            case "pps" :
            case "ppsx" :
                $my->class = '-powerpoint';
                $my->color = '#B7472A';
            break;

            case "html" :
                $my->class = '-code';
                $my->color = '#FF5931';
            break;

            case "avi" :
            case "mp4" :
            case "divx" :
            case "mov" :
            case "mkv" :
            case "mpeg" :
                $my->class = '-video';
                $my->color = '#c4302b';
            break;

            case "pdf" :
                $my->class = '-pdf';
                $my->color = '#FD0000';
            break;

            case "txt" :
                $my->class = '-txt';
                $my->color = '#000000';
                break;

            default:
                $my->class = '';
                $my->color = '#ff0000';
            break;
        }

        return $my ;
    }

    public function getNameById()
    {
        $rst = \DB::for_table('document')
            ->select('document_name')
            ->where_equal( 'document_id' , $this->getDocumentId() )
            ->find_one();

        if ( $rst )
        {
            $this->setDocumentName( $rst->document_name ) ;
        }

        if ( $rst )	return true ;
        else		return false ;
    }
}