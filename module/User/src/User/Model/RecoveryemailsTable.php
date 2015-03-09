<?php 
 
namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class RecoveryemailsTable extends AbstractTableGateway
{
    protected $table = 'y2m_recoveryemails_enc';

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Recoveryemails());
        $this->initialize();
    }
    public function fetchAll(){ return $this->select(); }
    public function saveRecovery(Recoveryemails $recoveryemails){
       $data = array(
            'user_id' => $recoveryemails->user_id,
            'user_email'  => $recoveryemails->user_email,
			'secret_code'  => $recoveryemails->secret_code,
			'status'  => $recoveryemails->status,						
        );
		$this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();			 
    }
	public function updateRecovery($data,$id){$this->update($data, array('id' => $id));	}
	public function checkResetRequest($code,$user){ 
		$rowset = $this->select(array('secret_code'=>$code));
        return $rowset->current(); 		 
	}
	public function ResetAllActiveRequests($user_id){
		$data['status'] = 1;
		$this->update($data, array('user_id' => $user_id));
	}
	 

}
