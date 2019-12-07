<?php
  ini_set("display_errors", "On");
  error_reporting(E_ALL);

  function sendPush($device_token, $title, $message, $agency_id=0, $package_id=0)
  {
    $url = "https://fcm.googleapis.com/fcm/send";
    $token = $device_token;
    $serverKey = 'AAAAyTmrYOc:APA91bEA7fefO5YzCIBdXj1oPTur5MP6G4-Oy3Iyje87uB9czBnM1SMZvze3pE3__4f8vVU1FRhG4yrYmpr5qaqNeTyb_a2sUiV5BRu1hB4HB14DgBKhIjQGUT9N5v323NRVCKfRs5n8';
    $title = $title;
    $body = $message;
    $notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1');
    if($agency_id > 0 && $package_id > 0)
    {
      $notification = array_merge($notification, array('agency_id' => $agency_id, 'package_id' => $package_id));
    }
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
  }

  $request = json_decode(file_get_contents("php://input"));

  if($request)
  {
    if($request->title == "")
    {
      echo "Invalid Push notification title";
    }
    elseif($request->message == "")
    {
      echo "Invalid Push notification message";
    }
    else
    {
      $title = $request->title;

      if(isset($_REQUEST['type'])) // type = 1 = General, type = 2 = Agency
      {
        $agency_id = 0;
        $package_id = 0;
        if($_REQUEST['type'] == 2) // Agency
        {
          if($request->agency_id)
            $agency_id = $request->agency_id;

          if($request->package_id)
            $package_id = $request->package_id;
        }

        //Get all users from Drupal db through following endpoint
        $url = 'http://52.53.72.145/take-off/web/getallusers?_format=json';
        $users = json_decode(file_get_contents($url));

        foreach($users as $user)
        {
          if($user->field_device_token != "" && $user->field_device_type != "")
          {
            $device_token = $user->field_device_token;
            $message = 'Hey '.$user->field_person_name.', '.$request->message;
            sendPush($device_token, $title, $message, $agency_id, $package_id);
          }
        }
      }
      else
      {
        $message = $request->message;
        $device_token = $request->device_token;
        sendPush($device_token, $title, $message);
      }
    }
  }
  else
  {
    echo "Invalid Parameters for Push notification";
  }
?>
