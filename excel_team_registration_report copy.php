<?php
    
  include "x-dbcnx.php";
  
  function calc_price($age, $late, $full_ale)
  {
    $age_cutoff              = 18;

    $full_ale_price          = 57;
    $full_ale_age_increment  = 3;
    $full_ale_late_amount    = 10;

    $half_ale_price          = 35;
    $half_ale_age_increment  = 2;
    $half_ale_late_amount    = 5;

    $dinner_price            = 12;
    $kids_dinner_price       = 6;
    $dinner_age_increment    = 0;

    if ($full_ale == 'full_ale')
    { 
      $price = $full_ale_price;
      $age_increment = $full_ale_age_increment;
      if ($age <= $age_cutoff) $price = $age * $age_increment;
    } 
    elseif ($full_ale == 'half_ale')
    {
      $price = $half_ale_price;
      $age_increment = $half_ale_age_increment;
      if ($age <= $age_cutoff) $price = $age * $age_increment;
    } 
    elseif ($full_ale == 'dinner')
    {
      $price = $dinner_price;
      $age_increment = $dinner_age_increment;
      if ($age <= $age_cutoff) $price = $kids_dinner_price;
    }
    else
    {
      $price = 0;
      $age_increment = 0;
    }

    if ($late == '1' && $age == 21 && $full_ale == 'full_ale') 
       $price = $price + $full_ale_late_amount;
    if ($late == '1' && $age == 21 && $full_ale == 'half_ale') 
       $price = $price + $half_ale_late_amount;

    return $price;
  }

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

  function get_teaminitials($team_name) {
    $team_initials = "";
    $team = strtolower($team_name);
    $teambits = explode(" ", $team);
    foreach ($teambits as $bit) {
      $ti = (substr($bit, 0, 1));
      if ($ti != "(") {
        $team_initials .= $ti;
      }
    }
    return $team_initials;
  }

  function team_selector()
  {
    echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"get\"> \n";
    echo "<p> \n";
    echo "Registration Report for: \n";
    echo "<select name=\"team_id\"> \n";
  
    $sql = "SELECT team_name, team_id FROM ale_team ORDER BY team_name";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_array($result) ) 
    {
      $team_name = $row['team_name'];
      $team_id   = $row['team_id'];
      echo "  <option value=\"$team_id\">$team_name</option> \n";
    }
    mysql_free_result($result);
  
    echo "</select> \n";
    echo "<input type=\"submit\" value=\"Submit\" /> \n";
    echo "</p> \n";
    echo "</form> \n";
  } 

  
  if (isset($_GET['team_id']))
  {
    $team_id = $_GET['team_id'];
    $team_name = get_teamname($team_id);

    // call PEAR class
    require_once "Spreadsheet/Excel/Writer.php";

    // create spreadsheet
    $xls =& new Spreadsheet_Excel_Writer();

    // less control on header and footer than would like
    // need formatting control and ability to write into all
    // the various sectors (left, center, right)
    $headerText = "Dog Days Ale " . date("Y") . " - Registration Report for $team_name";
    $footerText = "";

    // create style format for title row
    $titleFormat =& $xls->addFormat();
    $titleFormat->setFontFamily('Bookman');
    $titleFormat->setSize('12');
    $titleFormat->setBold();
    $titleFormat->setAlign('center');
    $titleFormat->setTextWrap();

    // create format for data cells
    $textFormat =& $xls->addFormat();
    $textFormat->setFontFamily('Arial');
    $textFormat->setSize('10');
    $textFormat->setAlign('left');
    $textFormat->setTextWrap();

    // create format for data cells
    $colFormat =& $xls->addFormat();
    $colFormat->setFontFamily('Arial');
    $colFormat->setSize('10');
    $colFormat->setAlign('center');
    $colFormat->setTextWrap();

    // create format for data cells
    $tallyFormat =& $xls->addFormat();
    $tallyFormat->setFontFamily('Arial');
    $tallyFormat->setSize('10');
    $tallyFormat->setAlign('center');
    $tallyFormat->setTextWrap();

    // create format for data cells
    $currencyFormat =& $xls->addFormat();
    $currencyFormat->setFontFamily('Arial');
    $currencyFormat->setSize('10');
    $currencyFormat->setAlign('right');
    $currencyFormat->setNumFormat('$0.00');
    $currencyFormat->setTextWrap();

    // create format for data cells
    $boldtextFormat =& $xls->addFormat();
    $boldtextFormat->setFontFamily('Arial');
    $boldtextFormat->setSize('12');
    $boldtextFormat->setBold();
    $boldtextFormat->setAlign('right');
    $boldtextFormat->setTextWrap();

    // create format for data cells
    $boldtallyFormat =& $xls->addFormat();
    $boldtallyFormat->setFontFamily('Arial');
    $boldtallyFormat->setSize('12');
    $boldtallyFormat->setBold();
    $boldtallyFormat->setAlign('center');
    $boldtallyFormat->setTextWrap();

    // create format for data cells
    $boldcurrencyFormat =& $xls->addFormat();
    $boldcurrencyFormat->setFontFamily('Arial');
    $boldcurrencyFormat->setSize('12');
    $boldcurrencyFormat->setAlign('right');
    $boldcurrencyFormat->setBold();
    $boldcurrencyFormat->setNumFormat('$0.00');
    $boldcurrencyFormat->setTextWrap();


    // create 1st worksheet
    $sheet1 =& $xls->addWorksheet("$team_name");

    // page setup for 1st worksheet
    $sheet1->setLandscape(); 
    $sheet1->setPaper(1); // 1 = US letter; 9 = UK A4
    $sheet1->setMargins_LR(0.5); // margin width in inches
    $sheet1->setMarginTop(0.75);
    $sheet1->setMarginBottom(0.62);
    $sheet1->setPrintScale(80); // 80% reduction
    $sheet1->centerHorizontally();
    
    // write headers and footers for 1st worksheet
    // uses common $headerText and $footerText
    $sheet1->setHeader($headerText, 0.25);
    $sheet1->setFooter($footerText, 0.25);
    
    // create title row
    $sheet1->write(0, 0, 'FIRST NAME',         $titleFormat);
    $sheet1->write(0, 1, 'LAST NAME',          $titleFormat);
    $sheet1->write(0, 2, 'AGE',                $titleFormat);
    $sheet1->write(0, 3, 'TYPE',               $titleFormat);
    $sheet1->write(0, 4, 'ALE FEE',            $titleFormat);
    $sheet1->write(0, 5, 'SHIRT ORDERS',       $titleFormat);
    $sheet1->write(0, 6, 'SHIRT COST',         $titleFormat);
    $sheet1->write(0, 7, 'TOTAL FEE',          $titleFormat);
    $sheet1->write(0, 8, 'FOOD RESTRICTIONS',  $titleFormat);
    $sheet1->write(0, 9, 'TOUR RESTRICTIONS',  $titleFormat);
    //$sheet1->write(0, 10, 'NOTES',             $titleFormat);

    // set height of title row
    $sheet1->setRow(0, 36); // row height = 36
    
    // set widths of columns
    $sheet1->setColumn(0, 0, 15); // column width = 15, etc.
    $sheet1->setColumn(1, 1, 14);
    $sheet1->setColumn(2, 2,  5);
    $sheet1->setColumn(3, 3,  8);
    $sheet1->setColumn(4, 4, 10);
    $sheet1->setColumn(5, 5, 25);
    $sheet1->setColumn(6, 6, 10);
    $sheet1->setColumn(7, 7, 10);
    $sheet1->setColumn(8, 8, 25);
    $sheet1->setColumn(9, 9, 25);
    //$sheet1->setColumn(10, 10, 25);

    // make title row repeat on all print pages
    $sheet1->repeatRows(0);
    
    // freeze title row
    $freeze = array(1, 0, 0, 0);
    $sheet1->freezePanes($freeze);
    
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
                    notes, late, full_ale
             FROM   ale_person, ale_team 
             WHERE  ale_person.team_id=$team_id
             AND    ale_person.team_id=ale_team.team_id
             ORDER BY last_name, first_name ";

    $result = mysql_query($sql);
    if (!$result) 
    {
      echo("<p>Error retrieving entries from database!<br />\n" .
           "Error: " . mysql_error() . "</p> \n\n");
      exit();
    }

    // start at row 1 (2nd row)
    $currentRow = 1;
    $total_shirt_quantity = 0;

    // writing data row by row for both sheets
    while ( $row = mysql_fetch_array($result) ) 
    {
      $person_id              = $row['person_id'];
      $first_name             = $row['first_name'];
      $last_name              = $row['last_name'];
      $age                    = $row['age'];
      $late                   = $row['late'];
      $full_ale               = $row['full_ale'];
      $food_restrictions      = $row['food_restrictions'];
      $tour_restrictions      = $row['tour_restrictions'];
      $notes                  = $row['notes'];

      $linebreak = ($team_restrictions != '' && $tour_restrictions != '')  ? " \n" : '';
      $tour_restrictions      = $team_restrictions . $linebreak . $tour_restrictions;

      if ($age == 21) 
      {
        $display_age = "Adult"; 
      } else {
        $display_age = $age; 
      }
      $registration_fee       = calc_price($age, $late, $full_ale);

      $sql2 = " SELECT order_id, ale_shirt_orders.shirt_id, quantity,
                       style, size, price
                FROM   ale_shirt_orders, ale_shirts
                WHERE  person_id = '$person_id'  
                AND    ale_shirt_orders.shirt_id = ale_shirts.shirt_id ";
      $result2 = mysql_query($sql2);
      $num_rows = mysql_num_rows($result2);
      $shirt_price = "";
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

          $order_price = $quantity * $price;
          $shirt_price = $shirt_price + $order_price;
          $shirt_list .= "$quantity $size $style \n";
          $total_shirt_quantity = $total_shirt_quantity + $quantity;
        }
      }
      mysql_free_result($result2);

      $total_fee              = $registration_fee + $shirt_price;

      $sheet1->write($currentRow, 0, $first_name,        $textFormat);
      $sheet1->write($currentRow, 1, $last_name,         $textFormat);
      $sheet1->write($currentRow, 2, $display_age,       $colFormat);
      $sheet1->write($currentRow, 3, $full_ale,          $colFormat);
      $sheet1->write($currentRow, 4, $registration_fee,  $currencyFormat);
      $sheet1->write($currentRow, 5, $shirt_list,        $textFormat);
      $sheet1->write($currentRow, 6, $shirt_price,       $currencyFormat);
      $sheet1->write($currentRow, 7, $total_fee,         $currencyFormat);
      $sheet1->write($currentRow, 8, $food_restrictions, $textFormat);
      $sheet1->write($currentRow, 9, $tour_restrictions, $textFormat);
      //$sheet1->write($currentRow, 10, $notes,            $textFormat);

      $currentRow++;
    }
    mysql_free_result($result);

    $shirts = ($total_shirt_quantity == 1) ? $total_shirt_quantity . " Shirt" : $total_shirt_quantity . " Shirts";

    $formula = '=SUM(E2:E' . $currentRow . ')';
    $sheet1->writeFormula($currentRow, 4, $formula,  $boldcurrencyFormat);
    $formula = '=SUM(G2:G' . $currentRow . ')';
    $sheet1->write($currentRow, 5, $shirts, $boldtextFormat);
    $sheet1->writeFormula($currentRow, 6, $formula,  $boldcurrencyFormat);
    $formula = '=SUM(H2:H' . $currentRow . ')';
    $sheet1->writeFormula($currentRow, 7, $formula,  $boldcurrencyFormat);

    $ale_fee_row = $currentRow + 1;
    
    $currentRow++;
    $currentRow++;

    $sheet1->write($currentRow, 6, 'Total Paid', $boldtextFormat);
    $sheet1->write($currentRow, 7, $amount_paid, $boldcurrencyFormat);

    $currentRow++;

    $sheet1->write($currentRow, 6, 'Balance Due', $boldtextFormat);

    $formula = '=H' . $ale_fee_row . '-H' . $currentRow;
    $sheet1->writeFormula($currentRow, 7, $formula,  $boldcurrencyFormat);

    // DONE! - output spreadsheet
    $team_initials = get_teaminitials($team_name);
    $xls_name = $team_initials . "_registration_report_" . date("Y-m-d") . ".xls";
    $xls->send("$xls_name");
    $xls->close();
  
    mysql_free_result($result);
  
  } else {

    team_selector();

  }
  
  mysql_close($dbcnx);

?>
