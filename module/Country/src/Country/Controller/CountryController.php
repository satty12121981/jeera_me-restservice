<?php
namespace Country\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;  
use Zend\Session\Container;   
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter; 
use Country\Model\Country;  
class CountryController extends AbstractActionController
{
    protected $countryTable;   
     
	public function ajaxCountryListAction(){
		$request = $this->getRequest();
		$countries  = $this->getCountryTable()->fetchAll();
		$result = new JsonModel(array( 'countries' => $countries));
		//$viewModel->setTerminal($request->isXmlHttpRequest());
		return $result;
	}
	public function getCountryTable()
    {
        $sm = $this->getServiceLocator();
		return $this->countryTable = (!$this->countryTable)?$this->countryTable = $sm->get('Country\Model\CountryTable'):$this->countryTable;
    }
	public function countrylistAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$countries  = $this->getCountryTable()->fetchAll();
			if(!empty($countries)){
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['countries'] = $countries;            
				echo json_encode($dataArr);
				exit;
			}else{				 
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No more Countries are exist";
				echo json_encode($dataArr);
						 
			}
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}
}