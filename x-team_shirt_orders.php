<?php
  
  // call mysql morris database
  include("x-dbcnx.php");

  $outer_sql = " SELECT DISTINCT team_name, ale_person.team_id as i 
                 FROM   ale_team, ale_person
                 WHERE  ale_team.team_id = ale_person.team_id
                 ORDER BY team_name ";
  $outer_result = mysql_query($outer_sql);
  if (!$outer_result) 
  {
    echo("<p>Error retrieving entries from database!<br />\n" .
         "Error: " . mysql_error() . "</p> \n\n");
    exit();
  }

  $sheet = array();

  while ( $outer_row = mysql_fetch_array($outer_result) ) 
  {
    $i         = $outer_row["i"];
    $team_name = $outer_row["team_name"];

echo "<h3> $team_name </h3>\n\n";
echo "<ul>\n";

    $sql = " SELECT SUM(quantity) AS quantities, style, size
             FROM   ale_shirts, ale_shirt_orders, ale_person
             WHERE  ale_shirt_orders.shirt_id = ale_shirts.shirt_id
             AND    ale_shirt_orders.person_id = ale_person.person_id
             AND    ale_person.team_id = '$i'
             GROUP BY ale_shirts.shirt_id
             ORDER BY ale_shirts.shirt_id ";

    $result = mysql_query($sql);
    if (!$result) 
    {
      echo("<p>Error retrieving entries from database!<br />\n" .
           "Error: " . mysql_error() . "</p> \n\n");
      exit();
    }

    // start at row 2 (3rd row)
    $currentRow = 2;

    $num_rows = mysql_num_rows($result);
    if ($num_rows == 0) echo "<li> No orders </li>\n";

    // writing data row by row for both sheets
    while ( $row = mysql_fetch_array($result) ) 
    {
      $quantities = $row["quantities"];
      $style      = $row["style"];
      $size       = $row["size"];

echo "<li> $quantities $size $style </li>\n";
    }
echo "</ul>\n\n";
  }

echo "<h3> Total </h3>\n\n";
echo "<ul>\n";

  $sql = " SELECT SUM(quantity) AS quantities, style, size
           FROM   ale_shirts, ale_shirt_orders
           WHERE  ale_shirts.shirt_id = ale_shirt_orders.shirt_id
           GROUP BY ale_shirts.shirt_id
           ORDER BY ale_shirts.shirt_id ";

  $result = mysql_query($sql);
  if (!$result) 
  {
    echo("<p>Error retrieving entries from database!<br />\n" .
         "Error: " . mysql_error() . "</p> \n\n");
    exit();
  }

    while ( $row = mysql_fetch_array($result) ) 
    {
      $quantities = $row["quantities"];
      $style      = $row["style"];
      $size       = $row["size"];

echo "<li> $quantities $size $style </li>\n";
    }

echo "</ul>\n\n";

  mysql_free_result($result);
  mysql_free_result($outer_result);
  mysql_close($dbcnx);

?>