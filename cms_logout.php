<?php

setcookie("id", "", time() - 1);
setcookie("hash", "", time() - 1);

header("Location: cms.php");

?>