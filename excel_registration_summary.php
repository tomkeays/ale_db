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
  $headerText = "Dog Days Ale " . date("Y") . " - Team Summary";
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
  $sheet1 =& $xls->addWorksheet('Team Summary');

  // page setup for 1st worksheet
  $sheet1->setLandscape(); 
  $sheet1->setPaper(1); // 1 = US letter; 9 = UK A4
  $sheet1->setMargins_LR(0.5); // margin width in inches
  $sheet1->setMarginTop(0.75);
  $sheet1->setMarginBottom(0.62);
  $sheet1->setPrintScale(90); // 90% reduction
  $sheet1->centerHorizontally();
  
  // write headers and footers for 1st worksheet
  // uses common $headerText and $footerText
  $sheet1->setHeader($headerText, 0.25);
  $sheet1->setFooter($footerText, 0.25);
  
  // create title row
  $sheet1->write(0, 0,  'Team',              $titleFormat);
  $sheet1->write(0, 1,  'Adults Full Ale',   $titleFormat);
  $sheet1->write(0, 2,  'Children Full Ale', $titleFormat);
  $sheet1->write(0, 3,  'Adults Half Ale',   $titleFormat);
  $sheet1->write(0, 4,  'Children Half Ale', $titleFormat);
  $sheet1->write(0, 5,  'Single Meal',       $titleFormat);
  $sheet1->write(0, 6,  'Ale Fees',          $titleFormat);
  $sheet1->write(0, 7,  'Shirt Orders',      $titleFormat);
  $sheet1->write(0, 8,  'Shirt Fees',        $titleFormat);
  $sheet1->write(0, 9,  'Total Fees',        $titleFormat);
  $sheet1->write(0, 10, 'Total Paid',        $titleFormat);
  $sheet1->write(0, 11, 'Amount Due',        $titleFormat);

  // set height of title row
  $sheet1->setRow(0, 36); // row height = 36
  
  // set widths of columns
  $sheet1->setColumn(0, 0, 20); // start col, end col, col width
  $sheet1->setColumn(1, 1, 10);
  $sheet1->setColumn(2, 2, 10);
  $sheet1->setColumn(3, 3, 10);
  $sheet1->setColumn(4, 4, 10);
  $sheet1->setColumn(5, 5, 10);
  $sheet1->setColumn(6, 6, 10);
  $sheet1->setColumn(7, 7, 10);
  $sheet1->setColumn(8, 8, 10);
  $sheet1->setColumn(9, 9, 10);
  $sheet1->setColumn(10, 10, 10);
  $sheet1->setColumn(11, 11, 10);
 
  // make title row repeat on all print pages
  $sheet1->repeatRows(0);
  
  // freeze title row
  $freeze = array(1, 0, 0, 0);
  $sheet1->freezePanes($freeze);
  
  // select  
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

    $total_fees = $ale_fees + $shirt_fees;
    $amount_due = $total_fees - $amount_paid;

    $sheet1->write($currentRow, 0,  $team_name,         $textFormat);
    $sheet1->write($currentRow, 1,  $full_ale_adults,   $tallyFormat);
    $sheet1->write($currentRow, 2,  $full_ale_children, $tallyFormat);
    $sheet1->write($currentRow, 3,  $half_ale_adults,   $tallyFormat);
    $sheet1->write($currentRow, 4,  $half_ale_children, $tallyFormat);
    $sheet1->write($currentRow, 5,  $dinner,            $tallyFormat);
    $sheet1->write($currentRow, 6,  $ale_fees,          $currencyFormat);
    $sheet1->write($currentRow, 7,  $shirt_orders,      $tallyFormat);
    $sheet1->write($currentRow, 8,  $shirt_fees,        $currencyFormat);
    $sheet1->write($currentRow, 9,  $total_fees,        $currencyFormat);
    $sheet1->write($currentRow, 10, $amount_paid,       $currencyFormat);
    $sheet1->write($currentRow, 11, $amount_due,        $currencyFormat);

    $currentRow++;
  }

  $sheet1->write($currentRow, 0, 'Totals',  $boldtextFormat);
  $formula = '=SUM(B2:B' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 1, $formula,  $boldtallyFormat);
  $formula = '=SUM(C2:C' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 2, $formula,  $boldtallyFormat);
  $formula = '=SUM(D2:D' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 3, $formula,  $boldtallyFormat);
  $formula = '=SUM(E2:E' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 4, $formula,  $boldtallyFormat);
  $formula = '=SUM(F2:F' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 5, $formula,  $boldtallyFormat);
  $formula = '=SUM(G2:G' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 6, $formula,  $boldcurrencyFormat);
  $formula = '=SUM(H2:H' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 7, $formula,  $boldtallyFormat);
  $formula = '=SUM(I2:I' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 8, $formula,  $boldcurrencyFormat);
  $formula = '=SUM(J2:J' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 9, $formula,  $boldcurrencyFormat);
  $formula = '=SUM(K2:K' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 10, $formula, $boldcurrencyFormat);
  $formula = '=SUM(L2:L' . $currentRow . ')';
  $sheet1->writeFormula($currentRow, 11, $formula, $boldcurrencyFormat);

  
  // DONE! - output spreadsheet
  $xls_name = "registration_summary_" . date("Y-m-d") . ".xls";
  $xls->send("$xls_name");
  $xls->close();

  mysql_free_result($result);
  mysql_close($dbcnx);

?>
