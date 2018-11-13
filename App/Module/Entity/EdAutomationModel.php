<?php

namespace App\Module\Entity;

use App\Kernel\Entity\Builder;

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
            ->notEmpty("Veuillez renseigner le nom du modèle")
            ->name("Nom");

        $this->build('subject')
            ->column(1, 3)
            ->isVarchar()
            ->notEmpty("Veuillez renseigner le sujet")
            ->name("Sujet");

        $this->build('key')
            ->column(1, 3)
            ->isVarchar()
            ->name("Clef");

        $this->build('vars')
            ->column(1, 3)
            ->isCheckbox()
            ->option( $this->getList_EdAutomationVar() )
            ->name("Variables d'environnements");
    }

    private function getList_EdAutomationVar()
    {
        $rst = [];

        // Find all groups
        $data_group = new \App\Kernel\Back\Data( "EdAutomationVarGroup" );
        $data_group->find([]);
        if( $data_group->getData() )
        {
            // For each group
            foreach( $data_group->getData() as $group )
            {
                // Find all elements in group
                $data_el = new \App\Kernel\Back\Data( "EdAutomationVar" );
                $data_el->find([ 'parent_id' => $group->get("id") ]);
                if( $data_el->getData() )
                {
                    $rst_temp = [];

                    foreach( $data_el->getData() as $element )
                    {
                        $rst_temp[ $element->get("id") ] = $element->get("label");
                    }

                    $rst[ $group->get("name") ] = $rst_temp;
                }
            }
        }

        return $rst;
    }
}