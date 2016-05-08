<?php

function load() {
	$contents = file_get_contents("data/ps.txt");
	$lines = split("\n", $contents);
	$hashes = array();
	foreach($lines as $line) {
		if($line == "")
			continue;
		list($id, $hash, $salt) = split(",", $line, 3);
		$hashes[$id] = array($hash, $salt);
	}
	return $hashes;
}

function save($hashes) {
	$contents = "";
	foreach($hashes as $id => $hash_and_salt) {
		$contents .= $id . "," . $hash_and_salt[0] . "," . $hash_and_salt[1] . "\n";
	}
	file_put_contents("data/ps.txt", $contents);
}

function make_hash($password, $salt) {
	return hash("sha256", $salt . $password);
}

function add($id, $password) {
	if(strlen($id) < 8) {
		print("id is too short");
		exit(-1);
	}
	$hashes = load();
	$salt = bin2hex(openssl_random_pseudo_bytes(32));
	$hash = make_hash($password, $salt);
	$hashes[$id] = array($hash, $salt);
	save($hashes);
}

function get_hash_from_password($id, $password) {
	$hashes = load();
	if(!isset($hashes[$id]))
		return "";
	$salt = $hashes[$id][1];
	$hash = make_hash($password, $salt);
	if($hash == $hashes[$id][0])
		return $hash;
	return "";
}

function verify_from_hash($id, $hash) {
	$hashes = load();
	return isset($hashes[$id]) && $hashes[$id][0] == $hash;
}

?>