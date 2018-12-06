<?php

namespace App\Kernel\Back;

use JasonGrimes\Paginator;

class Controller extends \App\Kernel\Common\Controller
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

	protected $_token 		= NULL ;
	protected $_lang 		= NULL ;
    protected $_id_parent 	= [] ;
    protected $_options 	= [] ;

    /* ************************************************** */
    /* ****************   CONSTRUCT   ******************* */
    /* ************************************************** */

    public function __construct( $options = [] )
    {
        $this->_optiondate_updateds = $options ;
    }

    /* ************************************************** */
    /* ******************   SETTER   ******************** */
    /* ************************************************** */

    public function setIdParent( $var )
    {
        $this->_id_parent = $var ;
    }

    public function setLang( $var )
    {
        $this->_lang = $var ;
    }

    public function setToken( $var )
    {
        $this->_token = $var ;
    }

    public function setOption( $key , $var )
    {
        $this->_options[ $key ] = $var ;
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    protected function getOption( $key )
    {
        if ( array_key_exists( $key , $this->_options ) )   return $this->_options[ $key ] ;
        else                                                return false ;
    }

    protected function getModule()
    {
        return $this->_module ;
    }

    protected function getIdParent()
    {
        return $this->_id_parent ;
    }

    protected function getUriParent()
    {
        return implode( '/' , $this->_id_parent ) ;
    }

    protected function getToken()
    {
        return $this->_token ;
    }

    protected function getLang()
    {
        return $this->_lang ;
    }

	/* ************************************************** */
	/* ******************    ISER    ******************** */
	/* ************************************************** */

	protected function isDepedency()
	{
		return ( $this->getDepedencyModule() !== NULL ? true : false );
	}

    /* ***************************************************** */
    /* ******************   CONTAINER   ******************** */
    /* ***************************************************** */

    public function getRepository(): \App\Kernel\Back\Repository
    {
        return $this->Container()->module( $this->getEntityName() )->getRepository( true ) ;
    }

    /* ************************************************** */
    /* *****************     INIT     ******************* */
    /* ************************************************** */

    protected function initRender()
    {
        $this->_renderArray = [] ;
    }

    public function init()
    {
        $this->appendEntityInfo();
        $this->getRepository()->checkIfPatchTable( $this->getEntityId() );
        $this->initRender();
    }

    /* ************************************************** */
    /* *****************   FUNCTION   ******************* */
    /* ************************************************** */

    public function execute()
    {
        if ( $this->loadEntity() == true )
        {
            $this->init() ;

            if ( $this->isValidAction() == true )
            {
            	$method = $this->getMethodName() ;
                $this->$method();
            }
        }
        else
        {
            $this->Factory()->Response()->error("Impopssible de charger l'entity '" . $this->getEntityName() . "'") ;
        }
    }

    /* ************************************************** */
    /* ******************   PARENT   ******************** */
    /* ************************************************** */

    protected function getParent()
    {
        $target = $this->getEntity()->get( $this->getEntity()->getParentTargetName() ) ;
        $alias  = 'titre' ;
        $array  = [] ;

        $content = $this->getRepository()->findAllForSelect( $target , $alias , $this->getEntity()->getParentName() ) ;

        if ( $content ) $array = $this->getTreeParent( $content , $alias ) ;

        return $array;
    }

    protected function getTreeParent( $rows , $alias , $parent_id = -1 , $level = 0 )
    {

        $tree = [];
        foreach( $rows as $key => $row )
        {
            if ( $row->get( $this->getEntity()->getParentName() ) == $parent_id or ( $row->get( $this->getEntity()->getParentName() ) === NULL && $parent_id == -1 ) )
            {
                $obj = new \stdClass();
                $obj->id 		= $row->get('id');
                $obj->$alias	= $row->get( $alias );
                $obj->level	    = $level;
                if ( $row->get('id') == $this->getId() ) $obj->noview = true ;
                unset( $rows[ $key ]);
                $obj->subpages 	= ( $row->get('id') != $this->getId() ? $this->getTreeParent($rows, $alias, $row->get('id'), $level + 1 ) : [] );
                $tree[] = $obj;
            }
        }

        return $tree;
    }

    /* ************************************************** */
    /* ******************   FORMER   ******************** */
    /* ************************************************** */

    protected function getDataView()
    {
        return "back" ;
    }

    protected function getTitleField( $field )
    {
        return $field->getData('title') ;
    }

    protected function generateForm( $value = false )
    {
        $form = parent::generateForm( $value );

        if ( $form === false )
        {
            $this->Factory()->Response()->flashAndRedirect( $this->m("have_no_content") ) ;
        }

        $this->setRender( 'cdn_css' , $form['cdn_css'] ) ;
        $this->setRender( 'cdn_js' , $form['cdn_js'] ) ;

        $this->setRender( 'css' , $form['css'] ) ;
		$this->setRender( 'js' , $form['js'] ) ;

        $this->setRender( 'tabs' , $form['tabs'] ) ;
        $this->setRender( 'condition' , $form['condition'] ) ;

        $this->setRender( 'form' , $this->renderForm([
            'field' => $form['field'],
            'tabs' => $form['tabs'],
            'onglet' => $_GET['o'],
            'condition' => $form['condition'],
            'uri_id_parent' => $this->getUriParent(),
            'route' => $this->Factory()->Url()->route( $this->getEntityName() , ( $value == false ? 'add' : 'edit' ) , $this->getUriParent() , $form['id'] ),
            'id' => $form['id']
        ])) ;
    }

    // Pour les checkbox dans le même module (systeme de table d'association)
    protected function getAssocValue( $module , $nameField , $id , $modAssoc )
    {
        $content = \DB::for_module_assoc( $this->getEntityName() , $nameField )
            ->select( \DB::getTableNameAssocValue( $this->getEntityName() , $nameField ) )
            ->where_equal( \DB::getTableNameAssoc( $this->getEntityName() , $nameField ) . '_' . \DB::getIdName( $this->getEntityName() ) , $this->getId() )
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

    /* ************************************************** */
    /* ************   TABLEAU DE GESTION   ************** */
    /* ************************************************** */

    protected function getIndexField()
    {
        $tab = [];
        //$tab[ $this->getEntity()->getIdName() ] = $this->getEntity()->getIdName();
        //if ( $this->getEntity()->getValidationName() != '' ) $tab[ $this->getEntity()->getValidationName() ] = $this->getEntity()->getValidationName();

        $rst = \DB::for_table('module_table')
            ->where_equal('module_table_module_id' , $this->getEntityId() )
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->module_table_field ] = $row->module_table_field ;
            }
        }

        return $tab ;
    }

    protected function filterTable( $content )
    {
        return $content ;
    }

    protected function filterContent( $content )
    {
        return $content ;
    }

    protected function generateTable()
    {
        $rightArray     = [] ;
        $thArray 	    = [] ;
        $tdArray 	    = [] ;
        $typeArray      = [] ;
        $indexTable     = $this->getIndexField() ;

        $elmtPerPage = ( $this->getApp()->request->get('elmt_per_page') != '' ? $this->getApp()->request->get('elmt_per_page') : 25 ) ;
        $page        = ( $this->getApp()->request->get('page') != '' ? $this->getApp()->request->get('page') : 1 ) ;
        $order       = ( $this->getApp()->request->get('order') != '' ? $this->getApp()->request->get('order') : NULL ) ;
        $by          = ( $this->getApp()->request->get('by') != '' ? $this->getApp()->request->get('by') : NULL ) ;

        if ( $elmtPerPage == 'all' ) $elmtPerPage = 0;

        $offset      = $elmtPerPage * ( $page - 1 );

        if ( $order === NULL && $by === NULL )
        {
            if ( $this->getEntity()->hasOrder() )
            {
                $order = $this->getEntity()->getOrderName() ;
                $by    = 'asc' ;
            }
            else
            {
                $order = $this->getEntity()->getIdName() ;
                $by    = 'desc' ;
            }
        } 

        $Guard = new \App\Kernel\Back\Acl;
        $Guard->setModule( $this->getEntityName() );
        $Guard->load();

        $rightArray['add']   		= $Guard->checkAdd() ;
        $rightArray['edit']  		= $Guard->checkUpdate() ;
        $rightArray['delete'] 		= $Guard->checkDelete() ;
        $rightArray['validation']  	= $Guard->checkValidation() ;
        $rightArray['config'] 		= $Guard->checkConfig() ;

        if ( !empty( $this->getEntity()->getField() ) )
        {
            // TH
            foreach( $this->getEntity()->getField() as $field )
            {
                if ( in_array( $field->getName() , $indexTable ) )
                {
                    $arrayDate = [];

                    if ( $field->getType() == 'date' )
                    {
                        $value_convert_start = NULL;
                        $value_convert_end   = NULL;

                        $valueSearch = $this->getApp()->request->get( $field->getName() ) ;
                        if ( ! empty( $valueSearch ) )
                        {
                            list( $start , $end ) = explode( ' - ' , $valueSearch );

                            $value_convert_start = $this->Factory()->Date()->convertUs( $start );
                            $value_convert_end   = $this->Factory()->Date()->convertUs( $end );
                        }

                        $arrayDate = [
                            'value_convert_start' => $value_convert_start,
                            'value_convert_end' => $value_convert_end,
                        ];
                    }

                    $option = NULL;
                    if ( $field->isAssociated() )
                    {
                        $option = $this->getValueAssociated( $field , "array" , true );
                    }
                    else if ( $field->hasOption() )
                    {
                        $option = $field->getOptions() ;
                    }

                    $thArray[ $field->getName() ] = array_merge([
                        'name' => $field->getName(),
                        'title' => $field->getTitle(),
                        'search' => ( is_callable( $field->getData('updateValue') ) ? false : true ),
                        'value' => $this->getApp()->request->get( $field->getName() ),
                        'value_start' => $this->getApp()->request->get( $field->getName() . "_start" ),
                        'value_end' => $this->getApp()->request->get( $field->getName() . "_end" ),
                        'type' => $field->getType(),
                        'boolean' => $field->getData('isBoolean'),
                        'options' => $option
                    ], $arrayDate);

                    if ( $field->getType() == "select" && $field->isAssociated() == true )
                    {

                        $option = $this->getValueAssociated( $field , 'array' );
                        $this->getEntity()->get( $field->getName() )->setData('option',$option);
                    }
                }
            }

            $count = $this->getRepository()->countTableIndex( $order , $by , $thArray , ( $this->getEntity()->isChild() ? end( $this->getIdParent() ) : NULL ) , $this->getDepedencyModule() , $this->getDepedencyElement() ) ;

            if ( $count < $offset )
            {
                $page = 1;
                $offset = 0;
            }

            $content = $this->getRepository()->getAllTableIndex( $order , $by , $thArray , ( $this->getEntity()->isChild() ? end( $this->getIdParent() ) : NULL ) , $offset , $elmtPerPage , $this->getDepedencyModule() , $this->getDepedencyElement() ) ;

            if ( $content )
            {
                $content = $this->filterTable( $content );

                $paginator = new Paginator($count, $elmtPerPage, $page, '(:num)');
                $paginator->setNextText('Suivant');
                $paginator->setPreviousText('Précédent');
                $paginator->setMaxPagesToShow(5);

                $this->setRender( 'paginator' , $paginator->getPages() ) ;

                // TD
                $i = 0;
                foreach( $content as $row )
                {
                    if ( ! empty( $this->getEntity()->getIcon() ) or is_callable( $field->getData('updateValue') ) or is_callable( $field->getData('javascript') ) or is_callable( $field->getData('style') ) or is_callable( $this->getEntity()->getShowDelete() ) )
                    {
                        $contentShow = new \stdClass;
                        foreach( $this->getEntity()->getField() as $f )
                        {
                            if ( ( ( $f->isParent() == true && $f->hasOption() == true ) or $f->isParent() != true ) && $f->getType() !== NULL )
                            {
                                $name = $f->getName() ;
                                $contentShow->$name = $row->get( $f->getColumn() );
                            }
                        }
                        $contentShow = $this->filterContent( $contentShow );
                    }

                    foreach( $this->getEntity()->getField() as $field )
                    {
                        if ( in_array( $field->getName() , $indexTable ) )
                        {
                            $typeField = $field->getType() ;

                            if ( $field->getType() == "select" && $field->getData('option') !== NULL )
                            {
                                $opt = $field->getData('option') ;

                                if ( is_array( current( $opt ) ) )
                                {
                                    foreach( $opt as $key => $valueArray )
                                    {
                                        if ( isset( $valueArray[ $row->get( $field->getColumn() ) ] ) )
                                        {
                                            $value = $valueArray[ $row->get( $field->getColumn() ) ] ;
                                        }
                                    }
                                }
                                else
                                {
                                    $value = $opt[ $row->get( $field->getColumn() ) ] ;
                                }
                            }
                            else if ( $field->getType() == "radio" && $field->getData('isBoolean') == true )
                            {
                                $typeField = 'boolean' ;
                                $value = $row->get( $field->getColumn() ) ;
                            }
                            else if ( $field->getType() == "date" )
                            {
                                if ( $row->get( $field->getColumn() ) === NULL )
                                {
                                    $value = "-" ;
                                }
                                else
                                {
                                    $date = new \DateTime( $row->get( $field->getColumn() ) );
                                    if ( $field->getData('hour') == true )  $value = $date->format('d/m/Y - H\hi') ;
                                    else                                    $value = $date->format('d/m/Y') ;
                                }
                            }
                            else if ( $field->getType() == "image" )
                            {
                                $Media = new \App\Kernel\Back\Media;
                                $Media->setModuleId( $this->getEntityId() ) ;
                                $Media->setImageId( $row->get( $field->getColumn() ) );
                                $Media->getNameById();

                                $img = str_replace( WEB_PATH , '' , IMAGE_PATH ) . '/' . $this->getEntity()->getFolder() . "/" . $Media->getMini( $Media->getImageName() , 't' , 100 , 100 ) ;
                                $value = $this->Factory()->Url()->get( $img , true ) ;
                            }
                            else
                            {
                                $value = '' ;
                                if ( $field->getData('unit') !== NULL && $field->getData('whereUnit') == "before" ) $value.= $field->getData('unit') . ' ' ;
                                $value.= $row->get( $field->getColumn() ) ;
                                if ( $field->getData('unit') !== NULL && $field->getData('whereUnit') == "after" ) $value.= ' ' . $field->getData('unit') ;
                            }

                            /* *************************************************** */
                            /* *************************************************** */
                            /*                       STYLE                         */
                            /* *************************************************** */
                            /* *************************************************** */

                            $style    = '' ;
                            $callableStyle = $field->getData('style') ;

                            if ( is_callable( $callableStyle ) )
                            {
                                $style = $callableStyle( $contentShow );
                            }

                            /* *************************************************** */
                            /* *************************************************** */
                            /*                      JAVSCRIPTS                     */
                            /* *************************************************** */
                            /* *************************************************** */

                            $js = '' ;
                            $callableJavascript = $field->getData('javascript') ;

                            if ( is_callable( $callableJavascript ) )
                            {
                                $js = $callableJavascript( $contentShow );
                            }

                            /* *************************************************** */
                            /* *************************************************** */
                            /*                    UPDATE VALUE                     */
                            /* *************************************************** */
                            /* *************************************************** */

                            $callableValue = $field->getData('updateValue') ;

                            if ( is_callable( $callableValue ) )
                            {
                                $value = $callableValue( $contentShow );
                            }

                            /* *************************************************** */
                            /* *************************************************** */
                            /*                     SET VALUE                       */
                            /* *************************************************** */
                            /* *************************************************** */

                            $typeArray[ $field->getName() ] = $field->getType() ;
                            $tdArray[ $i ]['td'][ $field->getName() ] = [
                                'style' => $style,
                                'javascript' => $js,
                                'value' => $value,
                                'module' => $field->getData('object'),
                                'subtype' => $field->getData('subtype'),
                                'id' => $row->get( $field->getColumn() ),
                                'type' => $typeField
                            ];
                        }
                    }

                    $tdArray[ $i ]['id'] = $row->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ;

                    /* *************************************************** */
                    /* *************************************************** */
                    /*                       DELETE                        */
                    /* *************************************************** */
                    /* *************************************************** */

                    $delete = true ;
                    $callableDelete = $this->getEntity()->getShowDelete() ;

                    if ( is_callable( $callableDelete ) )
                    {
                        $delete = $callableDelete( $contentShow );
                    }

                    $tdArray[ $i ]['delete'] = $delete ;

                    /* *************************************************** */
                    /* *************************************************** */
                    /*                       ICONS                         */
                    /* *************************************************** */
                    /* *************************************************** */

                    $tabIcon = [];
                    if ( ! empty( $this->getEntity()->getIcon() ) )
                    {
                        foreach( $this->getEntity()->getIcon() as $icon )
                        {
                            if ( is_callable( $icon['showIF'] ) )
                            {
                                $function = $icon['showIF'];
                                if ( $function( $contentShow ) == true )
                                {
                                    $tabIcon[] = $icon ;
                                }
                            }
                            else
                            {
                                $tabIcon[] = $icon ;
                            }
                        }
                    }

                    $tdArray[ $i ]['icons'] = $tabIcon ;
                    if ( $this->getEntity()->hasValidation() )
                    {
                        $fieldValidation = $this->getEntity()->get( $this->getEntity()->getValidationName() ) ;
                        $callableValue = $fieldValidation->getData('updateValue') ;

                        if ( is_callable( $callableValue ) )
                        {
                            $value = $callableValue( $row );
                        }
                        else
                        {
                            $value = $row->get( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() ) ;
                        }

                        $tdArray[ $i ]['validation'] = $value ;
                    }

                    if ( $this->getEntity()->hasParent() ) 		$tdArray[ $i ][ $this->getEntity()->getParentName() ] = $row->get( $this->getEntity()->get( $this->getEntity()->getParentName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasOrder() ) 		$tdArray[ $i ]['order'] = $row->get( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasDefault() ) 	$tdArray[ $i ]['default'] = $row->get( $this->getEntity()->get( $this->getEntity()->getDefaultName() )->getColumn() ) ;
                    if ( $this->getEntity()->hasUrl() )
                    {
                        if ( ! $this->isMain() )
                        {
                            $this->loadModuleUrl();
                        }

                        $Seo = new \App\Kernel\Front\Seo;
                        $Seo->setElementId( $row->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;
                        $Seo->setModuleId( $this->getEntityId() ) ;
                        $tdArray[ $i ]['url'] = \App\Kernel\Http::getInstance()->getUrl() . '/' ;

                        if ( $this->Lang()->count() > 1 )
                        {
                            $tdArray[ $i ]['url'].= \App\Kernel\Lang::getInstance()->getActive()->url . "/" ;
                        }

                        $tdArray[ $i ]['url'].= $this->getModuleUrl() . $Seo->getUrl() ;
                    }

                    $i++;
                }
            }
        }

        if ( $this->getEntity()->hasParent() )
        {
            $tdArray = $this->getTreeTableParent( $tdArray ) ;
        }

        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;
        $this->setRender( 'depedency' , $this->isDepedency() ) ;
        $this->setRender( 'dModule' , $this->getDepedencyModule() ) ;
        $this->setRender( 'dElement' , $this->getDepedencyElement() ) ;
        $this->setRender( 'page' , $page ) ;
        $this->setRender( 'right' , $rightArray ) ;
        $this->setRender( 'hasOrder' , $this->getEntity()->hasOrder() ) ;
        $this->setRender( 'hasValidation' , $this->getEntity()->hasValidation() ) ;
        $this->setRender( 'hasDefault' , $this->getEntity()->hasDefault() ) ;
        $this->setRender( 'validationName' , $this->getEntity()->getValidationName() ) ;
        $this->setRender( 'hasURL' , $this->getEntity()->hasURL() ) ;
        $this->setRender( 'th' , $thArray ) ;
        $this->setRender( 'td' , $tdArray ) ;
        $this->setRender( 'order' , $order ) ;
        $this->setRender( 'by' , $by ) ;
        $this->setRender( 'type' , $typeArray ) ;
        $this->setRender( 'search' , $this->getApp()->request->get('search') == 1 ? 1 : 0 ) ;
        $this->setRender( 'elmt_per_page' , ( $elmtPerPage == 0 ? 'all' : $elmtPerPage ) ) ;
    }

    protected function getTreeTableParent( $rows, $parent_id = -1 )
    {
        $tree = [];

        foreach ( $rows as $key => $row )
        {
            if ( $row[ $this->getEntity()->getParentName() ] == $parent_id or ( $row[ $this->getEntity()->getParentName() ] === NULL && $parent_id == -1 ) )
            {
                $array = $row;
                unset( $rows[ $key ]);
                $array['subpages'] = $this->getTreeTableParent($rows, $row['id']);
                $tree[] = $array;
            }
        }

        return $tree;
    }

    protected function getGlobalVar()
    {
        $this->setRender( 'libimage' , ( empty( $this->getEntity()->getImageField() ) ? false : true ) ) ;
    }

    /* ************************************************** */
    /* ******************  FUNCTIONS  ******************* */
    /* ************************************************** */

    protected function appendEntityInfo()
    {
        $rst = \DB::for_table('module')
            ->select('module_name')
            ->select('module_icon')
            ->select('module_id')
            ->where(array('module_class_name' => $this->getEntityName() , 'module_active' => 1))
            ->find_one();

        if ( $this->getOption('noAppend') == false )
        {
            $this->getApp()->view()->appendData([
                'mod' => [
                    'id'    	    => $this->getEntityId(),
                    'name'  	    => $this->getEntityName(),
                    'title' 	    => $rst->module_name,
                    'icon'  	    => $rst->module_icon,
					'canCreate'     => $this->canCreate(),
					'canImport'     => $this->getEntity()->canImport(),
					'canDelete'     => $this->getEntity()->canDelete(),
					'canUpdate'     => $this->getEntity()->canUpdate(),
					'canDuplicate'  => $this->getEntity()->canDuplicate()
				],
                'action'    => $this->getActionName(),
                'fields'    => $this->getImportFiled(),
                'depedency' => $this->isDepedency()
			]);
        }

        $this->setEntityId( $rst->module_id ) ;
    }

    public function getImportFiled()
    {
        $alphas   = range('A', 'Z');
        $tabField = [];
        $i        = 0;

        $fieldActive = $this->getIndexField() ;

        foreach( $this->getEntity()->getField() as $row )
        {
            if ( $row->getTitle() != '' && $row->getData('noindex') !== true )
            {
                $tabField[ $row->getName() ] = [
                    'table' => ( $row->getName() != $this->getEntity()->getValidationName() && $row->back() == true ? true : false ),
                    'letter' => $alphas[$i],
                    'title'  => $row->getTitle(),
                    'active'  => ( in_array( $row->getName() , $fieldActive ) ? true : false )
                ];
                $i++;
            }
        }

        return $tabField ;
    }

    protected function checkToken()
    {
        if ( $this->Factory()->Token()->check( $this->getToken() ) == false )
        {
            $this->Factory()->Response()->returnJSON( $this->m("token_is_bad") ) ;
        }
    }

    /* Fonction appelée par "add" & "update" */
    protected function pushData( $add = true , $noCheck = false )
    {
		if ( $add ) $hookBeforeCheck = 'hookAddCheckBefore' ;
        else        $hookBeforeCheck = 'hookUpdateCheckBefore' ;

        $result = $this->$hookBeforeCheck();

        if ( $result === true )
        {
            $result = [
                'msg' => '',
                'url' => '',
                'result' => false
            ];

            if ( $noCheck ) $check = true ;
            else			$check = $this->checkForm() ;

            if ( $check )
            {
                if ( $add ) $hookAfterCheck = 'hookAddCheckAfter' ;
                else        $hookAfterCheck = 'hookUpdateCheckAfter' ;

                $resultHook = $this->$hookAfterCheck();

                if ( $resultHook === true )
                {
                    if ( !empty( $this->getEntity()->getField() ) )
                    {
                        if ( $this->getId() !== NULL )
                        {
                            $content = $this->getRepository()->findOne( $this->getId() );
                            if ( ! $content )
                            {
                                $this->Factory()->Response()->flashAndRedirect( $this->m("have_no_content") ) ;
                            }
                        }
                        else
                        {
                            $content = $this->getRepository()->create();
                        }

                        if ( $this->getEntity()->hasMultilang() )
                        {
                            foreach( $this->Lang()->getAll() as $lang )
                            {
                                if ( $this->getId() !== NULL )
                                {
                                    $contentLang[ $lang->url ] = \DB::for_module_lang( $this->getEntityName() , $this->getId() , $lang->id )->find_one();

                                    if ( ! $contentLang[ $lang->url ] )
                                    {
                                        $contentLang[ $lang->url ] = $this->getRepository()->createLang();
                                        $contentLang[ $lang->url ]->set( \DB::getLangIdLangName( $this->getEntityName() ) , $lang->id ) ;
                                    }
                                }
                                else
                                {
                                    $contentLang[ $lang->url ] = $this->getRepository()->createLang();
                                    $contentLang[ $lang->url ]->set( \DB::getLangIdLangName( $this->getEntityName() ) , $lang->id ) ;
                                }
                            }
                        }

                        foreach( $this->getEntity()->getField() as $row )
                        {
                            if ( $row->back() !== false && $row->save() !== false )
                            {
                                if ( ! $row->hasLang() )
                                {
                                    if ( $row->isOrder() == true && $add == true )
                                    {
                                        $content->set( $row->getColumn() , $row->getDefault() ) ;
                                    }
                                    else if ( $row->getType() != "checkbox" && $row->canUpdate() == true && $row->isOrder() == false )
                                    {
                                        $content->set( $row->getColumn() , $row->getValue() ) ;
                                    }
                                }
                                else
                                {
                                    foreach( $this->Lang()->getAll() as $lang )
                                    {
                                        $contentLang[ $lang->url ]->set( $row->getColumn() , $row->getValue( $lang->url ) ) ;
                                    }
                                }
                            }
                        }

                        $date = new \DateTime();

                        if ( $add == true )
                        {
                            $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
                            $content->set( $this->getEntity()->get('date_created')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
                        }
                        else
                        {
                            $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $content->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
                        }
                        $content->set( $this->getEntity()->get('date_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;

                        if ( $this->getEntity()->hasDefault() )
                        {
                            if ( $this->getEntity()->isChild() )
                            {
                                $ct = $this->getRepository()->countWithParent( end( $this->getIdParent() ) );
                            }
                            else
                            {
                                $ct = $this->getRepository()->count();
                            }

                            if ( $ct == 0 )
                            {
                                $content->set( $this->getEntity()->get('default')->getColumn() , 1 ) ;
                            }
                        }

                        // On ajoute les infos sans multi-langue
                        $content->save() ;

                        if ( $this->getId() === NULL ) $this->setId( $content->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;

                        foreach( $this->getEntity()->getField() as $nameField => $field )
                        {
                            if ( $field->getType() == "image" && $field->getData('hasAltText') == true && $noCheck == false )
                            {
                                foreach( $this->Lang()->getAll() as $lang )
                                {
                                    $Alt = new \App\Kernel\Back\Alt;
                                    $Alt->setElementId( $this->getId() );
                                    $Alt->setModuleId( $this->getEntityId() );
                                    $Alt->setFieldName( $field->getName() );
                                    $Alt->setLangId( $lang->id );
                                    $Alt->setValue( $this->getApp()->request->post('alt_' . $field->getColumn() . '_' . $lang->flag ) );
                                    $Alt->update();
                                }
                            }
                            else if ( $field->getType() == "checkbox" )
                            {
                                $this->getRepository()->pushDataAssoc( $nameField , $field , $this->getId() ) ;
                            }
                            else if ( $field->getType() == "gallery" && $add == true )
                            {
                                // On met a jour les -1
                                $Gallery = new \App\Kernel\Back\Gallery;
                                $Gallery->setElementId( $this->getId() );
                                $Gallery->setField( $field->getName() );
                                $Gallery->setModuleId( $this->getEntityId() );
                                $Gallery->updateZero();
                            }
                            else if ( $field->getType() == "document" )
                            {
                                $post = $this->getApp()->request->post($field->getColumn() . '_alt' ) ;

                                if ( !empty( $post ) )
                                {
                                    foreach( $post as $id_document => $text )
                                    {
                                        $Doc = new \App\Kernel\Back\Document;
                                        $Doc->setDocumentId( $id_document );
                                        $Doc->setAltText( ( empty( $text ) ? NULL : $text ) );
                                        $Doc->updateAlt();
                                    }
                                }
                            }
                        }

                        if ( $this->getEntity()->hasUrl() && $add == true )
                        {
                            if ( $this->getEntity()->hasMultilang() )
                            {
                                $field = $this->getEntity()->build( $this->getEntity()->getUrlName() )->field() ;

                                foreach( $this->Lang()->getAll() as $lang )
                                {
                                    $seo = new \App\Kernel\Back\Seo;
                                    $seo->setElementId( $this->getId() );
                                    $seo->setModuleId( $this->getEntityId() );
                                    $seo->setTitle( $field->getValue( $lang->url ) );
                                    $seo->setLangId( $lang->id );
                                    $seo->save();
                                }
                            }
                            else
                            {
                                $seo = new \App\Kernel\Back\Seo;
                                $seo->setElementId( $this->getId() );
                                $seo->setModuleId( $this->getEntityId() );
                                $seo->setTitle( $this->getEntity()->build( $this->getEntity()->getUrlName() )->field()->getValue() );
                                $seo->setLangId( $this->Lang()->getDefault()->id );
                                $seo->save();
                            }
                        }
                    }

                    if ( $this->getEntity()->hasMultilang() )
					{
						// On ajoute les infos avec multi-langue
						foreach( $this->Lang()->getAll() as $lang )
						{
							$contentLang[ $lang->url ]->set( \DB::getIdNameInLang( $this->getEntityName() ) , $this->getId() ) ;
							$contentLang[ $lang->url ]->save() ;
						}
					}

                    if ( $this->getApp()->request->post('buttonaction') == "stay" )
                    {
                        $result['url'] = $this->Factory()->Url()->route( $this->getEntityName() , 'edit' , $this->getUriParent() , $this->getId() ) ;
                    }
                    else
                    {
                        $result['url'] = $this->Factory()->Url()->route( $this->getEntityName() , 'index' , $this->getUriParent() ) ;
                    }

                    if ( $add ) $result['msg'] = $this->m("add_success") ;
                    else		$result['msg'] = $this->m("edit_success") ;

                    $result['result'] = true;

                    $this->Log()->info( ( $add ? 100 : 101 ) , "#" . $this->getId() . " - " . $this->getEntityName() , $this->getEntityId() , $this->getId() ) ;

                    if ( $add ) $hookAfterCheck = 'hookAddSaveAfter' ;
                    else        $hookAfterCheck = 'hookUpdateSaveAfter' ;

                    $this->$hookAfterCheck( $content );
                }
                else
                {
                    $result = $resultHook ;
                }
            }
            else
            {
                $tab   = [];
                $first = true;
                if ( !empty( $this->getEntity()->getField() ) )
                {
                    foreach( $this->getEntity()->getField() as $row )
                    {
                        if ( $row->getError() != '' )
                        {
                            $tab[] = [
                                'field' => $row->getName(),
                                'error' => $row->getError()
                            ];

                            if ( $first )
                            {
                                $result['msg']   = $row->getError() ;
                                $result['tab']   = $row->getTab() ;
                                $result['field'] = $row->getName() ;
                                $first = false ;

                                if ( $this->getEntity()->itsDepedency() )
                                {
                                    $result['tab'] = strtolower( $this->getEntityName() ) ;
                                }
                            }
                        }
                    }
                }

                $result['fields'] = $tab ;
            }
        }

        return $result ;
    }

    /* ************************************************** */
    /* ******************  DEPEDENCY  ****************** */
    /* ************************************************** */

    protected function loadDepedencies()
    {
        if ( $this->getEntity()->hasDependency() )
        {
            foreach( $this->getEntity()->getDependency() as $depedency )
            {
                $depedencies[] = [
                    'name'   => $depedency['name'],
                    'class'  => $depedency['class'],
                    'slug'   => strtolower( $depedency['class'] ),
                    'icon'   => $depedency['icon'],
                    'fields' => $this->Container()->module( $depedency['class'] )->getController(true)->getImportFiled(),
                    'max'    => $this->Container()->module( $depedency['class'] )->getEntity()->getMaxElement(),
                    'delete' => $this->Container()->module( $depedency['class'] )->getEntity()->canDelete()
                ];
            }
        }

        $this->setRender( 'depedencies' , $depedencies ) ;
    }

    /* ************************************************** */
    /* ******************  UP / DOWN  ******************* */
    /* ************************************************** */

    protected function order()
    {
        if ( $this->getEntity()->hasOrder() )
        {
            $table = $this->getApp()->request()->post('table-' . $this->getEntityName() );
            if ( $table )
            {
                $position = $this->getRepository()->minPosition( $table ) ;
                foreach( $table as $row )
                {
                    $elmt = \DB::find( $this->getEntityName() , $row );
                    $elmt->set( $this->getEntity()->get( $this->getEntity()->getOrderName() )->getColumn() , $position );
                    $elmt->save();
                    $position++;
                }
            }

            $this->Log()->info( 105 , $this->getEntityName() , $this->getEntityId() ) ;

            return true;
        }
        else
        {
            return false ;
        }
    }

    /* ************************************************** */
    /* *****************  VALIDATION  ******************* */
    /* ************************************************** */

    protected function updatePublication( $value )
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( ! $content ) return false ;

        $date = new \DateTime();
        $content->set( $this->getEntity()->get('date_last_updated')->getColumn() , $content->get( $this->getEntity()->get('date_updated')->getColumn() ) ) ;
        $content->set( $this->getEntity()->get('date_updated')->getColumn() , $date->format('Y-m-d H:i:s') ) ;
        $content->set( $this->getEntity()->get( $this->getEntity()->getValidationName() )->getColumn() , $value ) ;
        $content->save();

        $this->Log()->info( ( $value == 0 ? 104 : 103 ) , "#" . $this->getId() . " - " . $this->getEntityName() , $this->getEntityId() , $this->getId() ) ;

        if ( $value == 0 )
        {
            $this->hookDisableAfter() ;
        }
        else
        {
            $this->hookEnableAfter() ;
        }

        return true ;
    }

    /* ************************************************** */
    /* ***************   MODULE PARENT   **************** */
    /* ************************************************** */

    public function getParentContent()
    {
        if ( empty( $this->getEntity()->getFieldReference() ) ) return '' ;

        $content = $this->getRepository()->getParentContent( ( $this->getEntity()->isChild() ? end( $this->getIdParent() ) : NULL ) );
        $tab     = [];
        if ( $content )
        {
            foreach( $content as $row )
            {
                $std = new \stdClass;
                $std->id        = $row->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() );
                $std->counter   = $this->Container()->module( $this->getChild() )->getRepository(true)->countWithParent( $std->id );
                $std->name      = '' ;

                $i = 0;
                foreach( $this->getEntity()->getFieldReference() as $ref )
                {
                    $std->name.= ( $i > 0 ? " " : "" ) . $row->get( $this->getEntity()->get( $ref )->getColumn() );
                    $i++;
                }

                if ( $this->getEntity()->hasImage() )
                {
                    $media = new Media;
                    $media->setImageId( $row->get( $this->getEntity()->get( $this->getEntity()->getFirstImageName() )->getColumn() ) );
                    $media->getNameById();
                    $std->image = $this->getEntity()->getFolder() . '/' . $media->getMini( $media->getImageName() , 't' , 260 , 130 ) ;
                    $std->image = $this->Factory()->Url()->image( $std->image , true ) ;
                }

                $tab[] = $std ;
            }
        }

        $this->setRender( 'hasImage' , $this->getEntity()->hasImage() ) ;
        $this->setRender( 'canCreate' , $this->canCreate() ) ;
        $this->setRender( 'content' , $tab ) ;
        $this->setRender( 'module' , $this->getModule() ) ;
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        $table = $this->fetch( 'index.parent.twig' );
        return $table;
    }

    protected function getParentArray()
    {
        if ( ! $this->getEntity()->isChild() ) return [] ;

        $tab = [] ;
        $remontada = true ;
        $parent = $this->getEntity()->getModuleParentName() ;
        $tab[] = $parent ;
        while( $remontada )
        {
            $Entity = \App\Kernel\Container::getInstance()->module( $parent )->getEntity();
            if ( $Entity->isChild() )
            {
                $parent = $Entity->getModuleParentName();
                $tab[] = $parent ;
            }
            else
            {
                $remontada = false ;
            }
        }

        if ( $tab )
        {
            $idParent = $this->getIdParent() ;
            $newTab = [];
            $idParentSave = [] ;
            $ct = count( $tab ) - 1;
            $j = 0;
            $current = false ;
            $first = false ;
            for( $i = $ct; $i >= 0; $i-- )
            {
                $module = \DB::for_table('module')
                    ->select('module_icon')
                    ->select('module_name')
                    ->where(array('module_class_name' => $tab[ $i ] , 'module_active' => 1))
                    ->find_one();

                $newTab[ $j ] = [
                    'name' => $tab[ $i ],
                    'id' => ( array_key_exists( $j , $idParent ) ? $idParent[ $j ] : NULL ),
                    'current' => false,
                    'url' => "",
                    'human_name' => $module->module_name,
                    'icon' => $module->module_icon
                ];

                if ( $first == true )
                {
                    $idParentSave[ $j - 1 ] = $idParent[ $j - 1 ] ;
                    $newTab[ $j ]['url'] = implode( '/' , $idParentSave ) ;
                }

                if ( ! array_key_exists( $j , $idParent ) && $current == false )
                {
                    $newTab[ $j ]['current'] = true ;
                    $newTab[ $j ]['url'] = $this->getUriParent() ;

                    $current = true ;
                    $currentModule = $tab[ $i ] ;
                }

                if ( $first == false && ( count( $tab ) == count( $this->getIdParent() ) or $newTab[ $j ]['current'] == true ) )
                {
                    $first = true ;
                }

                $j++;
            }
        }

        return [
            'last' => $parent,
            'current' => $currentModule,
            'count' => count( $newTab ),
            'array' => $newTab
        ];
    }

    protected function viewParent()
    {
        $arrayParent = $this->getParentArray();

        $Controller = \App\Kernel\Container::getInstance()->module( $arrayParent['current'] )->getController( true );
        $Controller->setIdParent( $this->getIdParent() ) ;
        $Controller->setChild( $this->getEntityName() ) ;
        $parentContent = $Controller->getParentContent();

        $this->setRender( 'parent' , $parentContent ) ;
        $this->setRender( 'parentLine' , $arrayParent ) ;
        $this->render('parent.twig');
    }

    /* ************************************************************** */
    /* ******************   CUSTOMIZATION TABLE   ******************* */
    /* ************************************************************** */

    protected function updateCustomization()
    {
        if ( $this->getApp()->request->post('active') == 1 )
        {
            $table = \DB::for_table('module_table')->create();
            $table->module_table_module_id = $this->getEntityId();
            $table->module_table_field = $this->getApp()->request->post('field');

            $table->save();
        }
        else
        {
            $table = \DB::for_table('module_table')
                ->where_equal('module_table_module_id' , $this->getEntityId() )
                ->where_equal('module_table_field' , $this->getApp()->request->post('field') )
                ->find_one();

            $table->delete();
        }
    }

    /* ************************************************** */
    /* ******************   ACTIONS   ******************* */
    /* ************************************************** */

    protected function indexAction()
    {
        $counter = 0 ;
        $arrayParent = $this->getParentArray();
        $this->setRender( 'isIndex' , true ) ;
        $this->getGlobalVar() ;

        if ( $this->getEntity()->isChild() )
        {
            $counter = $arrayParent['count'] ;
        }

        if ( $this->getEntity()->isChild() && $counter > count( $this->getIdParent() ) )
        {
            if ( count( $this->getIdParent() ) < $arrayParent['count'] )
            {
                $this->viewParent() ;
            }
            else
            {
                $this->generateTable() ;
                if ( $this->getEntity()->hasParent() )  $template = 'table_parent' ;
                else                                    $template = 'table' ;

                $this->setRender( 'parentLine' , $arrayParent ) ;
                $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

                $table = $this->fetch( $template . '.twig' );

                $this->setRender( 'table' , $table ) ;
                $this->render('index.twig');
            }
        }
        else
        {
            $count = $this->getRepository()->count() ;

            if ( $this->getEntity()->getMaxElement() == 1 && $this->getEntity()->canDelete() == false && $count == 1 )
            {
                $first = $this->getRepository()->first();
                $url = $this->Factory()->Url()->route( $this->getEntityName() , 'edit' , $this->getUriParent() , $first->get( $this->getEntity()->get( $this->getEntity()->getIdName() )->getColumn() ) ) ;
                $this->Factory()->Response()->redirect( $url );
            }
            else if ( $count == 0 && $this->getEntity()->canCreate() == true )
            {
                $url = $this->Factory()->Url()->route( $this->getEntityName() , 'add' , $this->getUriParent() ) ;
                $this->Factory()->Response()->redirect( $url );
            }

            $this->generateTable() ;
            if ( $this->getEntity()->hasParent() )  $template = 'table_parent' ;
            else                                    $template = 'table' ;

            $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;
            $this->setRender( 'parentLine' , $arrayParent ) ;
            $this->setRender( 'isChild' , $this->getEntity()->isChild() ) ;

            $table = $this->fetch( $template . '.twig' );

            $this->setRender( 'table' , $table ) ;
            $this->render('index.twig');
        }
    }

    /* ************************************************** */
    /* ******************  DUPLICATE  ******************* */
    /* ************************************************** */

    protected function duplicateAction()
    {
        $content = $this->getRepository()->findOne( $this->getId() );

        if ( ! $content )
        {
            $rst = [
                'result' => false,
                'msg' => "Le contenu n'est plus disponible",
            ];
        }
        else
        {
            $data = new Data( $this->getEntityName() );
            $data->create();
            foreach( $this->getEntity()->getField() as $row )
            {
                if ( $row->hasLang() == false && $row->getType() != "checkbox" && $row->canUpdate() == true && $row->isOrder() == false )
                {
                    $data->set( $row->getName() , $content->get( $row->getColumn() ) );
                }
            }

            if ( $this->getEntity()->hasValidation() )
            {
                $data->set( $this->getEntity()->getValidationName() , 0 );
            }

            if ( $this->getEntity()->hasValidation() )
            {
                $data->set( $this->getEntity()->getValidationName() , 0 );
            }

            if ( ! empty( $this->getEntity()->getFieldReference() ) )
            {
                if ( count( $this->getEntity()->getFieldReference() ) == 1 )
                {
                    $field =  $this->getEntity()->getFieldReference()[0] ;

                    if ( $this->getEntity()->get( $field )->getType() == 'text' )
                    {
                        $data->set( $field , "Copie de " . $content->get( $this->getEntity()->get( $field )->getColumn() ) );
                    }
                }
            }

            $data->save();

            $rst = [
                'result' => true,
                'msg' => "Le contenu a bien été dupliqué",
            ];
        }

        return $this->Factory()->Response()->printJSON( $rst ) ;
    }

    /* ************************************************** */
    /* ******************    TABLE    ******************* */
    /* ************************************************** */

    protected function tableAction()
    {
        $this->getGlobalVar() ;
        $this->generateTable() ;

        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;

        if ( $this->getEntity()->hasParent() )  $template = 'table_parent' ;
        else                                    $template = 'table' ;

        $this->render( $template . '.twig');
    }

    protected function importAction()
    {
        set_time_limit(0);

        if ( $this->getApp()->request->isPost() )
        {
            $import = new Import;
            $import->setFields( $this->getImportFiled() );
            $rst = $import->upload();

			$error    = false ;
			$errorMsg = '' ;

            if ( $rst !== false )
			{
				foreach( $rst as $lineNumber => $arrayValue )
				{
					foreach( $this->getEntity()->getField() as $field )
					{
						$return = $field->checkImport( $arrayValue[ $field->getName() ] );

						if ( $return === false )
						{
							$errorMsg.= "Ligne $lineNumber: " . $field->getError() . "\n";
							$error = true ;
						}
					}
				}

				if ( $error === false )
				{
				    foreach( $rst as $lineNumber => $arrayValue )
					{
                        foreach( $this->getEntity()->getField() as $field )
                        {
                            $this->getEntity()->get( $field->getName() )->parseWithImport( $arrayValue[ $field->getName() ] , $this->getEntityId() , $this->getEntityName() );
                        }

						if ( $this->getEntity()->isChild() )
						{
                            $this->getEntity()->get( $this->getEntity()->getModuleParentIdName() )->parseWithImport( end( $this->getIdParent() ) , $this->getEntityId() , $this->getEntityName() );
						}

						$a = [];
                        foreach( $this->getEntity()->getField() as $field )
                        {
                            $a[ $field->getName() ] = $field->getValue();
                        }

                        $return = $this->pushData(true , true );
                        $this->setId(NULL);

						foreach( $this->getEntity()->getField() as $field )
						{
							$this->getEntity()->get( $field->getName() )->clearValue();
						}
					}
				}
			}

			$std = new \stdClass;
			$std->result 	= ( ! $error );
            $std->message 	= nl2br( $errorMsg );
			$std->id 		= 1;
			$std->key 		= 1;

			$this->Factory()->Response()->printJSON($std);
        }
        else
        {
            $this->getGlobalVar() ;
            $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;
            $this->setRender( 'parentLine' , $this->getParentArray() ) ;

            $this->render( 'import.twig');
        }
    }

    protected function customizationAction()
    {
        if ( $this->getApp()->request->isPost() && $this->getApp()->request->isAjax() )
        {
            $this->updateCustomization() ;
        }
    }

    protected function addAction()
    {
        if ( $this->getApp()->request->isPost() && $this->getApp()->request->isAjax() )
        {
            $rst = $this->pushData() ;

            if ( is_array( $rst ) ) return $this->Factory()->Response()->printJSON( $rst ) ;
            else                    return ;
        }

        $this->getGlobalVar() ;
        $this->loadDepedencies() ;
        $this->generateForm() ;

        $arrayParent = $this->getParentArray();
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;
        $this->setRender( 'parentLine' , $arrayParent ) ;

        $this->render('formulaire.twig') ;
    }

    protected function editAction()
    {
		if ( $this->getEntity()->hasUrl() )
        {
            $seo = new \App\Kernel\Back\Seo;
            $seo->setElementId( $this->getId() );
            $seo->setModuleId( $this->getEntityId() );

            $this->setRender( 'seo' , $this->getEntity()->hasUrl() ) ;
            $this->setRender( 'content' , $seo->getAll() ) ;
            $this->setRender( 'index' , $seo->getIndex() ) ;
        }

        if ( $this->getApp()->request->isPost() && $this->getApp()->request->isAjax() )
        {
			if ( $this->getEntity()->hasUrl() ) $seo->update() ;
            $rst = $this->pushData( false ) ;

            if ( is_array( $rst ) ) return $this->Factory()->Response()->printJSON( $rst ) ;
            else                    return ;
        }

        $this->getGlobalVar() ;
        $this->loadDepedencies() ;
        $this->generateForm( true ) ;

        $arrayParent = $this->getParentArray();

        $this->setRender( 'id' , $this->getId() ) ;
        $this->setRender( 'onglet' , $_GET['o'] ) ;
        $this->setRender( 'lang' , $this->Lang()->getAll() ) ;
        $this->setRender( 'parentLine' , $arrayParent ) ;
        $this->setRender( 'uri_id_parent' , $this->getUriParent() ) ;
        $this->setRender( 'history' , $this->Log()->getElementHistory( $this->getEntityId() , $this->getId() ) ) ;

        $this->render('formulaire.twig') ;
    }

    protected function orderAction()
    {
        $this->checkToken() ;

        if ( $this->order() )	$this->Factory()->Response()->returnJSON( $this->m("order_success") , true ) ;
        else					$this->Factory()->Response()->returnJSON( $this->m("order_failed") ) ;
    }

    protected function enableAction()
    {
        $this->checkToken() ;

        if ( $this->updatePublication(1) )	$this->Factory()->Response()->returnJSON( $this->m("enable_success") , true ) ;
        else								        $this->Factory()->Response()->returnJSON( $this->m("enable_failed") ) ;
    }

    protected function disableAction()
    {
        $this->checkToken() ;

        if ( $this->updatePublication(0) )	$this->Factory()->Response()->returnJSON( $this->m("disable_success") , true ) ;
        else								        $this->Factory()->Response()->returnJSON( $this->m("disable_failed") ) ;
    }

    protected function showDelete( $c )
    {
        return true ;
    }

    protected function deleteAction()
    {
        if ( $this->getApp()->request->isPost() && $this->getApp()->request->isAjax() )
        {
            $this->setToken( $this->getApp()->request->post( $this->getApp()->config('token') ) ) ;
            $this->checkToken() ;
            $rst = $this->delete();
            return $this->Factory()->Response()->printJSON( $rst ) ;
        }

        $this->setRender( 'id' , $this->getId() ) ;
        $this->render('delete.twig') ;
    }

    protected function defaultAction()
    {
        if ( $this->getApp()->request->isPost() && $this->getApp()->request->isAjax() )
        {
            $search = [
                'default' => 1
            ];

            if ( !empty( $this->getIdParent() ) )
            {
                $search['element_module_parent_id'] = end( $this->getIdParent() );
            }

            $data = new Data( $this->getEntityName() );
            $rst = $data->find($search);

            if ( $rst )
            {
                $data->set('default' , 0 );
                $data->save();
            }

            $newData = new Data( $this->getEntityName() );
            $rst = $data->find( $this->getId() );
            if ( $rst )
            {
                $data->set('default' , 1 );
                $data->save();
            }

            return $this->Factory()->Response()->returnJSON( $this->m("default_success") , true ) ;
        }
    }

    /* ************************************************** */
    /* ******************    MEDIA    ******************* */
    /* ************************************************** */

    protected function libimagesupdateAction()
    {
        $tab = [];

        if ( $this->getEntity()->getImageField() )
        {
            foreach( $this->getEntity()->getImageField() as $row )
            {
                foreach( $this->getEntity()->getField() as $field )
                {
                    if ( $row == $field->getColumn() )
                    {
                        $Media = new Media;
                        $Media->setModuleId( $this->getEntityId() ) ;
                        $Media->setModuleName( $this->getEntityName() ) ;
                        $Media->setFolder( $this->getEntity()->getFolder() ) ;
                        $Media->setField( $field->getName() ) ;

                        $tab[ $field->getName() ]['title']  = $field->getTitle();
                        $tab[ $field->getName() ]['images'] = $Media->getAllModel();
                    }
                }
            }
        }

        $this->getGlobalVar() ;
        $this->setRender( 'fields' , $tab );

        $this->render('libimages_update.twig') ;
    }

    protected function libimagesAction()
    {
        $Media = new Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->setModuleName( $this->getEntityName() ) ;
        $Media->setFolder( $this->getEntity()->getFolder() ) ;
        $Media->setField( $_GET['field_name'] ) ;

        $this->setRender( 'images' , $Media->getAllModel() );
        $this->setRender( 'name' , $_GET['name'] );
        $this->setRender( 'field_name' , $_GET['field_name'] );
        $this->render('libimages.twig') ;
    }

    protected function uploadAction()
    {
        $Media = new Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->setModuleName( $this->getEntityName() ) ;
		$Media->setFolder( $this->getEntity()->getFolder() ) ;
		$rst = $Media->upload( UPLOAD_PATH ) ;

		return $this->Factory()->Response()->printJSON( $rst ) ;
    }

    protected function deletemediaAction()
    {
        $this->checkToken() ;

        $Media = new Media;
        $Media->setModuleId( $this->getEntityId() ) ;
        $Media->setImageId( $this->getId() );
        $Media->setFolder( $this->getEntity()->getFolder() ) ;
        $images = $Media->getAll() ;

        if ( $images )
        {
            if ( array_key_exists( $this->getId() , $images ) )
            {
                if ( $images[ $this->getId() ]->media_delete == true )
                {
                    $rst = $Media->delete();

                    if ( $rst ) $this->Factory()->Response()->returnJSON( $this->m("deletemedia_success") , true ) ;
                    else		$this->Factory()->Response()->returnJSON( $this->m("deletemedia_failed") ) ;
                }
                else
                {
                    $this->Factory()->Response()->returnJSON( $this->m("deletemedia_delete_is_impossible") ) ;
                }
            }
            else
            {
                $this->Factory()->Response()->returnJSON( $this->m("deletemedia_media_not_found") ) ;
            }
        }
        else
        {
            $this->Factory()->Response()->returnJSON( $this->m("deletemedia_no_ressource") ) ;
        }
    }

    /* ************************************************** */
    /* *****************   DOCUMENT   ******************* */
    /* ************************************************** */

    protected function deletedocumentAction()
    {
        $Doc = new Document;
        $Doc->setModuleId( $this->getEntityId() ) ;
        $Doc->setDocumentId( $this->getId() );
        $Doc->setFolder( $this->getEntity()->getFolder() ) ;
        $docs = $Doc->getAll() ;

        if ( $docs )
        {
            if ( array_key_exists( $this->getId() , $docs ) )
            {
                if ( $docs[ $this->getId() ]->document_delete == true )
                {
					$content = $this->getRepository()->findOne( $this->getApp()->request->post('element') );
					$file = $content->get( $this->getEntity()->get( $this->getApp()->request->post('field') )->getColumn() );
					$exp = explode( ',' , $file );

					if ( $exp )
					{
						if ( ( $key = array_search( $this->getId(), $exp ) ) !== false )
						{
							unset( $exp[ $key ] );
							$result = implode( ',' , $exp );

							$content->set( $this->getEntity()->get( $this->getApp()->request->post('field') )->getColumn() , $result );
							$content->save();
						}
					}

                    $rst = $Doc->delete();

                    if ( $rst ) $this->Factory()->Response()->returnJSON( $this->m("deletedocument_success") , true ) ;
                    else		$this->Factory()->Response()->returnJSON( $this->m("deletedocument_failed") ) ;
                }
                else
                {
                    $this->Factory()->Response()->returnJSON( $this->m("deletedocument_delete_is_impossible") ) ;
                }
            }
            else
            {
                $this->Factory()->Response()->returnJSON( $this->m("deletedocument_media_not_found") ) ;
            }
        }
        else
        {
            $this->Factory()->Response()->returnJSON( $this->m("deletedocument_no_ressource") ) ;
        }
    }

    protected function uploaddocumentAction()
    {
        $Doc = new Document;
		$Doc->setModuleId( $this->getEntityId() ) ;
		$Doc->setModuleName( $this->getEntityName() ) ;
		$Doc->setFolder( $this->getEntity()->getFolder() ) ;
		$rst = $Doc->upload() ;

		return $this->Factory()->Response()->printJSON( $rst ) ;
    }

    /* ************************************************** */
    /* *****************   GALLERY    ******************* */
    /* ************************************************** */

    protected function uploadgalleryAction()
    {
        $field = $this->getEntity()->get( $this->getApp()->request->post('fieldname') );

        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setElementId( $this->getApp()->request->post('id') );
        $Gallery->setModuleId( $this->getEntityId() );
        $Gallery->setField( $this->getApp()->request->post('fieldname') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );

        if ( $field->hasThumb() )
        {
            foreach( $field->getThumb() as $thumb )
            {
                // width, height
                $Gallery->setThumb( $thumb[0] , $thumb[1] );
            }
        }

        $rst = $Gallery->add();

        return $this->Factory()->Response()->printJSON( $rst ) ;
    }

    protected function deletegalleryAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setImageId( $this->getApp()->request->post('id') );
        $Gallery->setFolder( $this->getEntity()->getFolder() );
        $Gallery->delete();

        return $this->Factory()->Response()->printJSON(["result" => true]) ;
    }

    protected function ordergalleryAction()
    {
        $Gallery = new \App\Kernel\Back\Gallery;
        $Gallery->setElementId( $this->getApp()->request->post('id') );
        $Gallery->setField( $this->getApp()->request->post('field') );
        $Gallery->setOrder( $this->getApp()->request->post('order') );
        $Gallery->setModuleId( $this->getEntityId() );
        $Gallery->setFolder( $this->getEntity()->getFolder() );
        $Gallery->order();
    }

    /*
     * Gestion des formulaire seul pour les dependences qui n'ont pas la suppression d'activer ainsi que le nombre max d element a 1
     */
    protected function formAction()
    {
        $form = parent::generateForm( $this->getId() !== NULL ? true : false );
/*
        if ( $form === false )
        {
            $this->Factory()->Response()->flashAndRedirect( $this->m("have_no_content") ) ;
        }
*/
/*
        $this->setRender( 'cdn_css' , $form['cdn_css'] ) ;
        $this->setRender( 'cdn_js' , $form['cdn_js'] ) ;

        $this->setRender( 'css' , $form['css'] ) ;
        $this->setRender( 'js' , $form['js'] ) ;

        $this->setRender( 'tabs' , $form['tabs'] ) ;
        $this->setRender( 'condition' , $form['condition'] ) ;
*/
        echo $this->renderForm([
            'field' => $form['field'],
            'tabs' => $form['tabs'],
            'condition' => $form['condition'],
            'uri_id_parent' => $this->getUriParent(),
            'route' => $this->Factory()->Url()->route( $this->getEntityName() , ( $value == false ? 'add' : 'edit' ) , $this->getUriParent() , $form['id'] ),
            'id' => $form['id']
        ]) ;

    }
}