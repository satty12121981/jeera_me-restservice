<?php
namespace Notification\Form;
use Zend\Form\Form;

class PushNotificationForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('service');

         $this->add(array(
             'name' => 'type',
             'type' => 'text',
             'options' => array(
                 'label' => 'Type( ios / android )::',
             ),
         ));
         $this->add(array(
             'name' => 'token',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Token::',
             ),
         ));
         $this->add(array(
             'name' => 'message',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Message::',
             ),
         ));
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Push',
                 'id' => 'submitbutton',
             ),
         ));
     }
 }
