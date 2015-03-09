<?php
namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserSuspendTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_suspend';

    public function __construct(Adapter $adapter)
    { 
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserSuspend());
        $this->initialize();
    }

    public function getUserSuspend($user_suspend_id)
    {
        $user_suspend_id  = (int) $user_suspend_id;
        $rowset = $this->select(array('user_suspend_id' => $user_suspend_id));
        $row = $rowset->current();
        return $row;
    }

	#It will fetch all data from table 
    public function fetchAll()
    { 
        $resultSet = $this->select();
        return $resultSet;
    }

    public function saveUserSuspend(UserSuspend $user)
    {
        $data = array(
            'user_suspend_id' => $user->user_suspend_id,
            'user_suspend_user_id' => $user->user_suspend_user_id,
            'user_suspend_start_date'  => $user->user_suspend_start_date,
            'user_suspend_end_date'  => $user->user_suspend_end_date,
            'user_suspend_added_ip_address'  => $user->user_suspend_added_ip_address,
			'user_suspend_reason'  => $user->user_suspend_reason,
			'user_suspend_status'  => $user->user_suspend_status,
			'user_suspend_problem_id'  => $user->user_suspend_problem_id,
			'user_suspend_other_reason'  => $user->user_suspend_other_reason,
        );

        $user_suspend_id = (int)$user->user_suspend_id;

        if ($user_suspend_id == 0) {
            $this->insert($data);
            return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getUserSuspend($user_suspend_id)) {
                $this->update($data, array('user_suspend_id' => $user_suspend_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function changeSuspendStatus($user_id,$status){
		$data['user_suspend_status'] = $status;
		$this->update($data, array('user_suspend_user_id' => $user_id));
		return true;
	}
    public function deleteUserSuspendByUser($user_id) {
        $this->delete(array('user_suspend_user_id' => $user_id));
    }


}

