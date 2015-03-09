<?php

namespace User\Form;
use Zend\InputFilter\InputFilter;

class LoginFilter extends InputFilter
{
    public function __construct() {
    
        $this->add(array(
            'name' => 'user_email',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
			'validators' => array(
        					array('name' => 'EmailAddress'),
							array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 1, 'max' => 100,),
                ),
			),
        ));

        $this->add(array(
            'name' => 'user_password',
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
