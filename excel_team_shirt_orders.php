<?php
  
  // call mysql morris database
  include("x-dbcnx.php");

  // call PEAR class
  require_once "Spreadsheet/Excel/Writer.php";

  // create spreadsheet
  $xls =& new Spreadsheet_Excel_Writer();

  // less control on header and footer than would like
  // need formatting control and ability to write into all
  // the various sectors (left, center, right)
  $headerText = "Dog Days Ale " . date("Y") . " - Shirt Orders by Team";
  $footerText = "";

  // create style format for title row
  $titleFormat =& $xls->addFormat();
  $titleFormat->setFontFamily('Bookman');
  $titleFormat->setSize('12');
  $titleFormat->setBold();
  $titleFormat->setAlign('center');

  // create format for data cells
  $rowFormat =& $xls->addFormat();
  $rowFormat->setFontFamily('Arial');
  $rowFormat->setSize('10');
  $rowFormat->setAlign('left');
  $rowFormat->setTextWrap();

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
    $i             = $outer_row["i"];
    $team_name     = $outer_row["team_name"];

    // create worksheets
    $sheet[$i] =& $xls->addWorksheet("$team_name");

    // page setup for 1st worksheet
    $sheet[$i]->setPortrait(); 
    $sheet[$i]->setPaper(1); // 1 = US letter; 9 = UK A4
    $sheet[$i]->setMargins_LR(0.5); // margin width in inches
    $sheet[$i]->setMarginTop(0.75);
    $sheet[$i]->setMarginBottom(0.62);
    $sheet[$i]->setPrintScale(100); // 100% reduction
    $sheet[$i]->centerHorizontally();
    
    // write headers and footers for 1st worksheet
    // uses common $headerText and $footerText
    $sheet[$i]->setHeader($headerText, 0.25);
    $sheet[$i]->setFooter($footerText, 0.25);
    
    // create title rows

    $sheet[$i]->write(0, 1, "$team_name", $titleFormat);
    
    $sheet[$i]->write(1, 0, 'QTY',   $titleFormat);
    $sheet[$i]->write(1, 1, 'STYLE', $titleFormat);
    $sheet[$i]->write(1, 2, 'SIZE',  $titleFormat);

    // set height of title rows
    $sheet[$i]->setRow(0, 18); // row height = 18
    $sheet[$i]->setRow(1, 18); // row height = 18
    
    // set widths of columns
    $sheet[$i]->setColumn(0, 0, 5); // column width = 5, etc.
    $sheet[$i]->setColumn(1, 1, 30);
    $sheet[$i]->setColumn(2, 2, 5);

    // make title row repeat on all print pages
    $sheet[$i]->repeatRows(0);
    
    // freeze title row
    $freeze = array(2, 0, 0, 0);
    $sheet[$i]->freezePanes($freeze);
    // select all registered users 
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

    // writing data row by row for both sheets
    while ( $row = mysql_fetch_array($result) ) 
    {
      $quantities = $row["quantities"];
      $style      = $row["style"];
      $size       = $row["size"];

      $sheet[$i]->write($currentRow, 0, $quantities, $rowFormat);
      $sheet[$i]->write($currentRow, 1, $style,      $rowFormat);
      $sheet[$i]->write($currentRow, 2, $size,       $rowFormat);

      $currentRow++;
    }
  }

  // DONE! - output spreadsheet
  $xls_name = "team_shirt_orders_" . date("Y-m-d") . ".xls";
  $xls->send("$xls_name");
  $xls->close();

  mysql_free_result($result);
  mysql_free_result($outer_result);
  mysql_close($dbcnx);
