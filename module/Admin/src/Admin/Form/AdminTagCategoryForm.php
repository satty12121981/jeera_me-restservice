<?php
namespace Admin\Form;
use Zend\Form\Form;
class AdminTagCategoryForm extends Form
{
    public function __construct($name = null) {        
        parent::__construct('admintag');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->add(array(
            'name' => 'tag_category_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));        
        $this->add(array(
            'name' => 'tag_category_title',
            'attributes' => array(
                'type'  => 'text',
            ),
        ));
        $this->add(array(
            'name' => 'tag_category_desc',
            'attributes' => array(
                'type'  => 'textarea',
            ),
        ));
        $this->add(array(
            'name' => 'tag_category_icon',
            'attributes' => array(
                'type'  => 'file',
            ),
        )); 
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
				'class' => 'alt_btn',
            ),
        ));
    }
}
