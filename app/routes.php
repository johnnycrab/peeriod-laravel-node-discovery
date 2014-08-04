<?php

use Carbon\Carbon as Carbon;


Route::get('/', function()
{

	while (1 == 1) {

		$user = User::orderByRaw('RAND()')->first();

		if(!$user){
			return Response::make('Not found', 404);
		}
		
		$timestamp = $user->updated_at;

		$now = Carbon::now();
		$now->subHours(1);

		if($timestamp->gt($now)) {
			return Response::json(json_decode($user->node_string), 200);
		} else {
			$user->delete();
		}
	}
});


Route::post('/', function()
{
	$ip = $_SERVER['REMOTE_ADDR'];

	$requestInstance = Request::instance();

	if(!$requestInstance->isJson()){
		return Response::make('Error', 400);
	}

	$postObject = Input::json()->all();

	if(!isset($postObject['addresses']) || !isset($postObject['id'])) {
		return Response::make('Error', 400);
	}

	$id = $postObject['id'];

	if(!ctype_xdigit($id) || strlen($id)!== 40){
		return Response::make('Error', 400);
	}

	$addresses = $postObject['addresses'];

	$reworkedAddresses = [];

	for($i = 0; $i < count($addresses); $i++) {
		$address = $addresses[$i];

		if(is_array($addresses) && !empty($address) && array_key_exists('ip', $address) && array_key_exists('port', $address)) { 
			$port = $address['port'];
			
			if(is_int($port) && $port >= 0 && $port <= 65535) {
				$reworkedAddress = [];
				$reworkedAddress['ip'] = $ip;
				$reworkedAddress['port'] = $port;

				$reworkedAddresses[] = $reworkedAddress;
			}
		}
	}

	if(empty($reworkedAddresses)) {
		return Response::make('Error', 400);
	}

	$postObject['addresses'] = $reworkedAddresses;

	$jsonToSave = json_encode($postObject);	

	$user = User::where('node_id', $id)->first();
	
	if(!$user) {
		$user = new User;
		$user->node_id = $id;
	}

	if ($user->node_string !== $jsonToSave) {
		$user->node_string = $jsonToSave;
	}
	else {
		$user->touch();
	}
	
	$user->save();

	return Response::make('', 202);

});