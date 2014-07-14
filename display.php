<?php
  include "x-dbcnx.php";
  include "x-fn14.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title> Ale Registration </title>
<link href="screen.css" type="text/css" rel="stylesheet" media="screen" />
</head>

<body>

<h1> Ale Registration </h1>

<?php 

  if (isset($_GET['team_id']))
  {
    display_attendees($_GET['team_id'], 0);
  } else {
    display_teams();
  }

?>

<?php include "x-footer.php"; ?>

</body>
</html>
