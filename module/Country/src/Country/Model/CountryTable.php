<?php
namespace Country\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class CountryTable extends AbstractTableGateway
{
    protected $table = 'y2m_country'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Country());
        $this->initialize();
    }	 
    public function fetchAll(Select $select = null)
    {
		if (null === $select)
        $select = new Select();
        $select->from($this->table);      
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);        
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();      
    } 
	public function getCountry($country_id)
    {
		$select = new Select(); 
		$select->from($this->table);
		$select->where("country_id",$country_id);
		$statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);        
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->current();    
    }
}