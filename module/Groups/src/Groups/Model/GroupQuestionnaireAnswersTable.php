<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupQuestionnaireAnswersTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_questionnaire_answers';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Groups());
        $this->initialize();
    }
	public function AddAnswer($answer_data){
		return $this->insert($answer_data);
	}	 
	public function getAnswerOfOneQuestion($group_id,$question_id,$user_id){
		$select = new Select;
		$select->from('y2m_group_questionnaire_answers')				 
				;				 
		$select->where(array('group_id'=>$group_id,"question_id"=>$question_id,"added_user_id"=>$user_id));		
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function UpdateAnswer($answer_id,$answer){	
		$data['answer'] = $answer;
		$this->update($data, array('answer_id' => $answer_id));
		return true;
	}
	public function  deleteAnswers($question_id){
		return $this->delete(array('question_id' => $question_id));
	}
	public function getAllQuestionswithanswers($group_id,$user_id){
		$select = new Select;
		$select->from('y2m_group_questionnaire_answers')
			   ->columns(array("answer","selected_options"))
			   ->join("y2m_group_joining_questionnaire","y2m_group_questionnaire_answers.question_id = y2m_group_joining_questionnaire.questionnaire_id",array("question","answer_type"))
			   ->where(array("y2m_group_questionnaire_answers.group_id"=>$group_id,"y2m_group_questionnaire_answers.added_user_id"=>$user_id,"y2m_group_joining_questionnaire.question_status"=>'active'));
		$statement = $this->adapter->createStatement();	
		//echo $select->getSqlString();exit;		
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
			   
	}
	public function deleteUserAnswersOfGroup($group_id,$user_id){
		return $this->delete(array('group_id' => $group_id,'added_user_id'=>$user_id));
	}
}