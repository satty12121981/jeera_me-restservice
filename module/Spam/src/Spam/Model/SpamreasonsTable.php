<?php
namespace Spam\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class SpamreasonsTable extends AbstractTableGateway
{
    protected $table = 'y2m_spam_reasons'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Spamreasons());
        $this->initialize();
    }   
	public function getReasonsByType($type){
		$select = new Select;
		$select->from('y2m_spam_reasons')
				->where(array('content_type' =>$type)); 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->toArray();
	}
	public function getReasonDetails($reason_id){
		$select = new Select;
		$select->from('y2m_spam_reasons')
				->where(array('reason_id' =>$reason_id)); 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->current();
	}
	public function getReasonDetailsWithTypeAndReasonId($type,$reason_id){
		$select = new Select;
		$select->from('y2m_spam_reasons')
			->where(array('reason_id' =>$reason_id,'content_type' =>$type));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
	}
}