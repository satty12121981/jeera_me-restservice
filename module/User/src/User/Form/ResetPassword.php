<?php 
namespace User\Form;
use Zend\Form\Form;
class ResetPassword extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user');
        $this->setAttribute('method', 'post');
		
		$this->add(array(
     'type' => 'Zend\Form\Element\Csrf',
     'name' => 'csrf',
     'options' => array(
             'csrf_options' => array(
                     'timeout' => 600,
					 'salt' => 'unique'
             )
     	)
 	));
         $this->add(array(
            'name' => 'user_password',
            'type' => 'Password',
            'options' => array(
                 
            ),
			'attributes' => array(               
				'id' => 'user_password',
				'size'  => '100',				
            ) 
        ));
		
		$this->add(array(
            'name' => 'user_retype_password',
            'type' => 'Password',
            'options' => array(
                
            ),
			'attributes' => array(               
				'id' => 'user_retype_password',
				'size'  => '100',				
            ) 
        ));
          $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Send',
                'id' => 'submitbutton',
				'class' => 'next_button blue-butn',
            ),
        ));
    }
}