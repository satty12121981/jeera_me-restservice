<?php
namespace Admin\Form;
use Zend\Form\Form;
class Login extends Form
{
    public function __construct($name = null)
    {
         
        parent::__construct('admin');
        $this->setAttribute('method', 'post');	 
        $this->add(array(
            'name' => 'admin_username',
            'type' => 'Text',
            'options' => array(
                'label' => 'Username:',			
            ),
			'attributes' => array(
                'placeholder' => 'Username', //set selecarray()ted to '1'
				'id' => 'admin_username',
				) 
        ));
        $this->add(array(
            'name' => 'admin_password',
            'type' => 'Password',
            'options' => array(
                'label' => 'Password',
            ),
			'attributes' => array(
                'placeholder' => 'Password', //set selecarray()ted to '1'
				'id' => 'admin_password',
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