<?php
namespace Tag\Model;

class TagCategory
{  
    public $tag_category_id;
    public $tag_category_title;
    public $tag_category_icon;
    public $tag_category_desc;
	public $tag_category_status;
    public function exchangeArray($data)
    {
        $this->tag_category_id     = (isset($data['tag_category_id'])) ? $data['tag_category_id'] : null;
        $this->tag_category_title = (isset($data['tag_category_title'])) ? $data['tag_category_title'] : null;
        $this->tag_category_icon = (isset($data['tag_category_icon'])) ? $data['tag_category_icon'] : null;
        $this->tag_category_desc  = (isset($data['tag_category_desc'])) ? $data['tag_category_desc'] : null;
		$this->tag_category_status  = (isset($data['tag_category_status'])) ? $data['tag_category_status'] : null;		 
    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
	public function selectFormatAllTagCategory($data){		
		$selectObject = array();				
		foreach($data as $tag_category){
			$selectObject[$tag_category->tag_category_id] = $tag_category->tag_category_title;			
		}		
		return $selectObject;
	}
 	
}