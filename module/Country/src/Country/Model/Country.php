<?php 
namespace Country\Model;
class Country
{  
    public $country_id;
    public $country_title;
    public $country_code;
	public $country_added_ip_address_address;
	public $country_added_timestamp;
	public $country_status;
	public $country_modified_timestamp;
	public $country_modified_ip_address;
	public $country_code_googlemap; 
	public function exchangeArray($data)
    {
        $this->country_id     = (isset($data['country_id'])) ? $data['country_id'] : null;
        $this->country_title = (isset($data['country_title'])) ? $data['country_title'] : null;
        $this->country_code  = (isset($data['country_code'])) ? $data['country_code'] : null;
		$this->country_added_ip_address  = (isset($data['country_added_ip_address'])) ? $data['country_added_ip_address'] : null;
		$this->country_added_timestamp  = (isset($data['country_added_timestamp'])) ? $data['country_added_timestamp'] : null;
		$this->country_status  = (isset($data['country_status'])) ? $data['country_status'] : null;
		$this->country_modified_timestamp  = (isset($data['country_modified_timestamp'])) ? $data['country_modified_timestamp'] : null;
		$this->country_modified_ip_address  = (isset($data['country_modified_ip_address'])) ? $data['country_modified_ip_address'] : null;		
		$this->country_code_googlemap  = (isset($data['country_code_googlemap'])) ? $data['country_code_googlemap'] : null;
    } 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

  
	
}