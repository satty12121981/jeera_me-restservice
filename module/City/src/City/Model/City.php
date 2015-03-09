<?php 
namespace City\Model;
class City
{  
    public $city_id;
    public $country_id;
    public $name;
	public $status;	
	public function exchangeArray($data)
    {
        $this->city_id     = (isset($data['city_id'])) ? $data['city_id'] : null;
        $this->country_id = (isset($data['country_id'])) ? $data['country_id'] : null;
        $this->name  = (isset($data['name'])) ? $data['name'] : null;	  	
		$this->status  = (isset($data['status'])) ? $data['status'] : null;			
    }	 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}