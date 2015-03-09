<?php
namespace Admin\Model; 
class Admin
{
    public $admin_id;
    public $admin_firstname;
    public $admin_lastname;
	public $admin_username;
    public $admin_email;
	public $admin_password;
    public $admin_about;
	public $admin_phone;
    public $admin_status;
	public $admin_added_date;
    public $admin_added_ip;
	public $admin_modified_date;
	public $admin_mdified_ip;   
	public $admin_picture;
    public function exchangeArray($data)
    {
        $this->admin_id     = (isset($data['admin_id'])) ? $data['admin_id'] : null;
        $this->admin_firstname = (isset($data['admin_firstname'])) ? $data['admin_firstname'] : null;
        $this->admin_lastname  = (isset($data['admin_lastname'])) ? $data['admin_lastname'] : null;
		$this->admin_username     = (isset($data['admin_username'])) ? $data['admin_username'] : null;
        $this->admin_email = (isset($data['admin_email'])) ? $data['admin_email'] : null;
        $this->admin_password  = (isset($data['admin_password'])) ? $data['admin_password'] : null;
		$this->admin_about     = (isset($data['admin_about'])) ? $data['admin_about'] : null;
        $this->admin_phone = (isset($data['admin_phone'])) ? $data['admin_phone'] : null;
        $this->admin_status  = (isset($data['admin_status'])) ? $data['admin_status'] : null;
		$this->admin_added_date     = (isset($data['admin_added_date'])) ? $data['admin_added_date'] : null;
        $this->admin_added_ip = (isset($data['admin_added_ip'])) ? $data['admin_added_ip'] : null;
		$this->admin_modified_date = (isset($data['admin_modified_date'])) ? $data['admin_modified_date'] : null;
		$this->admin_mdified_ip = (isset($data['admin_mdified_ip'])) ? $data['admin_mdified_ip'] : null;
		$this->admin_picture = (isset($data['admin_picture'])) ? $data['admin_picture'] : null;
    }
	    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}