<?php

namespace App\Kernel\Common;

use App\Kernel\Back\Alt;
use App\Kernel\Back\Gallery;
use App\Kernel\Back\Seo;
use App\Kernel\Container;
use App\Kernel\Front\Translate;
use Slim\Slim;

class Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $_url_module = "" ;
    protected $_main = false;
    protected $_id = NULL ;

    protected $_entity = NULL ;
    protected $_entity_name = "" ;
    protected $_entity_id = NULL ;

    protected $_child = "" ;

    protected $_action_name = "" ;
    protected $_action = NULL ;

    protected $_module = NULL ;

    protected $_renderArray = [] ;

    protected $_depedency_module = NULL ;
    protected $_depedency_element = NULL ;

    protected $_msg 		= [] ;

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    /**
     * @param null $depedency_module
     */
    public function setDepedencyModule( $depedency_module )
    {
        $this->_depedency_module = $depedency_module;
    }

    /**
     * @param null $depedency_element
     */
    public function setDepedencyElement( $depedency_element )
    {
        $this->_depedency_element = $depedency_element;
    }

    public function setRender( $key , $value )
    {
        $this->_renderArray[ $key ] = $value ;
    }

    public function setModuleUrl( $var )
    {
        $this->_url_module = $var ;
    }

    public function setMain()
    {
        $this->_main = true ;
    }

    public function setId( $var )
    {
        $this->_id = $var ;
    }

    protected function setEntityId( $var )
    {
        $this->_entity_id = $var ;
    }

    public function setEntityName( $var )
    {
        $this->_entity_name = ucfirst( $var ) ;
    }

    protected function setEntity( $var )
    {
        $this->_entity = $var ;
    }

    public function setActionName( $var )
    {
        $this->_action_name = $var ;
    }

    public function setChild( $var )
    {
        $this->_child = $var ;
    }

    protected function setModule( $row )
    {
        $this->_module = new \stdClass;
        $this->_module->icon       = $row->module_icon ;
        $this->_module->name       = $row->module_kernel ? Translate::getInstance()->getText( $row->module_name ) : $row->module_name  ;
        $this->_module->class_name = $row->module_class_name ;
        $this->_module->default    = $row->module_default  ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    /**
     * @return null
     */
    public function getDepedencyModule()
    {
        return $this->_depedency_module;
    }

    /**
     * @return null
     */
    public function getDepedencyElement()
    {
        return $this->_depedency_element;
    }

    public function getModuleUrl()
    {
        return ( $this->_url_module != '' ? $this->_url_module . '/' : '' ) ;
    }

    protected function getRender()
    {
        return $this->_renderArray ;
    }

    protected function getChild()
    {
        return $this->_child ;
    }

    protected function getId()
    {
        return $this->_id ;
    }

    public function getEntityId()
    {
        return $this->_entity_id ;
    }

    public function getEntityName()
    {
        return $this->_entity_name ;
    }

    protected function getActionName()
    {
        return $this->_action_name ;
    }

    protected function getMethodName()
    {
        return $this->_action_name . 'Action' ;
    }

    /* ************************************************** */
    /* ******************    ISER    ******************** */
    /* ************************************************** */

    protected function isMain()
    {
        return $this->_main ;
    }

    /* ***************************************************** */
    /* ******************     TOOLS     ******************** */
    /* ***************************************************** */

    protected function Log()
    {
        return \App\Kernel\Back\Log::getInstance() ;
    }

    protected function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    protected function Lang()
    {
        return \App\Kernel\Lang::getInstance() ;
    }

    protected function Message()
    {
        return \App\Kernel\Message::getInstance() ;
    }

    protected function CMS()
    {
        return \App\Kernel\CMS::getInstance() ;
    }

    protected function getApp(): Slim
    {
        return Slim::getInstance() ;
    }

    /* ************************************************** */
    /* ******************    CANER    ******************* */
    /* ************************************************** */

    protected function canCreate()
    {
        if ( $this->getEntity()->canCreate() == false )
        {
            return false ;
        }
        else if ( $this->getEntity()->getMaxElement() == 0 )
        {
            return true ;
        }
        else if ( $this->isDepedency() == false && $this->getEntity()->isChild() == false && $this->getEntity()->getMaxElement() > $this->getRepository()->count() )
        {
            return true;
        }
        else if ( $this->isDepedency() == false && $this->getEntity()->isChild() == true && $this->getEntity()->getMaxElement() > $this->getRepository()->countWithParent( end( $this->getIdParent() ) ) )
        {
            return true;
        }
        else if ( $this->isDepedency() == true && $this->getEntity()->isChild() == false && $this->getEntity()->getMaxElement() > $this->getRepository()->countWithDepedencyElement( $this->getDepedencyModule() , $this->getDepedencyElement() ) )
        {
            return true;
        }
        else
        {
            $this->getEntity()->removeAction('add');
            return false;
        }
    }

    /* ************************************************** */
    /* *****************   MESSAGES   ******************* */
    /* ************************************************** */

    protected function m( $key )
    {
        if ( array_key_exists( $key , $this->getMessage() ) ) 	return $this->getMessage( $key ) ;
        else													return $this->Message()->get( $key ) ;
    }

    protected function getMessage( $key = NULL )
    {
        if ( $key === NULL ) return $this->_msg ;
        else				 return $this->_msg[ $key ] ;
    }

    /* ***************************************************** */
    /* ******************   CONTAINER   ******************** */
    /* ***************************************************** */

    protected function Container()
    {
        return Container::getInstance() ;
    }

    public function getEntity(): \App\Kernel\Entity\Builder
    {
        return $this->Container()->module( $this->getEntityName() )->getEntity() ;
    }

    /* ********************************************************* */
    /* ******************      REQUEST       ******************* */
    /* ********************************************************* */

    public function _get( $key = NULL )
    {
        return ( $key === NULL ? $_GET : $_GET[ $key ] ) ;
    }

    public function _post( $key = NULL )
    {
        return ( $key === NULL ? $_POST : $_POST[ $key ] ) ;
    }

    // obselete, a supprimer asap
    protected function post( $key = NULL )
    {
        return $this->_post( $key );
    }

    /* ********************************************************* */
    /* ******************   FETCH / RENDER   ******************* */
    /* ********************************************************* */

    protected function render( $template , $force = false )
    {
        if ( ( isset( $_GET['noview'] ) or isset( $_POST['noview'] ) ) && $force == false )
        {
            return "" ;
        }
        else
        {
            $View = $this->Container()->newClass('App\Kernel\View');
            $View->render( 'module/' . $template , $this->getRender() );
        }
    }

    protected function fetch( $template )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/' . $template , $this->getRender() );
    }


    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    protected function loadModuleUrl()
    {
        if ( ! $this->isMain() )
        {
            $row = \DB::for_table('module_lang')
                ->select('module_lang_url')
                ->where(['module_lang_lang_id' => $this->Lang()->getActive()->id, 'module_lang_module_id' => $this->getEntityId()])
                ->find_one();

            $this->setModuleUrl( $row->module_lang_url ) ;
        }
    }

    protected function isValidAction()
    {
        if ( $this->getEntity()->hasAction( $this->getActionName() ) )
        {
            if ( method_exists( $this , $this->getMethodName() ) == true )  return true ;
            else			 												return $this->Factory()->Response()->error("La méthode '" . $this->getMethodName() . "' n'est pas disponible dans le controller") ;
        }
        else
        {
            $this->Factory()->Response()->error("L'action '" . $this->getActionName() . "' est impossible") ;
        }
    }

    public function loadEntity()
    {
        $module = \DB::for_table('module')
            ->where(array('module_class_name' => $this->getEntityName() , 'module_active' => 1))
            ->find_one();

        if ( ! $module ) return false ;

        if ( $module->module_default == 1 ) $this->setMain();
        $this->setModule( $module ) ;
        $this->setEntityId( $module->module_id ) ;

        if ( $this->Container()->module( $this->getEntityName() )->getEntity() === NULL )
        {
            $this->Factory()->Response()->error("Le fichier '" . $this->getEntityName() . "' n'éxiste pas");
            return false ;
        }

        if ( !is_object( $this->getEntity() ) ) return false ;
        else							 		return true ;
    }

    /* ************************************************** */
    /* ******************   SELECT   ******************** */
    /* ************************************************** */

    // Systeme de many / one TO many / one
    public function getValueAssociated( $row , $returnType = NULL , $form = false , $fieldCheckbox = false , $index = false )
    {
        $Controller = Container::getInstance()->module( $row->getObject() )->getController(true, [ 'noAppend' => true ]);


        if ( $row->getData('var') !== NULL && $row->getData('var') == $Controller->getEntity()->getParentTargetName() )
        {
            $this->getEntity()->build( $row->getName() )->field()->setData( "parent" , true ) ;
            $this->getEntity()->build( $row->getName() )->field()->setData( "target" , 'titre' ) ;
            $this->getEntity()->build( $row->getName() )->field()->setData( "noEmptyValue" , true ) ;

            if ( $form ) $returnType = NULL ;
        }
        else if ( $Controller->getEntity()->getParentTargetName() )
		{
			$this->getEntity()->build( $row->getName() )->field()->setData( "parent" , true ) ;
			$this->getEntity()->build( $row->getName() )->field()->setData( "target" , 'titre' ) ;
//			$this->getEntity()->build( $row->getName() )->field()->setData( "noEmptyValue" , true ) ;

			if ( $index ) $returnType = "array" ;
			else if ( $form ) $returnType = NULL ;
		}

        if ( $Controller === false )
        {
            $this->Factory()->Response()->error("L'objet '" . $row->getObject() . "' dans le champ '" . $row->getTitle() . "' est impossible à charger");
        }
        else
        {
            $checkboxValue = [];
            if ( $fieldCheckbox !== false )
            {
                $checkboxValue = $this->getRepository()->getAssocSimpleValueIndex( $fieldCheckbox );
            }
            $tab = $Controller->getElementForAssociation( $row->getData('var') , $returnType , $checkboxValue ) ;

            unset( $Controller );
            return $tab ;
        }
    }

    public function getElementForAssociation( $name = NULL , $returnType = NULL , $checkbox = false )
    {
        $alias = 'titre' ;

        if ( $name !== NULL )
        {
            $target  = $this->getEntity()->get( $name );
            $content = $this->getRepository()->findAllForSelect( $target , $alias , $this->getEntity()->getParentName() ) ;
        }
        else
        {
            $content = $this->getRepository()->findAllForSelect2( $alias , $checkbox ) ;
        }

        switch( $returnType )
        {
            case "array" :
                $tab = [];

                if ( $this->getEntity()->get( $this->getEntity()->getFieldReference()[0] )->isUser() && defined('MODULE_USER') )
                {
                    $secondTab = $this->Container()->module( MODULE_USER )->getRepository(true)->findAllForSelect2();

                    if ( !empty( $secondTab ) && !empty( $content ) )
                    {
                        foreach( $secondTab as $row )
                        {
                            $userTab[ $row->id ] = $row->get( $alias );
                        }

                        foreach( $content as $row )
                        {
                            $tab[ $row->id ] = $userTab[ $row->get( $alias ) ];
                        }
                    }
                }
                else
                {
                    if ( $content )
                    {
                        foreach( $content as $row )
                        {
                            $tab[ $row->id ] = $row->get( $alias );
                        }
                    }
                }

                return $tab ;
                break;
            default :
                if ( $this->getEntity()->hasParent() ) $content = $this->getTreeParent( $content , $alias ) ;
                break;
        }

        return $content ;
    }

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function renderForm( $values )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/form.twig' , $values );
    }

    protected function generateForm( $valueF = false , $data = [] )
    {
        $form = $this->Factory()->Form() ;
        $form->initLib();
        $form->setModuleId( $this->getEntityId() );
        $form->setElementId( $this->getId() );
        $form->setFolder( $this->getEntity()->getFolder() );

        if ( $this->getEntity()->reCAPTCHA() == true )
        {
            $form->activeRecaptcha() ;
        }

        $contentShow = new \stdClass;

        if ( $valueF == true )
        {
            $content = $this->getRepository()->findOne( $this->getId() );

            if ( ! $content )
            {
                return false ;
            }
            else
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->hasLang() == false )
                    {
                        $name = $row->getName() ;
                        $contentShow->$name = $content->get( $row->getColumn() );
                    }
                }
            }

            if ( $this->getEntity()->hasMultiLang() )
            {
                foreach( $this->Lang()->getAll() as $lang )
                {
                    $contentLang[ $lang->id ] = $this->getRepository()->findOneLang( $this->getId() , $lang->id );
                }
            }

            if ( !empty( $this->getEntity()->getField() ) )
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->hasLang() == true )
                    {
                        $array = [];
                        foreach( $this->Lang()->getAll() as $lang )
                        {
                            if ( is_object( $contentLang[ $lang->id ] ) ) $array[ $lang->url ] = $contentLang[ $lang->id ]->get( $row->getColumn() );
                        }
                        $value = $array ;
                    }
                    else
                    {
                        if ( $row->getType() == 'checkbox' )
                        {
                            $value = $this->getAssocValueForm( $row->getName() ) ;
                        }
                        else if ( $row->getType() == 'date' )
                        {
                            $date  = new \DateTime( $content->get( $row->getColumn() ) ) ;
                            if ( $row->getData('hour') === true )
                            {
                                $value = $date->format('d/m/Y - H:i');
                            }
                            else
                            {
                                $value = $date->format('d/m/Y');
                            }
                        }
                        else if ( $row->getType() == 'gallery' )
                        {
                            $value = $this->getId();
                        }
                        else
                        {
                            $value = $content->get( $row->getColumn() );
                        }
                    }

                    $this->getEntity()->build( $row->getName() )->field()->setValue( $value ) ;

                    if ( $row->getName() == $this->getEntity()->getElementIdName() )
                    {
                        $this->setDepedencyElement( $value ) ;
                    }
                    else if ( $row->getName() == $this->getEntity()->getModuleIdName() )
                    {
                        $this->setDepedencyModule( $value ) ;
                    }
                }
            }
        }
        else
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                $this->getEntity()->build( $row->getName() )->field()->setValue( NULL ) ;
            }
        }

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( $row->isParent() == true )
                {
                    $opt = $this->getParent();
                    $this->getEntity()->get( $row->getName() )->setData( 'option' , $opt );
                }
                else if ( $row->isAssociated() == true )
                {
                    $opt = $this->getValueAssociated( $row , "array" , true );
                    $this->getEntity()->get( $row->getName() )->setData( 'option' , $opt );
                }
                else if ( $row->isParentModule() )
                {
                    $this->getEntity()->build( $row->getName() )->field()->setValue( end( $this->getIdParent() ) ) ;
                }
                else if ( $row->getName() == $this->getEntity()->getElementIdName() )
                {
                    $this->getEntity()->build( $row->getName() )->field()->setValue( $this->getDepedencyElement() ) ;
                }
                else if ( $row->getName() == $this->getEntity()->getModuleIdName() )
                {
                    $this->getEntity()->build( $row->getName() )->field()->setValue( $this->getDepedencyModule() ) ;
                }
            }
        }

        if ( $valueF == false )
        {
            if ( !empty( $this->getEntity()->getField() ) )
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    if ( $row->getType() == 'select' && $row->isRequired() == true )
                    {
                        $first = current( $row->getOptions() );
                        $name  = $row->getName();
                        if ( is_array( $first ) )
                        {
                            $first = array_keys( $first , current( $first ) )[0];
                        }
                        else
                        {
                            $first = array_keys( $row->getOptions() , $first )[0];
                        }

                        $contentShow->$name = $first ;
                    }
                }
            }
        }

        if ( ! empty( $data ) )
        {
            foreach( $data as $key => $value )
            {
                $this->getEntity()->build( $key )->field()->setValue( $value ) ;
                $contentShow->$key = $value ;
            }
        }

        $shows      = $this->getShow( true , $contentShow );
        $condition  = false ;
        $arrayField = [] ;

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
                {
                    if ( $row->hasCondition() )
                    {
                        $condition = true ;
                    }

                    $show = $shows['fields'][ $row->getName() ]['show'] ;

                    $arrayField[] = [
                        "name"      => $row->getName(),
                        "value"     => $row->getValue(),
                        "fieldname" => $row->getColumn(),
                        "Form_HTML" => $form->genHTML( $row ),
                        "title" 	=> $this->getTitleField( $row ),
                        "type" 		=> $row->getType(),
                        "tab" 		=> $row->getTab(),
                        "visible"   => $row->visible(),
                        "show" 		=> $show,
                        "group" 	=> $row->getGroup(),
                        "class" 	=> $row->getData('classField'),
                        "part" 		=> $row->getData('part'),
                        "width" 	=> $row->getData('width'),
                        "comment"	=> $row->getComment(),
                        "required"	=> $row->isRequired(),
                        "unit"      => $row->getUnit(),
                        "error" 	=> $row->getError(),
                    ];
                }
            }
        }

        return [
            'condition'  => $condition,
            'field'      => $arrayField,
            'tabs'       => $shows['tabs'],
            'route_type' => ( $valueF == false ? 'add' : 'edit' ),
            'id'         => $this->getId(),
            'cdn_css'    => $form->getCdnCSS(),
            'cdn_js'     => $form->getCdnJS(),
            'css'        => $form->getLibCSS(),
            'js'         => $form->getLibJS()
        ];
    }

    protected function checkCustomField( $field )
    {
        if ( $this->_post('moduleCustom') == 1 )
        {
            /*if ( $field->getType() == 'hidden' )
            {
                return false ;
            }
            else */if ( $field->getType() == 'document' )
            {
                if ( array_key_exists( "doc_" . $field->getColumn() , $this->_post() ) )
                {
                    return true ;
                }
                else
                {
                    return false ;
                }
            }
            else
            {
                if ( array_key_exists( $field->getColumn() , $this->_post() ) )
                {
                    return true ;
                }
                else
                {
                    return false ;
                }
            }
        }
        else
        {
            return true ;
        }
    }

    // Check si tous les champs sont corrects
    // Appelée dans la fonction "pushData" ; "listenForm" ; "User::register" ; "User::update"
    public function checkForm()
    {
        $arrayShow = $this->getShow(true);

        $this->_return = true ;

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( ( $arrayShow['fields'][ $row->getName() ]['show'] == true or $row->getType() == 'hidden' ) && $this->checkCustomField( $row ) )
                {
                    if ( $row->getType() == 'gallery' )
                    {
                        $this->field( $row->getName() )->setData( "id" , $this->getId() ) ;
                        $this->field( $row->getName() )->setData( "entity_id" , $this->getEntityId() ) ;
                        $this->field( $row->getName() )->setData( "controller" , $this->getEntityName() ) ;
                    }

                    if ( $this->field( $row->getName() )->checkEmpty() == false )
                    {
                        $this->_return = false ;
                    }
                }
            }

            if ( $this->_return == true )
            {
                foreach( $this->getEntity()->getField() as $row )
                {
                    $this->field( $row->getName() )->getFormatValue() ;
                }
            }
        }

        return $this->_return ;
    }

    protected function field( $name )
    {
        return $this->getEntity()->build( $name )->field() ;
    }

    /* ***************************************************** */
    /* ******************     HOOK      ******************** */
    /* ***************************************************** */

    /*  **** ADD **** */
    protected function hookAddCheckBefore() { return true; }
    protected function hookAddCheckAfter() { return true; }
    protected function hookAddSaveAfter( $c ) { return true; }

    /*  **** UPDATE **** */
    protected function hookUpdateCheckBefore() { return true; }
    protected function hookUpdateCheckAfter() { return true; }
    protected function hookUpdateSaveAfter( $c ) { return true; }

    /*  **** DELETE **** */
    protected function hookDeleteBefore() { return true; }
    protected function hookDeleteAfter() { return true; }

    /*  **** VALIDATION **** */
    protected function hookEnableAfter() { return true; }
    protected function hookDisableAfter() { return true; }

    /* ***************************************************** */
    /* ******************    SHOW IF    ******************** */
    /* ***************************************************** */

    protected function convertPost()
    {
        $contentShow = $this->getApp()->request->post();
        $std         = new \stdClass;
        $prefix      = \DB::getColumnName( '' , $this->getEntityName() );


        foreach( $contentShow as $key => $value )
        {
            $cle = str_replace( $prefix , '' , $key );
            $std->$cle = $value ;
        }

        return $std ;
    }

    public function getShow( $naming = false , $content = NULL )
    {
        if ( $content === NULL ) $contentShow = $this->convertPost();
        else                     $contentShow = $content ;

        $arrayTab      = $this->getEntity()->getTabs() ;
        $arrayField    = [] ;
        $newArrayTab   = [];
        $newArrayField = [];

        if ( !empty( $this->getEntity()->getField() ) )
        {
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL && $row->getType() !== 'hidden' )
                {
                    $show = $row->show( $contentShow ) ;

                    if ( $show == true )
                    {
                        $arrayTab[ $row->getTab() ]['show'] = true ;

                        if ( $row->getGroup() !== NULL )
                        {
                            $arrayTab[ $row->getTab() ]['group'][ $row->getGroup() ]['show'] = true ;
                        }
                    }
                    else
                    {
                        $name = $row->getName() ;
                        $contentShow->$name = NULL;
                    }

                    $arrayField[ $row->getName() ] = [
                        "name" => $row->getColumn(),
                        "show" => $show
                    ];
                }
            }
        }

        foreach( $arrayTab as $keyTab => $tab )
        {
            if ( is_callable( $tab['showIF'] ) )
            {
                $function = $tab['showIF'];
                $show = $function( $contentShow ) ;

                if ( $show == false )
                {
                    $arrayTab[ $keyTab ]['show'] = false ;

                    if ( !empty( $this->getEntity()->getField() ) )
                    {
                        foreach( $this->getEntity()->getField() as $row )
                        {
                            if ( $row->getTab() == $tab['key'] && $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
                            {
                                $name = $row->getName() ;
                                $contentShow->$name = NULL;

                                $arrayField[ $row->getName() ] = [
                                    "name" => $row->getColumn(),
                                    "show" => false
                                ];
                            }
                        }
                    }

                    if ( ! empty( $tab['group'] ) )
                    {
                        foreach( $tab['group'] as $keyGrp => $grp )
                        {
                            $arrayTab[ $keyTab ]['group'][ $keyGrp ]['show'] = false ;
                        }
                    }
                }
                else
                {
                    // on check les showif des groupes
                    if ( !empty( $tab['group'] ) )
                    {
                        $ctGrpHide = 0;
                        $ctGrp     = count( $tab['group'] );

                        foreach( $tab['group'] as $keyGrp => $grp )
                        {
                            if ( is_callable( $grp['showIF'] ) )
                            {
                                $function = $grp['showIF'];
                                $show = $function( $contentShow ) ;

                                if ( $show == false )
                                {
                                    $ctGrpHide++;
                                    $arrayTab[ $keyTab ][ 'group' ][ $keyGrp ][ 'show' ] = false;

                                    if ( ! empty( $this->getEntity()->getField() ) )
                                    {
                                        foreach ( $this->getEntity()->getField() as $row )
                                        {
                                            if ( $row->getGroup() == $grp[ 'key' ] && $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
                                            {
                                                $name = $row->getName();
                                                $contentShow->$name = NULL;

                                                $arrayField[ $row->getName() ] = [ "name" => $row->getColumn() , "show" => false ];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ( $ctGrpHide == $ctGrp )
                        {
                            $arrayTab[ $keyTab ]['show'] = false ;
                        }
                    }
                }
            }
            else
            {
                // on check les showif des groupes
                if ( !empty( $tab['group'] ) )
                {
                    $ctGrpHide = 0;
                    $ctGrp     = count( $tab['group'] );

                    foreach( $tab['group'] as $keyGrp => $grp )
                    {
                        if ( is_callable( $grp['showIF'] ) )
                        {
                            $function = $grp['showIF'];
                            $show = $function( $contentShow ) ;

                            if ( $show == false )
                            {
                                $ctGrpHide++;
                                $arrayTab[ $keyTab ][ 'group' ][ $keyGrp ][ 'show' ] = false;

                                if ( ! empty( $this->getEntity()->getField() ) )
                                {
                                    foreach ( $this->getEntity()->getField() as $row )
                                    {
                                        if ( $row->getGroup() == $grp[ 'key' ] && $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
                                        {
                                            $name = $row->getName();
                                            $contentShow->$name = NULL;

                                            $arrayField[ $row->getName() ] = [ "name" => $row->getColumn() , "show" => false ];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ( $ctGrpHide == $ctGrp )
                    {
                        $arrayTab[ $keyTab ]['show'] = false ;
                    }
                }
            }
        }

        if ( $naming == false )
        {
            foreach( $arrayField as $field )
            {
                $newArrayField[] = $field ;
            }
        }
        else
        {
            $newArrayField = $arrayField;
        }

        if ( $arrayTab )
        {
            foreach( $arrayTab as $tab )
            {
                $data = [];
                if ( $tab['group'] )
                {
                    foreach( $tab['group'] as $grp )
                    {
                        unset( $grp['showIF'] );
                        $data[] = $grp;
                    }

                    $tab['group'] = $data;
                    unset( $tab['showIF'] );
                }

                $newArrayTab[] = $tab ;
            }
        }

        return [
            'tabs'   => $newArrayTab,
            'fields' => $newArrayField
        ];
    }

    /* ***************************************************** */
    /* ******************    ACTIONS    ******************** */
    /* ***************************************************** */

    public function showAction()
    {
        return $this->Factory()->Response()->printJSON( $this->getShow() );
    }

    /* ************************************************** */
    /* ************         DELETE         ************** */
    /* ************************************************** */

    public function delete()
    {
        if ( $this->getEntity()->canDelete() == false )
        {
            return [
                'msg' => Translate::getInstance()->getText(unauthorized_delete),
                'url' => '',
                'result' => false
            ];
        }

        $result = $this->hookDeleteBefore() ;

        if ( $result === true )
        {
            $result = [
                'msg' => '',
                'url' => '',
                'result' => false
            ];

            $content = $this->getRepository()->findOne( $this->getId() );

            if ( $content )
            {
                if ( $this->getEntity()->hasMultiLang() )
                {
                    \DB::for_module_lang( $this->getEntityName() , $this->getId() )
                        ->delete_many();
                }

                if ( !empty( $this->getEntity()->getField() ) )
                {
                    foreach( $this->getEntity()->getField() as $name => $row )
                    {
                        if ( $row->getType() == "image" && $row->getData('hasAltText') == true )
                        {
                            $Alt = new Alt;
                            $Alt->setElementId( $this->getId() );
                            $Alt->setModuleId( $this->getEntityId() );
                            $Alt->delete();
                        }
                        else if ( $row->getType() == 'checkbox' )
                        {
                            \DB::for_module_assoc( $this->getEntityName() , $name )
                                ->where_equal( \DB::getTableNameAssoc( $this->getEntityName() , $name ) . '_' . \DB::getIdName( $this->getEntityName() ) , $this->getId() )
                                ->delete_many();
                        }
                        else if ( $row->getType() == 'gallery' )
                        {
                            $Gallery = new Gallery;
                            $Gallery->setElementId( $this->getId() );
                            $Gallery->setField( $row->getName() );
                            $Gallery->setModuleId( $this->getEntityId() );
                            $Gallery->setFolder( $this->getEntity()->getFolder() );
                            if ( $row->hasThumb() )
                            {
                                foreach( $row->getThumb() as $thumb )
                                {
                                    // width, height
                                    $Gallery->setThumb( $thumb[0] , $thumb[1] );
                                }
                            }
                            $Gallery->deleteElement();
                        }
                    }
                }

                if ( $this->getEntity()->hasUrl() )
                {
                    $seo = new Seo;
                    $seo->setElementId( $this->getId() );
                    $seo->setModuleId( $this->getEntityId() );
                    $seo->delete();
                }

                if ( $this->getEntity()->hasParent() )
                {
                    $parent_id = $content->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasOrder() )
                    {
                        $max = \DB::for_module( $this->getEntityName() );

                        if ( is_null( $parent_id ) )    $max = $max->where_null( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() );
                        else                            $max = $max->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $parent_id );

                        $max = $max->count();
                    }

                    $rst = \DB::for_module( $this->getEntityName() )->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $this->getId() ) ;
                    if ( $this->getEntity()->hasOrder() ) $rst = $rst->order_by_asc( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() );
                    $rst = $rst->find_many();

                    if ( $rst )
                    {
                        $order = $max + 1;
                        foreach( $rst as $rep )
                        {
                            $rep->set( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $parent_id ) ;
                            if ( $this->getEntity()->hasOrder() ) $rep->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order );
                            $rep->save();
                            $order++;
                        }
                    }
                }

                if ( $this->getEntity()->hasOrder() )
                {
                    $order = $content->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                    $rst = \DB::for_module( $this->getEntityName() )
                        ->select( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() )
                        ->select( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasParent() ) $rst = $rst->where_equal( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() , $content->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ) ;
                    $rst = $rst->where_gt( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order )->find_many();

                    if ( $rst )
                    {
                        foreach( $rst as $row )
                        {
                            $order = $row->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) - 1 ;
                            $row->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $order );
                            $row->save();
                        }
                    }
                }

                $content->delete();

                $this->hookDeleteAfter() ;

                $this->Log()->warning( 102 , "#" . $this->getId() . " - " . $this->getEntityName() , $this->getEntityId() , $this->getId() ) ;

                $result['msg'] = $this->m("delete_success");
                $result['result'] = true ;
            }
            else
            {
                $result['msg'] = $this->m("delete_success");
            }
        }

        return $result ;
    }
    protected function getAssocValueForm( $nameField , $id = NULL )
    {
        $content = \DB::for_module_assoc( $this->getEntityName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getEntityName() , $nameField ) )
            ->where_equal( \DB::getTableNameAssoc( $this->getEntityName() , $nameField ) . '_' . \DB::getIdName( $this->getEntityName() ) , ( $id === NULL ? $this->getId() : $id ) )
            ->find_many();

        $result  = array() ;

        if ( $content )
        {
            foreach( $content as $row )
            {
                $result[] = $row->get( \DB::getTableNameAssocValue( $this->getEntityName() , $nameField ) ) ;
            }
        }

        return $result ;
    }
}