<?php
namespace Notification\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Notification\Model\Notification;
use Notification\Model\NotificationTable;
use Application\Controller\Plugin\PushNotifications;
use Notification\Model\PushNotificationDeviceTokenTable;
use \Exception;

use Zend\View\Model\ViewModel;
use Notification\Form\PushNotificationForm;       // <-- Add this import

class PushNotificationController extends AbstractActionController
{
    protected $pushNotificationDeviceTokenTable;
    public function PushNotifyAction(){

        $vm = new ViewModel();
        $request = $this->getRequest();
        $form = new PushNotificationForm();
        $vm->setVariable('form', $form);
        $request = $this->getRequest();
        $form->setData($request->getPost());
        if ($request->isPost()) {
            if ($form->isValid()) {
                if ($form->get('type')->getValue() == "ios") {
                    $pushNotifications = new PushNotifications();
                    $config = $this->getServiceLocator()->get('Config');
                    //$token = '76931ba93ad92e3e1291fb01503f13171ee105c9c0a99a5c863627868c1086b3';
                    $token = $form->get('token')->getValue();
                    $message = $form->get('message')->getValue();
                    $pushNotifications->APNS($config, $token, $message);
                } else if ($form->get('type')->getValue() == "android") {
                    $GoogleCloudMsgs = new PushNotifications();
                    //$token = 'ej9mLk5SC_c:APA91bGGVSX2wuGn_JWzS0zVTlkiP9YV1oa9UmYeX49um-_BCEcKNJ4UycrzV44iM8zuikizpcuF94IDVBA74RIGYjg2s0pMB5INcwDHHzxKG1_Pm023bLud6F9c_Md84JuyjOklHJ0H';
                    $token = $form->get('token')->getValue();
                    $message = $form->get('message')->getValue();
                    $GoogleCloudMsgs->GCM($token, $message);
                }
            }
        }
        return array('form' => $form);
    }
    public function PushNotifyRegisteredUsersAction(){

        $vm = new ViewModel();
        $request = $this->getRequest();
        $form = new PushNotificationForm();
        $vm->setVariable('form', $form);
        $request = $this->getRequest();
        $form->setData($request->getPost());
        if ($request->isPost()) {
            if ($form->isValid()) {
                $pushNotificationTokenData = $this->getPushNotificationDeviceTokenTable()->getAllPushNotificationTokens();
                $message = $form->get('message')->getValue();
                $config = $this->getServiceLocator()->get('Config');
                if (!empty($pushNotificationTokenData) && count($pushNotificationTokenData) >=1 ){
                    foreach($pushNotificationTokenData as $pushTokenData){
                        if ($pushTokenData->device_type == "ios") {
                            $pushNotifications = new PushNotifications();
                            //$token = '76931ba93ad92e3e1291fb01503f13171ee105c9c0a99a5c863627868c1086b3';
                            $token = $pushTokenData->device_token;
                            $pushNotifications->APNS($config, $token, $message);
                        } else if ($pushTokenData->device_type == "android") {
                            $GoogleCloudMsgs = new PushNotifications();
                            //$token = 'ej9mLk5SC_c:APA91bGGVSX2wuGn_JWzS0zVTlkiP9YV1oa9UmYeX49um-_BCEcKNJ4UycrzV44iM8zuikizpcuF94IDVBA74RIGYjg2s0pMB5INcwDHHzxKG1_Pm023bLud6F9c_Md84JuyjOklHJ0H';
                            $token = $pushTokenData->device_token;
                            $GoogleCloudMsgs->GCM($token, $message);
                        }

                    }
                }
            }
        }
        return array('form' => $form);
    }
    public function getPushNotificationDeviceTokenTable(){
        $sm = $this->getServiceLocator();
        return  $this->pushNotificationDeviceTokenTable = (!$this->pushNotificationDeviceTokenTable)?$sm->get('Notification\Model\PushNotificationDeviceTokenTable'):$this->pushNotificationDeviceTokenTable;
    }
}