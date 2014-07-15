<div class="footer">
<table summary="Report Grid" border="0">
<tr>
  <td>Czar Reports: </td>
  <td>
    <a href="../excel_button_list.php">Button List</a> /
    <a href="../excel_food_restrictions.php">Food Restrictions</a> 
    (<a href="../food_restrictions.php" target="_blank">HTML</a>) /
    <a href="../excel_shirt_orders.php">Shirt Orders (Aggregate)</a> /
    <a href="../excel_team_shirt_orders.php">Shirt Orders by Team</a> /
    <a href="../excel_registration_summary.php">Registration Summary</a>
  </td>
</tr>
<tr>
  <td>Registration Reports: </td>
  <td>
<?php
  include "../x-dbcnx.php";
  $sql = "SELECT team_id, team_name FROM ale_team WHERE 1 ORDER BY team_name";
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) 
  {
    $team_id   = $row['team_id'];
    $team_name = $row['team_name'];
    echo "    <a href=\"../excel_team_registration_report.php?team_id=$team_id\">$team_name</a> / \n";
  }
  mysql_free_result($result);
?>
  </td>
</tr>
<tr>
  <td colspan="2">
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Registration&nbsp;Entry&nbsp;Form</a> /  
    <a href="../">Registration&nbsp;Display</a>
  </td>
</tr>
</table>
</div>
