<?php

/*
    On ne met pas de namepspace pour eviter de l'appeler par le namespace
*/

use App\Kernel\Container;

class DB extends ORM
{
    protected static $prefix           = "mod_" ;
    protected static $suffix_lang      = "_lang" ;
    protected static $suffix_id        = "_id" ;
    protected static $suffix_assoc     = "_value" ;

    /* ************************************************** */
    /* **************  GET TABLE / CHAMP  *************** */
    /* ************************************************** */

    public static function getTableName( $mod )
    {
        return strtolower( self::$prefix . $mod );
    }

    public static function getTableNameLang( $mod )
    {
        return strtolower( self::$prefix . $mod . self::$suffix_lang ) ;
    }

    public static function getTableNameAssoc( $mod , $field )
    {
        return strtolower( self::getTableName( $mod ) . '_assoc_' . $field ) ;
    }

    public static function getTableNameAssocValue( $mod , $field )
    {
        return self::getTableNameAssoc( $mod , $field ) . self::$suffix_assoc ;
    }

    public static function getIdName( $mod )
    {
        return self::getTableName( $mod ) . self::$suffix_id ;
    }

    public static function getIdLangName( $mod )
    {
        return self::getTableNameLang( $mod ) . self::$suffix_id ;
    }

    public static function getLangIdLangName( $mod )
    {
        return self::getTableNameLang( $mod ) . self::$suffix_lang . self::$suffix_id ;
    }

    public static function getIdNameInLang( $mod )
    {
        return self::getTableNameLang( $mod ) . "_" . self::getIdName( $mod ) ;
    }

    public static function getColumnName( $name , $mod , $lang = false )
    {
        return ( $lang == true ? self::getTableNameLang( $mod ) : self::getTableName( $mod ) ) . "_" . $name ;
    }

    /* ************************************************** */
    /* ****************  CHECK   TABLE  ***************** */
    /* ************************************************** */

    public static function patchModuleTable( $name )
    {
        $haveLang = Container::getInstance()->module( $name )->getEntity()->hasMultilang();
        $fields   = Container::getInstance()->module( $name )->getEntity()->getField();

        self::checkModuleTable( $name , $haveLang , $fields ) ;

        $rst = self::for_table('')->raw_query("SHOW COLUMNS FROM " . self::getTableName( $name ) )->find_many();
        $column = [];
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $column[ $row->Field ] = [
                    'name' => $row->Field,
                    'type' => $row->Type,
                    'null' => $row->Null,
                    'default' => $row->Default
                ];
            }
        }

        if ( $haveLang == true )
        {
            $rst = self::for_table('')->raw_query("SHOW COLUMNS FROM " . self::getTableNameLang( $name ) )->find_many();
            $columnLang = [];
            if ( $rst )
            {
                foreach( $rst as $row )
                {
                    $columnLang[ $row->Field ] = [
                        'name' => $row->Field,
                        'type' => $row->Type,
                        'null' => $row->Null,
                        'default' => $row->Default
                    ];
                }
            }
        }

        $sql = "" ;
        foreach( $fields as $field )
        {
            if ( $field->save() == true )
			{
				if ( ! $field->hasLang() )
				{
					if ( ! array_key_exists( $field->getColumn() , $column ) && $field->getData('SQL_TYPE') !== NULL )
					{
						$sql.= "ALTER TABLE `" . self::getTableName( $name ) . "` ADD " . self::createColumn( $field ). ";\n" ;
					}
				}
				else
				{
					if ( ! array_key_exists( $field->getColumn() , $columnLang ) && $field->getData('SQL_TYPE') !== NULL )
					{
						$sql.= "ALTER TABLE `" . self::getTableNameLang( $name ) . "` ADD " . self::createColumn( $field ). ";\n" ;
					}
				}
			}
        }

        if ( ! empty( $sql ) ) self::get_db()->exec( $sql ) ;
    }

    /* ************************************************** */
    /* ****************  CHECK   TABLE  ***************** */
    /* ************************************************** */

    public static function checkModuleTable( $mod , $haveLang , $fields )
    {
		$isCreate = 0 ;
        $rst = self::for_table('')->raw_query("SHOW TABLES")->find_many();
        if ( $rst )
        {
            $array = [];
            foreach( $rst as $value )
            {
                if ( $value->get( 'Tables_in_' . DB_DATABASE ) == self::getTableName( $mod ) )
                {
                    $isCreate++ ;
                }
                else if ( $value->get( 'Tables_in_' . DB_DATABASE ) == self::getTableNameLang( $mod ) & $haveLang == true )
                {
                    $isCreate++ ;
                }
            }

            if ( $isCreate == 0 or ( $isCreate == 1 && $haveLang == true ) )
            {
                self::createTable( $fields , $mod ) ;
            }
        }

        if ( ! empty( $fields ) )
        {
            foreach( $fields as $field )
            {
                if ( $field->getType() == 'checkbox' )
                {
                    $assocCreate = false ;
                    foreach( $rst as $value )
                    {
                        if ( $value->get( 'Tables_in_' . DB_DATABASE ) == self::getTableNameAssoc( $mod , $field->getName() ) ) $assocCreate = true ;
                    }

                    if ( $assocCreate == false )
                    {
                        if ( $field->getData('option') !== NULL )
                        {
                            $varchar = true ;
                            foreach( $field->getData('option') as $key => $value )
                            {
                                if ( is_numeric( $key ) ) $varchar = false ;
                            }
                        }
                        self::createTableAssoc( $mod , $field->getName() , $varchar ) ;
                    }
                }
            }
        }

        return false ;
    }

    /* ************************************************** */
    /* ****************  CREATE TEABLE  ***************** */
    /* ************************************************** */

    public static function createTableAssoc( $mod , $nameField , $varchar = false )
    {
        $Tbl = "CREATE TABLE IF NOT EXISTS `" . self::getTableNameAssoc( $mod , $nameField ) . "` (\n" ;
        $Tbl.= "\t`" . self::getTableNameAssoc( $mod , $nameField ) . self::$suffix_id . "` int(11) NOT NULL AUTO_INCREMENT,\n" ;
        $Tbl.= "\t`" . self::getTableNameAssoc( $mod , $nameField ) . '_' . self::getIdName( $mod ) . "` int(11) NOT NULL,\n" ;

        if ( $varchar ) $Tbl.= "\t`" . self::getTableNameAssoc( $mod , $nameField ) . self::$suffix_assoc . "` VARCHAR(100) NOT NULL,\n" ;
        else			$Tbl.= "\t`" . self::getTableNameAssoc( $mod , $nameField ) . self::$suffix_assoc . "` int(11) NOT NULL,\n" ;

        $Tbl.= "\tUNIQUE (`" . self::getTableNameAssoc( $mod , $nameField ) . self::$suffix_id . "`)\n" ;
        $Tbl.= ") ENGINE=InnoDB CHARACTER SET=utf8;\n\n" ;

        self::get_db()->exec( $Tbl ) ;
    }

    public static function createColumn( $field )
    {
        if ( $field->getData('SQL_VALUE') !== NULL )
        {
            if ( $field->getData('SQL_DEFAULT') !== NULL )
            {
                return "`" . $field->getColumn() . "` " . $field->getData('SQL_TYPE') . "(" . $field->getData('SQL_VALUE') . ") NULL DEFAULT '" . $field->getData('SQL_DEFAULT') . "'"  ;
            }
            else
            {
                return "`" . $field->getColumn() . "` " . $field->getData('SQL_TYPE') . "(" . $field->getData('SQL_VALUE') . ") NULL DEFAULT NULL" . ( $field->getData('SQL_AUTO_INCREMENT') ? ' AUTO_INCREMENT' : '' )  ;
            }
        }
        else
        {
            return "`" . $field->getColumn() . "` " . $field->getData('SQL_TYPE') . " NULL DEFAULT NULL" ;
        }
    }

    public static function createTable( $fields , $module )
    {
        $haveLang = false ;
        /* *************************** BASE *************************** */

        $Tbl = "CREATE TABLE IF NOT EXISTS `" . self::getTableName( $module ) . "` (\n" ;

        foreach( $fields as $field )
        {
            if ( $field->hasLang() == false && $field->save() == true && !empty( $field->getData('SQL_TYPE') ) )
            {
                $Tbl.= "\t" . self::createColumn( $field ). ",\n" ;
            }
            else if ( $field->hasLang() == true && $field->save() == true )
            {
                $haveLang = true ;
            }
        }

        $Tbl.= "\tUNIQUE (`" . self::getIdName( $module ) . "`)\n" ;
        $Tbl.= ") ENGINE=InnoDB CHARACTER SET=utf8;\n\n" ;

        /* *************************** MULTI-LANGUE *************************** */

        if ( $haveLang == true )
        {
            $Tbl.= "CREATE TABLE IF NOT EXISTS `" . self::getTableNameLang( $module ) . "` (\n" ;
            $Tbl.= "\t`" . self::getIdLangName( $module ) . "` int(11) NOT NULL AUTO_INCREMENT,\n" ;
            $Tbl.= "\t`" . self::getIdNameInLang( $module ) . "` int(11) NOT NULL,\n" ;
            $Tbl.= "\t`" . self::getLangIdLangName( $module ) . "` int(11) NOT NULL,\n" ;

            foreach( $fields as $field )
            {
                if ( $field->hasLang() == true && $field->save() == true && !empty( $field->getData('SQL_TYPE') ) )
                {
                    $Tbl.= "\t" . self::createColumn( $field ) . ",\n" ;
                }
            }

            $Tbl.= "\tUNIQUE (`" . self::getIdLangName( $module ) . "`)\n" ;
            $Tbl.= ") ENGINE=InnoDB CHARACTER SET=utf8;\n\n" ;
        }

		self::get_db()->exec( $Tbl ) ;
    }

    public function truncate( $Tbl )
    {
        return self::get_db()->exec( "TRUNCATE " . $Tbl . ";" ) ;
    }

    /* ********************************************************* */
    /* ******************    GESTION BASE    ******************* */
    /* ********************************************************* */

    public static function createSimpleTable( $tableName , $fields )
    {
        $Tbl = "CREATE TABLE IF NOT EXISTS `" . $tableName . "` (\n" ;

        $ct = count( $fields );
        $i  = 1 ;
        foreach( $fields as $columnName => $field )
        {
            $Tbl.= "\t" . self::createSimpleColumn( $columnName , $field['value'] , $field['type'] , $field['default'] , $field['empty'] , $field['increment'] ) . ( $i != $ct ? "," : "" ) . "\n" ;

            if ( $field['increment'] == true )
            {
                $primary = $columnName ;
            }
            $i++;
        }

        $Tbl.= ") ENGINE=InnoDB DEFAULT CHARSET=utf8;\n" ;

        self::get_db()->exec( $Tbl ) ;
        self::get_db()->exec( "ALTER TABLE `" . $tableName . "` ADD PRIMARY KEY (`" . $primary . "`);" ) ;
        self::get_db()->exec( "ALTER TABLE `" . $tableName . "` MODIFY `" . $primary . "` int(11) NOT NULL AUTO_INCREMENT;" ) ;
    }

    public static function createSimpleColumn( $columnName , $value , $type , $default = NULL , $empty = false , $increment = false )
    {
        $str = "`" . $columnName . "` " . $type . ( $value !== NULL ? "(" . $value . ")" : "" ) . " " . ( $empty ? "NULL" : "NOT NULL" ) ;

        if ( $default !== NULL or $empty == true )
        {
            $str.= " DEFAULT " ;

            if ( $default !== NULL ) $str .= "'" . $default . "'" ;
            else                     $str.= "NULL" ;
        }

        return $str ;
    }

    public static function alterSimpleColumn( $tableName , $columnName , $field )
    {
        $query = "ALTER TABLE `" . $tableName . "` ADD " . self::createSimpleColumn( $columnName , $field['value'] , $field['type'] , $field['default'] , $field['empty'] , $field['increment'] ) . ";";

        self::get_db()->exec( $query ) ;
    }

    public static function getColumnsTable( $tableName )
    {
        $rst = self::for_table('')->raw_query("SHOW COLUMNS FROM " . $tableName )->find_many();
        $column = [];
        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $column[ $row->Field ] = [
                    'name' => $row->Field,
                    'type' => $row->Type,
                    'null' => $row->Null,
                    'default' => $row->Default
                ];
            }
        }

        return $column ;
    }

    /* ************************************************** */
    /* ******************    DEBUG    ******************* */
    /* ************************************************** */

    public function printQuery()
    {
        \App\Kernel\Debug::dump( $this->_build_select() );
    }

    /* ************************************************** */
    /* ******************  FUNCTIONS  ******************* */
    /* ************************************************** */

    public static function find_all_for_select( $entity , $target , $alias , $idlang , $parent = NULL , $filter = NULL )
    {
        if ( $target->hasLang() )
        {
            $table 			= self::getTableName( $entity ) ;
            $tableLang  	= self::getTableNameLang( $entity ) ;
            $idName			= self::getIdName( $entity ) ;
            $idNameInLang	= self::getIdNameInLang( $entity ) ;
            $langIdLangName	= self::getLangIdLangName( $entity ) ;

            $content = self::for_module( $entity )
                ->select( $tableLang . "." . $target->getColumn() , $alias )
                ->select( $table . "." . $idName , 'id' )
                ->left_outer_join( $tableLang , array( $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ))
                ->where_equal( $tableLang . '.' . $langIdLangName , $idlang ) ;
        }
        else
        {
            $table 			= self::getTableName( $entity ) ;
            $idName			= self::getIdName( $entity ) ;

            $content = self::for_module( $entity )
                ->select( $table . "." . $target->getColumn() , $alias )
                ->select( $table . "." . $idName , 'id' ) ;
        }

        if ( $parent !== NULL )
        {
            $content = $content->select( $table . "." . self::getColumnName( $parent , $entity ) , $parent );
        }

        $entity = Container::getInstance()->module( $entity )->getEntity();

        if ( $entity->hasOrder() )  $content = $content->order_by_asc( $table . "." . $entity->get( $entity->getOrderName() )->getColumn() );
        else                        $content = $content->order_by_asc( ( $target->hasLang() ? $tableLang : $table ) . "." . $target->getColumn() );

        if ( is_callable( $filter ) )
        {
            $content = $filter( $content );
        }

        return $content->find_many() ;
    }

    public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION)
    {
        self::_setup_db($connection_name);
        $obj = new self($table_name, array(), $connection_name);
        return $obj->use_id_column( $table_name . "_id" );
    }

    public static function for_module( $module )
    {
        return self::for_table( self::getTableName( $module ) );
    }

    public static function find( $module , $id )
    {
        return self::for_module( $module )->where_id_is( $id )->find_one();
    }

    public static function for_module_assoc( $module , $field )
    {
        return self::for_table( self::getTableNameAssoc( $module , $field ) ) ;
    }

    public static function add_assoc( $module , $nameField , $id , $value )
    {
        $obj = self::for_module_assoc( $module , $nameField )->create();
        $obj->set( self::getTableNameAssoc( $module , $nameField ) . '_' . self::getIdName( $module ) , $id ) ;
        $obj->set( self::getTableNameAssoc( $module , $nameField ) . self::$suffix_assoc , $value ) ;
        return $obj->save();
    }

    public static function for_module_lang( $module , $id = null , $idlang = null )
    {
        $obj = self::for_table( self::getTableNameLang( $module ) );

        if ( $id !== null && $idlang === null ) 	 $obj = $obj->where( array( self::getIdNameInLang( $module ) => $id ) ) ;
        else if ( $id !== null && $idlang !== null ) $obj = $obj->where( array( self::getIdNameInLang( $module ) => $id , self::getLangIdLangName( $module ) => $idlang ) ) ;

        return $obj ;
    }

    public static function for_module_join_lang( $entity , $idlang )
    {
        $table 			= self::getTableName( $entity ) ;
        $tableLang  	= self::getTableNameLang( $entity ) ;
        $idName			= self::getIdName( $entity ) ;
        $idNameInLang	= self::getIdNameInLang( $entity ) ;
        $langIdLangName	= self::getLangIdLangName( $entity ) ;

        return self::for_module( $entity )
            ->left_outer_join( $tableLang , array( $table . '.' . $idName , '=', $tableLang . '.' . $idNameInLang ))
            ->where_equal( $tableLang . '.' . $langIdLangName , $idlang ) ;
    }

    protected function _add_date_condition_function($type, $column_name, $separator, $value, $function) {
        $multiple = is_array($column_name) ? $column_name : array($column_name => $value);
        $result = $this;

        foreach($multiple as $key => $val) {
            // Add the table name in case of ambiguous columns
            if (count($result->_join_sources) > 0 && strpos($key, '.') === false) {
                $table = $result->_table_name;
                if (!is_null($result->_table_alias)) {
                    $table = $result->_table_alias;
                }

                $key = "{$table}.{$key}";
            }
            $key = $function . '(' . $result->_quote_identifier($key) . ')' ;
            $result = $result->_add_condition($type, "{$key} {$separator} ?", $val);
        }
        return $result;
    }

    public function where_date($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '=', $value, 'DATE');
    }

    public function where_date_lte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '<=', $value, 'DATE');
    }

    public function where_date_gte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '>=', $value, 'DATE');
    }

    public function where_month($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '=', $value, 'MONTH');
    }

    public function where_month_lte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '<=', $value, 'MONTH');
    }

    public function where_month_gte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '>=', $value, 'MONTH');
    }

    public function where_year($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '=', $value, 'YEAR');
    }

    public function where_year_lte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '<=', $value, 'YEAR');
    }

    public function where_year_gte($column_name, $value=null) {
        return $this->_add_date_condition_function('where', $column_name, '>=', $value, 'YEAR');
    }

    public function order_by_rand() {
        $this->_order_by[] = "RAND()";
        return $this;
    }
}