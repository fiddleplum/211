<?php

function load_users() {
	$contents = @file_get_contents("ps.txt");
	if($contents === FALSE)
		$contents = "";
	$lines = split("\n", $contents);
	$users = array();
	foreach($lines as $line) {
		if($line == "")
			continue;
		list($id, $hash, $salt) = split(",", $line, 3);
		$users[$id] = array($hash, $salt);
	}
	if(!isset($users["cmsadmin"])) {
		$salt = bin2hex(openssl_random_pseudo_bytes(32));
		$hash = make_hash("1234", $salt);
		$users["cmsadmin"] = array($hash, $salt);
		save_users($users);
	}
	return $users;
}

function save_users($users) {
	$contents = "";
	foreach($users as $id => $hash_and_salt) {
		$contents .= $id . "," . $hash_and_salt[0] . "," . $hash_and_salt[1] . "\n";
	}
	file_put_contents("ps.txt", $contents);
}

function make_hash($password, $salt) {
	return hash("sha256", $salt . $password);
}

function add_user($id, $password) {
	if(strlen($id) < 8) {
		print("id is too short");
		exit(-1);
	}
	$users = load_users();
	$salt = bin2hex(openssl_random_pseudo_bytes(32));
	$hash = make_hash($password, $salt);
	$users[$id] = array($hash, $salt);
	save_users($users);
}

function remove_user($id) {
	$users = load_users();
	unset($users[$id]);
	save_users($users);
}

function get_hash_from_password($id, $password) {
	$users = load_users();
	if(!isset($users[$id]))
		return "";
	$salt = $users[$id][1];
	$hash = make_hash($password, $salt);
	if($hash == $users[$id][0])
		return $hash;
	return "";
}

function verify_user_from_hash($id, $hash) {
	$users = load_users();
	return isset($users[$id]) && $users[$id][0] == $hash;
}

?>