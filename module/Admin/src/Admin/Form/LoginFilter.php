<?php

namespace Admin\Form;
use Zend\InputFilter\InputFilter;

class LoginFilter extends InputFilter
{
    public function __construct() {
    
        $this->add(array(
            'name' => 'admin_username',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
			'validators' => array(        					 
							array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 3, 'max' => 100,),
                ),
			),
        ));

        $this->add(array(
            'name' => 'admin_password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 100,),
                ),
            ),
        ));
		
    }
}
