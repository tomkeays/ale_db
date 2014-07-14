<?php

  function get_config()
  {
    $sql = 'SELECT * FROM ale_config';
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $config[$row['key']] = $row['value'];
    }
    mysql_free_result($result);
    
    return $config;
  }

  $config = get_config();

  function get_teamname($team_id)
  {
    $sql = "SELECT team_name FROM ale_team WHERE team_id = '$team_id'";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $team_name = $row['team_name'];
    }
    mysql_free_result($result);
    
    return $team_name;
  }

  function add_team()
  {
    echo "<p> \n";
    echo "  <em>Add another team? \n";
    echo "  <a href=\"" . $_SERVER['PHP_SELF'] . "?team_id=0&amp;mode=add_team\">Yes</a> / \n";
    echo "  <a href=\"" . $_SERVER['PHP_SELF'] . "\">No</a></em> \n";
    echo "</p> \n";
  }

  function add_person()
  {
    echo "<p> \n";
    echo "  <em>Register another person? \n";
    echo "  <a href=\"" . $_SERVER['PHP_SELF'] . "?team_id=" . $_GET['team_id']
          . "&amp;person_id=0\">Yes</a> / \n";
    echo "  <a href=\"" . $_SERVER['PHP_SELF'] . "\">No</a></em> \n";
    echo "</p> \n";
  }

  function get_personname($person_id)
  {
    $sql = " SELECT first_name, last_name
             FROM   ale_person
             WHERE  person_id = '$person_id' ";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $first_name  = $row['first_name'];
      $last_name   = $row['last_name'];
      $person_name = "$first_name $last_name";
    }
    return $person_name;
    mysql_free_result($result);
  }

  function format_price($amt)
  {
    $amt = number_format($amt, 2, '.', ''); 
    return $amt;
  }

  function update_ale_fee($total_registration_fee, $team_id)
  {
    mysql_query("UPDATE ale_team SET ale_fees='$total_registration_fee' WHERE team_id=$team_id");
  }

  function calc_price($age, $late, $attending)
  {  
    global $config;

    if ($attending == 'full_ale')
    { 
      $price = $config['full_ale_price'];
      if ($age <= $config['age_cutoff']) $price = $age * $config['full_ale_age_increment'];
    } 
    elseif ($attending == 'half_ale')
    {
      $price = $config['half_ale_price'];
      if ($age <= $config['age_cutoff']) $price = $age * $config['half_ale_age_increment'];
    } 
    elseif ($attending == 'dinner')
    {
      $price = $config['dinner_price'];
      if ($age < $config['age_cutoff']) $price = $config['dinner_price_kids'];
    }
    else
    {
      $price = 0;
    }

    if ($late == '1' && $age >= 21 && $attending == 'full_ale') 
       $price += $config['full_ale_late_amount'];
    if ($late == '1' && $age >= 21 && $attending == 'half_ale') 
       $price += $config['half_ale_late_amount'];

    return $price;
  }

  function display_attendees($team_id, $update_ale_teams)
  {
    $team_name = get_teamname($team_id);
    echo "<h2>$team_name</h2>\n\n";
    
    echo "<form action=\"" . $_SERVER['PHP_SELF'] 
           . "?team_id=$team_id\" method=\"post\">\n";
    echo "<table summary=\"Registered Attendees\" class=\"grid\">\n";
    echo "<tr>\n";
    if ($update_ale_teams == 1)
    {
      echo "  <th class=\"grid\">&nbsp;</th>\n";
    }
    echo "  <th class=\"grid\">First Name</th>\n";
    echo "  <th class=\"grid\">Last Name</th>\n";
    echo "  <th class=\"grid\">Age</th>\n";
    echo "  <th class=\"grid\">Registration</th>\n";
    echo "  <th class=\"grid\">Late</th>\n";
    echo "  <th class=\"grid\">Ale Fee</th>\n";
    echo "  <th class=\"grid\">Shirt Orders</th>\n";
    echo "  <th class=\"grid\">Shirt Cost</th>\n";
    echo "  <th class=\"grid\">Total Fee</th>\n";
    echo "  <th class=\"grid\">Food Restrictions</th>\n";
    echo "  <th class=\"grid\">Tour Restrictions</th>\n";
    echo "  <th class=\"grid\">Notes</th>\n";
    echo "</tr>\n";

    $total_full_ale_adult = 0;
    $total_full_ale_children = 0;
    $total_half_ale_adult = 0;
    $total_half_ale_children = 0;
    $total_dinner = 0;
    $total_registration_fee = 0;
    $total_order_quantity = 0;
    $total_order_price = 0;
    $total_ale_fee = 0;
   
    // select team information 
    $sql = " SELECT amount_paid, tour_restrictions  
             FROM   ale_team 
             WHERE  team_id=$team_id ";
    $result = mysql_query($sql);
    if (!$result) 
    {
      echo("<p>Error retrieving entries from database!<br />\n" .
           "Error: " . mysql_error() . "</p> \n\n");
      exit();
    }
    while ( $row = mysql_fetch_array($result) ) 
    {
      $amount_paid       = $row['amount_paid'];
      $team_restrictions = $row['tour_restrictions'];
    }
    mysql_free_result($result);
    
    // select all registered users 
    $sql = " SELECT person_id, first_name, last_name, age, 
                    food_restrictions, ale_person.tour_restrictions, 
                    notes, late, full_ale, team_name
             FROM   ale_person, ale_team 
             WHERE  ale_person.team_id=$team_id
             AND    ale_person.team_id=ale_team.team_id
             ORDER BY last_name ";

    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $person_id              = $row['person_id'];
      $first_name             = $row['first_name'];
      $last_name              = $row['last_name'];
      $age                    = $row['age'];
      $late                   = $row['late'];
      $full_ale               = $row['full_ale'];
      $registration_fee       = calc_price($age, $late, $full_ale);
      $total_registration_fee = $total_registration_fee + $registration_fee;
      if ($age == 21) 
      {
        $display_age = "Adult"; 
      } else {
        $display_age = $age; 
      }
      if ($age == 21 && $full_ale == 'full_ale') $total_full_ale_adult++; 
      if ($age == 21 && $full_ale == 'free')     $total_full_ale_adult++; 
      if ($age <  21 && $full_ale == 'full_ale') $total_full_ale_children++; 
      if ($age <  21 && $full_ale == 'free')     $total_full_ale_children++; 
      if ($age == 21 && $full_ale == 'half_ale') $total_half_ale_adult++; 
      if ($age <  21 && $full_ale == 'half_ale') $total_half_ale_children++; 
      if ($age >   0 && $full_ale == 'dinner')   $total_dinner++; 
      $food_restrictions   = $row['food_restrictions'];
      $tour_restrictions   = $row['tour_restrictions'];
      $team_name           = $row['team_name'];
      $notes               = $row['notes'];
      
      $linebreak = ($team_restrictions != '' && $tour_restrictions != '')  ? " <br /> \n" : '';
      $tour_restrictions      = $team_restrictions . $linebreak . $tour_restrictions;

      echo "<tr>\n";
      if ($update_ale_teams == 1)
      {
        echo "  <td class=\"grid\">";
        echo "<em><a href=\"" . $_SERVER['PHP_SELF'] 
                   . "?team_id=$team_id&amp;person_id=$person_id\">Edit</a></em>";
        echo "</td>\n";
      }
      echo "  <td class=\"grid\">$first_name</td>\n";
      echo "  <td class=\"grid\">$last_name</td>\n";
      echo "  <td class=\"grid\" align=\"right\">$display_age</td>\n";
      echo "  <td class=\"grid\" align=\"center\">\n";
      echo "    <select name=\"full_ale_check[$person_id]\">\n";
      echo "      <option value=\"full_ale\"";
      if ($full_ale == 'full_ale') echo " selected=\"selected\"";
      echo ">Full Ale</option>\n";
      echo "      <option value=\"half_ale\"";
      if ($full_ale == 'half_ale') echo " selected=\"selected\"";
      echo ">Half Ale</option>\n";
      echo "      <option value=\"dinner\"";
      if ($full_ale == 'dinner') echo " selected=\"selected\"";
      echo ">Single Meal</option>\n";
      echo "      <option value=\"shirt\"";
      if ($full_ale == 'shirt') echo " selected=\"selected\"";
      echo ">Shirt</option>\n";
      echo "      <option value=\"free\"";
      if ($full_ale == 'free') echo " selected=\"selected\"";
      echo ">Free</option>\n";
      echo "      <option value=\"not_cve\"";
      if ($full_ale == 'not_cve') echo " selected=\"selected\"";
      echo ">~(CvE)</option>\n";
      echo "    </select>\n";
      echo "  </td>\n";
      echo "  <td class=\"grid\" align=\"center\">";
      echo "<input type=\"checkbox\" name=\"late_check[$person_id]\"";
      if ($late == 1) echo " checked=\"checked\"";
      echo " /></td>\n";
      $registration_fee = format_price( $registration_fee );
      echo "  <td class=\"grid\" align=\"right\">$registration_fee</td>\n";

      $sql2 = " SELECT order_id, ale_shirt_orders.shirt_id, quantity,
                       style, size, price
                FROM   ale_shirt_orders, ale_shirts
                WHERE  person_id = '$person_id'  
                AND    ale_shirt_orders.shirt_id = ale_shirts.shirt_id ";
      $result2 = mysql_query($sql2);
      $num_rows = mysql_num_rows($result2);
      $shirt_price = 0;
      $shirt_list  = "";
      if ($num_rows>0)
      {
        while ( $row2 = mysql_fetch_array($result2) ) 
        {
          $order_id    = $row2['order_id'];
          $shirt_id    = $row2['shirt_id'];
          $quantity    = $row2['quantity'];
          $style       = $row2['style'];
          if ($quantity>1) $style = $style ."s";
          $size        = $row2['size'];
          $price       = $row2['price'];

          $order_price = format_price( $quantity * $price );
          $shirt_price = format_price( $shirt_price + $order_price );
          $total_order_price = format_price( $total_order_price + $order_price );
          $shirt_list .= "    <div class=\"shirt_orders\"> $quantity $size $style </div>\n";
          $total_order_quantity = $total_order_quantity + $quantity;
        }
      }
      mysql_free_result($result2);
      
      $ale_fee = format_price( $registration_fee + $shirt_price );

      echo "  <td class=\"grid\" align=\"left\">\n$shirt_list  </td>\n";
      echo "  <td class=\"grid\" align=\"right\">$shirt_price</td>\n";
      echo "  <td class=\"grid\" align=\"right\">$ale_fee</td>\n";
      echo "  <td class=\"grid\">$food_restrictions</td>\n";
      echo "  <td class=\"grid\">$tour_restrictions</td>\n";
      echo "  <td class=\"grid\">$notes</td>\n";
      echo "</tr>\n";
    }
    $total_registration_fee = format_price( $total_registration_fee );
    $total_ale_fee = format_price( $total_registration_fee + $total_order_price );
    
    if ($update_ale_teams == 1)
    {
      mysql_query(" UPDATE ale_team SET 
                    full_ale_adults='$total_full_ale_adult',
                    full_ale_children='$total_full_ale_children',
                    half_ale_adults='$total_half_ale_adult',
                    half_ale_children='$total_half_ale_children',
                    dinner='$total_dinner',
                    ale_fees='$total_registration_fee',
                    shirt_orders='$total_order_quantity',
                    shirt_fees='$total_order_price'
                    WHERE team_id=$team_id ");
    }

    echo "<tr>\n";
    if ($update_ale_teams == 1)
    {
      echo "  <td class=\"grid\" colspan=\"3\" align=\"left\">\n";
      echo "    <input type=\"hidden\" name=\"team_id\" value=\"$team_id\" />\n";
      echo "    <input type=\"hidden\" name=\"mode\" value=\"recalc\" />\n";
      echo "    <input type=\"submit\" name=\"submit\"value=\"Recalculate Fees\" />\n";
      echo "  </td>\n";
    } else {
      echo "  <td class=\"grid\" colspan=\"2\" align=\"left\">\n";
      echo "  </td>\n";
    }
    echo "  <td class=\"grid\" colspan=\"3\" align=\"right\"><strong>Totals:</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>&#36;$total_registration_fee</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>$total_order_quantity Shirts</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>&#36;$total_order_price</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>&#36;$total_ale_fee</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"3\" align=\"right\"><strong> </strong></td>\n";
    echo "</tr>\n";

    $colspan = ($update_ale_teams == 1) ? 9 : 8;

    echo "<tr>\n";
    echo "  <td class=\"grid\" colspan=\"$colspan\" align=\"right\"><strong>Amount Paid:</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>&#36;$amount_paid</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"3\" align=\"right\"><strong> </strong></td>\n";
    echo "</tr>\n";

    $balance_due = format_price( $total_ale_fee - $amount_paid );

    echo "<tr>\n";
    echo "  <td class=\"grid\" colspan=\"$colspan\" align=\"right\"><strong>Balance Due:</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"1\" align=\"right\"><strong>&#36;$balance_due</strong></td>\n";
    echo "  <td class=\"grid\" colspan=\"3\" align=\"right\"><strong> </strong></td>\n";
    echo "</tr>\n";

    echo "</table>\n";
    echo "</form>\n\n";
    mysql_free_result($result);
  }

  function display_registration_form($team_id, $person_id=0, $j=10)
  {
    if ($person_id == 0)
    {
      $age = 21;
    } else {
      $sql = " SELECT first_name, last_name, age, late, full_ale,
                      food_restrictions, tour_restrictions, notes
               FROM   ale_person 
               WHERE  person_id=$person_id
               ORDER BY last_name ";
      $result = mysql_query($sql);
      while ( $row = mysql_fetch_array($result) ) 
      {
        $first_name        = $row['first_name'];
        $last_name         = $row['last_name'];
        $age               = $row['age'];
        $late              = $row['late'];
        $full_ale          = $row['full_ale'];
        $food_restrictions = $row['food_restrictions'];
        $tour_restrictions = $row['tour_restrictions'];
        $notes             = $row['notes'];
      }
      mysql_free_result($result);
    }
?>
<p>
Required fields are marked with <span class="required">*</span>
</p>

<form action="<?php echo $_SERVER['PHP_SELF'] . "?team_id=$team_id&amp;person_id=$person_id"; ?>" method="post">

<h3> Registration Information </h3>

<table summary="Registration Information">
  <tr>
    <td align="right">First Name: </td>
    <td align="left"><input type="text" name="first_name" value="<?php echo $first_name ?>" class="entry" /><span class="required">&nbsp;*</span></td>
  </tr>
  <tr>
    <td align="right">Last Name: </td>
    <td align="left"><input type="text" name="last_name" value="<?php echo $last_name ?>" class="entry" /><span class="required">&nbsp;*</span></td>
  </tr>
  <tr>
    <td align="right">Age: </td>
    <td align="left">
      <select name="age">
<?php 
  for($i = 0; $i <= 21; $i++)
  {
    echo "        <option value=\"$i\"";
    if ($i == $age) echo " selected=\"selected\"";
    if ($i < 21) { echo ">$i"; } else { echo ">Adult"; }
    echo "</option> \n";
  }
?>
      </select><span class="required">&nbsp;*</span>
    </td>
  </tr>
  <tr>
    <td align="right">Full Ale: </td>
    <td align="left">
      <select name="full_ale">
        <option value="full_ale"<?php if ($full_ale == 'full_ale') echo " selected=\"selected\""; ?>>Full Ale</option>
        <option value="half_ale"<?php if ($full_ale == 'half_ale') echo " selected=\"selected\""; ?>>Half Ale</option>
        <option value="dinner"<?php   if ($full_ale == 'dinner')   echo " selected=\"selected\""; ?>>Single Meal</option>
        <option value="shirt"<?php    if ($full_ale == 'shirt')    echo " selected=\"selected\""; ?>>Shirt</option>
        <option value="free"<?php     if ($full_ale == 'free')     echo " selected=\"selected\""; ?>>Free</option>
        <option value="not_cve"<?php  if ($full_ale == 'not_cve')  echo " selected=\"selected\""; ?>>~(CvE)</option>
      </select><span class="required">&nbsp;*</span>
    </td>
  </tr>
  <tr>
    <td align="right">Late: </td>
    <td align="left"><input type="checkbox" name="late_check"<?php 
          if ($late == 1) echo " checked=\"checked\""; ?> /></td>
  </tr>
  <tr>
    <td align="right">Food Restrictions: </td>
    <td align="left"><textarea name="food_restrictions" rows="3" cols="40" class="entry"><?php echo $food_restrictions ?></textarea></td>
  </tr>
  <tr><td align="right">Tour Restrictions: </td>
      <td align="left"><textarea name="tour_restrictions" rows="3" cols="40" class="entry"><?php echo $tour_restrictions ?></textarea></td>
  </tr>
  <tr>
    <td align="right">Other Information: </td>
    <td align="left"><textarea name="notes" rows="3" cols="40" class="entry"><?php echo $notes ?></textarea></td>
  </tr>
</table>

<h3> Shirt Orders </h3>

<table summary="Shirt Orders" border="0" cellspacing="3" cellpadding="0">
  <tr>
    <th>Qty</th>
    <th>Style</th>
    <th>Size</th>
  </tr>
<?php
    for ($i = 1; $i <= $j; $i++) 
    {
      display_order_row($person_id, $i);
    }
?>
  <tr> 
    <td colspan="3">
      <input type="submit" name="submit" value="Submit" />
      <input type="hidden" name="team_id" value="<?php echo $team_id ?>" />
<?php
  if ($person_id == 0 || $person_id == '' || $person_id == NULL) 
  {
    echo "      <input type=\"hidden\" name=\"mode\" value=\"add_person\" />\n";
  } else {
    echo "      <input type=\"hidden\" name=\"person_id\" value=\"$person_id\" />\n";
    echo "      <input type=\"hidden\" name=\"mode\" value=\"update_person\" />\n";
  }
?>
    </td>
  </tr>

</table>
</form>

<form action="<?php echo $_SERVER['PHP_SELF'] . "?team_id=$team_id"; ?>" method="post">
  <input type="hidden" name="person_id" value="<?php echo $person_id ?>" />
  <input type="hidden" name="team_id" value="<?php echo $team_id ?>" />
  <input type="hidden" name="mode" value="delete_person" />
  <input type="submit" name="submit" value="Delete" style="float:right; margin: -1em 5em 1em 1em; background-color: red; color: white;" />
  <br clear="all">
</form>

<?php
  }

  function edit_team($team_id, $mode)
  {
    if ($team_id > 0)
    {
      $sql = " SELECT team_id, team_name, tour_restrictions,
                      amount_paid, ale_fees, shirt_fees, contact, password
               FROM   ale_team
               WHERE  team_id = '$team_id' ";
      $result = mysql_query($sql);
      while ( $row = mysql_fetch_array($result) ) 
      {
        $team_name         = $row['team_name'];
        $amount_paid       = $row['amount_paid'];
        $amount_paid       = format_price($amount_paid);
        $ale_fees          = $row['ale_fees'];
        $shirt_fees        = $row['shirt_fees'];
        $total_fees        = $ale_fees + $shirt_fees;
        $ale_fees          = format_price($ale_fees);
        $shirt_fees        = format_price($shirt_fees);
        $total_fees        = format_price($total_fees);
        $tour_restrictions = $row['tour_restrictions'];
        $contact           = $row['contact'];
        $password          = $row['password'];
      }
      echo "<h3>Edit $team_name</h3>\n\n";
    }
    elseif ($team_id == 0)
    {
      echo "<h3>Add New Team</h3>\n\n";  
    }

?>   
<form action="<?php echo $_SERVER['PHP_SELF'] . "?team_id=$team_id"; ?>" method="post">
<table summary="Edit Team">
<tr>
  <td align="right">Team Name: </td>
  <td align="left"><input type="text" name="team_name" value="<?php echo $team_name ?>" class="entry" /></td>
</tr>
<tr>
  <td align="right">Tour Restrictions: </td>
  <td align="left"><textarea name="tour_restrictions" rows="3" cols="40" class="entry"><?php echo $tour_restrictions ?></textarea></td>
</tr>
<tr>
  <td align="right">Amount Paid: </td>
  <td align="left"><input type="text" name="amount_paid" value="<?php echo $amount_paid ?>" class="entry" /></td>
</tr>
<tr>
  <td align="right">Total Fees: </td>
  <td align="left">$<a href="register.php?<?php echo "team_id=$team_id&amp;mode=display_team"; ?>"><?php echo $total_fees; ?></a> (based on current registration of $<?php echo $ale_fees; ?> and shirt orders of $<?php echo $shirt_fees; ?>)</td>
</tr>
<tr>
  <td align="right">Contact Information: </td>
  <td align="left"><textarea name="contact" rows="3" cols="40" class="entry" /><?php echo $contact ?></textarea></td>
<tr>
  <td align="right">&nbsp;</td>
  <td align="left">
    <input type="hidden" name="team_id" value="<?php echo $team_id; ?>" />
    <input type="hidden" name="mode"    value="<?php echo $mode; ?>" />
    <input type="submit" name="submit"  value="Edit Team" />
  </td>
</tr>
</table>
</form>

<?php
  }

  function display_teams()
  {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table summary="Teams" class="grid">
<tr>
  <th class="grid">Team</th>
  <th class="grid">Adults Full Ale</th>
  <th class="grid">Children Full Ale</th>
  <th class="grid">Adults Half Ale</th>
  <th class="grid">Children Half Ale</th>
  <th class="grid">Single Meal</th>
  <th class="grid">Ale Fees</th>
  <th class="grid">Shirt Orders</th>
  <th class="grid">Shirt Fees</th>
  <th class="grid">Total Fees</th>
  <th class="grid">Total Paid</th>
  <th class="grid">Amount Due</th>
</tr>
<?php

    $sql = " SELECT team_id, team_name,
                    full_ale_adults, full_ale_children, 
                    half_ale_adults, half_ale_children,
                    dinner, ale_fees, 
                    shirt_orders, shirt_fees,
                    total_fees, amount_paid, 
                    tour_restrictions, contact, password
             FROM   ale_team
             WHERE  team_id > 0
             ORDER BY team_name ";

    $summed_full_ale_adults   = 0;
    $summed_full_ale_children = 0;
    $summed_half_ale_adults   = 0;
    $summed_half_ale_children = 0;
    $summed_dinner            = 0;
    $summed_ale_fees          = 0;
    $summed_shirt_orders      = 0;
    $summed_shirt_fees        = 0;
    $summed_total_fees        = 0;
    $summed_amount_paid       = 0;
    $summed_amount_due        = 0;

    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $team_id           = $row['team_id'];
      $team_name         = $row['team_name'];
      $full_ale_adults   = $row['full_ale_adults'];
      $full_ale_children = $row['full_ale_children'];
      $half_ale_adults   = $row['half_ale_adults'];
      $half_ale_children = $row['half_ale_children'];
      $dinner            = $row['dinner'];
      $ale_fees          = $row['ale_fees'];
      $shirt_orders      = $row['shirt_orders'];
      $shirt_fees        = $row['shirt_fees'];
      $amount_paid       = $row['amount_paid'];
      $tour_restrictions = $row['tour_restrictions'];
      $contact           = $row['contact'];
      $password          = $row['password'];
      
      $total_registration_fees  = $ale_fees + $shirt_fees;
      $amount_due               = $total_registration_fees - $amount_paid;

      $ale_fees                 = format_price($ale_fees);
      $shirt_fees               = format_price($shirt_fees);
      $amount_paid              = format_price($amount_paid);
      $total_registration_fees  = format_price($total_registration_fees);
      $amount_due               = format_price($amount_due);

      $summed_full_ale_adults   = $summed_full_ale_adults   + $full_ale_adults;
      $summed_full_ale_children = $summed_full_ale_children + $full_ale_children;
      $summed_half_ale_adults   = $summed_half_ale_adults   + $half_ale_adults;
      $summed_half_ale_children = $summed_half_ale_children + $half_ale_children;
      $summed_dinner            = $summed_dinner            + $dinner;
      $summed_shirt_orders      = $summed_shirt_orders      + $shirt_orders;
      $summed_shirt_fees        = $summed_shirt_fees        + $shirt_fees;
      $summed_ale_fees          = $summed_ale_fees          + $ale_fees;
      $summed_total_fees        = $summed_total_fees        + $total_registration_fees;
      $summed_amount_paid       = $summed_amount_paid       + $amount_paid;
      $summed_amount_due        = $summed_amount_due        + $amount_due;
 ?>
<tr>
  <td class="grid"><?php echo $team_name; ?></td>
  <td class="grid" align="right"><?php echo $full_ale_adults; ?></td>
  <td class="grid" align="right"><?php echo $full_ale_children; ?></td>
  <td class="grid" align="right"><?php echo $half_ale_adults; ?></td>
  <td class="grid" align="right"><?php echo $half_ale_children; ?></td>
  <td class="grid" align="right"><?php echo $dinner; ?></td>
  <td class="grid" align="right"><?php echo $ale_fees; ?></td>
  <td class="grid" align="right"><?php echo $shirt_orders; ?></td>
  <td class="grid" align="right"><?php echo $shirt_fees; ?></td>
  <td class="grid" align="right"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?team_id=<?php echo $team_id; ?>&amp;mode=display_team"><?php echo $total_registration_fees; ?></a></td>
  <td class="grid" align="right"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?team_id=<?php echo $team_id; ?>&amp;mode=update_team"><?php echo $amount_paid; ?></a></td>
  <td class="grid" align="right"><?php echo $amount_due; ?></td>
</tr>
<?php
    }

    $summed_ale_fees    = format_price($summed_ale_fees);
    $summed_shirt_fees  = format_price($summed_shirt_fees);
    $summed_total_fees  = format_price($summed_total_fees);
    $summed_amount_paid = format_price($summed_amount_paid);
    $summed_amount_due  = format_price($summed_amount_due);
?>
<tr>
  <th class="grid" align="right">Totals</th>
  <th class="grid" align="right"><?php echo $summed_full_ale_adults; ?></th>
  <th class="grid" align="right"><?php echo $summed_full_ale_children; ?></th>
  <th class="grid" align="right"><?php echo $summed_half_ale_adults; ?></th>
  <th class="grid" align="right"><?php echo $summed_half_ale_children; ?></th>
  <th class="grid" align="right"><?php echo $summed_dinner; ?></th>
  <th class="grid" align="right"><?php echo "&#36;" . $summed_ale_fees; ?></th>
  <th class="grid" align="right"><?php echo $summed_shirt_orders; ?></th>
  <th class="grid" align="right"><?php echo "&#36;" . $summed_shirt_fees; ?></th>
  <th class="grid" align="right"><?php echo "&#36;" . $summed_total_fees; ?></th>
  <th class="grid" align="right"><?php echo "&#36;" . $summed_amount_paid; ?></th>
  <th class="grid" align="right"><?php echo "&#36;" . $summed_amount_due; ?></th>
</tr>
</table>
</form>

<?php
  }

  function display_order_row($person_id, $i)
  {
    $sql = " SELECT quantity, style, size
             FROM ale_shirt_orders, ale_shirts
             WHERE person_id = '$person_id'
             AND ale_shirt_orders.shirt_id = ale_shirts.shirt_id
             ORDER BY ale_shirt_orders.shirt_id ";
    $result = mysql_query($sql);
    $quantity_array = array();
    $style_array    = array();
    $size_array     = array();
    $k = 1;
    while ( $row = mysql_fetch_array($result) ) 
    {
      $quantity_array[$k] = $row['quantity'];
      $style_array[$k]    = $row['style'];
      $size_array[$k]     = $row['size'];
      $k++;
    }
    mysql_free_result($result);
?>
  <tr>
    <td align="center">
      <input name="qty[<?php echo $i; ?>]" value="<?php if ($quantity_array[$i] > 0) echo $quantity_array[$i]; ?>" maxlength="3" size="3" style="text-align:center" />
    </td>
    <td align="center">
      <select name="style_select[<?php echo $i; ?>]">
        <option value="">SELECT AN ITEM</option>
<?php
    $sql = " SELECT DISTINCT style
             FROM ale_shirts
             WHERE 1
             ORDER BY shirt_id ";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $style = $row['style'];
      echo "        <option value=\"$style\"";
      if ($style_array[$i] == $style) echo " selected=\"selected\"";
      echo ">- $style</option> \n";
    }
    mysql_free_result($result);
?>
      </select>
    </td>
    <td align="center">
      <select name="size_select[<?php echo $i; ?>]">
        <option value="S"<?php if ($size_array[$i] == "S") echo " selected=\"selected\""; ?>>S</option>
        <option value="M"<?php if ($size_array[$i] == "M") echo " selected=\"selected\""; ?>>M</option>
        <option value="L"<?php if ($size_array[$i] == "L") echo " selected=\"selected\""; ?>>L</option>
        <option value="XL"<?php if ($size_array[$i] == "XL") echo " selected=\"selected\""; ?>>XL</option>
        <option value="XXL"<?php if ($size_array[$i] == "XXL") echo " selected=\"selected\""; ?>>XXL</option>
        <option value="XXXL"<?php if ($size_array[$i] == "XXXL") echo " selected=\"selected\""; ?>>XXXL</option>
        <option value="XXXXL"<?php if ($size_array[$i] == "XXXXL") echo " selected=\"selected\""; ?>>XXXXL</option>
      </select>
    </td>
  </tr>

<?php
  }

  function team_selector()
  {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<p>
What team are you registering with? 
<select name="team_id">
<?php  
    $sql = "SELECT team_name, team_id FROM ale_team ORDER BY team_name";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $team_name = $row['team_name'];
      $team_id   = $row['team_id'];
      echo "  <option value=\"$team_id\">$team_name</option> \n";
    }
    mysql_free_result($result);
?>
</select>
<input type="submit" />
</p>
</form>
<?php
  } 

?>

