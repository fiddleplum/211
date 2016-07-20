<?php
	function get_time_string($time) { // time is in the format HHMM, as a number
		$hour = floor($time / 100);
		$minute = $time % 100;
		$text = '';
		if($hour == 0)
			$text .= ($hour + 12);
		else if($hour > 0 && $hour <= 12)
			$text .= $hour;
		else
			$text .= ($hour - 12);
		if($minute > 0) {
			if($minute < 10)
				$text .= ':0' . $minute;
			else
				$text .= ':' . $minute;
		}
		if($hour < 12 || $hour == 24)
			$text .= ' am';
		else
			$text .= ' pm';
		return $text;
	}

	@require("ps.php");

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
    <link rel="stylesheet" href="../css/foundation.min.css">
    <link rel="stylesheet" type="text/css" href="cms.css"/>
    <script src="../js/jquery-2.2.2.min.js"></script>
    <script type="text/javascript" src="../js/foundation.min.js"></script>
  </head>
  <body>
    <header>
      <div class="top-bar">
        <div class="menu-text text-center">
          <a href=".">Bridging Pasadena</a>
        </div>
      </div>
    </header>
    <div id="page">

    <?php

    $id = isset($_COOKIE["id"]) ? $_COOKIE["id"] : "";
    $hash = isset($_COOKIE["hash"]) ? $_COOKIE["hash"] : "";

    $verified = verify_user_from_hash($id, $hash);

    if($verified !== true) {
    	?>
      <div class="row login-row">
        <div class="small-12 large-6 large-centered columns">
          <div>
            <h1 class="text-center">Database Login</h1>
            <p class="text-center">Please enter your User ID and password</p>
            <form method="post" action="?op=login">
              <div class="row collapse">
                <div class="small-12 columns">
                  <label for="username">ID</label>
                  <input name="id" id="username" type="text" placeholder="username">
                </div>
              </div>
              <div class="row collapse">
                <div class="small-12 columns ">
                  <label for="password">Password</label>
                  <input name="password" id="password" type="text" placeholder="password">
                </div>
              </div>
              <input type="submit" name="login" value="login" class="expanded button">
            </form>
          </div>
        </div>
       </div>

    	<?php
    }
    else {
    	@require("db.php");

    	$service = isset($_GET["service"]) ? $_GET["service"] : "";

    	if($op == "") {
    		?>
    		<h1 class="text-center">Content Management System</h1>
        <div class="row">
    		<?php
    		if($id == "cmsadmin") {
    			?>
    			<div class="small-12 medium-6 columns">
      			<h3 class="text-center">Services</h3>
      			<a class="expanded button" href="?op=add">Add a Service</a>
      			<a class="expanded button" href="?op=choose&op2=edit">Edit a Service</a>
      			<a class="expanded button" href="?op=choose&op2=remove_confirm">Remove a Service</a>
      			<a class="expanded button" href="?op=latlon">Update Map Lat/Lons</a>
    			</div>
    			<div class="small-12 medium-6 columns">
      			<h3 class="text-center">Users</h3>
      			<a class="expanded button" href="?op=choose&op2=create_user">Create/Reset User</a>
      			<a class="expanded button" href="?op=choose&op2=remove_user">Remove User</a>
    			<?php
    		}
    		else {
    			?>
    			<div class="small-12 medium-6 columns">
      			<h3 class="text-center">Services</h3>
      			<a class="expanded button" href="?op=edit&service=<?php echo htmlspecialchars($id); ?>">Edit Your Service</a>
      			<a class="expanded button" href="?op=remove_confirm&service=<?php echo htmlspecialchars($id); ?>">Remove Your Service</a>
    			</div>
    			<div class="small-12 medium-6 columns">
    			     <h3 class="text-center">Users</h3>
    			<?php
    		}
    		?>
      		<a class="expanded button" href="?op=change_password_form&service=<?php echo htmlspecialchars($id); ?>">Change Your Password</a>
      		<a class="expanded button" href="?op=logout">Logout</a>
    		</div>
      </div> <!-- closing .row -->
    		<?php
    	}
    	else if(($op == "add" && $id == "cmsadmin") || ($op == "edit" && ($id == "cmsadmin" || $id == $service))) {
    		if($op == "add") {
    			$service = bin2hex(openssl_random_pseudo_bytes(4));
    			$services = loadServices();
    			$categories = getCategoriesFromServices($services);
    			$services[$service] = createEmptyService();
    			?>
    			<h1 class="text-center">Add a Service</h1>
    			<?php
    		}
    		else if ($op == "edit") {
    			$services = loadServices();
    			$categories = getCategoriesFromServices($services);
    			?>
    			<h1 class="text-center">Edit a Service</h1>
    			<?php
    		}
    		if(isset($services[$service]) === false) {
    			?>
    			<p>Invalid service. Please choose another.</p>
    			<a class="button" href=".">Return</a>
    			<?php
    		}
    		else {
    			?>
    			<form action="?op=save&service=<?php echo htmlspecialchars($service); ?>" method="post">
            <div class="row">
              <div class="small-12 large-8 large-offset-2 columns">
                <div>
                  <label>Name:
                    <input name="name" type="text" value="<?php echo htmlspecialchars($services[$service]["name"]); ?>" placeholder="service name" />
                </label>
                </div>
          			<div>
                  <label>Short Description:
                    <textarea name="short_description" style="height: 5em;" placeholder="a short description of the service"><?php echo htmlspecialchars($services[$service]["short_description"]); ?></textarea>
                  </label>
                </div>
          			<div>
                  <label>Long Description:
                    <textarea name="long_description" style="height: 10em;" placeholder="a longer, more detailed information about the service"><?php echo htmlspecialchars($services[$service]["long_description"]); ?></textarea>
                  </label>
                </div>
          			<div>
                  <label>Full Address:
                    <input name="address" type="text" value="<?php echo htmlspecialchars($services[$service]["address"]); ?>" placeholder="example: 100 Garfield Ave, Pasadena, CA 91101" />
                  </label>
                </div>
          			<div>
                  <label>Point of Contact:
                    <input name="point_of_contact" type="text" value="<?php echo htmlspecialchars($services[$service]["point_of_contact"]); ?>" placeholder="service point of contact" />
                  </label>
                </div>
          			<div>
                  <label>Phone 1:
                    <input name="phone_1" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_1"]); ?>" placeholder="###-###-####" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="(###) ###-####" />
                  </label>
                </div>
          			<div>
                  <label>Phone 2:
                    <input name="phone_2" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_2"]); ?>" placeholder="###-###-####" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="(###) ###-####" />
                  </label>
                </div>
          			<div>
                  <label>Phone 3:
                    <input name="phone_3" type="text" value="<?php echo htmlspecialchars($services[$service]["phone_3"]); ?>" placeholder="###-###-####" ata-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="(###) ###-####" />
                  </label>
                </div>
          			<div>
                  <label>Email 1:
                    <input name="email_1" type="text" value="<?php echo htmlspecialchars($services[$service]["email_1"]); ?>" placeholder="someone@example.com" />
                  </label>
                </div>
          			<div>
                  <label>Email 2:
                    <input name="email_2" type="text" value="<?php echo htmlspecialchars($services[$service]["email_2"]); ?>" placeholder="someone@example.com" />
                  </label>
                </div>
          			<div>
                  <label>Email 3:
                    <input name="email_3" type="text" value="<?php echo htmlspecialchars($services[$service]["email_3"]); ?>" placeholder="someone@example.com" />
                  </label>
                </div>
          			<div>
                  <label>Website:
                    <input name="website" type="text" value="<?php echo htmlspecialchars($services[$service]["website"]); ?>" placeholder="www.example.com" />
                  </label>
                </div>
          			<div class="hours">
                  <label>Hours:</label>
                  <?php
            			$days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            			$hours = explode(",", $services[$service]["hours"]);
            			for($day = 0; $day < 7; $day++) {
            				if(isset($hours[$day])) {
            					$serviceTimes = explode("-", $hours[$day]);
            				}
            				else {
            					$serviceTimes = array("", "");
            				}
            				$closed = ($serviceTimes[0] == "0000" && $serviceTimes[1] == "0000");
            				echo "<div class=\"row\"><div class=\"small-12 medium-8 medium-offset-2 columns\"><label>" . $days[$day] . ":<select name=\"hours_open_$day\">";
            				echo "<option disabled selected value=\"\">Open Time</option>";
            				for($time = 0; $time < 2400; $time += 30) {
            					$hour = floor($time / 100);
            					$minute = $time % 100;
            					echo "<option value=\"" . $time . "\"" . ($serviceTimes[0] == $time && !$closed && $hours[$day] != "" ? " selected" : "") . ">" . get_time_string($time) . "</option>";
            					if($minute == 30) {
            						$time = ($hour * 100) + 70;
            					}
            				}
            				echo "</select></label> to <label><select name=\"hours_close_$day\">";
            				echo "<option disabled selected value=\"\">Close Time</option>";
            				for($time = 0; $time < 2400; $time += 30) {
            					$hour = floor($time / 100);
            					$minute = $time % 100;
            					echo "<option value=\"" . $time . "\"" . ($serviceTimes[1] == $time && !$closed && $hours[$day] != "" ? " selected" : "") . ">" . get_time_string($time) . "</option>";
            					if($minute == 30) {
            						$time = ($hour * 100) + 70;
            					}
            				}
            				echo "</select></label> <label>Closed: <input name=\"closed_$day\" type=\"checkbox\"" . ($closed ? "checked" : "") . " value=\"checked\"/></label></div></div>";
            			}

            			?>
                </div>
          			<div>
                  <label>Extra Info:
                    <textarea name="extra_info" style="height: 5em;"><?php echo htmlspecialchars($services[$service]["extra_info"]); ?></textarea>
                  </label>
                </div>
          			<div>
                  <label>Categories:
                    <?php
            			$serviceCategories = explode(",", $services[$service]["categories"]);
            			foreach($categories as $category) {
            				echo "<div><input name=\"category_" . $category . "\" type=\"checkbox\"" . (in_array($category, $serviceCategories) ? "checked" : "") . " value=\"checked\"/> " . $category . "</div>";
            			}
            			?>
                  </label>
          			<div class="float-right">
                  <a class="button" href=".">Cancel</a>
                  <input class="button" type="submit" value="Save" />
                </div>
              </div> <!-- close .columns -->
            </div> <!-- close .row -->
    			</form>
    			<?php
    		}
    	}
    	else if($op == "remove_confirm" && ($id == "cmsadmin" || $id == $service)) {
        ?>
        <div class="row">
        <?php
    		$services = loadServices();
    		if(isset($services[$service]) === false) {
    			?>
    			<p class="text-center">Invalid service. Please choose another.</p>
    			<a class="button float-right" href=".">Return</a>
    			<?php
    		}
    		else {
    			?>
    			<form class="small-12 columns" action="?op=remove&service=<?php echo htmlspecialchars($service); ?>" method="post">
      			<h2 class="text-center">Are you sure you want to remove this item permanently?</h1>
      			<h1 class="text-center">"<?php echo htmlspecialchars($services[$service]["name"]); ?>"</h1>
      			<div>
              <input class="button float-center" id="choose_submit" type="submit" value="Remove" />
            </div>
    			</form>
    			<?php
    		}
        ?>
      </div>
        <?php
    	}
    	else if($op == "choose" && $id == "cmsadmin") {
    		$op2 = isset($_GET["op2"]) ? $_GET["op2"] : "";
    		$services = loadServices();
        function alphabetizeServices($a, $b) {
            if ($a['name'] == $b['name']) {
                return 0;
            }
            return ($a['name'] > $b['name']) ? 1 : -1;
        }
        usort($services, "alphabetizeServices");
    		?>
    		<h1 class="text-center">Choose a Service</h1>
        <div class="row">
          <form class="small-12 columns" action="." method="get">
      		<input name="op" value="<?php echo $op2 ?>" type="hidden" />
      		<select id="select_service" name="service">
      		<?php
      		foreach($services as $service) {
      			?>
      			<option value="<?php echo $service["id"] ?>"><?php echo htmlspecialchars($service["name"]); ?></option>
      			<?php
      		}
      		?>
      		</select>
      		<div><input class="button float-right" id="choose_submit" type="submit" value="Choose" /></div>
      		</form>
        </div>
    		<?php
    	}
    	else if($op == "save" && ($id == "cmsadmin" || $id == $service)) {
    		// make hours string
    		$hours = "";
    		for($day = 0; $day < 7; $day++) {
    			if(isset($_POST["closed_$day"]) && $_POST["closed_$day"] == "checked") {
    				$hours .= "0000-0000";
    			}
    			else if($_POST["hours_open_$day"] == "" || $_POST["hours_close_$day"] == "") {
    				$hours .= "";
    			}
    			else {
    				$hours .= str_pad($_POST["hours_open_$day"], 4, "0000", STR_PAD_LEFT) . "-" . str_pad($_POST["hours_close_$day"], 4, "0000", STR_PAD_LEFT);
    			}
    			if($day < 6) {
    				$hours .= ",";
    			}
    		}

    		// make categories string
    		$categoriesString = "";
    		foreach($_POST as $key => $value) {
    			if(strpos($key, "category_") !== FALSE) {
    				if($categoriesString != "") {
    					$categoriesString .= ",";
    				}
    				$categoriesString .= substr($key, 9);
    			}
    		}

    		// set the new service info
    		$services = loadServices();
    		$services[$service]["id"] = $service;
    		$services[$service]["name"] = $_POST["name"];
    		$services[$service]["short_description"] = $_POST["short_description"];
    		$services[$service]["long_description"] = $_POST["long_description"];
    		$services[$service]["address"] = $_POST["address"];
    		$services[$service]["point_of_contact"] = $_POST["point_of_contact"];
    		$services[$service]["phone_1"] = $_POST["phone_1"];
    		$services[$service]["phone_2"] = $_POST["phone_2"];
    		$services[$service]["phone_3"] = $_POST["phone_3"];
    		$services[$service]["email_1"] = $_POST["email_1"];
    		$services[$service]["email_2"] = $_POST["email_2"];
    		$services[$service]["email_3"] = $_POST["email_3"];
    		$services[$service]["website"] = $_POST["website"];
    		$services[$service]["hours"] = $hours;
    		$services[$service]["extra_info"] = $_POST["extra_info"];
    		$services[$service]["categories"] = $categoriesString;
    		updateGeocode($services[$service], false);
    		saveServices($services);
    		?>
        <div class="row">
          <div class="small-12 columns">
            <p>Saving...</p>
          </div>
        </div>
    		<script>setTimeout(function() {document.location = "."}, 1000);</script>
    		<?php
    	}
    	else if($op == "remove" && ($id == "cmsadmin" || $id == $service)) {
    		$services = loadServices();
    		if(isset($services[$service]) === false) {
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p>Invalid service. Please choose another.</p>
        			<a class="button" href=".">Return</a>
            </div>
          </div>
    			<?php
    		}
    		else {
    			unset($services[$service]);
    			saveServices($services);
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p>Removing...</p>
            </div>
          </div>	
    			<script>setTimeout(function() {document.location = "."}, 1000);</script>
    			<?php
    		}
    	}
    	else if($op == "latlon" && $id == "cmsadmin") {
    		$services = loadServices();
    		?>
        <div class="row">
          <div class="small-12 columns">
            <h1 class="text-center">Updating geocoordinates for services.</h1>
            <div class="text-center">
              <p>This will only update up to 100 services that have an empty lat/lon field. To update an existing service, blank out the lat/lon fields in the database.</p>
          		<p>You can run this multiple times if there are more than 100 services that need updating.</p>
            </div>
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
        		<a class="button" href=".">Return</a>
          </div>
        </div>

    		<?php
    	}
    	else if($op == "create_user" && $id == "cmsadmin") {
    		$password = bin2hex(openssl_random_pseudo_bytes(4));
    		add_user($service, $password);
    		?>
        <div class="row">
          <div class="small-12 columns">
            <p>The user has been created or updated. The username is <b><?php echo $service ?></b> and the password is <b><?php echo $password ?></b>. This is the only time it will be visible, so record it now.</p>
            <a class="button" href=".">Return</a>
          </div>
        </div>

    		<?php
    	}
    	else if($op == "remove_user" && $id == "cmsadmin") {
    		if($service == "cmsadmin") {
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p>Invalid service. Please choose another.</p>
        			<a class="button" href=".">Return</a>
            </div>
          </div>
    			<?php
    		}
    		else {
    			remove_user($service);
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p>The user has been removed.</p>
        			<a class="button" href=".">Return</a>
            </div>
          </div>
    			<?php
    		}
    	}
    	else if($op == "change_password_form" && ($id == "cmsadmin" || $id == $service)) {
    		?>
        <div class="row">
          <h1 class="text-center">Change Your Password</h1>
      		<p class="text-center">Please choose a password that is a phrase or contains both letters and numbers.</p>
          <form class="small-12 columns" action="?op=change_password&service=<?php echo htmlspecialchars($id); ?>" method="post">
      		<div><span>Password:</span><span><input name="password" type="password" /></span></div>
      		<div class="float-right"><a class="button" href=".">Cancel</a> <input class="button" type="submit" value="Change" /></div>
      		</form>
        </div>

    		<?php
    	}
    	else if($op == "change_password" && ($id == "cmsadmin" || $id == $service)) {
    		if(isset($_POST["password"]) == false || $_POST["password"] == "") {
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p class="text-center">Invalid password. Please choose another.</p>
        			<a class="button" href=".">Return</a>
            </div>
          </div>
    			<?php
    		}
    		else {
    			$password = $_POST["password"];
    			add_user($id, $password);
    			?>
          <div class="row">
            <div class="small-12 columns">
              <p class="text-center">Your password has been updated.</p>
        			<a class="button" href=".">Return</a>
            </div>
          </div>
    			<?php
    		}
    	}
    	else {
    		?>
        <div class="row">
          <div class="small-12 columns">
            <p class="text-center">You do not have permission to edit this service.</p>
        		<a class="button" href=".">Return</a>
          </div>
        </div>
    		<?php
    	}
    }

    ?>
    </div> <!-- close #page -->
  </body>
</html>
