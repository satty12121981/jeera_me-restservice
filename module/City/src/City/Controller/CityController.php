<?php
namespace City\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	
use Zend\View\Model\JsonModel; 
use Zend\Session\Container;   
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter; 
use City\Model\City;  
class CityController extends AbstractActionController
{
    protected $cityTable;	 
	protected $countryTable;  
    public function ajaxCitiesListAction(){
		$error = array();
		$request   = $this->getRequest();
		$cities = array(); 
		if ($request->isPost()){
			$post = $request->getPost();  
			$country = $post->get('country_id');  			 
			$cities = $this->getCityTable()->selectAllCity($country);
		}
		$result = new JsonModel(array( 'cities' => $cities));		 
		return $result;
	}
	public function getCityTable()
    {
		$sm = $this->getServiceLocator();       
		return $this->cityTable =(!$this->cityTable)? $this->cityTable = $sm->get('City\Model\CityTable'):$this->cityTable; 
    }
	public function getCountryTable()
    {
        $sm = $this->getServiceLocator();
		return $this->countryTable =(!$this->countryTable)?$sm->get('Country\Model\CountryTable'):$this->countryTable; 
    } 
	public function ajaxCitiesForAdminPlanetAction(){
		$error = array();
		$request   = $this->getRequest();
		$cities = array();
		if ($request->isPost()){
			$post = $request->getPost();
			$country = $post->get('country_id');			 
			$cities = $this->getCityTable()->selectAllCity($country);
		}
		$viewModel = new ViewModel(array( 'cities' => $cities));
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;
	}
	public function loadAllCitiesListAction(){

		$request = $this->getRequest();

		if($this->getRequest()->getMethod() == 'POST') {

			$cities = $this->getCityTable()->selectAllCityWithCountry();
			//print_r($cities);

			if(!empty($cities)) {
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['cities'] = $cities;
				$loadcitiescountries = array();
	            if ($dataArr){
	            	foreach($cities as $index => $citylist){
						$tempcites = explode(",", $citylist['city_name']);
						$arr_cities[0] = array();
						foreach($tempcites as $indexes => $splitlist){
							$arr_cities = array();
							$arr_cities = explode("|", $splitlist);
            				$objarr_city[] = array('city_id'=>$arr_cities[0],'city_name'=>$arr_cities[1]);
        				
            			}
            			$loadcitiescountries[] = array(
							'country_id' =>$citylist['country_id'],
							'country_title' =>$citylist['country_title'],
							'country_code' =>$citylist['country_code'],
							'cities' =>$objarr_city,
							);
						unset($objarr_city);
            		}

	            }
				echo json_encode($loadcitiescountries);
				exit;
			} else {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No more cities are available";
				echo json_encode($dataArr);
			}
		}
		else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}
}