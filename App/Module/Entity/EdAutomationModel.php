<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;
use App\Kernel\Front\Translate;

class EdAutomationModel extends Builder
{
    protected function load()
    {
        $this->setFieldReference( 'name' );
        $this->setModuleChild( 'EdAutomation' );
        $this->setModuleParent( 'EdAutomationModelGroup' );

        $this->build('name')
            ->full()
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText( 'mandatory_model_name'))
            ->name(Translate::getInstance()->getText( 'nom'));

        $this->build('subject')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty(Translate::getInstance()->getText( 'mandatory_subject_name'))
            ->name(Translate::getInstance()->getText( 'subject'));

        $this->build('key')
            ->column(1, 3)
            ->isVarchar()
            ->name(Translate::getInstance()->getText( 'key'));

        $this->build('vars')
            ->column(1, 3)
            ->isCheckbox()
            ->option( $this->getList_EdAutomationVar() )
            ->name(Translate::getInstance()->getText( 'variables_environnement'));
    }


	private function getList_EdAutomationVar()
	{
		// Prepare modules and result
		$mod_group = \App\Kernel\Container::getInstance()->module("EdAutomationVarGroup");
		$mod_element = \App\Kernel\Container::getInstance()->module("EdAutomationVar");
		$rst = [];

		// Find all groups
		$all_group = $mod_group->getRepository()->findAll();
		if( $all_group )
		{
			// For each groups
			foreach( $all_group as $row_group ) {
				// Parse group and create self result
				$group = $mod_group->getController()->parseValue($row_group);
				$rst_temp = [];

				// Find elements in group
				$all_element = $mod_element->getRepository()->getKit()
					->where_equal( $mod_element->getEntity()->get('element_module_parent_id')->getColumn(), $group['id'] )
					->find_many();
				if( $all_element )
				{
					foreach( $all_element as $row_element )
					{
						$element = $mod_element->getController()->parseValue($row_element);
						$rst_temp[ $element['id'] ] = $element['label'];
					}
					// Add group to final result
					$rst[ $group['name'] ] = $rst_temp;
				}
			}
		}
		// Return final result
		return $rst;
	}
}