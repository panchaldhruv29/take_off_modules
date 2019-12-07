<?php  

/**  
 * @file  
 * Contains Drupal\welcome\Form\CustomfirebaseForm.  
 */  

namespace Drupal\custom_firebase\Form;  

use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  
use Drupal\user\Entity\User;

class CustomfirebaseForm extends ConfigFormBase {  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'custom_firebase.settings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'custom_firebase_form';  
  }  

/**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('custom_firebase.settings');  

    $form['firebase_sender_id'] = [  
      '#type' => 'textfield',  
      '#title' => $this->t('Sender ID'),  
      '#description' => $this->t('Firebase Sender ID.'),  
      '#default_value' => $config->get('firebase_sender_id'),  
    ]; 
    
    $form['firebase_key'] = [  
      '#type' => 'textfield',  
      '#title' => $this->t('Server Key'),  
      '#description' => $this->t('Firebase console key.'),  
      '#default_value' => $config->get('firebase_key'),  
    ]; 
    
    $form['firebase_title'] = [  
      '#type' => 'textfield',  
      '#title' => $this->t('Title'),  
      '#description' => $this->t('Firebase title.'),  
      '#default_value' => $config->get('firebase_title'),  
    ];     
    
    $form['firebase_message'] = [  
      '#type' => 'textarea',  
      '#title' => $this->t('Message'),  
      '#description' => $this->t('Message to send in notification to users.'),  
      '#default_value' => $config->get('firebase_message'),  
    ];  
    	  
    $form['actions'] = [
      '#type' => 'actions',
    ];
    
	$form['submit'] = [  
		'#type' => 'submit',  
		'#value' => t('Send'),  
	];   

    return parent::buildForm($form, $form_state);  
  }
  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  
	
	$users_ids = \Drupal::entityQuery('user')
				->condition('status', 1)
				->condition('roles', 'customer')
				->execute();
	$users_details = User::loadMultiple($users_ids);
	foreach($users_details as $users_details_array){
		$username_name_array = $users_details_array->get('name')->getValue();
		$user_name = $username_name_array[0]['value'];
		$serverKey = $form_state->getValue('firebase_key');
		$title = $form_state->getValue('firebase_title');
		$body = $form_state->getValue('firebase_message');
		$device_type_array = $users_details_array->get('field_device_type')->getValue(); //1. Iphone, 2. Android
		$device_token_array = $users_details_array->get('field_device_token')->getValue();		
		$device_type = $device_type_array[0]['value'];
		$device_token = $device_token_array[0]['value'];
		
		// Confirm that device token field and device type are not null
		
		if(!empty($device_token) && !empty($device_type)){
			// API call for firebase. 
			// Note: you cannot put below code in a function because we have extended Formbase class. Therefore you have to keep it like this.		
			$url = "https://fcm.googleapis.com/fcm/send";
			$notification = array('title' =>$user_name.'-'.$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
			$arrayToSend = array('to' => $device_token, 'notification' => $notification,'priority'=>'high');
			$json = json_encode($arrayToSend);
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Authorization: key='. $serverKey;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
			//Send the request
			$response = curl_exec($ch);
			//Close request
			if ($response === FALSE) {
				die('FCM Send Error: ' . curl_error($ch));
			}
			curl_close($ch);	
		}	
	}
	$this->config('custom_firebase.settings')  
      ->set('firebase_message', $form_state->getValue('firebase_message'))  
      ->set('firebase_key', $form_state->getValue('firebase_key'))
      ->set('firebase_sender_id', $form_state->getValue('firebase_sender_id'))
      ->set('firebase_title', $form_state->getValue('firebase_title'))  
      ->save();      
            
  }  
    
}  
