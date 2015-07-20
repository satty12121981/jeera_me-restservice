<?php
namespace Spam\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class SpamreportsTable extends AbstractTableGateway
{
    protected $table = 'y2m_spam_reports'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Spamreports());
        $this->initialize();
    }   
	public function checkAlreadyRequested($type,$content_id,$user_id){
		$select = new Select;
		$select->from('y2m_spam_reports')
				->where(array('content_type' =>$type,'content_id'=>$content_id,'reporter_id'=>$user_id)); 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->toArray();
	}
	 public function saveSpamReports(Spamreports $Spamreports){
       $data = array(
            'content_id' => $Spamreports->content_id,
            'content_type'  => $Spamreports->content_type,
			'reason_id'  => $Spamreports->reason_id,
			'report_comment'  => $Spamreports->report_comment,
			'reporter_id'  => $Spamreports->reporter_id,
		 
		);
       $this->insert($data);
    }
}