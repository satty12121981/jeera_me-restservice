<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupQuestionnaireOptionsTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_questionnaire_options';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Groups());
        $this->initialize();
    }
	public function AddOptions($option_data){
		return $this->insert($option_data);
	}	 
	public function getoptionOfOneQuestion($question_id){
		$select = new Select;
		$select->from('y2m_group_questionnaire_options');				 
		$select->where(array("question_id"=>$question_id));		
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();	
	}
	public function UpdateOptions($option,$option_id){	
		$data['option'] = $option;
		$this->update($data, array('option_id' => $option_id));
		return true;
	}
	public function DeleteOptions($question_id){
		return $this->delete(array('question_id' => $question_id));
	}
	public function getSelectedOptionValue($option_id){
		$select = new Select;
		$select->from('y2m_group_questionnaire_options')				 
				->columns(array("option"));				 
		$select->where(array("option_id"=>$option_id));		
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function getAnswerOptions($selected_options){
		$select = new Select;
		$select->from('y2m_group_questionnaire_options')				 
				->columns(array("option"));				 
		$select ->where->in('y2m_group_questionnaire_options.option_id',$selected_options);	
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
}