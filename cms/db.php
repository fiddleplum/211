<?php

function getCategoriesFromServices($services) {
	$categories = [];
	foreach($services as $service) {
		$serviceCategories = explode(",", $service["categories"]);
		foreach($serviceCategories as $serviceCategory) {
			if(!in_array($serviceCategory, $categories)) {
				array_push($categories, $serviceCategory);
			}
		}
	}
	return $categories;
}

function loadServices() {
	$data = array_map('str_getcsv', file("../data/database.txt"));
	for($i = 0; $i < count($data); $i++) {
		if($i == 0)
			continue;
		$c = 0;
		$service = array();
		$service["id"] = str_pad($data[$i][$c], 8, "0", STR_PAD_LEFT); $c++;
		$service["name"] = $data[$i][$c]; $c++;
		$service["short_description"] = $data[$i][$c]; $c++;
		$service["long_description"] = $data[$i][$c]; $c++;
		$service["address"] = $data[$i][$c]; $c++;
		$service["point_of_contact"] = $data[$i][$c]; $c++;
		$service["phone_1"] = $data[$i][$c]; $c++;
		$service["phone_2"] = $data[$i][$c]; $c++;;
		$service["phone_3"] = $data[$i][$c]; $c++;
		$service["email_1"] = $data[$i][$c]; $c++;
		$service["email_2"] = $data[$i][$c]; $c++;
		$service["email_3"] = $data[$i][$c]; $c++;
		$service["website"] = $data[$i][$c]; $c++;
		$service["hours"] = $data[$i][$c]; $c++;
		$service["extra_info"] = $data[$i][$c]; $c++;
		$service["categories"] = $data[$i][$c]; $c++;
		$service["lat"] = $data[$i][$c]; $c++;
		$service["lon"] = $data[$i][$c]; $c++;
		$services[$service["id"]] = $service;
	}
	return $services;
}

function saveServices($services) {
	$contents = "id,name,short_description,long_description,address,point_of_contact,phone_1,phone_2,phone_3,email_1,email_2,email_3,website,hours,extra_info,categories,lat,lon\n";
	foreach($services as $service) {
		$contents .= cleanCsvField($service["id"]) . ",";
		$contents .= cleanCsvField($service["name"]) . ",";
		$contents .= cleanCsvField($service["short_description"]) . ",";
		$contents .= cleanCsvField($service["long_description"]) . ",";
		$contents .= cleanCsvField($service["address"]) . ",";
		$contents .= cleanCsvField($service["point_of_contact"]) . ",";
		$contents .= cleanCsvField($service["phone_1"]) . ",";
		$contents .= cleanCsvField($service["phone_2"]) . ",";
		$contents .= cleanCsvField($service["phone_3"]) . ",";
		$contents .= cleanCsvField($service["email_1"]) . ",";
		$contents .= cleanCsvField($service["email_2"]) . ",";
		$contents .= cleanCsvField($service["email_3"]) . ",";
		$contents .= cleanCsvField($service["website"]) . ",";
		$contents .= cleanCsvField($service["hours"]) . ",";
		$contents .= cleanCsvField($service["extra_info"]) . ",";
		$contents .= cleanCsvField($service["categories"]) . ",";
		$contents .= cleanCsvField($service["lat"]) . ",";
		$contents .= cleanCsvField($service["lon"]);
		$contents .= "\n";
	}
	file_put_contents("../data/database.txt", $contents);
}

function cleanCsvField($field) {
	$field = trim($field);
	if(strpos($field, ",") !== FALSE || strpos($field, "\"") !== FALSE) {
		$field = str_replace("\\", "\\\\", $field);
		$field = str_replace("\"", "\"\"", $field);
		$field = "\"" . $field . "\"";
	}
	return $field;
}

function createEmptyService() {
	$service = array();
	$service["id"] = "";
	$service["name"] = "";
	$service["short_description"] = "";
	$service["long_description"] = "";
	$service["address"] = "";
	$service["point_of_contact"] = "";
	$service["phone_1"] = "";
	$service["phone_2"] = "";;
	$service["phone_3"] = "";
	$service["email_1"] = "";
	$service["email_2"] = "";
	$service["email_3"] = "";
	$service["website"] = "";
	$service["hours"] = "";
	$service["extra_info"] = "";
	$service["categories"] = "";
	$service["lat"] = "";
	$service["lon"] = "";
	return $service;
}

function updateGeocode(&$service, $output) {
	$address = $service["address"];
	$encodedAddress = urlencode($address);
	$url = "http://maps.google.com/maps/api/geocode/json?address=$encodedAddress";
	$contents = @file_get_contents($url);
	$response = json_decode($contents, true);
	if($response["status"] == "OK") {
		$service["lat"] = $response['results'][0]['geometry']['location']['lat'];
        $service["lon"] = $response['results'][0]['geometry']['location']['lng'];
		if($output)
			print("<p>Updated " . $service["name"] . " with (" . $service["lat"] . ", " . $service["lon"] . ").</p>");
	}
	else {
		if($output)
			print("<p>Could not get coordinates for " . $service["name"] . ".</p>");
	}
}

?>