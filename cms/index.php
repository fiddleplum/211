<?php
	@require("ps.php");

	// THIS CODE IS TO BE UNCOMMENTED OUT ONLY IF THE CMSADMIN USER IS ACCIDENTALLY REMOVED OR THE PASSWORD IS LOST.
	// IT CHANGES THE CMSADMIN PASSWORD TO 1234. UNCOMMENT IT, RUN THIS PAGE ONCE, COMMENT IT OUT, AND LOG IN.
	// add_user("cmsadmin", "1234");
	
	$op = isset($_GET["op"]) ? $_GET["op"] : "";

	// Needs to be done before any HTML is written.
	if($op == "login") {
		$id = "";
		if(isset($_POST["id"]))
			$id = $_POST["id"];

		$password = "";
		if(isset($_POST["password"]))
			$password = $_POST["password"];

		$hash = get_hash_from_password($id, $password);

		$expire = time() + 60 * 60 * 24 * 7; // 7 days

		setcookie("id", $id, $expire);
		setcookie("hash", $hash, $expire);
		header("Location: .");
		exit(0);
	}
	else if($op == "logout") {
		setcookie("id", "", time() - 1);
		setcookie("hash", "", time() - 1);
		header("Location: .");
		exit(0);
	}
?>

<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Content Management System</title>
<link rel="stylesheet" type="text/css" href="cms.css"/>
</head>
<body>
<div id="page">

<?php

$id = isset($_COOKIE["id"]) ? $_COOKIE["id"] : "";
$hash = isset($_COOKIE["hash"]) ? $_COOKIE["hash"] : "";

$verified = verify_user_from_hash($id, $hash);

if($verified !== true) {
	?>
	<h1>CMS Login</h1>
	<form method="post" action="?op=login">
	<p>Please enter your ID and password.</p>
	<div><span class="left">ID:</span><span class="right"><input name="id" type="text" /></span></div>
	<div><span class="left">Password:</span><span class="right"><input name="password" type="password" /></span></div>
	<div class="buttons"><span class="right" style="text-align: right;"><input type="submit" value="Login" /></span></div>
	</form>
	<?php
}
else {
	@require("db.php");

	$service = isset($_GET["service"]) ? $_GET["service"] : "";
	
	if($op == "") {
		?>
		<h1>Content Management System</h1>
		<?php
		if($id == "cmsadmin") {
			?>
			<div id="left_menu">
			<p>Services</p>
			<a class="menu" href="?op=add">Add a Service</a>
			<a class="menu" href="?op=choose&op2=edit">Edit a Service</a>
			<a class="menu" href="?op=choose&op2=remove_confirm">Remove a Service</a>
			<a class="menu" href="?op=latlon">Update Map Lat/Lons</a>
			</div>
			<div id="right_menu">
			<p>Users</p>
			<a class="menu" href="?op=choose&op2=create_user">Create/Reset User</a>
			<a class="menu" href="?op=choose&op2=remove_user">Remove User</a>
			<?php
		}
		else {
			?>
			<div id="left_menu">
			<p>Services</p>
			<a class="menu" href="?op=edit&service=<?php echo htmlspecialchars($id); ?>">Edit Your Service</a>
			<a class="menu" href="?op=remove_confirm&service=<?php echo htmlspecialchars($id); ?>">Remove Your Service</a>
			</div>
			<div id="right_menu">
			<p>Users</p>
			<?php
		}
		?>
		<a class="menu" href="?op=change_password_form&service=<?php echo htmlspecialchars($id); ?>">Change Your Password</a>
		<a class="menu" href="?op=logout">Logout</a>
		</div>
		<?php
	}
	else if(($op == "add" && $id == "cmsadmin") || ($op == "edit" && ($id == "cmsadmin" || $id == $service))) {
		if($op == "add") {
			$service = bin2hex(openssl_random_pseudo_bytes(8));
			$services = array();
			$services[$service] = createEmptyService();
			?>
			<h1>Add a Service</h1>
			<?php
		}
		else if ($op == "edit") {
			$services = loadServices();
			?>
			<h1>Edit a Service</h1>
			<?php
		}
		if(isset($services[$service]) === false) {
			?>
			<p>Invalid service. Please choose another.</p>
			<a href=".">Return</a>
			<?php
		}
		else {
			?>
			<form action="?op=save&service=<?php echo htmlspecialchars($service); ?>" method="post">
			<div><span class="left">Name:</span><span class="right"><input name="name" type="text" value="<?php echo htmlspecialchars($services[$service]["name"]); ?>" /></span></div>
			<div><span class="left">Description:</span><span class="right"><textarea name="description" style="height: 10em;"><?php echo htmlspecialchars($services[$service]["description"]); ?></textarea></span></div>
			<div><span class="left">Address:</span><span class="right"><input name="address" type="text" value="<?php echo htmlspecialchars($services[$service]["address"]); ?>" /></span></div>
			<div><span class="left">Point of Contact:</span><span class="right"><input name="point_of_contact" type="text" value="<?php echo htmlspecialchars($services[$service]["point_of_contact"]); ?>" /></span></div>
			<div><span class="left">Phone 1:</span><span class="right"><input name="phone_1" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_1"]); ?>" /></span></div>
			<div><span class="left">Phone 2:</span><span class="right"><input name="phone_2" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_2"]); ?>" /></span></div>
			<div><span class="left">Phone 3:</span><span class="right"><input name="phone_3" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_3"]); ?>" /></span></div>
			<div><span class="left">E-mail 1:</span><span class="right"><input name="email_1" type="text" value="<?php echo htmlspecialchars($services[$service]["email_1"]); ?>" /></span></div>
			<div><span class="left">E-mail 2:</span><span class="right"><input name="email_2" type="text" value="<?php echo htmlspecialchars($services[$service]["email_2"]); ?>" /></span></div>
			<div><span class="left">E-mail 3:</span><span class="right"><input name="email_3" type="text" value="<?php echo htmlspecialchars($services[$service]["email_3"]); ?>" /></span></div>
			<div><span class="left">Website:</span><span class="right"><input name="website" type="text" value="<?php echo htmlspecialchars($services[$service]["website"]); ?>" /></span></div>
			<div><span class="left">Categories:</span><span class="right"><input name="categories" type="text" value="<?php echo $services[$service]["categories"]; ?>" /></span></div>
			<div class="buttons" style="text-align: right;"><a href=".">Cancel</a><input type="submit" value="Save" /></div>
			</form>
			<?php
		}
	}
	else if($op == "remove_confirm" && ($id == "cmsadmin" || $id == $service)) {
		$services = loadServices();
		if(isset($services[$service]) === false) {
			?>
			<p>Invalid service. Please choose another.</p>
			<a href=".">Return</a>
			<?php
		}
		else {
			?>
			<form action="?op=remove&service=<?php echo htmlspecialchars($service); ?>" method="post">
			<h2>Are you sure you want to remove this item permanently?</h1>
			<h1>"<?php echo htmlspecialchars($services[$service]["name"]); ?>"</h1>
			<div><input id="choose_submit" type="submit" value="Remove" /></div>
			</form>
			<?php
		}
	}
	else if($op == "choose" && $id == "cmsadmin") {
		$op2 = isset($_GET["op2"]) ? $_GET["op2"] : "";
		$services = loadServices();
		?>
		<h1>Choose a Service</h1>
		<form action="." method="get">
		<input name="op" value="<?php echo $op2 ?>" type="hidden" />
		<select id="select_service" name="service">
		<?php
		foreach($services as $serviceId => $service) {
			?>
			<option value="<?php echo $serviceId ?>"><?php echo htmlspecialchars($service["name"]); ?></option>
			<?php
		}
		?>
		</select>
		<div><input id="choose_submit" type="submit" value="Choose" /></div>
		</form>
		<?php
	}
	else if($op == "save" && ($id == "cmsadmin" || $id == $service)) {
		$services = loadServices();
		$services[$service]["id"] = $service;
		$services[$service]["name"] = $_POST["name"];
		$services[$service]["description"] = $_POST["description"];
		$services[$service]["address"] = $_POST["address"];
		$services[$service]["point_of_contact"] = $_POST["point_of_contact"];
		$services[$service]["phone_1"] = $_POST["phone_1"];
		$services[$service]["phone_2"] = $_POST["phone_2"];
		$services[$service]["phone_3"] = $_POST["phone_3"];
		$services[$service]["email_1"] = $_POST["email_1"];
		$services[$service]["email_2"] = $_POST["email_2"];
		$services[$service]["email_3"] = $_POST["email_3"];
		$services[$service]["website"] = $_POST["website"];
		$services[$service]["categories"] = $_POST["categories"];
		updateGeocode($services[$service], false);
		saveServices($services);
		?>
		<p>Saving...</p>
		<script>document.location = ".";</script>
		<?php
	}
	else if($op == "remove" && ($id == "cmsadmin" || $id == $service)) {
		$services = loadServices();
		if(isset($services[$service]) === false) {
			?>
			<p>Invalid service. Please choose another.</p>
			<a href=".">Return</a>
			<?php
		}
		else {
			unset($services[$service]);
			saveServices($services);
			?>
			<p>Removing...</p>
			<script>document.location = ".";</script>
			<?php
		}
	}
	else if($op == "latlon" && $id == "cmsadmin") {
		$services = loadServices();
		?>
		<h1>Updating geocoordinates for services.</h1>
		<p>This will only update up to 100 services that have an empty lat/lon field. To update an existing service, blank out the lat/lon fields in the database.</p>
		<p>You can run this multiple times if there are more than 100 services that need updating.</p>
		<?php
		$count = 0;
		foreach($services as &$service) {
			if($count > 100)
				break;
			if($service["lat"] == "" || $service["lon"] == "") {
				updateGeocode($service, true);
				$count++;
			}
		}
		saveServices($services);
		?>
		<p>Done.</p>
		<a href=".">Return</a>
		<?php
	}
	else if($op == "create_user" && $id == "cmsadmin") {
		$password = bin2hex(openssl_random_pseudo_bytes(4));
		add_user($service, $password);
		?>
		<p>The user has been created or updated. The password is <?php echo $password ?>. This is the only time it will be visible, so record it now.</p>
		<a href=".">Return</a>
		<?php
	}
	else if($op == "remove_user" && $id == "cmsadmin") {
		if($service == "cmsadmin") {
			?>
			<p>Invalid service. Please choose another.</p>
			<a href=".">Return</a>
			<?php
		}
		else {
			remove_user($service);
			?>
			<p>The user has been removed.</p>
			<a href=".">Return</a>
			<?php
		}
	}
	else if($op == "change_password_form" && ($id == "cmsadmin" || $id == $service)) {
		?>
		<form action="?op=change_password&service=<?php echo htmlspecialchars($id); ?>" method="post">
		<h1>Change Your Password</h1>
		<p>Please choose a password that is a phrase or contains both letters and numbers.</p>
		<div><span class="left">Password:</span><span class="right"><input name="password" type="password" /></span></div>
		<div class="buttons" style="text-align: right;"><a href=".">Cancel</a><input type="submit" value="Change" /></div>
		</form>
		<?php
	}
	else if($op == "change_password" && ($id == "cmsadmin" || $id == $service)) {
		if(isset($_POST["password"]) == false || $_POST["password"] == "") {
			?>
			<p>Invalid password. Please choose another.</p>
			<a href=".">Return</a>
			<?php
		}
		else {
			$password = $_POST["password"];
			add_user($id, $password);
			?>
			<p>Your password has been updated.</p>
			<a href=".">Return</a>
			<?php
		}
	}
	else {
		?>
		<p>You do not have permission to edit this service.</p>
		<a href=".">Return</a>
		<?php
	}
}

?>
</div>
</body>
</html>