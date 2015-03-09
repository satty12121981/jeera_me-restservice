<?php
namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserBlockTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_block';

    public function __construct(Adapter $adapter)
    { 
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserBlock());
        $this->initialize();
    }

    public function getUserBlock($user_block_id)
    {
        $user_block_id  = (int) $user_block_id;
        $rowset = $this->select(array('user_block_id' => $user_block_id));
        $row = $rowset->current();
        return $row;
    }

	#It will fetch all data from table 
    public function fetchAll()
    { 
        $resultSet = $this->select();
        return $resultSet;
    }

    public function saveUserBlock(UserBlock $user)
    {
        $data = array(
            'user_block_id' => $user->user_block_id,
            'user_block_user_id' => $user->user_block_user_id,
            'user_block_problem_id'  => $user->user_block_problem_id,
            'user_block_reason'  => $user->user_block_reason,
            'user_block_other_reason'  => $user->user_block_other_reason,
			'user_block_added_ip_address'  => $user->user_block_added_ip_address,			
			'user_block_status'  => $user->user_block_status,		 
        );

        $user_block_id = (int)$user->user_block_id;

        if ($user_block_id == 0) {
            $this->insert($data);
            return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getUserBlock($user_block_id)) {
                $this->update($data, array('user_block_id' => $user_block_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function changeBlockStatus($user_id,$status){
		$data['user_block_status'] = $status;
		$this->update($data, array('user_block_id' => $user_id));
		return true;
	}
    public function deleteUserBlockByUser($user_id) {
        $this->delete(array('user_block_id' => $user_id));
    }


}

