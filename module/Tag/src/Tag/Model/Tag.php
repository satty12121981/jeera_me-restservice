<?php
namespace Tag\Model;

class Tag
{  
    public $tag_id;
    public $tag_title;   
	public $tag_added_ip_address;
	public $category_id; 
	 
	
/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->tag_id     = (isset($data['tag_id'])) ? $data['tag_id'] : null;
        $this->tag_title = (isset($data['tag_title'])) ? $data['tag_title'] : null;
		 $this->category_id = (isset($data['category_id'])) ? $data['category_id'] : null;
       
		$this->tag_added_ip_address  = (isset($data['tag_added_ip_address'])) ? $data['tag_added_ip_address'] : null;
		 
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
	public function selectFormatAllTag($data){
		//This function will return the format of  '0' => 'Apple', '1' => 'Mango' of tags					 
				$selectObject =array();				
				foreach($data as $tag){
					$selectObject[$tag->tag_id] = $tag->tag_title;
					//print_r($row);
				}		
			return $selectObject;	//return blank array
	}
 
	
}