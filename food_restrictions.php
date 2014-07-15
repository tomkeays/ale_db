<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title> Food Restrictions </title>
<link href="screen.css" type="text/css" rel="stylesheet" media="screen" />
</head>

<body>

<h1> Food Restrictions </h1>

<p>Vegetarian is read as understood and not reported in this version of the report, since all meal options are vegetarian-friendly.</p>

<?php
  
  // call mysql morris database
  include("x-dbcnx.php");

  // select all registered users
  $sql = " SELECT first_name, last_name, team_name, food_restrictions
            FROM ale_person, ale_team
            WHERE ale_person.team_id=ale_team.team_id
            AND food_restrictions != ''
            ORDER BY team_name, last_name, first_name ";

  $result = mysql_query($sql);
  if (!$result)
  {
    echo("<p>Error retrieving entries from database!<br />\n" .
         "Error: " . mysql_error() . "</p> \n\n");
    exit();
  }

  // writing data row by row for both sheets
  while ( $row = mysql_fetch_array($result) )
  {
    $last_name = $row["last_name"];
    $first_name = $row["first_name"];
    $team_name = $row["team_name"];
    $food_restrictions = $row["food_restrictions"];
    if ($food_restrictions != "vegetarian"
      && $food_restrictions != "Vegetarian"
      && $food_restrictions != "veg") {
      $output_array[] = "  <tr><td width=\"40%\">$first_name $last_name ($team_name) &nbsp; </td><td width=\"60%\">$food_restrictions</td></tr>\n";
    }
  }

  echo "<table border=\"1\" cellpadding=\"2\">\n";
  foreach ($output_array as $output_value) {
    echo $output_value;
  }
  echo "</table>\n";

  mysql_free_result($result);
  mysql_close($dbcnx);

?>

<br>
<?php include "x-footer.php"; ?>

</body>
</html>
