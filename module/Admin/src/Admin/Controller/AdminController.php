<?php
namespace Admin\Controller; 
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\View\Model\ViewModel;
use Zend\Crypt\BlockCipher;	#for encryption
use Zend\Crypt\Password\Bcrypt;	#for password Encryption
use Admin\Auth\BcryptDbAdapter as AuthAdapter;
#session
use Zend\Session\Container; // We need this when using sessions     
use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Admin\Form\Login; 
use Admin\Form\LoginFilter;
use Admin\Model\Admin;
use Zend\Session\Container as SessionContainer;
class AdminController extends AbstractActionController
{
    protected $adminTable;
    public function indexAction()
    {
		$error = array();
		$success = array();
		$vm = new ViewModel(); 
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		 
		if ($auth->hasIdentity()) {  
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;
			return $vm;		
        }else{			
			 return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));		
		}	
    }	
	public function loginAction()
    {
		$vm = new ViewModel(); 
		$form = new Login();
		$error = array();
		$this->layout('layout/admin_login');
		$vm->setVariable('form', $form);
		$request = $this->getRequest();
		if ($request->isPost()) {	
			$form->setInputFilter(new LoginFilter());			
			$form->setData($request->getPost()); 
			if ($form->isValid()) {	
				$data = $request->getPost();
				$dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
				$authAdapter = new AuthAdapter($dbAdapter); 
				$authAdapter
					->setTableName('y2m_admin')
					->setIdentityColumn('admin_username')
					->setCredentialColumn('admin_password');					
				$authAdapter
					->setIdentity(addslashes($data['admin_username']))
					->setCredential($data['admin_password']);				
				$result = $authAdapter->authenticate();
				if (!$result->isValid()) {					
					$error[] = "Invalid Email or Password"; 	
				} else {
					$auth = new AuthenticationService();
					$auth->setStorage(new SessionStorage('admin'));
					$storage = $auth->getStorage();
					$storage->write($authAdapter->getResultRowObject(
						null,
						'admin_password'
					));
					//$authNamespace = new Container(Session::NAMESPACE_DEFAULT);
					//$authNamespace->getManager()->rememberMe(20000);					   
					$error = 0;
					$msg = '';
					return $this->redirect()->toRoute('jadmin', array('action' => 'index'));		
				}
			}
		}
		$vm->setVariable('error', $error);
		return $vm;		
     }
	public function logoutAction(){
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));		
	  	$auth->clearIdentity();
		unset($_SESSION);	  
		$this->flashmessenger()->addMessage("You've been logged out");		 
		return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));
	}
	
	public function getAdminTable()
    {
        $sm = $this->getServiceLocator();
		 return $this->adminTable= (!$this->adminTable)?$sm->get('Admin\Model\AdminTable'):$this->adminTable; 
    }
	public function creatAdmin(){
		$bcrypt = new Bcrypt();
		$data['admin_password'] = $bcrypt->create('123abc456d');
		$data['admin_username'] = 'admin';
		$data['admin_firstname'] = 'Super Admin';
		$data['admin_lastname'] = '';
		$data['admin_about'] = '';
		$data['admin_phone'] = '';		 
		$data['admin_added_ip'] = '';
		$data['admin_mdified_ip'] = '';
		$data['admin_picture'] = '';
		$data['admin_status'] = 1;
		$data['admin_email'] = 'admin@jeera.com';
		$admin = new Admin();
		$admin->exchangeArray($data);
		$insertedUserId = $this->getAdminTable()->saveAdmin($admin);
	}
}