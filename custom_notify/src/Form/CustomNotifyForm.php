<?php

namespace Drupal\custom_notify\Form;

/**
 * @file
 * Provides custom_notify functionality.
 */

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\EntityQueryInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Query\Sql\Query;




/**
 * Implements the youtube subscriber button form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class CustomNotifyForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_notify_form';
  }

  /**
   * {@inheritdoc}
   */
   public function buildForm(array $form, FormStateInterface $form_state) {
		$ids = \Drupal::entityQuery('user')
		->condition('status', 1)
		->condition('roles', 'travel_agency')
		->execute();
		$users = User::loadMultiple($ids);
		foreach($users as $user){
			$username = $user->get('name')->value;
			$uid = $user->get('uid')->value;
			$userlist[$uid] = $username;
		}
		$form['notification_type'] = array(
			'#type' => 'radios',
			'#title' => t('Notification Type'),
			'#options' => array( 1 => 'General Notification', 2 => 'Agency notification'),
			'#default_value' => 1,
			'#required' => TRUE,
		);
		
		$form['agency_id'] = array(
			'#type' => 'select', 
			'#title' => t('Agency'), 
			'#options' => $userlist,
			);

        
		$nids = db_select('node', 'n')
			->fields('n', array('nid'))
			->condition('n.type', 'tour_package')
			->execute()
			->fetchAll();         

        foreach($nids as $nids_array){
			$nid_number = $nids_array->nid;
			$node_load = Node::load($nid_number);
			$title[$nid_number] = $node_load->getTitle();
		}

		$form['package_id'] = array(
			'#type' => 'select', 
			'#title' => t('Package'), 
			'#options' => $title,
			);

		$form['push_notify_title'] = array(
		  '#type' => 'textfield',
		  '#title' => t('Title'),
		  //'#attributes' => array('placeholder' => t('Search'),),
		  '#required' => TRUE,
		);

		$form['push_notify_message'] = array(
		  '#type' => 'textarea',
		  '#title' => t('Message'),
		  //'#attributes' => array('placeholder' => t('Search'),),
		  '#required' => TRUE,
		);		
					
		$form['submit'] = array(
		  '#type' => 'submit',
		  '#value' => t('Notify'),
		);
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
	
	$notification_type = $form_state->getValue('notification_type');
	$ids = \Drupal::entityQuery('user')
		->condition('status', 1)
		->condition('roles', 'customer')
		->execute();
	$users = User::loadMultiple($ids);

	foreach($users as $user_array){
		$device_token = trim($user_array->get('field_device_token')->value);

	if($notification_type == '2'){
	
		global $base_url;
		//echo $base_url; exit;
		$agency_id = $form_state->getValue('agency_id');
		$package_id = $form_state->getValue('package_id');
		$push_notify_title = $form_state->getValue('push_notify_title');
		$push_notify_message = $form_state->getValue('push_notify_message');
		

		$url = "https://fcm.googleapis.com/fcm/send";
		$token = $device_token;//'eZWnN_A9fRk:APA91bF2ZOkmc4vRU7EEHNNyL7R_-O4lvoSbKWbjs7bpU6r02dnpbMMzgbWOyMfdmvmlUyUx9kaIQWjRRefoUQEzKEfibjtiiR46YvguuxsMTqy9JPpoLVvBkN2Ii99GJxcCRoCuOL-D';
		$serverKey = 'AAAAyTmrYOc:APA91bEA7fefO5YzCIBdXj1oPTur5MP6G4-Oy3Iyje87uB9czBnM1SMZvze3pE3__4f8vVU1FRhG4yrYmpr5qaqNeTyb_a2sUiV5BRu1hB4HB14DgBKhIjQGUT9N5v323NRVCKfRs5n8';
		$title = $form_state->getValue('push_notify_title');
		$body = $form_state->getValue('push_notify_message');
		$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
		$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
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
		//$url_develop = $base_url.'/push-notify?type='.$notification_type.'&agency_id='.$agency_id.'&package_id='.$package_id.'&push_notify_title='.$push_notify_title.'&push_notify_message='.$push_notify_message;
		//$response = new RedirectResponse($url_develop);
		//$response->send();
		
		
	}elseif($notification_type == '1'){
	
		global $base_url;
				//echo $base_url; exit;
		$push_notify_title = $form_state->getValue('push_notify_title');
		$push_notify_message = $form_state->getValue('push_notify_message');
		$url = "https://fcm.googleapis.com/fcm/send";
		$token = $device_token;//'eZWnN_A9fRk:APA91bF2ZOkmc4vRU7EEHNNyL7R_-O4lvoSbKWbjs7bpU6r02dnpbMMzgbWOyMfdmvmlUyUx9kaIQWjRRefoUQEzKEfibjtiiR46YvguuxsMTqy9JPpoLVvBkN2Ii99GJxcCRoCuOL-D';
		$serverKey = 'AAAAyTmrYOc:APA91bEA7fefO5YzCIBdXj1oPTur5MP6G4-Oy3Iyje87uB9czBnM1SMZvze3pE3__4f8vVU1FRhG4yrYmpr5qaqNeTyb_a2sUiV5BRu1hB4HB14DgBKhIjQGUT9N5v323NRVCKfRs5n8';
		$title = $form_state->getValue('push_notify_title');
		$body = $form_state->getValue('push_notify_message');
		$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
		$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
		$json = json_encode($arrayToSend);
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='. $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,

		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		$response = curl_exec($ch);
		//Close request
		if ($response === FALSE) {
		  die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
		//$url_develop = $url_develop = $base_url.'/push-notify?type='.$notification_type.'&push_notify_title='.$push_notify_title.'&push_notify_message='.$push_notify_message;
		//$response = new RedirectResponse($url_develop);
		//$response->send();
		
	}		
				//echo '<pre>';
		//print_r(trim($user_array->get('field_device_token')->value));
		//exit;	
		//$username = $user->get('name')->value;
		//$uid = $user->get('uid')->value;
		//$userlist[$uid] = $username;
		//echo '<pre>';
		//print_r($user_array);
	}
	
//	exit;
	

	return;
  }
    
}
