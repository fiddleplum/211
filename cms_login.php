<?php

@require("ps.php");

$id = "";
if(isset($_POST["id"]))
	$id = $_POST["id"];

$password = "";
if(isset($_POST["password"]))
	$password = $_POST["password"];

$hash = get_hash_from_password($id, $password);

print_r($hash);

$expire = time() + 60 * 60 * 24 * 7; // 7 days
setcookie("id", $id, $expire);
setcookie("hash", $hash, $expire);

header("Location: cms.php");

?>