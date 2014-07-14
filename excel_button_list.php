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
  $headerText = "Dog Days Ale " . date("Y") . " - Names for Buttons";
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


  // create 1st worksheet
  $sheet1 =& $xls->addWorksheet('Names for Buttons');

  // page setup for 1st worksheet
  $sheet1->setPortrait(); 
  $sheet1->setPaper(1); // 1 = US letter; 9 = UK A4
  $sheet1->setMargins_LR(0.5); // margin width in inches
  $sheet1->setMarginTop(0.75);
  $sheet1->setMarginBottom(0.62);
  $sheet1->setPrintScale(100); // 100% reduction
  $sheet1->centerHorizontally();
  
  // write headers and footers for 1st worksheet
  // uses common $headerText and $footerText
  $sheet1->setHeader($headerText, 0.25);
  $sheet1->setFooter($footerText, 0.25);
  
  // create title row
  $sheet1->write(0, 0, 'FIRST NAME',  $titleFormat);
  $sheet1->write(0, 1, 'LAST NAME', $titleFormat);
  $sheet1->write(0, 2, 'TEAM NAME',  $titleFormat);

  // set height of title row
  $sheet1->setRow(0, 18); // row height = 18
  
  // set widths of columns
  $sheet1->setColumn(0, 0, 15); // column width = 15, etc.
  $sheet1->setColumn(1, 1, 14);
  $sheet1->setColumn(2, 2, 35);

  // make title row repeat on all print pages
  $sheet1->repeatRows(0);
  
  // freeze title row
  $freeze = array(1, 0, 0, 0);
  $sheet1->freezePanes($freeze);
  
  // select all registered users 
  $sql = " SELECT team_name, last_name, first_name 
           FROM  ale_person, ale_team 
           WHERE ale_person.team_id=ale_team.team_id 
           ORDER BY team_name, last_name, first_name ";

  $result = mysql_query($sql);
  if (!$result) 
  {
    echo("<p>Error retrieving entries from database!<br />\n" .
         "Error: " . mysql_error() . "</p> \n\n");
    exit();
  }

  // start at row 1 (2nd row)
  $currentRow = 1;

  // writing data row by row for both sheets
  while ( $row = mysql_fetch_array($result) ) 
  {
    $last_name  = $row["last_name"];
    $first_name = $row["first_name"];
    $team_name  = $row["team_name"];

    $sheet1->write($currentRow, 0, $first_name, $rowFormat);
    $sheet1->write($currentRow, 1, $last_name,  $rowFormat);
    $sheet1->write($currentRow, 2, $team_name,  $rowFormat);

    $currentRow++;
  }
  
  // DONE! - output spreadsheet
  $xls_name = "button-list_" . date("Y-m-d") . ".xls";
  $xls->send("$xls_name");
  $xls->close();

  mysql_free_result($result);
  mysql_close($dbcnx);

?>