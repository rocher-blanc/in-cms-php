<?php

namespace App\Kernel\Front;

use App\Kernel\Container;

class Search
{
	private $query , $key , $modules, $moduleIds = [], $ids = [];
	const FIELD_TYPE_TEXT   = [ 'text', 'textarea' ];

	/**
	 * Modifie la clé de recherche
	 * @param $query
	 * @return $this
	 */
	public function setQuery( $query ) : self
	{
		$this->query = $query;
		$this->key = "%{$query}%";
		return $this;
	}

	/**
	 * Ajoute un module dans la recherche.
	 * Configs (toutes les données sont facultatives) :
	 * - 'fields'       : si existant, effectue une recherche dans les champs renseigné, sinon prend en compte tous les champs de texte.
	 * - 'dependencies' : renseigne les dépendances dans lesquelles le module va chercher les données
	 * - 'selects'      : ajoute des champs select et checkbox dans la recherche
	 * @param string $moduleName
	 * @param array  $configs
	 * @return $this
	 */
	public function addModule( string $moduleName , array $configs = [] ) : self
	{
		$this->modules[ $moduleName ] = $configs;
		return $this;
	}

	/**
	 * Execute la recherche
	 * @return array[ 'query', 'count', 'results' ]
	 */
	public function search() : Array
	{
		// Préparation des variables
		$results   = [];
		$count     = 0;

		// Si une clé de cherche est bien définie
		if( ! empty( trim( $this->query ) ) )
		{
			// Pour chaque module à rechercher
			foreach( $this->modules as $moduleName => $config )
			{
				// Effectue la recherche sur le module
				$results[ $moduleName ] = $this->searchModule( $moduleName );
				// Incrémente le nombre total de résultats
				$count += count( $results[ $moduleName ] );
			}
		}

		// Retour du résultat
		return [
			'query'   => $this->query,
			'count'   => $count,
			'results' => $results
		];
	}

	/* ************************************************** */
	/* *************   MODULE SEARCHING   *************** */
	/* ************************************************** */

	/**
	 * Effectue une recherche dans un module
	 * @param $moduleName
	 * @return array
	 * @throws \App\Kernel\Exception
	 */
	private function searchModule( $moduleName )
	{
		// Réinitialisation des ids des résultats de recherche
		$this->ids = [];
		// Recherche dans le module concerné
		$this->searchModule_mainModule( $moduleName );
		// Recherche dans les dépendances
		$this->searchModule_dependencies( $moduleName );
		// Recherche dans les sélects
		$this->searchModule_selects( $moduleName );

		// Si des résultats sont disponible
		if( count( $this->ids ) > 0 )
		{
			// Chargement du module et récupération des éléments par ids
			$mod = Container::getInstance()->module( $moduleName );
			$req = $mod->getRepository()->getKit()
				->where_in( $mod->getEntity()->get('id')->fieldSql() , $this->ids )
				->find_many();

			if( $req )
			{
				return $mod->getController()->parseAll( $req );
			}
		}

		return [];
	}

	/**
	 * Recherche dans le module
	 * @param $moduleName
	 * @throws \App\Kernel\Exception
	 */
	protected function searchModule_mainModule( $moduleName )
	{
		$mod      = Container::getInstance()->module( $moduleName );
		$where1   = [];
		$where2   = [];

		// Parmi tous les champs du module
		foreach( $mod->getEntity()->getfield() as $field )
		{
			// S'il s'agit d'un champ textuel
			if( $this->isAvailableTextField( $field , $moduleName ) )
			{
				$where1[] = [ $field->fieldSql() => $this->key];
				$where2[ $field->fieldSql() ] = 'LIKE';
			}
		}

		// Recherche des ids des éléments concernés
		$req = $mod->getRepository()->getKit()
			->select( $mod->getEntity()->get('id')->fieldSql() , 'id' )
			->where_any_is( $where1 , $where2 )
			->find_array();

		// Ajout des ids dans la liste du résultat
		$req = $this->extractIds( $req );
		$this->addIdsEntries( $req );
	}

	/**
	 * Recherche dans les dépendances
	 * @param $moduleName
	 * @throws \App\Kernel\Exception
	 */
	protected function searchModule_dependencies( $moduleName )
	{
		$mod      = Container::getInstance()->module( $moduleName );
		$config   = $this->modules[ $moduleName ];
		$moduleId = $this->getModuleId( $moduleName );

		// Si la configuration dépendance est définie
		if( array_key_exists( 'dependencies' , $config ) )
		{
			// Pour chaque dépendance du module
			foreach( $mod->getEntity()->getDependency() as $dependency )
			{
				// Si la dépendance est dans liste des dépendances à chercher
				if( in_array( $dependency['class'] , $config['dependencies'] ) )
				{
					// Récupération des ids des éléments liés à la dépendance
					$req = $this->findIdsInDependency( $dependency['class'], $moduleId );
					$this->addIdsEntries( $req );
				}
			}
		}
	}

	/**
	 * Recherche dans les modules liés à des champs select
	 * @param $moduleName
	 * @throws \App\Kernel\Exception
	 */
	protected function searchModule_selects( $moduleName )
	{
		$mod      = Container::getInstance()->module( $moduleName );
		$config   = $this->modules[ $moduleName ];

		if( array_key_exists( 'selects' , $config ) )
		{
			foreach( $mod->getEntity()->getfield() as $field )
			{
				if( in_array( $field->getName() , $config['selects'] ) )
				{
					switch( $field->getType() )
					{
						case "select" :
							$ids = $this->findIdsInSelect( $moduleName , $field->getName() , $field->getObject() );
							$this->addIdsEntries( $ids );
							break;

						case "checkbox" :
							$ids = $this->findIdsInCheckbox( $moduleName , $field->getName() , $field->getObject() );
							$this->addIdsEntries( $ids );
							break;
					}
				}
			}
		}
	}

	protected function findIdsInDependency( $moduleName , $parentModuleId )
	{
		$mod = Container::getInstance()->module( $moduleName );
		$where1 = [];
		$where2 = [];

		foreach( $mod->getEntity()->getfield() as $field )
		{
			if( in_array( $field->getType() , self::FIELD_TYPE_TEXT ) )
			{
				$where1[] = [ $mod->getEntity()->get( $field->getName() )->fieldSql() => $this->key ];
				$where2[ $mod->getEntity()->get( $field->getName() )->fieldSql() ] = 'LIKE' ;
			}
		}

		$req = $mod->getRepository()->getKit()
			->select( $mod->getEntity()->get('element_id')->fieldSql() , 'element_id' )
			->where_equal( $mod->getEntity()->get('module_id')->fieldSql() , $parentModuleId )
			->where_any_is( $where1 , $where2 )
			->find_array();

		if( $req )
		{
			return $this->extractIds( $req , 'element_id' );
		}
		else
		{
			return [];
		}
	}

	protected function findIdsInSelect( $moduleName , $fieldName , $targetModuleName )
	{
		$mod    = Container::getInstance()->module( $moduleName );
		$target = Container::getInstance()->module( $targetModuleName );
		$where1 = [];
		$where2 = [];

		foreach( $target->getEntity()->getfield() as $field )
		{
			if( in_array( $field->getType() , self::FIELD_TYPE_TEXT ) )
			{
				$where1[] = [ $target->getEntity()->get( $field->getName() )->fieldSql() => $this->key ];
				$where2[ $target->getEntity()->get( $field->getName() )->fieldSql() ] = 'LIKE' ;
			}
		}

		$req = $target->getRepository()->getKit()
			->select( $target->getEntity()->get('id')->fieldSql() , 'id' )
			->where_any_is( $where1 , $where2 )
			->find_array();

		if( $req )
		{
			$ids = $this->extractIds( $req );

			$req2 = $mod->getRepository()->getKit()
				->select( $mod->getEntity()->get('id')->fieldSql() , 'id' )
				->where_in( $mod->getEntity()->get( $fieldName )->fieldSql() , $ids )
				->find_array();

			return $this->extractIds( $req );
		}
		else
		{
			return [];
		}
	}

	protected function findIdsInCheckbox( $moduleName , $fieldName , $targetModuleName )
	{
		$mod    = Container::getInstance()->module( $moduleName );
		$target = Container::getInstance()->module( $targetModuleName );
		$where1 = [];
		$where2 = [];

		foreach( $target->getEntity()->getfield() as $field )
		{
			if( in_array( $field->getType() , self::FIELD_TYPE_TEXT ) )
			{
				$where1[] = [ $target->getEntity()->get( $field->getName() )->fieldSql() => $this->key ];
				$where2[ $target->getEntity()->get( $field->getName() )->fieldSql() ] = 'LIKE' ;
			}
		}

		$req = $target->getRepository()->getKit()
			->select( $target->getEntity()->get('id')->fieldSql() , 'id' )
			->where_any_is( $where1 , $where2 )
			->find_array();

		if( $req )
		{
			$ids = $this->extractIds( $req );

			$req2 = \DB::for_module_assoc( $moduleName , $fieldName )
				->select( \DB::getTableNameAssoc( $moduleName , $fieldName ) . '_' . \DB::getIdName( $moduleName ) , 'element_id' )
				->where_in( \DB::getTableNameAssocValue( $moduleName , $fieldName ) , $ids )
				->find_many();

			return $this->extractIds( $req2 , 'element_id' );
		}
		else
		{
			return [];
		}
	}


	/* ************************************************** */
	/* ******************   TOOLS   ********************* */
	/* ************************************************** */

	private function addIdsEntries( $newEntries )
	{
		foreach( $newEntries as $i )
		{
			if( ! in_array( $i , $this->ids ) )
			{
				$this->ids[] = $i ;
			}
		}
	}

	private function extractIds( $list , $value = 'id' )
	{
		return array_map( function( $v ) use ($value)
		{
			return $v[ $value ];
		}, $list );
	}

	private function getModuleId( $moduleName )
	{
		if( ! array_key_exists( $moduleName , $this->moduleIds ) )
		{
			$req = \DB::for_table('module')
				->select( 'module_id' )
				->where_equal( 'module_class_name' , $moduleName )
				->find_one();

			$this->moduleIds[$moduleName] = $req->module_id;
		}

		return $this->moduleIds[$moduleName];
	}

	private function isAvailableTextField( $field , $moduleName )
	{
		if( in_array( $field->getType() , self::FIELD_TYPE_TEXT ) )
		{
			if( array_key_exists( 'fields' , $this->modules[$moduleName] ) )
			{
				if( array_key_exists( $field , $this->modules[$moduleName][ 'fields' ] ) )
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		}

		return false;
	}

}