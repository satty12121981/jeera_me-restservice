<?php 
namespace Album\Model;
class GroupAlbum
{  
    public $album_id;
    public $group_id;
    public $creator_id;
	public $album_title;
	public $album_description;
    public $created_date;
	public $created_ip;
	public $album_status;
	public function exchangeArray($data)
    {
        $this->album_id     = (isset($data['album_id'])) ? $data['album_id'] : null;
        $this->group_id = (isset($data['group_id'])) ? $data['group_id'] : null;
        $this->creator_id  = (isset($data['creator_id'])) ? $data['creator_id'] : null;	  	
		$this->album_title  = (isset($data['album_title'])) ? $data['album_title'] : null;		
		$this->album_description  = (isset($data['album_description'])) ? $data['album_description'] : null;		
		$this->created_date  = (isset($data['created_date'])) ? $data['created_date'] : null;		
		$this->created_ip  = (isset($data['created_ip'])) ? $data['created_ip'] : null;
		$this->album_status  = (isset($data['album_status'])) ? $data['album_status'] : null;					
    }	 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}