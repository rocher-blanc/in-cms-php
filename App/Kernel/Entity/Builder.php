<?php

namespace App\Kernel\Entity;

use App\Kernel\Entity\Field;
use App\Kernel\Exception;
use App\Kernel\Front\Translate;

class Builder extends Model
{
    /*
     * @array
     * Variable contenant tous les champs de l'entité
     */
    protected $_field = [] ;

    /*
     * @array
     * Variable contenant toutes les tabs
     */
    protected $_tab = [] ;

    /*
     * @array
     * Variable contenant tous les groupes
     */
    protected $_group = [] ;

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
     * Définit si la duplication est autorisée
     */
    protected $_dupliacate = false;

    /*
     * @boolean
     * Définit si une gestion d'ordre est présente
     */
    protected $_hasOrder = false;

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
     * Définit s'il y a des dépendances
     */
    public $_hasDependency = false;

    /*
     * @boolean
     * Définit s'il y a une valeur par défaut dans le module
     */
    public $_hasDefault = false;

    /*
     * @array
     * Liste toutes les dépendances du module
     */
    public $_dependency = [];

    /*
     * @boolean
     * Définit si c'est une dépendance
     */
    public $_isDependency = false;

    /*
     * @boolean
     * Définit si on active le recaptcha
     */
    public $_reCAPTCHA = false;

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

    /*
     * @array
     * Variable contenant toutes les icones pour les modules (tableau de gestion)
     */
    protected $icon = [];

    /*
     * @boolean
     * Variable qui définit si le module est un parent
     */
    protected $_hasModuleParent = false;

    /*
     * @string
     * Variable contenant le nom du module parent
     */
    protected $_module_parent_name = '' ;

    /*
     * @string
     * Variable contenant le nom du module enfant
     */
    protected $_module_child_name = [] ;

    /*
     * @string
     * Variable contenant le nom du champ stockant l'ID de l'èlément de module parent
     */
    protected $_module_parent_id_name = '' ;

    /*
     * @string
     * Variable contenant le nom du champ de la premiere image dans le module
     * Elle peut égalment être modifier
     */
    protected $_first_image_name = '' ;

    /*
     * @string
     * Variable contenant le nom du champ stockant l'ID de l'utilisateur
     */
    protected $_user_id_name = 'user_front_id' ;

    /*
     * @array
     * Variable contenant le ou les champs de références quand un autre module appel celui ci
     */
    protected $_field_reference = ["id"] ;

    /*
     * @int
     * Variable qui gère le nombre d'element par page
     */
    protected $_pagination = NULL ;

    /*
     * @boolean
     * Définit si le module est un module utilisateur
     */
    protected $_module_user = false ;

    /*
     * @boolean
     * Désactive la suppression
     */
    protected $_delete = true ;

    /*
     * @boolean
     * Désactive la modification
     */
    protected $_update = true ;

    /*
     * @boolean
     * Désactive la creation
     */
    protected $_create = true ;

    /*
     * @boolean
     * Désactive l'import
     */
    protected $_import = true ;

    /*
     * @callable
     * Affiche ou non le bouton de delete
     */
    protected $showDelete = NULL ;

    /*
     * @callable
     * Affiche ou non le bouton d'edit
     */
    protected $showEdit = NULL ;

    /*
     * @int
     * Définit le nombre maximum d'element dans un module
     * Si ce nombre est attient, l'ajout devient impossible
     * Par défaut, le nombre est à 0, ce qui vaut à illimité
     */
    protected $_max_element = 0;

    protected $_default_order = NULL ;
    protected $_default_order_by = NULL ;

    protected $forbidden_field = [
        /* Gestion utilisateurs */
        'user_login',
        'user_action',
        'user_password',
        'user_password_confirm',
        'user_new_password',
        'user_new_password_confirm',

        /* Gestion depedency */
        'module_id',
        'element_id',

        /* Module front pour les utilisateurs */
        'user_front_id',

        /* Module parent */
        'element_module_parent_id',

        /* Element par defaut */
        'default',

        /* Element parent */
        'parent_id',

        /* Autres */
        'id',
        'parent',
        'url',
        'order',
        'date_created',
        'date_last_updated',
        'date_updated',
        'isValid',
        'moduleCustom'
    ];
    private $_id_name;
    private $_default_name;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct()
    {
        // Tab par défaut pour les formulaires
        $this->addTab( 'contenu' , "Contenu" , 'icon-home2' ) ;

        $this->setCustomFolder( $this->getClassName() );
        $this->initDefaultField() ;
        $this->setDefaultSetting() ;
        $this->load() ;
        $this->check() ;
    }

    /* ************************************************** */
    /* *******************   ISER   ******************** */
    /* ************************************************** */

    public function isDependency()
    {
        $this->_isDependency = true ;

        $this->build('module_id' , true )->isModuleId();
        $this->build('element_id' , true )->isElementId();
    }

    public function isChild()
    {
        return ( empty( $this->_module_parent_name ) ? false : true ) ;
    }

    public function isModuleUser()
    {
        return $this->_module_user ;
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

    public function hasDefault()
    {
        return $this->_hasDefault ;
    }

    public function hasDependency()
    {
        return $this->_hasDependency ;
    }

    public function hasModuleParent()
    {
        return $this->_hasModuleParent ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    /**
     * @param int $max_element
     */
    public function setMaxElement( int $max_element )
    {
        $this->_max_element = $max_element;
    }

    public function setModuleUser()
    {
        $this->_module_user = true ;
    }

    /**
     * @param null $default_order_by
     */
    public function setDefaultOrderBy($default_order_by)
    {
        $this->_default_order_by = $default_order_by;
    }

    /**
     * @param null $default_order
     */
    public function setDefaultOrder($default_order)
    {
        $this->_default_order = $default_order;
    }

    protected function setMultilang()
    {
        $this->_hasMultiLang = true ;
    }

    protected function setOrder()
    {
        $this->_hasOrder = true ;
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

    protected function setIndexName( $name )
    {
        $this->_index_name = $name ;
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

    protected function setModuleIdName( $name )
    {
        $this->_module_id_name = $name ;
        return $this ;
    }

    protected function setElementIdName( $name )
    {
        $this->_element_id_name = $name ;
        return $this ;
    }

    public function setFirstImageName( $name )
    {
        $this->_first_image_name = $name ;
        return $this ;
    }

    public function setDefaultName( $n )
    {
        $this->_default_name = $n ;
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

    /**
     * @param string $module_parent_name
     */
    public function setModuleParent($module_parent_name)
    {
        $this->addAction("parent") ;
        $this->_module_parent_name = $module_parent_name;
        $this->build('element_module_parent_id' , true )->isModuleParentId();
    }

    /**
     * @param string $module_child_name
     */
    public function setModuleChild($module_child_name)
    {
        $this->_hasModuleParent = true;
        $this->_module_child_name[$module_child_name] = $module_child_name;
    }

    /**
     * @param string $module_child_name
     */
    public function setDefault()
    {
        $this->_hasDefault = true;
        $this->build('default' , true )->isDefault();

    }

    protected function setFieldReference( $var )
    {
        if ( ! is_array( $var ) )
        {
            $var = [ $var ] ;
        }
        $this->_field_reference = $var ;
    }

    protected function setModuleParentIdName( $name )
    {
        $this->_module_parent_id_name = $name;
    }

    /**
     * @param null $pagination
     */
    public function setPagination( $pagination )
    {
        $this->_pagination = $pagination;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    /**
     * @return boolean
     */
    public function itsDepedency()
    {
        return $this->_isDependency;
    }

    /**
     * @return null
     */
    public function getDefaultOrderBy()
    {
        return strtolower($this->_default_order_by);
    }

    /**
     * @return null
     */
    public function getDefaultOrder()
    {
        return $this->_default_order;
    }

    /**
     * @return int
     */
    public function getMaxElement()
    {
        return $this->_max_element;
    }

    /**
     * @return null
     */
    public function getPagination()
    {
        return $this->_pagination;
    }

    /*
     *
     */
    public function getModuleParentIdName()
    {
        return $this->_module_parent_id_name ;
    }

    public function getDefaultName()
    {
        return $this->_default_name ;
    }

    /**
     * @return array
     */
    public function getFieldReference():array
    {
        return $this->_field_reference;
    }

    /**
     * @return string
     */
    public function getModuleParentName():string
    {
        return $this->_module_parent_name;
    }

    /**
     * @return array
     */
    public function getModuleChildName():array
    {
        return $this->_module_child_name;
    }

    public function getFirstImageName()
    {
        return $this->_first_image_name ;
    }

    protected function getLast()
    {
        return $this->_last ;
    }

    protected function getDepe()
    {
        return $this->_last ;
    }

    /**
     * @return array
     */
    public function getField():array
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

    public function getIndexName()
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

    public function getModuleIdName()
    {
        return $this->_module_id_name ;
    }

    public function getElementIdName()
    {
        return $this->_element_id_name ;
    }

    public function getUserIdName()
    {
        return $this->_user_id_name ;
    }

    public function getParentTargetName()
    {
        return $this->_parent_target_name ;
    }

    public function getDependency()
    {
        return $this->_dependency ;
    }

    public function getFolder()
    {
        return $this->_folder_name ;
    }

    /* ************************************************** */
    /* *****************    reCAPTCHA    **************** */
    /* ************************************************** */

    public function activeReCAPTCHA()
    {
        $this->_reCAPTCHA = true ;
    }

    public function reCAPTCHA()
    {
        return $this->_reCAPTCHA ;
    }

    /* ************************************************** */
    /* *****************    DUPLICATE    **************** */
    /* ************************************************** */

    public function duplicate()
    {
        $this->addAction('duplicate');
        $this->_dupliacate = true ;
    }
 
    public function canDuplicate()
    {
        return $this->_dupliacate ;
    }

    /* ************************************************** */
    /* *****************     DELETE      **************** */
    /* ************************************************** */

    /*
     * Désactivation de la suppression
     */
    public function disableDelete()
    {
        $this->_delete = false ;
        $this->removeAction('delete');
    }

    public function canDelete()
    {
        return $this->_delete ;
    }

    public function showDelete( callable $function )
    {
        $this->showDelete = $function ;
    }

    public function getShowDelete()
    {
        return $this->showDelete ;
    }

    public function showEdit( callable $function )
    {
        $this->showEdit = $function ;
    }

    public function getShowEdit()
    {
        return $this->showEdit ;
    }

    /* ************************************************** */
    /* *****************     UPDATE      **************** */
    /* ************************************************** */

    /*
     * Désactivation de la suppression
     */
    public function disableUpdate()
    {
        $this->_update = false ;
        $this->removeAction('edit');
    }

    public function canUpdate()
    {
        return $this->_update ;
    }

    /* ************************************************** */
    /* *****************     CREATE      **************** */
    /* ************************************************** */

    /*
     * Désactivation de la suppression
     */
    public function disableCreate()
    {
        $this->_create = false ;
        $this->removeAction('add');
        $this->disableImport();
    }

    public function canCreate()
    {
        return $this->_create ;
    }

    /* ************************************************** */
    /* *****************     IMPORT      **************** */
    /* ************************************************** */

    /*
     * Désactivation de la suppression
     */
    public function disableImport()
    {
        $this->_import = false ;
        $this->removeAction('import');
    }

    public function canImport()
    {
        return $this->_import ;
    }

    /* ************************************************** */
    /* *****************   TABULATIONS   **************** */
    /* ************************************************** */

    public function addTab( $key , $name , $icon , $showIF = NULL )
    {
        $this->_tab[ $key ] = [
            'group'  => [],
            'name'   => $name,
            'icon'   => $icon,
            'key'    => $key,
            'show'   => false,
            'showIF' => $showIF
        ];
    }

    public function getTabs()
    {
        $tab = [];
        if ( ! empty( $this->getField() ) )
        {
            foreach( $this->getField() as $row )
            {
                if ( $row->getType() != 'hidden' )
                {
                    if ( ! array_key_exists( $row->getTab() , $tab ) )
                    {
                        if ( array_key_exists( $row->getTab() , $this->_tab ) )
                        {
                            $tab[ $row->getTab() ] = $this->_tab[ $row->getTab() ];
                        }
                        else
                        {
                            $tab[ $row->getTab() ] = [
                                'name' => $row->getTab(),
                                'icon' => 'icon-question',
                                'key'  => $row->getTab(),
                                'show' => false
                            ];
                        }
                    }

                    if ( $row->getGroup() !== NULL )
                    {
                        if ( array_key_exists( $row->getGroup() , $this->_group ) )
                        {
                            $tab[ $row->getTab() ]['group'][ $row->getGroup() ] = $this->_group[ $row->getGroup() ];
                        }
                        else
                        {
                            $tab[ $row->getTab() ]['group'][ $row->getGroup() ] = [
                                'name' => $row->getGroup(),
                                'key'  => $row->getGroup(),
                                'show' => false
                            ];
                        }
                    }
                }
            }
        }

        return $tab ;
    }

    /* ************************************************** */
    /* *****************     ICONES      **************** */
    /* ************************************************** */

    public function addIcon( $class , $action , $showIF = NULL , $type = NULL )
    {
        $this->addAction( $action );
        $this->icon[ $action ] = [
            'action'   => $action,
            'class'    => $class,
            'blank'    => ( $type == 'blank' ? true : false ),
            'ajax'     => ( $type == 'ajax' ? true : false ),
            'modal'    => ( $type == 'modal' ? true : false ),
            'showIF'   => $showIF
        ];
    }

    public function getIcon()
    {
        return $this->icon ;
    }

    /* ************************************************** */
    /* *****************     GROUPES     **************** */
    /* ************************************************** */

    public function addGroup( $key , $name , $showIF = NULL )
    {
        $this->_group[ $key ] = [
            'name'   => $name,
            'key'    => $key,
            'show'   => false,
            'showIF' => $showIF
        ];
    }

    public function getGroup( $key )
    {
        return $this->_group[ $key ] ;
    }

    /* ************************************************** */
    /* ****************   DEPEDENCY   ******************* */
    /* ************************************************** */

    public function addDependency( $name , $showIF = NULL )
    {
        $pass = true ;
        if ( is_callable( $showIF ) )
        {
            $pass = $showIF();
        }

        if ( ! in_array( $name , $this->_dependency ) && $pass === true )
        {
            $rst = \DB::for_table('module')
                ->select('module_icon')
                ->select('module_name')
                ->where(array('module_class_name' => $name , 'module_active' => 1))
                ->find_one();

            if( $rst )
            {
                $this->_hasDependency = true ;
                $this->_dependency[] = [
                    'name' => $rst->module_name,
                    'icon' => $rst->module_icon,
                    'class' => $name
                ];
            }
        }
    }

    /* ************************************************** */
    /* ****************   GENERICS FIELDS   ************* */
    /* ************************************************** */

    /* ORDER */
    protected function enableOrder()
    {
        $this->build('order' , true )->isOrder();
    }

    /* VALIDATION */
    protected function enableValidation()
    {
        $this->build('isValid' , true )
            ->isBoolean()
            ->noFront()
            ->full()
            ->defaut(1)
            ->name('En ligne');

        $this->addAction("enable") ;
        $this->addAction("disable") ;
        $this->setValidation() ;
        $this->setValidationName( $this->field()->getName() ) ;
    }

    /* USER */
    protected function enableUser()
    {
        if ( ACTIVE_USER )
        {
            if ( $this->getApp()->config('config') == 'back' )
            {
                if ( defined('MODULE_USER') )
                {
                    $this->build('user_front_id' , true )
                        ->isSelect()
                        ->user()
						->ManyToMany( MODULE_USER )
                        ->name('Utilisateur');
                }
                else
                {
                    $this->build('user_front_id' , true )
                        ->isSelect()
                        ->user()
                        ->isInteger()
                        ->name('Utilisateur');
                }
            }
            else
            {
                $this->build('user_front_id' , true )
                    ->isHidden("INT" , 11)
                    ->user()
                    ->defaut(function() {
                        return ( \App\Kernel\Front\User::getInstance()->isLogged() ? \App\Kernel\Front\User::getInstance()->getId() : \App\Kernel\Front\User::getInstance()->getTmpId() ) ;
                    } , true  );
            }
        }
    }

    protected function getUserAction()
    {
        return ( \App\Kernel\Front\User::getInstance()->isLogged() ? 'update' : 'register' ) ;
    }

    protected function enableUserModule()
    {
        if ( ACTIVE_USER )
        {
            $this->addGroup('connexion' , 'Informations de connexion');
            $this->setModuleUser() ;
            //$this->disableCreate() ;

            $this->build('user_front_id' , true )
                ->isHidden("INT" , 11)
                ->defaut(function() {
                    return ( \App\Kernel\Front\User::getInstance()->isLogged() ? \App\Kernel\Front\User::getInstance()->getId() : \App\Kernel\Front\User::getInstance()->getTmpId() ) ;
                } , true  );

            $this->build('user_action' , true )
                ->isHidden()
                ->group('connexion')
                ->noSave()
                ->noBack()
                ->noRename()
                ->defaut( $this->getUserAction() , true  );

            $this->build('user_login' , true )
                ->isVarchar("150", "email")
                ->group('connexion')
                ->notEmpty('user_register_login_empty' , true )
                ->noSave()
                ->noRename()
                ->formated(function( $c ) {
                    return filter_var( $c, FILTER_VALIDATE_EMAIL ) ;
                }, "user_login_not_valid" , "L'adresse email est invalide" )
                ->setData('fieldSql' , 'user_front.user_front_login')
                ->defaut( ( \App\Kernel\Front\User::getInstance()->isLogged() ? \App\Kernel\Front\User::getInstance()->getLogin() : '' ) , true )
                ->name('Email');

            $this->build('user_front_user_front_group_id' , true )
                ->isSelect()
                ->option( \App\Kernel\Back\User::getInstance()->getGroups() )
                ->group('connexion')
                ->notEmpty("Merci d'indiquer le groupe d'utulisateur" )
                ->noSave()
                ->noFront()
                ->noRename()
                ->setData('fieldSql' , 'user_front.user_front_user_front_group_id')
                ->defaut(function() {
                    return ( \App\Kernel\Front\User::getInstance()->isLogged() ? \App\Kernel\Front\User::getInstance()->getGroup() : \App\Kernel\Front\User::getInstance()->getDefaultGroup() ) ;
                } , true  )
                ->name("Groupe d'utilisateurs");

            if ( $this->getApp()->config('config') == 'front' )
            {
                if ( ! \App\Kernel\Front\User::getInstance()->isLogged() )
                {
                    $this->build('user_password' , true )
                        ->isPassword()
                        ->group('connexion')
                        ->notEmpty('user_register_password_empty' , true )
                        ->noRename()
                        ->noBack()
                        ->name( Translate::getInstance()->getText('password') );

                    $this->build('user_password_confirm' , true )
                        ->isPassword()
                        ->group('connexion')
                        ->notEmpty('user_register_confirm_password_empty' , true )
                        ->noRename()
                        ->noBack()
                        ->name( Translate::getInstance()->getText('mdp_confirm') );
                }
            }
            else
            {
                $this->enableValidation();
                $this->build('user_password' , true )
                    ->isPassword()
                    ->group('connexion')
                    //->notEmpty( Translate::getInstance()->getText('mandatory_password') )
                    ->noRename()
                    ->noFront()
                    ->noindex()/*
                    ->showIf(function($c) {
                        return ( $c->user_front_id == '' ? true : false );
                    })*/
                    ->formated(function( $c ) {
                        if ( $_POST['id_element'] == '' ) {
                            if ( empty( $_POST['user_password'] ) ) {
                                return false ;
                            }

                            return true ;
                        }
                        else
                        {
                            if ( ( empty( $_POST['user_password'] ) && ! empty( $_POST['user_password_confirm'] ) ) or ( ! empty( $_POST['user_password'] ) && empty( $_POST['user_password_confirm'] ) ) )
                            {
                                return false ;
                            }
                            else
                            {
                                return true ;
                            }
                        }
                    }, Translate::getInstance()->getText('mandatory_password') )
                    ->name( Translate::getInstance()->getText('password') );

                $this->build('user_password_confirm' , true )
                    ->isPassword()
                    ->group('connexion')
                    //->notEmpty( Translate::getInstance()->getText('mandatory_confirm_password') )
                    ->noRename()
                    ->noFront()
                    ->noindex()/*
                    ->showIf(function($c) {
                        return ( $c->user_front_id == '' ? true : false );
                    })*/
                    ->formated(function( $c ) {
                        if ( $_POST['id_element'] == '' ) {
                            if ( empty( $_POST['user_password'] ) ) {
                                return false ;
                            }
                            else if ( $_POST['user_password_confirm'] != $_POST['user_password'] )
                            {
                                return false ;
                            }
                            else
                            {
                                return true ;
                            }
                        }
                        else
                        {
                            if ( $_POST['user_password_confirm'] != $_POST['user_password'] )
                            {
                                return false ;
                            }
                            else
                            {
                                return true ;
                            }
                        }
                    }, "Les deux mots de passe sont différents" )
                    ->name( Translate::getInstance()->getText('mandatory_password') );
            }
        }
    }

    /* PARENTS */
    protected function enableParent( $target )
    {
        $this->build('parent_id' , true )
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
        $this->field()->setData( "twig" , 'id' ) ;
        $this->setIdName( $this->field()->getName() ) ;

        return $this ;
    }

    protected function isDefault()
    {
        $this->field()->setData( "SQL_VALUE" , 1 ) ;
        $this->field()->setData( "SQL_TYPE" , "TINYINT" ) ;
        $this->field()->setData( "SQL_DEFAULT" , 0 ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        $this->field()->setData( "twig" , 'default' ) ;
        $this->setDefaultName( $this->field()->getName() ) ;
        $this->addAction("default") ;

        return $this ;
    }

    protected function isModuleParentId()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "hidden" ) ;
        $this->field()->setData( "moduleParent" , true ) ;
        $this->field()->setData( "twig" , "parent" ) ;
        $this->field()->setData( "object" , $this->getModuleParentName() ) ;
        $this->setModuleParentIdName( $this->field()->getName() ) ;

        return $this ;
    }

    protected function isModuleId()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "hidden" ) ;
        $this->setModuleIdName( $this->field()->getName() ) ;

        return $this ;
    }

    protected function isElementId()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "hidden" ) ;
        $this->field()->setData( "twig" , "depedency" ) ;
        $this->setElementIdName( $this->field()->getName() ) ;

        return $this ;
    }

    /* ************************************************** */
    /* *****************   FUNCTIONS   ****************** */
    /* ************************************************** */

    public function build( $name , $force = false )
    {
        $this->setLast( $name ) ;

        if ( ! array_key_exists( $name , $this->getField() ) )
        {
            if ( $force == false && in_array( $name , $this->forbidden_field ) )
            {
                throw new Exception("Prohibit naming this field \"" . $this->getLast() . "\" - Entity : " . $this->getClassName() ) ;
            }
            else
            {
                $this->initField( $name ) ;
            }
        }

        return $this ;
    }

    protected function initField( $name )
    {
        /* On initialiste tout par défaut */
        $field = new Field;
        $field->setEntityName( $this->getClassName() );
        $field->setName( $name );

        $this->_field[ $name ] = $field;
        $this->column( 1 , 2 ) ;
        $this->twig( $name ) ;
    }

    public function field()
    {
        if ( is_object( $this->_field[ $this->getLast() ] ) ) 	return $this->_field[ $this->getLast() ] ;
        else													throw new Exception("No field with that name \"" . $this->getLast() . "\" - Entity : " . $this->getClassName() ) ;
    }

    public function get( $field ): Field
    {
        return $this->setLast( $field )->field() ;
    }

    protected function initDefaultField()
    {
        $this->build('id' , true )->isId();
        $this->build('date_created' , true )->twig('date_created')->isHiddenDate();
        $this->build('date_last_updated' , true )->twig('date_last_updated')->isHiddenDate();
        $this->build('date_updated' , true )->twig('date_updated')->isHiddenDate();
    }

    protected function getUserList()
	{
		$users = [];
		$req_user = \DB::for_table("user_front")->find_many();
		foreach( $req_user as $row )
		{
			$users[ $row->user_front_id ] = $row->user_front_login;
		}
		return $users;
	}

    /* ************************************************** */
    /* ******************   CHAMPS   ******************** */
    /* ************************************************** */

    protected function isVideo()
    {
        $this->field()->setData( "SQL_VALUE" , 255 ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "video" ) ;
        return $this ;
    }

    protected function isIndex()
    {
        $this->field()->setData( "SQL_VALUE" , 1 ) ;
        $this->field()->setData( "SQL_TYPE" , "TINYINT" ) ;
        $this->field()->setData( "SQL_DEFAULT" , 1 ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        $this->setIndexName( $this->field()->getName() ) ;

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
        $this->field()->setData( "SQL_VALUE" , "255" ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "document" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathDocument(false) ) ;

        $this->setDocument() ;
        $this->setDocumentField( $this->field()->getData("columnName") ) ;

        $this->addAction("uploaddocument") ;
        $this->addAction("orderdocument") ;
        $this->addAction("deletedocument") ;

        return $this ;
    }

    protected function isHiddenDate()
    {
        $this->field()->setData( "SQL_TYPE" , "DATETIME" ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        return $this ;
    }

    protected function isHidden( $type = NULL , $size = NULL , $defaut = NULL )
    {
        $this->field()->setData( "SQL_VALUE" , $size ) ;
        $this->field()->setData( "SQL_TYPE" , $type ) ;
        $this->field()->setData( "SQL_DEFAULT" , $defaut ) ;
        $this->field()->setData( "tab" , "" ) ;
        $this->field()->setData( "type" , "hidden" ) ;
        return $this ;
    }

    protected function isDate( $hour = false , $old = false )
    {
        $this->field()->setData( "SQL_TYPE" , "DATE" . ( $hour ? "TIME" : "" ) ) ;
        $this->field()->setData( "type" , "date" ) ;
        $this->field()->setData( "hour" , $hour ) ;
        $this->field()->setData( "old" , $old ) ;
        return $this ;
    }

    protected function isHour()
    {
        $this->field()->setData( "SQL_VALUE" , "5" ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "hour" ) ;
        return $this ;
    }

    protected function isPassword()
    {
        $this->field()->setData( "type" , "password" ) ;
        $this->noSave();
        return $this ;
    }

    protected function isText( $opt = '' )
    {
        $this->field()->setData( "SQL_TYPE" , $opt . "TEXT" ) ;
        $this->field()->setData( "type" , "textarea" ) ;
        return $this ;
    }

    protected function isIcon( array $list , $libCss = [] )
    {
        $this->field()->setData( "SQL_VALUE" , 40 ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "listIcons" , $list ) ;
        $this->field()->setData( "libCss" ,$libCss ) ;
        $this->field()->setData( "type" , "icon" ) ;
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

    protected function isLink()
    {
        $this->field()->setData( "SQL_VALUE" , 255 ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "link" ) ;
        $this->field()->setData( "maxLength" , 255 ) ;
        return $this ;
    }

    protected function isVarchar( $t = 255 , $type = 'text' )
    {
        $this->field()->setData( "SQL_VALUE" , $t ) ;
        $this->field()->setData( "SQL_TYPE" , "VARCHAR" ) ;
        $this->field()->setData( "type" , "text" ) ;
        $this->field()->setData( "subtype" , $type ) ;
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

    protected function isInteger()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "number" ) ;
        $this->field()->setData( "step" , 1 ) ;
        return $this ;
    }

    protected function isImage()
    {
        $this->field()->setData( "SQL_VALUE" , 11 ) ;
        $this->field()->setData( "SQL_TYPE" , "INT" ) ;
        $this->field()->setData( "type" , "image" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathImage(false) ) ;
        $this->field()->setData( "hasAltText" , true ) ;
        $this->Thumb( 260 , 130 );
        $this->Thumb( 100 , 100 );

        if ( count( $this->getImageField() ) == 0 && empty( $this->getFirstImageName() ) )
        {
            $this->setFirstImageName( $this->field()->getName() );
        }

        $this->setImage() ;
        $this->setImageField( $this->field()->getData("columnName") ) ;

        $this->addAction("upload") ;
        $this->addAction("deletemedia") ;
        $this->addAction("libimages") ;
        $this->addAction("libimagesgallery") ;
        $this->addAction("libimagesupdate") ;

        return $this ;
    }

    protected function isGallery()
    {
        $this->field()->setData( "type" , "gallery" ) ;
        $this->field()->setData( "module" , $this->getClassName(false) ) ;
        $this->field()->setData( "folder" , $this->getPathImage(false) ) ;
        $this->field()->setData( "noUpdate" , true ) ;
        $this->field()->setData( "noOffset" , true ) ;

        $this->setGallery() ;
        $this->setGalleryField( $this->field()->getColumn() ) ;

        $this->addAction("uploadgallery") ;
        $this->addAction("deletegallery") ;
        $this->addAction("ordergallery") ;

        return $this ;
    }

    protected function isCheckbox()
    {
        $this->field()->setData( "type" , "checkbox" ) ;
        $this->field()->setData( "noOffset" , true ) ;
        return $this ;
    }

    protected function isSelect( $integer = true  , $taille = 11 )
    {
        $this->field()->setData( "type" , "select" ) ;
        $this->field()->setData( "SQL_VALUE" , $taille ) ;
        $this->field()->setData( "SQL_TYPE" , ( $integer == true ? "INT" : "VARCHAR" ) ) ;

        return $this ;
    }

    protected function isCustom( $type , $typeSql = NULL , $valueSql = NULL )
    {
        if ( $valueSql !== NULL ) $this->field()->setData( "SQL_VALUE" , $valueSql ) ;
        if ( $typeSql !== NULL ) $this->field()->setData( "SQL_TYPE" , $typeSql ) ;
        $this->field()->setData( "type" , $type ) ;
        $this->field()->setData( "custom" , true ) ;
        return $this ;
    }

    /* ************************************************** */
    /* ******************   OPTIONS   ******************* */
    /* ************************************************** */

    protected function isURL()
    {
        $this->setUrlName( $this->field()->getName() );
        $this->field()->setData( "isURL" , true ) ;
        $this->setUrl() ;
        $this->addAction("seo") ;

        return $this ;
    }

    public function setData( $key , $value )
    {
        $this->field()->setData( $key , $value ) ;
        return $this;
    }

    protected function noFront()
    {
        $this->field()->setData( 'front' , false ) ;
        return $this ;
    }

    protected function noBack()
    {
        $this->field()->setData( 'back' , false ) ;
        return $this ;
    }

    protected function format( $name , $format )
    {
        $this->field()->setFormat( $name , $format ) ;
        return $this ;
    }

    protected function isReference()
    {
        $this->setFirstImageName( $this->field()->getName() ) ;
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

    protected function noAltText()
    {
        $this->field()->setData( "hasAltText" , false ) ;
        return $this ;
    }

    protected function full()
    {
        $this->column( 1 , 1 ) ;
        return $this ;
    }

    protected function readonly()
    {
        $this->field()->setData( "readonly" , true ) ;
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

    protected function ManyToMany( $object , $var = NULL )
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

    protected function defaut( $t = 0  , $force = false )
    {
        $this->field()->setData( "defaut" , $t ) ;
        $this->field()->setData( "force" , $force ) ;
        return $this ;
    }

    protected function defautSearch( $t )
    {
        $this->field()->setData( "defautSearch" , $t ) ;
        return $this ;
    }

    protected function noindex()
    {
        $this->field()->setData( "noindex" , true ) ;
        return $this ;
    }

    protected function showIf( callable $result )
    {
        $this->field()->setData( "showIf" , $result ) ;
        $this->addAction("show") ;
        return $this ;
    }

    protected function style( callable $result )
    {
        $this->field()->setData( "style" , $result ) ;
        return $this ;
    }

    protected function javascript( callable $result )
    {
        $this->field()->setData( "javascript" , $result ) ;
        return $this ;
    }

    protected function updateValue( callable $result )
    {
        $this->field()->setData( "updateValue" , $result ) ;
        return $this ;
    }

    protected function formated( callable $result , string $t , string $msg = "" )
    {
        if ( $msg != '' )
        {
            if ( $this->getApp()->config('config') == 'back' )
            {
                $s = $msg;
            }
            else
            {
                $s = \App\Kernel\Front\Translate::getInstance()->getText( $t );
            }
        }
        else
        {
            $s = $t;
        }

        $this->field()->setData( "formated" , $result ) ;
        $this->field()->setData( "notFormated_msg" , $s ) ;

        return $this ;
    }

    protected function transform( callable $result )
    {
        $this->field()->setData( "transform" , $result ) ;
        return $this ;
    }

    protected function comment( $t )
    {
        $this->field()->setData( "comment" , $t ) ;
        return $this ;
    }

    protected function visibie()
    {
        $this->field()->setData( "visibility" , true ) ;
        return $this ;
    }

    protected function column( $part , $width )
    {
        if ( $part == $width / 2 )	$className = 'col_half' ;
        else if ( $part == $width )	$className = 'col_full' ;
        else 						$className = 'col_' . $this->convertNumber( $part ) . '_' . $this->convertNumber2( $width ) ;

        $this->field()->setData( "classField" , $className ) ;
        $this->field()->setData( "part" , $part ) ;
        $this->field()->setData( "width" , $width ) ;
        return $this ;
    }

    protected function convertNumber( $i )
    {
        switch( $i )
        {
            case '1' : return 'one';   break;
            case '2' : return 'two';   break;
            case '3' : return 'three'; break;
            case '4' : return 'four';  break;
            case '5' : return 'five';  break;
        }
    }

    protected function convertNumber2( $i )
    {
        switch( $i )
        {
            case '3' : return 'third';  break;
            case '4' : return 'fourth'; break;
            case '5' : return 'fifth';  break;
        }
    }

    protected function twig( $t )
    {
        $this->field()->setData( "twig" , $t ) ;
        return $this ;
    }

    protected function tab( $t )
    {
        $this->field()->setData( "tab" , $t ) ;
        return $this ;
    }

    protected function group( $t )
    {
        $this->field()->setData( "group" , $t ) ;
        return $this ;
    }

    protected function unit( $unit , $where )
    {
        $this->field()->setData( "unit" , $unit ) ;
        $this->field()->setData( "whereUnit" , $where ) ;
        return $this ;
    }

    protected function notEmpty( $t = "" , $traduction = false )
    {
        $this->field()->setData( "notEmpty_msg" , $traduction ? \App\Kernel\Front\Translate::getInstance()->getText( $t ) : $t ) ;
        $this->field()->setData( "notEmpty" , true ) ;
        return $this ;
    }

    protected function editor()
    {
        $this->addAction("editor") ;
        $this->field()->setData( "editor" , true ) ;
        return $this ;
    }

    protected function name( $t = "" )
    {
        $this->field()->setData( "title" , $t ) ;
        return $this ;
    }

    protected function mode( $t )
    {
        $this->field()->setData( "mode" , $t ) ;
        return $this ;
    }

    protected function onTab()
    {
        $this->field()->setData( "tab" , true ) ;
        return $this ;
    }

    // DEPRECIATED
    protected function viewOnIndex()
    {
        return $this ;
    }

    protected function option( $val )
    {
        $this->field()->setData( "option" , $val ) ;
        return $this ;
    }

    protected function data( $key , $val )
    {
        $this->field()->setData( $key , $val ) ;
        return $this ;
    }

    protected function noSave()
    {
        $this->field()->setData( "nosave" , true ) ;
        return $this ;
    }

    protected function user()
    {
        $this->field()->setData( "user" , true ) ;
        return $this ;
    }

    protected function noRename()
    {
        $this->field()->setData( "norename" , true ) ;
        return $this ;
    }
}