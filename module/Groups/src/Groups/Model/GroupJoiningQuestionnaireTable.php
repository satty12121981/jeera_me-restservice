<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupJoiningQuestionnaireTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_joining_questionnaire';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Groups());
        $this->initialize();
    }
	public function AddQuestion($question_data){
		$this->insert($question_data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	public function getQuestionnaire($group_id){
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')				 
				;				 
		$select->where(array('group_id'=>$group_id));		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}
	public function getQuestionFromQuestionId($question_id){
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')				 
				;				 
		$select->where(array('questionnaire_id'=>$question_id));		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function updateQuestion($data,$question_id){
		return $this->update($data, array('questionnaire_id' => $question_id));
	}
	public function DeleteQuestions($question_id){
		return $this->delete(array('questionnaire_id' => $question_id));
	}
	public function getQuestionnaireCount($group_id){
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')	
			   ->columns(array(new Expression('COUNT(y2m_group_joining_questionnaire.questionnaire_id) as  question_count')))		
				;				 
		$select->where(array('group_id'=>$group_id));		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function getQuestionnaireWithPagination($group_id,$offset,$page){
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')				 
				;				 
		$select->where(array('group_id'=>$group_id));	
		$select->limit($page);
		$select->offset($offset);	
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}
	public function getQuestionFromQuestionIdAndGroupId($question_id,$group_id){	
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')				 
				;				 
		$select->where(array('questionnaire_id'=>$question_id,"group_id"=>$group_id));		
		$statement = $this->adapter->createStatement();
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function RemoveAllGroupQuestionnairesAndAnswers($group_id){
		$sql = "DELETE FROM y2m_group_questionnaire_answers WHERE y2m_group_questionnaire_answers.group_id = ".$group_id;	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_group_questionnaire_options WHERE question_id IN (SELECT  questionnaire_id FROM  y2m_group_joining_questionnaire WHERE y2m_group_joining_questionnaire.group_id = ".$group_id.")";	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_group_joining_questionnaire WHERE y2m_group_joining_questionnaire.group_id = ".$group_id;	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		return true;
	}
	public function getQuestionnaireArray($group_id){
		$select = new Select;
		$select->from('y2m_group_joining_questionnaire')				 
				;				 
		$select->where(array('group_id'=>$group_id));		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();	
	}
}