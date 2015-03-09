<?php
namespace Like\Form;
use Zend\Form\Form;

class LikeForm extends Form
{
    public function __construct($like_system_type_id,$like_refer_id)
    {
        // we want to ignore the name passed
        parent::__construct('comment');		 
       
	    $this->add(array(
            'name' => 'like_system_type_id',
            'attributes' => array(
                'type'  => 'hidden',
				'value'  => $like_system_type_id,
            ),
        ));
		
		$this->add(array(
            'name' => 'like_refer_id',
            'attributes' => array(
                'type'  => 'hidden',
				'value'  => $like_refer_id,
            ),
        ));
		
		$this->add(array(
            'name' => 'like_status',
            'attributes' => array(
                'type'  => 'hidden',
				'value'  => 1,
            ),
        ));

    }
}
