<?php

$services = array();
function initializeDatabase() {
	global $services;
	$data = array_map('str_getcsv', file("data/database.csv"));
	for($i = 0; $i < count($data); $i++) {
		if($i == 0)
			continue;
		$c = 0;
		$service = array();
		$service["id"] = $data[$i][$c]; $c++;
		$service["name"] = $data[$i][$c]; $c++;
		$service["description"] = $data[$i][$c]; $c++;
		$service["address"] = $data[$i][$c]; $c++;
		$service["point_of_contact"] = $data[$i][$c]; $c++;
		$service["phone_1"] = $data[$i][$c]; $c++;
		$service["phone_2"] = $data[$i][$c]; $c++;;
		$service["phone_3"] = $data[$i][$c]; $c++;
		$service["email_1"] = $data[$i][$c]; $c++;
		$service["email_2"] = $data[$i][$c]; $c++;
		$service["email_3"] = $data[$i][$c]; $c++;
		$service["website"] = $data[$i][$c]; $c++;
		$service["categories"] = $data[$i][$c]; $c++;
		$service["lat"] = $data[$i][$c]; $c++;
		$service["lon"] = $data[$i][$c]; $c++;
		$services[$service["id"]] = $service;
	}
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

function saveDatabase() {
	global $services;
	$contents = "id,name,description,address,point_of_contact,phone_1,phone_2,phone_3,email_1,email_2,email_3,website,categories,lat,lon\n";
	foreach($services as $service) {
		$contents .= cleanCsvField($service["id"]) . ",";
		$contents .= cleanCsvField($service["name"]) . ",";
		$contents .= cleanCsvField($service["description"]) . ",";
		$contents .= cleanCsvField($service["address"]) . ",";
		$contents .= cleanCsvField($service["point_of_contact"]) . ",";
		$contents .= cleanCsvField($service["phone_1"]) . ",";
		$contents .= cleanCsvField($service["phone_2"]) . ",";
		$contents .= cleanCsvField($service["phone_3"]) . ",";
		$contents .= cleanCsvField($service["email_1"]) . ",";
		$contents .= cleanCsvField($service["email_2"]) . ",";
		$contents .= cleanCsvField($service["email_3"]) . ",";
		$contents .= cleanCsvField($service["website"]) . ",";
		$contents .= cleanCsvField($service["categories"]) . ",";
		$contents .= cleanCsvField($service["lat"]) . ",";
		$contents .= cleanCsvField($service["lon"]);
		$contents .= "\n";
	}
	file_put_contents("data/database.csv", $contents);
}

function updateGeocode(&$service) {
	$address = $service["address"];
	if(stripos($address, "pasadena") === false)
		$address .= ", Pasadena, CA";
	$encodedAddress = urlencode($address);
	$url = "http://maps.google.com/maps/api/geocode/json?address=$encodedAddress";
	$contents = file_get_contents($url);
	$response = json_decode($contents, true);
	if($response["status"] == "OK") {
		$service["lat"] = $response['results'][0]['geometry']['location']['lat'];
        $service["lon"] = $response['results'][0]['geometry']['location']['lng'];
		print("<p>Updated " . $service["name"] . " with (" . $service["lat"] . ", " . $service["lon"] . ").</p>");
	}
	else {
		print("<p>Could not get coordinates for " . $service["name"] . ".</p>");
	}
}

function error($message) {
	print("<html>$message</html>");
	exit(-1);
}

initializeDatabase();

$q = "";
if(isset($_POST["q"]))
	$q = $_POST["q"];
if($q == "" && isset($_GET["q"]))
	$q = $_GET["q"];

if($q == "save") {
	$id = -1;
	if(isset($_POST["id"]) && is_numeric($_POST["id"]))
		$id = intval($_POST["id"]);
	if(!isset($services[$id]))
		error("Invalid id!");
	$services[$id]["name"] = $_POST["name"];
	$services[$id]["description"] = $_POST["description"];
	$services[$id]["address"] = $_POST["address"];
	$services[$id]["point_of_contact"] = $_POST["point_of_contact"];
	$services[$id]["phone_1"] = $_POST["phone_1"];
	$services[$id]["phone_2"] = $_POST["phone_2"];
	$services[$id]["phone_3"] = $_POST["phone_3"];
	$services[$id]["email_1"] = $_POST["email_1"];
	$services[$id]["email_2"] = $_POST["email_2"];
	$services[$id]["email_3"] = $_POST["email_3"];
	$services[$id]["website"] = $_POST["website"];
	$services[$id]["categories"] = $_POST["categories"];
	updateGeocode($services[$id]);
	saveDatabase();
}

if($q == "latlon") {
	print("<h1>Updating geocoordinates for services.</h1>");
	print("<p>This will only update services that have an empty lat/lon field. To update an existing service, blank out the lat/lon fields in the database.</p>");
	$count = 0;
	foreach($services as &$service) {
		if($count > 100)
			break;
		if($service["lat"] == "" || $service["lon"] == "") {
			updateGeocode($service);
			$count++;
		}
	}
	saveDatabase();
}

print("<p><a href='cms.html'/>Return to the Content Management System</a></p>");

?>