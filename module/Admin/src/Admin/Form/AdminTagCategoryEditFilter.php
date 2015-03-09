<?php
namespace Admin\Form;
use Zend\InputFilter\InputFilter;
class AdminTagCategoryEditFilter extends InputFilter
{
    private $dbAdapter;
	public function __construct($dbAdapter, $id) {  		 
		$this->add(array(
            'name' => 'tag_category_title',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),				 
            ),
			'validators' => array(
				array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 1, 'max' => 200,),),
				array('name' => 'Db\NoRecordExists', 'options' => array('table' => 'y2m_tag_category','field' => 'tag_category_title',  'exclude' => array ('field' => 'tag_category_id', 'value' => $id),  'adapter' => $dbAdapter),),                
			),
        ));		
    }
}
