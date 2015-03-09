<?php
namespace City\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class CityTable extends AbstractTableGateway
{
    protected $table = 'y2m_city'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new City());
        $this->initialize();
    }   
	public function selectAllCity($country_id){
		$data =  $select = new Select();
        $select->from($this->table);
		$select->where(array('country_id = '.$country_id));
		$statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);        
		//echo $select->getSqlString();exit;
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();  
	}	
	public function selectFormatAllCity($country_id){
		$data =  $select = new Select();
        $select->from($this->table);
		$select->where(array('country_id = '.$country_id));		
        $resultSet = $this->selectWith($select);
        $resultSet->buffer();
        $data = $resultSet;;		
		$selectObject =array();
		foreach($data as $city){
			$selectObject[$city->city_id] = $city->name;			
		}		
		return $selectObject;
	} 
	public function getCountOfAllCities($country_id,$search){
		$select = new Select;
		$select->from('y2m_city')		
			   ->columns(array(new Expression('COUNT(y2m_city.city_id) as city_count')))
			   ->where(array("y2m_city.country_id"=>$country_id));
		if($search!=''){
			$select->where->like('y2m_city.name',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return  $resultSet->current()->city_count;
	}
	public function getAllCityList($country_id,$limit,$offset,$field="country_id",$order='ASC',$search=''){ 
		$select = new Select;	 
		$select->from('y2m_city')
				->columns(array('*'))
				 ->where(array("y2m_city.country_id"=>$country_id));
		$select->limit($limit);
		$select->offset($offset);
		$select->order($field.' '.$order);
		if($search!=''){
			$select->where->like('y2m_city.name',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());			 	
		return  $resultSet->buffer();
	}
	public function getCity($city_id)
    {
        $city_id  = (int) $city_id;
        $rowset = $this->select(array('city_id' => $city_id));
        $row = $rowset->current();
        return $row;
    }
	public function saveCity(City $city)
    {
       $data = array(
            'country_id' => $city->country_id,
            'name'  => $city->name,			 
        );
        $city_id = (int)$city->city_id;
        if ($city_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getCity($city_id)) {
                $this->update($data, array('city_id' => $city_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function updateCity($data,$city_id){
		$this->update($data, array('city_id' => $city_id));
	}
	public function selectAllCityWithCountry(){
        $data = $select = new Select();

        $expression = new Expression(
            "GROUP_CONCAT(city_id,'|',name)"
        );

        $select->from($this->table);
        $select->columns(array('city_name'=>$expression));
        $select->join(array('y2m_country'=>'y2m_country'),'y2m_city.country_id = y2m_country.country_id',array('country_id','country_code','country_title'));
        $select->group('y2m_country.country_id');

        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);        
        //echo $select->getSqlString();exit;
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();  
    } 
}