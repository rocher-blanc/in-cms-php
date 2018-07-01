<?php

namespace App\Kernel\Common;

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

    protected $_action_name = "" ;
    protected $_action = NULL ;

    protected $_module = NULL ;

    protected $_renderArray = [] ;

    protected $_depedency_module = NULL ;
    protected $_depedency_element = NULL ;

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

    protected function setModule( $row )
    {
        $this->_module = new \stdClass;
        $this->_module->icon       = $row->module_icon ;
        $this->_module->name       = $row->module_name  ;
        $this->_module->class_name = $row->module_class_name  ;
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
        return $this->_url_module . '/' ;
    }

    protected function getRender()
    {
        return $this->_renderArray ;
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

    protected function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    /* ***************************************************** */
    /* ******************   CONTAINER   ******************** */
    /* ***************************************************** */

    protected function Container()
    {
        return \App\Kernel\Container::getInstance() ;
    }

    public function getEntity()
    {
        return $this->Container()->module( $this->getEntityName() )->getEntity() ;
    }

    public function getRepository()
    {
        return $this->Container()->module( $this->getEntityName() )->getRepository() ;
    }

    /* ********************************************************* */
    /* ******************   FETCH / RENDER   ******************* */
    /* ********************************************************* */

    protected function render( $template )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        $View->render( 'module/' . $template , $this->getRender() );
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

        if ( ! file_exists( ENTITY_PATH . '/' . $this->getEntityName() . '.php' ) )
        {
            $this->Factory()->Response()->error("Le fichier '" . $this->getEntityName() . "' n'éxiste pas");
            return false ;
        }

        if ( !is_object( $this->getEntity() ) ) return false ;
        else							 		return true ;
    }

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function renderForm( $values )
    {
        $View = $this->Container()->newClass('App\Kernel\View');
        return $View->fetch( 'module/form.twig' , $values );
    }

    protected function generateForm( $value = false )
    {
        $form = $this->Factory()->Form() ;
        $form->initLib();
        $form->setModuleId( $this->getEntityId() );
        $form->setElementId( $this->getId() );

        $contentShow = new \stdClass;

        if ( $value == true )
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
                            $value = $this->getAssocValue( $row->getName() ) ;
                        }
                        else if ( $row->getType() == 'date' )
                        {
                            $date  = new \DateTime( $content->get( $row->getColumn() ) ) ;
                            $value = $date->format('d/m/Y');
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
                }
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
                        "Form_HTML" => $form->genHTML( $row ),
                        "title" 	=> $row->getData('title'),
                        "type" 		=> $row->getType(),
                        "tab" 		=> $row->getTab(),
                        "show" 		=> $show,
                        "group" 	=> $row->getGroup(),
                        "class" 	=> $row->getData('classField'),
                        "part" 		=> $row->getData('part'),
                        "width" 	=> $row->getData('width'),
                        "comment"	=> $row->getComment(),
                        "required"	=> $row->isRequired(),
                        "error" 	=> $row->getError()
                    ];
                }
            }
        }

        return [
            'condition'  => $condition,
            'field'      => $arrayField,
            'tabs'       => $shows['tabs'],
            'route_type' => ( $value == false ? 'add' : 'edit' ),
            'id'         => $this->getId(),
            'cdn_css'    => $form->getCdnCSS(),
            'cdn_js'     => $form->getCdnJS(),
            'css'        => $form->getLibCSS(),
            'js'         => $form->getLibJS()
        ];
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
                if ( $arrayShow['fields'][ $row->getName() ]['show'] == true )
                {
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
    protected function hookAddSaveAfter() { return true; }

    /*  **** UPDATE **** */
    protected function hookUpdateCheckBefore() { return true; }
    protected function hookUpdateCheckAfter() { return true; }
    protected function hookUpdateSaveAfter() { return true; }

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

    protected function getShow( $naming = false , $content = NULL )
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
                if ( $row->getData( $this->getDataView() ) == true && ( ( $row->isParent() == true && $row->hasOption() == true ) or $row->isParent() != true ) && $row->getType() !== NULL )
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
}