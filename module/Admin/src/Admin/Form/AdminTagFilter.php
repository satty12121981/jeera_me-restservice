<?php
namespace Admin\Form;
use Zend\InputFilter\InputFilter;
class AdminTagFilter extends InputFilter
{
    private $dbAdapter;
	public function __construct($dbAdapter) {  			
		
		$this->add(array(
            'name' => 'tag_title',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
			'validators' => array(
        					array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 1, 'max' => 200,),),
							array('name' => 'Db\NoRecordExists', 'options' => array('table' => 'y2m_tag','field' => 'tag_title',  'adapter' => $dbAdapter),),                
			),
        ));		 	
		
    }
}
