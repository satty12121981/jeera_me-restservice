<?php 
namespace Album\Model;
class GroupEventAlbum
{  
    public $event_album_id;
    public $event_id;
    public $album_id;
	public $assignedby; 
	public function exchangeArray($data)
    {
        $this->event_album_id     = (isset($data['event_album_id'])) ? $data['event_album_id'] : null;
        $this->event_id = (isset($data['event_id'])) ? $data['event_id'] : null;
        $this->album_id  = (isset($data['album_id'])) ? $data['album_id'] : null;	  	
		$this->assignedby  = (isset($data['assignedby'])) ? $data['assignedby'] : null;			 
    }	 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}