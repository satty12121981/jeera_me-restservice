<?php
namespace Notification\Form;
use Zend\Form\Form;

class PushNotificationRegisteredUsersForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('service');
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
