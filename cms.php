<html>
<head>
<meta charset="UTF-8">
<title>Content Management System</title>
<link rel="stylesheet" type="text/css" href="css/cms.css"/>
</head>
<body>
<div id="page">

<?php

@require("ps.php");

$id = isset($_COOKIE["id"]) ? $_COOKIE["id"] : "";
$hash = isset($_COOKIE["hash"]) ? $_COOKIE["hash"] : "";

$verified = verify_from_hash($id, $hash);

if($verified !== true) {
	?>
	<h1>CMS Login</h1>
	<form method="post" action="cms_login.php">
	<p>Please enter your ID and password.</p>
	<div><span class="left">ID:</span><span class="right"><input name="id" type="text" /></span></div>
	<div><span class="left">Password:</span><span class="right"><input name="password" type="password" /></span></div>
	<div><span class="right" style="text-align: right;"><input type="submit" value="Login" /></span></div>
	</form>
	<?php
}
else {
	$op = isset($_GET["op"]) ? $_GET["op"] : "";
	$op2 = isset($_GET["op2"]) ? $_GET["op2"] : "";
	$service = isset($_GET["service"]) ? $_GET["service"] : "";
	
	if($op == "") {
		?>
		<h1>Content Management System</h1>
		<?php
		if($id == "cmsadmin") {
			?>
			<a class="menu" href="cms.php?op=add">Add a Service</a>
			<a class="menu" href="cms.php?op=choose&op2=edit">Edit a Service</a>
			<a class="menu" href="cms.php?op=choose&op2=remove">Remove a Service</a>
			<a class="menu" href="cms.php?op=latlon">Update Map Lat/Lons</a>
			<?php
		}
		else {
			?>
			<a class="menu" href="cms.php?op=edit&service=<?php echo $id ?>">Edit Your Service</a>
			<a class="menu" href="cms.php?op=remove&service=<?php echo $id ?>">Remove Your Service</a>
			<?php
		}
		?>
		<a class="menu" href="cms_logout.php">Logout</a>
		<?php
	}
	else if(($op == "add" && $id == "cmsadmin") || ($op == "edit" && ($id == "cmsadmin" || $id == $service))) {
		if($op == "add") {
			?>
			<h1>Add a Service</h1>
			<?php
		}
		else if ($op == "edit") {
			?>
			<h1>Edit a Service</h1>
			<?php
		}
		?>
		<form action="cms.php?op=save&service=<?php echo $service ?>" method="post">
		<div><span class="left">Name:</span><span class="right"><input id="name" name="name" type="text" /></span></div>
		<div><span class="left">Description:</span><span class="right"><textarea id="description" name="description" style="height: 10em;"></textarea></span></div>
		<div><span class="left">Address:</span><span class="right"><input id="address" name="address" type="text" /></span></div>
		<div><span class="left">Point of Contact:</span><span class="right"><input id="point_of_contact" name="point_of_contact" type="text" /></span></div>
		<div><span class="left">Phone 1:</span><span class="right"><input id="phone_1" name="phone_1" type="text" /></span></div>
		<div><span class="left">Phone 2:</span><span class="right"><input id="phone_2" name="phone_2" type="text" /></span></div>
		<div><span class="left">Phone 3:</span><span class="right"><input id="phone_3" name="phone_3" type="text" /></span></div>
		<div><span class="left">E-mail 1:</span><span class="right"><input id="email_1" name="email_1" type="text" /></span></div>
		<div><span class="left">E-mail 2:</span><span class="right"><input id="email_2" name="email_2" type="text" /></span></div>
		<div><span class="left">E-mail 3:</span><span class="right"><input id="email_3" name="email_3" type="text" /></span></div>
		<div><span class="left">Website:</span><span class="right"><input id="website" name="website" type="text" /></span></div>
		<div><span class="left">Categories:</span><span class="right"><input id="categories" name="categories" type="text" /></span></div>
		<div class="buttons" style="text-align: right;"><a href="cms.php">Cancel</a><input id="edit_submit" type="submit" value="Save" /></div>
		</form>
		<?php
	}
	else if($op == "remove" && isset($_GET["id"]) && $id == $_GET["id"]) {
		?>
		<form action="cms.php?op=remove" method="post">
		<h1>Are you sure you want to remove this item permanently?</h1>
		<input id="id" name="id" type="hidden" />
		<div id="name"></div>
		<div><input id="choose_submit" type="submit" value="Remove" /></div>
		</form>
		<?php
	}
	else if($op == "choose" && $id == "cmsadmin") {
		?>
		<h1>Choose a Service</h1>
		<form action="cms.php" method="get">
		<input name="op" value="" type="hidden" />
		<select id="select_service" name="id">
		</select>
		<div><input id="choose_submit" type="submit" value="Choose" /></div>
		</form>
		<?php
	}
	else if($op == "logout") {
		?>
		<h1>Logging out...</h1>
		<script>window.location = "cms.php";</script>
		<?php
	}
	else {
		?>
		<p>You do not have permission to edit this service.</p>
		<a href="cms.php">Return</a>
		<?php
	}
}

?>
<script>

// Returns a query parameter.
function getParameterByName(name) {
    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}
var q = getParameterByName('q');
var q2 = getParameterByName('q2');

Database.initialize(function() {
	if(q == '' || q == null)
		$('#menu')[0].style.display = 'block';
	else if(q == 'edit') {
		$('#edit')[0].style.display = 'block';
		var id = getParameterByName('id');
		if(id != '' && id != null) {
			var service = Database.getServiceById(id);
			$('#edit #id')[0].value = service.id;
			$('#edit #name')[0].value = service.name;
			$('#edit #description')[0].value = service.description;
			$('#edit #address')[0].value = service.address;
			$('#edit #point_of_contact')[0].value = service.point_of_contact;
			$('#edit #phone_1')[0].value = service.phone_1;
			$('#edit #phone_2')[0].value = service.phone_2;
			$('#edit #phone_3')[0].value = service.phone_3;
			$('#edit #email_1')[0].value = service.email_1;
			$('#edit #email_2')[0].value = service.email_2;
			$('#edit #email_3')[0].value = service.email_3;
			$('#edit #website')[0].value = service.website;
			$('#edit #categories')[0].value = service.categories;
		}
	}
	else if(q == 'remove') {
		$('#remove')[0].style.display = 'block';
		var id = getParameterByName('id');
		var service = Database.getServiceById(id);
		$('#remove #id')[0].value = service.id;
		$('#remove #name')[0].innerHTML = service.name;
	}
	else if(q == 'choose') {
		$('#choose')[0].style.display = 'block';
		$('#choose #q')[0].value = q2;
		var services = Database.getAllServices();
		var html = '';
		for(var i in services)
			html += '<option value="' + services[i].id + '">' + services[i].name + '</option>';
		$('#select_service')[0].innerHTML = html;
	}
});

</div>
</body>
</html>