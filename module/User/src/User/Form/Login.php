<?php
namespace User\Form;
use Zend\Form\Form;
class Login extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user');
        $this->setAttribute('method', 'post');
		
		 
        $this->add(array(
            'name' => 'user_email',
            'type' => 'Text',
            'options' => array(
                'label' => 'Email:',
				
            ),
			'attributes' => array(
                'placeholder' => 'mail@yourdomain', //set selecarray()ted to '1'
				'id' => 'user_email',
				'size'  => '100',
				
            ) 
        ));
        $this->add(array(
            'name' => 'user_password',
            'type' => 'Password',
            'options' => array(
                'label' => 'Password',
            ),
			'attributes' => array(
                'placeholder' => 'Password', //set selecarray()ted to '1'
				'id' => 'user_password',
				'size'  => '100',
				
            ) 
        ));
		 
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Login',
                'id' => 'submitbutton',
            ),
        ));
    }
}