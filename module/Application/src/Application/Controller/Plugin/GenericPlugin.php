<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Http\PhpEnvironment\RemoteAddress;
 
class GenericPlugin extends AbstractPlugin {

	protected $ipaddress;
	protected $remote;
	protected $basePath;
	
	public function getBasePath(){
		$this->basePath = $this->getController()->getServiceLocator()->get('Request')->getBasePath();
		return $this->basePath;
	}

    public function getRemoteAddress(){
		$this->remote = new RemoteAddress;
        return $this->remote->getIpAddress();
    }
	
	public function getHeaderFiles(){
		$this->getViewHelper('HeadScript')->appendFile($this->getBasePath().'/js/jquery.min.js','text/javascript');
		$this->getViewHelper('HeadScript')->appendFile($this->getBasePath().'/js/jquery-ui.js','text/javascript');
		$this->getViewHelper('HeadScript')->appendFile($this->getBasePath().'/js/1625.js','text/javascript');			
		$this->getViewHelper('HeadLink')->appendStylesheet($this->getBasePath().'/css/jquery-ui.css','text/css');
	}
	
	public function getHeaderFile($file_header,$file_type){
		//echo $this->getBasePath().'/'.$file_type.'/'.$file_header.$file_type;
		if ( $file_type == 'js' ) $this->getViewHelper('HeadScript')->appendFile($this->getBasePath().'/'.$file_type.'/'.$file_header.'.'.$file_type,'text/javascript');
		else $this->getViewHelper('HeadLink')->appendStylesheet($this->getBasePath().'/'.$file_type.'/'.$file_header.'.'.$file_type,'text/css');
	}
	
	#to load the css and javascript need for particular action
	protected function getViewHelper($helperName)
	{
		return $this->getController()->getServiceLocator()->get('viewhelpermanager')->get($helperName);
	}

}