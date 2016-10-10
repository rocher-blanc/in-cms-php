<?php

namespace App\Kernel\Entity;

class Builder extends Model
{
    /*
     * @array
     * Variable contenant tous les champs de l'entité
     */
    protected $_field = [] ;

    /*
     * @string
     * Variable contenant le nom du dernier champ construit
     */
    protected $_last = "" ;

    /*
     * @boolean
     * Définit si des champs multi-langues sont présents
     */
    protected $_hasMultiLang = false;

    /*
     * @boolean
     * Définit si une gestion d'ordre est présente
     */
    protected $_hasOrder = false;

    /*
     * @boolean
     * Définit s'il y a une gestion des restrictions d'affichage dans pour les groupes d'utulisateurs
     */
    protected $_hasRestrictionGroup = false;

    /*
     * @boolean
     * Définit si une gestion de validation est présente
     */
    protected $_hasValidation = false;

    /*
     * @boolean
     * Définit si une gestion des URL dans le module est présente
     */
    protected $_hasURL = false;

    /*
     * @string
     * Contient le nom du champ URL
     * NULL par défaut
     */
    protected $_url_name = NULL ;

    /*
     * @boolean
     * Définit s'il y a des images dans le module
     */
    protected $_hasImage = false;

    /*
     * @boolean
     * Définit s'il y a des galeries dans le module
     */
    protected $_hasGallery = false;

    /*
     * @boolean
     * Définit s'il y a des documents dans le module
     */
    protected $_hasDocument = false;

    /*
     * @boolean
     * Définit s'il y a des familles dans le module
     */
    protected $_hasParent = false;

    /*
     * @boolean
     * Définit s'il y a des paragraphes dans le module
     */
    protected $_hasParagraph = false;

    /*
     * @boolean
     * Définit s'il y a des dépendances
     */
    public $_hasDependency = false;


    /*
     * @array
     * Variable contenant tous les messages d'erreurs par défaut
     */
    protected $_msg = [];

    /*
     * @array
     * Variable contenant tous les champs images
     */
    protected $_img_field = [];

    /*
     * @array
     * Variable contenant tous les champs galeries
     */
    protected $_gallery_field = [];

    /*
     * @array
     * Variable contenant tous les champs documents
     */
    protected $_doc_field = [];

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        $this->setCustomFolder( $this->getClassName() );
        $this->initDefaultField() ;
        $this->setDefaultSetting() ;
        $this->load() ;
        $this->check() ;
    }

    /* ************************************************** */
    /* *******************   HASER   ******************** */
    /* ************************************************** */

    public function hasMultilang()
    {
        return $this->_hasMultiLang ;
    }

    public function hasOrder()
    {
        return $this->_hasOrder ;
    }

    public function hasRestrictionGroup()
    {
        return $this->_hasRestrictionGroup ;
    }

    public function hasValidation()
    {
        return $this->_hasValidation ;
    }

    public function hasUrl()
    {
        return $this->_hasURL ;
    }

    public function hasImage()
    {
        return $this->_hasImage ;
    }

    public function hasGallery()
    {
        return $this->_hasGallery ;
    }

    public function hasDocument()
    {
        return $this->_hasDocument ;
    }

    public function hasParent()
    {
        return $this->_hasParent ;
    }

    public function hasParagraph()
    {
        return $this->_hasParagraph ;
    }

    public function hasDependency()
    {
        return $this->_hasDependency ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    protected function setMultilang()
    {
        $this->_hasMultiLang = true ;
    }

    protected function setOrder()
    {
        $this->_hasOrder = true ;
    }

    protected function setRestrictionGroup()
    {
        $this->_hasRestrictionGroup = true ;
    }

    protected function setUrl()
    {
        $this->_hasURL = true ;
    }

    protected function setValidation()
    {
        $this->_hasValidation = true ;
    }

    protected function setGallery()
    {
        $this->setImage() ;
        $this->_hasGallery = true ;
    }

    protected function setImage()
    {
        $this->_hasImage = true ;
    }

    protected function setDocument()
    {
        $this->_hasDocument = true ;
    }

    protected function setParent()
    {
        $this->_hasParent = true ;
    }

    protected function setDependency()
    {
        $this->_hasDependency = true ;
    }

    protected function setLast( $name )
    {
        $this->_last = $name ;
        return $this ;
    }

    protected function setUrlName( $name )
    {
        $this->_url_name = $name ;
        return $this ;
    }

    protected function setIdName( $name )
    {
        $this->_id_name = $name ;
        return $this ;
    }

    protected function setOrderName( $name )
    {
        $this->_order_name = $name ;
        return $this ;
    }

    protected function setValidationName( $name )
    {
        $this->_validation_name = $name ;
        return $this ;
    }

    protected function setParentName( $name )
    {
        $this->_parent_name = $name ;
        return $this ;
    }

    protected function setDependencyName( $name )
    {
        $this->_dependency_name = $name ;
        return $this ;
    }

    protected function setParentTarget( $name )
    {
        $this->_parent_target_name = $name ;
        return $this ;
    }

    protected function setGalleryField( $name )
    {
        $this->_gallery_field[] = $name ;
        return $this ;
    }

    protected function setImageField( $name )
    {
        $this->_img_field[] = $name ;
        return $this ;
    }

    protected function setDocumentField( $name )
    {
        $this->_doc_field[] = $name ;
        return $this ;
    }

    protected function setCustomFolder( $name )
    {
        $this->_folder_name = $this->Factory()->Url()->encode( $name ) ;
        return $this ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getLast()
    {
        return $this->_last ;
    }

    public function getField()
    {
        return $this->_field ;
    }

    public function getImageField()
    {
        return $this->_img_field ;
    }

    public function getDocumentField()
    {
        return $this->_doc_field ;
    }

    public function getIdName()
    {
        return $this->_id_name ;
    }

    public function getOrderName()
    {
        return $this->_order_name ;
    }

    public function getUrlName()
    {
        return $this->_url_name ;
    }

    public function getValidationName()
    {
        return $this->_validation_name ;
    }

    public function getParentName()
    {
        return $this->_parent_name ;
    }

    public function getParentTargetName()
    {
        return $this->_parent_target_name ;
    }

    public function getDependencyName()
    {
        return $this->_dependency_name ;
    }

    public function getFolder()
    {
        return $this->_folder_name ;
    }

    /* ************************************************** */
    /* ******************   CHAMPS   ******************** */
    /* ************************************************** */

    public function build( $name )
    {
        $this->setLast( $name ) ;

        if ( ! array_key_exists( $name , $this->getField() ) )
        {
            $this->initField( $name ) ;
        }

        return $this ;
    }

    protected function initField( $name )
    {
        /* On initialiste tout par défaut */
        $field = new \App\Kernel\Entity\Field;
        $field->setEntityName( $this->getClassName() );
        $field->setName( $name );

        $this->_field[ $name ] = $field;
    }

    public function field()
    {
        if ( is_object( $this->_field[ $this->getLast() ] ) ) 	return $this->_field[ $this->getLast() ] ;
        else													throw new \App\Kernel\Exception("No field with that name \"" . $this->getLast() . "\" - Entity : " . $this->getClassName() ) ;
    }

    public function get( $field )
    {
        return $this->setLast( $field )->field() ;
    }

    /* ID */
    protected function initDefaultField()
    {
        $this->build('id')->isId();
        $this->build('date_created')->isHiddenDate();
        $this->build('date_last_updated')->isHiddenDate();
        $this->build('date_updated')->isHiddenDate();
    }

    /* PARAGRAPH */
    public function enableParagraph()
    {
        $this->_hasParagraph = true ;
        $this->addAction("content") ;
        $this->addAction("paragraphlist") ;
    }

    /* ORDER */
    protected function enableOrder()
    {
        $this->build('order')->isOrder();
    }

    /* DEPENDANCE */
    protected function enableDependency( $module )
    {
        $this->setDependency();
        $this->setDependencyName( $module );
    }

    /* VALIDATION */
    protected function enableValidation()
    {
        $this->build('isValid')
            ->isBoolean()
            ->defaut(1)
            ->name('Visible ?');

        $this->addAction("enable") ;
        $this->addAction("disable") ;
        $this->setValidation() ;
        $this->setValidationName( $this->field()->getName() ) ;
    }

    /* PARENTS */
    protected function enableParent( $target )
    {
        $this->build('parent_id')
            ->isSelect()
            ->name('Parent');

        $this->setParent() ;
        $this->setParentName( $this->field()->getName() ) ;
        $this->setParentTarget( $target ) ;
        $this->field()->setData( "parent" , true ) ;
        $this->field()->setData( "target" , $target ) ;
    }

    /* TYPE DE CHAMPS */
    protected function isId()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "SQL_AUTO_INCREMENT" , true ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        $this->setIdName( $this->field()->getName() ) ;

        return $this ;
    }

    protected function isURL()
    {
        $this->setUrlName( $this->field()->getName() );
        $this->field()->setData( "isURL" , true ) ;
        $this->setUrl() ;
        $this->addAction("seo") ;
        return $this ;
    }

    protected function isOrder()
    {
        $default = 9999;

        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_DEFAULT" , $default ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "order" , true ) ;

        $this->addAction("order") ;

        $this->defaut( $default ) ;
        $this->setOrder() ;
        $this->setOrderName( $this->field()->getName() ) ;
        return $this ;
    }

    protected function isDocument()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "document" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathDocument(false) ) ;

        $this->setDocument() ;
        $this->setDocumentField( $this->field()->getData("columnName") ) ;

        $this->addAction("doc_newupload") ;
        $this->addAction("doc_postupload") ;
        $this->addAction("doc_upload") ;
        $this->addAction("doc_postclick") ;
        $this->addAction("document") ;
        $this->addAction("deletedocument") ;

        return $this ;
    }

    protected function isHiddenDate()
    {
        $this->field()->setData( "SQL_TYPE" , "DATETIME" ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        return $this ;
    }

    protected function isDate( $hour = false )
    {
        $this->field()->setData( "SQL_TYPE" , "DATE" . ( $hour ? "TIME" : "" ) ) ;
        $this->field()->setData( "type" , "date" ) ;
        return $this ;
    }

    protected function format( $name , $format )
    {
        $this->field()->setFormat( $name , $format ) ;
        return $this ;
    }

    protected function isText()
    {
        $this->field()->setData( "SQL_TYPE" , "TEXT" ) ;
        $this->field()->setData( "type" , "textarea" ) ;
        return $this ;
    }

    protected function isBoolean()
    {
        $this->field()->setData( "SQL_VALUE" , 1 ) ;
        $this->field()->setData( "SQL_TYPE" , "TINYINT" ) ;
        $this->field()->setData( "SQL_DEFAULT" , 0 ) ;
        $this->field()->setData( "isBoolean" , true ) ;
        $this->field()->setData( "type" , "radio" ) ;
        return $this ;
    }

    protected function isVarchar( $t = 255 )
    {
        $this->field()->setData( "SQL_VALUE" , $t ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "text" ) ;
        $this->field()->setData( "maxLength" , $t ) ;
        return $this ;
    }

    protected function isFloat( $step = 1 )
    {
        $this->field()->setData( "SQL_TYPE" , "FLOAT" ) ;
        $this->field()->setData( "type" , "number" ) ;
        $this->field()->setData( "step" , $step ) ;
        return $this ;
    }

    protected function isImage()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "image" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathImage(false) ) ;

        $this->setImage() ;
        $this->setImageField( $this->field()->getData("columnName") ) ;

        $this->addAction("newupload") ;
        $this->addAction("postupload") ;
        $this->addAction("upload") ;
        $this->addAction("postclick") ;
        $this->addAction("media") ;
        $this->addAction("deletemedia") ;

        return $this ;
    }

    protected function isGallery()
    {
        $this->field()->setData( "type" , "gallery" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathImage(false) ) ;

        $this->setGallery() ;
        $this->setGalleryField( $this->field()->getColumn() ) ;

        $this->addAction("jgallery") ;
        $this->addAction("jgallery_upload") ;
        $this->addAction("jgallery_delete") ;
        $this->addAction("jgallery_crop") ;
        $this->addAction("jgallery_order") ;

        return $this ;
    }

    protected function Thumb( $width , $height )
    {
        $this->field()->setThumb( array( $width , $height ) ) ;
        return $this;
    }

    protected function Crop( $width , $height )
    {
        $this->field()->setCrop( array( $width , $height ) ) ;
        $this->addAction("crop") ;
        $this->addAction("cropimage") ;
        return $this;
    }

    protected function isCheckbox()
    {
        $this->field()->setData( "type" , "checkbox" ) ;
        return $this ;
    }

    protected function isSelect( $integer = true  , $taille = 11 )
    {
        $this->field()->setData( "type" , "select" ) ;
        $this->field()->setData( "SQL_VALUE" , $taille ) ;
        $this->field()->setData( "SQL_TYPE" , ( $integer == true ? "INT" : "VARCHAR" ) ) ;

        return $this ;
    }

    /* ASSOCIATION DE CHAMPS */
    protected function OneToOne( $object , $var )
    {
        $this->field()->setData( "oneToOne" , true ) ;
        $this->field()->setData( "object" , $object ) ;
        $this->field()->setData( "var" , $var ) ;
        return $this ;
    }

    protected function OneToMany( $object )
    {
        $this->field()->setData( "oneToMany" , true ) ;
        $this->field()->setData( "object" , $object ) ;
        $this->field()->setData( "var" , $var ) ;
        return $this ;
    }

    protected function ManyToOne( $object , $var )
    {
        $this->field()->setData( "manyToOne" , true ) ;
        $this->field()->setData( "object" , $object ) ;
        $this->field()->setData( "var" , $var ) ;
        return $this ;
    }

    protected function ManyToMany( $object , $var )
    {
        $this->field()->setData( "manyToMany" , true ) ;
        $this->field()->setData( "object" , $object ) ;
        $this->field()->setData( "var" , $var ) ;
        return $this ;
    }

    /* OPTIONS DES CHAMPS */
    protected function isLang()
    {
        $this->field()->setLang() ;
        if ( $this->field()->getData("isURL") == true ) $this->setUrlName( $this->field()->getName() ) ;
        $this->setMultilang() ;
        return $this ;
    }

    protected function search()
    {
        $this->field()->setData( "search" , true ) ;
        return $this ;
    }

    protected function defaut( $t = 0 )
    {
        $this->field()->setData( "defaut" , $t ) ;
        return $this ;
    }

    protected function comment( $t )
    {
        $this->field()->setData( "comment" , $t ) ;
        return $this ;
    }

    protected function unit( $unit , $where )
    {
        $this->field()->setData( "unit" , $unit ) ;
        $this->field()->setData( "whereUnit" , $where ) ;
        return $this ;
    }

    protected function notEmpty( $t = "" )
    {
        $this->field()->setData( "notEmpty_msg" , $t ) ;
        $this->field()->setData( "notEmpty" , true ) ;
        return $this ;
    }

    protected function editor()
    {
        $this->field()->setData( "editor" , true ) ;
        return $this ;
    }

    protected function name( $t = "" )
    {
        $this->field()->setData( "title" , $t ) ;
        return $this ;
    }

    protected function onTab()
    {
        $this->field()->setData( "tab" , true ) ;
        return $this ;
    }

    protected function viewOnIndex()
    {
        $this->field()->setData( "index" , true ) ;
        return $this ;
    }

    protected function option( $val )
    {
        $this->field()->setData( "option" , $val ) ;
        return $this ;
    }
}