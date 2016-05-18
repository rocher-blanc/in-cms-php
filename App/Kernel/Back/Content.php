<?php

namespace App\Kernel\Back;

class Content
{
	/* ************************************************** */
	/* ****************   VARIABLES   ******************* */
	/* ************************************************** */
	
	private $id ;
	private $langid ;
	private $moduleid ;

	/* ************************************************** */
	/* ****************   CONSTRUCT   ******************* */
	/* ************************************************** */

	public function __construct() {}

	/* ************************************************** */
	/* ******************   SETTER   ******************** */
	/* ************************************************** */
	
	public function setId( $var )
	{
		$this->id = $var ;
	}
	
	public function setLangId( $var )
	{
		$this->langid = $var ;
	}
	
	public function setModuleId( $var )
	{
		$this->moduleid = $var ;
	}

	/* ************************************************** */
	/* ******************   GETTER   ******************** */
	/* ************************************************** */
	
	public function getId()
	{
		return $this->id ;
	}
	
	public function getLangId()
	{
		return $this->langid ;
	}
	
	public function getModuleId()
	{
		return $this->moduleid ;
	}
	
	protected function getApp()
	{
		return \Slim\Slim::getInstance() ;
	}
	
	/* ************************************************** */
	/* *****************   FUNCTION   ******************* */
	/* ************************************************** */

	public function generic_paragraph_list()
	{
		$genericParagraphRows = \DB::for_table('content_line')
			->left_outer_join('content_column', array('content_line.content_line_id', '=', 'content_column.content_column_content_line_id'), 'content_column')
			->left_outer_join('content_paragraph', array('content_column.content_column_id', '=', 'content_paragraph.content_paragraph_content_column_id'), 'content_paragraph')	
			->where(array('content_line.content_line_element_id' => $this->getId(),
					'content_line.content_line_module_id' => $this->getModuleId(),
					'content_line.content_line_lang_id' => $this->getLangId()))
			->order_by_asc('content_line.content_line_order')
			->order_by_asc('content_column.content_column_order')
			->order_by_asc('content_paragraph.content_paragraph_order')
			->find_many();

		$genericLineArray = array();
		$currentLine = -1; $currentColumn = -1;
		$genericParagraphArray = array();
		$currentLineObj = NULL; $currentColumnObj = NULL;
		$alreadyAddedParagraphs = array();
		
		foreach ($genericParagraphRows as $genericParagraphRow)
		{
			if ($currentColumn != $genericParagraphRow['content_column_id'])
			{
				if ($currentColumnObj != NULL)
					$currentLineObj->columns[] = $currentColumnObj;
				
				if ($genericParagraphRow['content_column_id'] == NULL)
					continue;
				$currentColumnObj = new \stdClass();
				$currentColumnObj->id = $genericParagraphRow['content_column_id'];
				$currentColumnObj->width = $genericParagraphRow['content_column_width'];
				$currentColumnObj->paragraphs = array();
				$currentColumn = $genericParagraphRow['content_column_id'];
			}
			if ($currentLine != $genericParagraphRow['content_line_id'])
			{
				if ($currentLineObj != NULL)
					$genericLineArray[] = $currentLineObj;
				$currentLineObj = new \stdClass();
				$currentLineObj->id = $genericParagraphRow['content_line_id'];
				$currentLineObj->columns = array();
				$currentLine = $genericParagraphRow['content_line_id'];
			}
			
			if ($genericParagraphRow['content_paragraph_id'] == NULL || in_array($genericParagraphRow['content_paragraph_id'], $alreadyAddedParagraphs))
				continue;
			
			$genericParagraphObj = new \stdClass();
			$genericParagraphObj->id = $genericParagraphRow['content_paragraph_id'];
			$genericParagraphObj->type = $genericParagraphRow['content_paragraph_type'];
			$genericParagraphObj->text = $genericParagraphRow['content_paragraph_text'];
			if ($genericParagraphRow['content_paragraph_type'] == 2)
			{
				$genericParagraphObj->text = htmlspecialchars($genericParagraphRow['content_paragraph_text']);
			}
			$genericParagraphObj->text2 = $genericParagraphRow['content_paragraph_text2'];
			$genericParagraphObj->link = $genericParagraphRow['content_paragraph_link'] != null ? $genericParagraphRow['content_paragraph_link'] : "";
			$genericParagraphObj->link_type = $genericParagraphRow['content_paragraph_link_type'];
			$genericParagraphObj->link_url = $genericParagraphRow['content_paragraph_link'] != null ? $genericParagraphRow['content_paragraph_link'] : "";
			$genericParagraphObj->link_display = $genericParagraphRow['content_paragraph_link'] != null ? $genericParagraphRow['content_paragraph_link'] : "";
			if ($genericParagraphObj->link_type == 1)
			{
				$linkArray = explode("-", $genericParagraphObj->link);
				if (count($linkArray) == 2)
				{
					$table = $linkArray[0];
					if (in_array($table, array('news', 'event', 'content')))
					{
						$itemRow = \DB::for_table($table)
							->left_outer_join($table.'_lang', array($table.'.'.$table.'_id', '=', $table.'_lang.'.$table.'_lang_'.$table.'_id'))
							->where(array($table.'_lang.'.$table.'_lang_lang_id' => $this->getApp()->environment['lang']->id, $table.'.'.$table.'_id' => $linkArray[1]))
							->find_one();

						if ($itemRow)
						{
							$genericParagraphObj->link_display = '[' . $linkArray[0] . '] '.$itemRow[$table.'_lang_title'];
						}
					}
					$genericParagraphObj->link_url = "javascript:;";
				}
			}
			$currentColumnObj->paragraphs[] = $genericParagraphObj;
			$alreadyAddedParagraphs[] = $genericParagraphObj->id;
		}
		
		if ($currentColumnObj != NULL)
		{
			$currentLineObj->columns[] = $currentColumnObj;
		}
		if ($currentLineObj != NULL)
		{
			$genericLineArray[] = $currentLineObj;
		}
		
		return $genericLineArray ;
	}
	
	public function save()
	{
		$paragraphLineIdArray = $this->getApp()->request->post('paragraph_line_id');
		$paragraphColumnWidthArray = $this->getApp()->request->post('paragraph_column_width');
		$paragraphColumnIdArray = $this->getApp()->request->post('paragraph_column_id');
		$paragraphColumnLinenumArray = $this->getApp()->request->post('paragraph_column_linenum');
		$paragraphParagraphColumnIdArray = $this->getApp()->request->post('paragraph_paragraph_column_id');
		$paragraphColumnParagraphLineIdArray = $this->getApp()->request->post('paragraph_column_paragraph_line_id');
		$paragraphLineNumArray = $this->getApp()->request->post('paragraph_linenum');
		$paragraphColumnNumArray = $this->getApp()->request->post('paragraph_columnnum');
		
		$paragraphTextArray = $this->getApp()->request->post('paragraph_text');
		$paragraphText2Array = $this->getApp()->request->post('paragraph_text2');
		$paragraphTypeArray = $this->getApp()->request->post('paragraph_type');
		$paragraphWidthArray = $this->getApp()->request->post('paragraph_width');
		$paragraphLinkArray = $this->getApp()->request->post('paragraph_link');
		$paragraphLinkTypeArray = $this->getApp()->request->post('paragraph_link_type');
		$paragraphIdArray = $this->getApp()->request->post('paragraph_id');
		
		$paragraphDeletedArray = explode(",", trim($this->getApp()->request->post('paragraph_deleted'), ","));
		$columnDeletedArray = explode(",", trim($this->getApp()->request->post('paragraph_column_deleted'), ","));
		$lineDeletedArray = explode(",", trim($this->getApp()->request->post('paragraph_line_deleted'), ","));

		//---------- Suppression
		foreach ($paragraphDeletedArray as $paragraphDeleted)
		{
			$genericParagraphRow = \DB::for_table('content_paragraph')
				->where(array('content_paragraph.content_paragraph_id' => $paragraphDeleted))
				->find_one();

			if ($genericParagraphRow)
			{
				if ($genericParagraphRow['content_paragraph_type'] == "3" || $genericParagraphRow['content_paragraph_type'] == "7")
				{
					$genericParagraphImageRows = \DB::for_table('content_paragraph')
						->where_not_equal('content_paragraph.content_paragraph_id', $paragraphDeleted)
						->where_equal('content_paragraph.content_paragraph_text', $genericParagraphRow['content_paragraph_text'])
						->where_equal('content_paragraph.content_paragraph_type', $genericParagraphRow['content_paragraph_type'])
						->find_many();

					$genericParagraphImage = UPLOADS_PATH . "/content/paragraph/" . $genericParagraphRow['content_paragraph_text'];
					if (count($genericParagraphImageRows) == 0 && is_file($genericParagraphImage))
					{
						unlink($genericParagraphImage);
					}
				}
				$genericParagraphRow->delete();
			}
		}
		
		\DB::for_table('content_column')
			->where_in('content_column_id', $columnDeletedArray)
			->delete_many();
		
		\DB::for_table('content_line')
			->where_in('content_line_id', $lineDeletedArray)
			->delete_many();
		
		$columnNumIdArray = array();
		if ($paragraphLineIdArray != "")
		{
			$lineNumIdArray = array();
			foreach ($paragraphLineIdArray as $i => $paragraphLineId)
			{
				$paragraphLineRow = NULL;
				if ($paragraphLineId > 0)
				{
					$paragraphLineRow = \DB::for_table('content_line')->where_id_is($paragraphLineId)->find_one();
				}
				if (!$paragraphLineRow)
				{
					$paragraphLineRow = \DB::for_table('content_line')->create();
					$paragraphLineRow['content_line_element_id'] = $this->getId();
					$paragraphLineRow['content_line_module_id'] = $this->getModuleId();
					$paragraphLineRow['content_line_lang_id'] = $this->getLangId();
				}
				$paragraphLineRow['content_line_order'] = $i + 1;
				$paragraphLineRow->save();
				$lineNumIdArray[$paragraphLineNumArray[$i]] = $paragraphLineRow['content_line_id'];
			}
			
			foreach ($paragraphColumnIdArray as $i => $paragraphColumnId)
			{
				$paragraphColumnRow = NULL;
				if ($paragraphColumnId > 0)
				{
					$paragraphColumnRow = \DB::for_table('content_column')->where_id_is($paragraphColumnId)->find_one();
				}
				if (!$paragraphColumnRow)
				{
					$paragraphColumnRow = \DB::for_table('content_column')->create();
					$paragraphColumnRow['content_column_content_line_id'] = $lineNumIdArray[$paragraphColumnLinenumArray[$i]];
				}
				$paragraphColumnRow['content_column_width'] = $paragraphColumnWidthArray[$i];
				$paragraphColumnRow['content_column_order'] = $i + 1;
				$paragraphColumnRow->save();
				$columnNumIdArray[$paragraphColumnNumArray[$i]] = $paragraphColumnRow['content_column_id'];
			}
			
			foreach ($paragraphTypeArray as $i => $paragraphType)
			{
				$genericParagraphRow = NULL;
				if ($paragraphIdArray[$i] > 0)
				{
					$genericParagraphRow = \DB::for_table('content_paragraph')
					->where(array('content_paragraph.content_paragraph_id' => $paragraphIdArray[$i]))
					->find_one();
				}
				if (!$genericParagraphRow)
				{
					$genericParagraphRow = \DB::for_table('content_paragraph')->create();
				}
				
				$genericParagraphRow['content_paragraph_type'] = $paragraphType;
				$genericParagraphRow['content_paragraph_'.'content_column_id'] = $columnNumIdArray[$paragraphParagraphColumnIdArray[$i]];
				$genericParagraphRow['content_paragraph_link'] = $paragraphLinkArray[$i];
				$genericParagraphRow['content_paragraph_link_type'] = $paragraphLinkTypeArray[$i];
				if ($paragraphType == 6)
				{
					$paragraphGalleryImagesArray = $this->getApp()->request->post('paragraph_text_'.$paragraphTextArray[$i]);
					$paragraphGalleryImagesAltArray = $this->getApp()->request->post('paragraph_text2_'.$paragraphTextArray[$i]);
					$genericParagraphRow['content_paragraph_text'] = implode("#@#", $paragraphGalleryImagesArray);
					$genericParagraphRow['content_paragraph_text2'] = implode("#@#", $paragraphGalleryImagesAltArray);
				}
				else
				{
					$genericParagraphRow['content_paragraph_text'] = $paragraphTextArray[$i];
					$genericParagraphRow['content_paragraph_text2'] = $paragraphText2Array[$i];
				}
				$genericParagraphRow['content_paragraph_order'] = $i+1;
				$genericParagraphRow->save();
			}
		}
	}
}