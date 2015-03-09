<?php
namespace Admin\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet; 
class AdminTable extends AbstractTableGateway
{
    protected $table = 'y2m_admin';
    public function __construct(Adapter $adapter)
    { 
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Admin());
        $this->initialize();
    }
    public function fetchAll()
    { 
        $resultSet = $this->select();
        return $resultSet;
    }
	 public function getAdmin($admin_id){
        $admin_id  = (int) $admin_id;
        $rowset = $this->select(array('admin_id' => $admin_id));
        $row = $rowset->current();         
        return $row;
    }
	public function saveAdmin(Admin $admin){
       $data = array(
            'admin_firstname' => $admin->admin_firstname,
            'admin_lastname'  => $admin->admin_lastname,
			'admin_username'  => $admin->admin_username,
			'admin_email'  => $admin->admin_email,
			'admin_password' =>$admin->admin_password,
			'admin_about'  => $admin->admin_about,
			'admin_phone'  => $admin->admin_phone,
			'admin_status'  => $admin->admin_status,
			'admin_added_date'  => $admin->admin_added_date,
			'admin_added_ip'  => $admin->admin_added_ip,
			'admin_modified_date'  => $admin->admin_modified_date,
			'admin_mdified_ip'  => $admin->admin_mdified_ip,
			'admin_picture'  => $admin->admin_picture,
			 
        );
		$admin_id = (int)$admin->admin_id;
        if ($admin_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
						 		 
        } else {
            if ($this->getAdmin($admin_id)) {
                $this->update($data, array('admin_id' => $admin_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
}

