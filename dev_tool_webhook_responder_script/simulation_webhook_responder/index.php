<?php

require('vendor/autoload.php');

require('config.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\LineFormatter;

$message = file_get_contents("php://input"); //read the HTTP body.

// Set the format
$output = "%channel%.%level_name%: %message%";
$formatter = new LineFormatter($output);

// Setup the logger
$logger = new Logger('webhook_responder');
$syslogHandler = new SyslogUdpHandler($syslog_server, $syslog_server_port);
$syslogHandler->setFormatter($formatter);
$logger->pushHandler($syslogHandler);

// Use the new logger
$logger->addInfo(serialize($message));

/* $log = new Logger('name');
$log->pushHandler(new StreamHandler('C:\wamp64\www\simulation_no_auth\log.txt', Logger::INFO));
$log->info($message); */

$data=array('grant_type' => 'client_credentials' , "client_id" => $client_id , "client_secret" => $client_secret);

$webhook_notification_data = json_decode($message);

$task = $webhook_notification_data->data->task;
//$logger->addInfo(" Task ".$task);

$object_variables = get_object_vars($webhook_notification_data->data);

//$logger->addInfo(" Object variables ".serialize($object_variables));

$request_variables = array();
foreach ($object_variables as $key=>$val ) {
    if ($key != "task" && $key != "message" ) {
        $request_variables[$key] = $val;
    }
}

//$logger->addInfo(" Request variables ".serialize($request_variables));

try 
	{
 	$ch = curl_init($server);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$token_request=curl_exec ($ch);

	$status = curl_getinfo($ch); 
	curl_close ($ch);
	$response = json_decode($token_request);  
    
    //$logger->addInfo(" Access token ".$token_request);
 
	if (isset($response->access_token))
		{
		$token = $response->access_token;
        
		// Now that we've got the access token, we can request access to the API
        $query_string  = "webhooks".build_webhook_query_string( $task , $request_variables );
        
        //$logger->addInfo("Query string : ".$query_string);
        
        $result = query_remote_server( $server , $token , "GET" , $query_string , array() , $logger );

		if ($result['response_code'] == "200"  )
			{
			$reply = json_decode($result['result']);
            $logger->addInfo("Successful response : ".$reply);
			}
		elseif ( $result['response_code'] == "204")
			{
            $logger->addInfo("No data to return");
			}
		else
			{
            $logger->addInfo("Response code ".$result['response_code']);
			$error = json_decode($result['result']);
			if (!is_null($error->meta->error_message))
                $logger->addInfo($error->meta->error_message);
			}
		}
	else
		{
        $logger->addInfo("Error, json & token not returned ".$token_request);
		}
	}
catch(Exception $e) 
	{
    $logger->addInfo('Message: ' .$e->getMessage());
	}
	
function build_webhook_query_string( $task , $parameters ) {
    $query_string = '';
	switch ( $task )
		{
		case 'booking_modified':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'blackbooking_added':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'blackbooking_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'booking_added':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'booking_cancelled':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'booking_note_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['note_uid'];
			break;
		case 'booking_note_save':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['note_uid'];
			break;
		case 'deposit_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'extra_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['extra_uid'];
			break;
		case 'extra_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['extra_uid'];
			break;
		case 'guest_checkedin':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'guest_checkedin_undone':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'guest_checkedout':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
		case 'guest_checkedout_undone':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['contract_uid'];
			break;
        case 'guest_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['guest_uid'];
			break;
        case 'guest_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['guest_uid'];
			break;
        case 'guest_type_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['guest_type_uid'];
			break;
        case 'guest_type_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['guest_type_uid'];
			break;
        case 'invoice_cancelled':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['invoice_uid'];
			break;
        case 'invoice_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['invoice_uid'];
			break;
        case 'property_added':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'property_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'property_published':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'property_saved':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'property_settings_updated':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'property_unpublished':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'review_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['review_uid'];
			break;
        case 'review_published':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['review_uid'];
			break;
        case 'review_saved':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['review_uid'];
			break;
        case 'review_unpublished':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['review_uid'];
			break;
        case 'room_added':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['room_uid'];
			break;
        case 'room_deleted':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['room_uid'];
			break;   
        case 'room_updated':
				$query_string .="/".$parameters['property_uid']."/".$task."/".$parameters['room_uid'];
			break; 
        case 'rooms_multiple_added':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
        case 'tariffs_updated':
				$query_string .="/".$parameters['property_uid']."/".$task;
			break;
            
		default :
			break;
		}
    return $query_string;
}

	
function query_remote_server( $server , $token , $method="GET" , $request ="" , $data=array(3) , $logger )
	{
    $logger->addInfo("Calling : ".$server.$request);
    
	$ch = curl_init($server.$request);

	switch ( $method )
		{
		case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			break;
		case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			break;
		default :
			break;
		}
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$token,
		'Accept: application/json',
		));
	
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	$errors = curl_error($ch);
	$status = curl_getinfo($ch); 
	$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $reply = array ("result" => $result , "status" => $status , "errors" => $errors , "response_code" => $response_code );
    $logger->addInfo("Reply : ".serialize($result));
	return $reply;
	}

http_response_code(200);
