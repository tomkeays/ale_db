<?php
  include "../x-dbcnx.php";
  include "../x-fn14.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title> Ale Registration Form </title>
<link href="../screen.css" type="text/css" rel="stylesheet" media="screen" />
</head>

<body>

<h1> Ale Registration Form </h1>

<?php 

  if (isset($_POST['submit']))
  {
    if (isset($_POST['team_id']))
    {
      $team_id = $_POST['team_id'];
      if (isset($_POST['mode']))
      {
        $mode = $_POST['mode'];
        switch($mode):
          case "add_person":
              $first_name                = $_POST['first_name'];
              $last_name                 = $_POST['last_name'];
              $age                       = $_POST['age'];
              $full_ale                  = $_POST['full_ale'];
              $food_restrictions         = $_POST['food_restrictions'];
              $tour_restrictions         = $_POST['tour_restrictions'];
              $notes                     = $_POST['notes'];
              $slashed_first_name        = addslashes($first_name);
              $slashed_last_name         = addslashes($last_name);
              $slashed_food_restrictions = addslashes($food_restrictions);
              $slashed_tour_restrictions = addslashes($tour_restrictions);
              $slashed_notes             = addslashes($notes);
              $late = !isset($_POST["late_check"]) ? "0" : "1";
              $sql = "INSERT INTO ale_person SET "
                   . "first_name        = '$slashed_first_name', "
                   . "last_name         = '$slashed_last_name', "
                   . "food_restrictions = '$slashed_food_restrictions', "
                   . "tour_restrictions = '$slashed_tour_restrictions', "
                   . "notes             = '$slashed_notes', "
                   . "team_id           = '$team_id', "
                   . "full_ale          = '$full_ale', "
                   . "late              = '$late', "
                   . "age               = '$age' ";
              if (mysql_query($sql)) {
                echo("<p>Entry added.</p> \n\n");
              } else {
                echo("<p>Error adding entry: " .
                     mysql_error() . "</p> \n\n");
              }
              $person_id = mysql_insert_id();
              foreach($_POST['qty'] as $key => $quantity)
              {
                if ($quantity > 0)
                {
                  if ($_POST['style_select'][$key] != "")
                  {
                    $style = $_POST['style_select'][$key];
                    $size  = $_POST['size_select'][$key];

                    $sql = " SELECT shirt_id
                             FROM   ale_shirts
                             WHERE  style = '$style'
                             AND    size  = '$size' 
                             ORDER BY shirt_id ";
                    $result = mysql_query($sql);
                    while ( $row = mysql_fetch_array($result) ) 
                    {
                      $shirt_id = $row['shirt_id'];
                    }
                    mysql_free_result($result);
              
                    mysql_query(" INSERT INTO ale_shirt_orders 
                                  SET person_id = '$person_id', 
                                      shirt_id  = '$shirt_id', 
                                      quantity  = '$quantity' ");
                  }
                }
              }
              display_attendees($team_id, 1);
              add_person();
            break;
          case "update_person":
              $first_name                = $_POST['first_name'];
              $last_name                 = $_POST['last_name'];
              $age                       = $_POST['age'];
              $full_ale                  = $_POST['full_ale'];
              $food_restrictions         = $_POST['food_restrictions'];
              $tour_restrictions         = $_POST['tour_restrictions'];
              $notes                     = $_POST['notes'];
              $slashed_first_name        = addslashes($first_name);
              $slashed_last_name         = addslashes($last_name);
              $slashed_food_restrictions = addslashes($food_restrictions);
              $slashed_tour_restrictions = addslashes($tour_restrictions);
              $slashed_notes             = addslashes($notes);
              $late = !isset($_POST["late_check"]) ? "0" : "1";
              $person_id                = $_POST['person_id'];
              mysql_query(" DELETE FROM ale_shirt_orders 
                            WHERE person_id = '$person_id' ");
              $sql = "UPDATE ale_person SET "
                   . "first_name        = '$slashed_first_name', "
                   . "last_name         = '$slashed_last_name', "
                   . "food_restrictions = '$slashed_food_restrictions', "
                   . "tour_restrictions = '$slashed_tour_restrictions', "
                   . "notes             = '$slashed_notes', "
                   . "team_id           = '$team_id', "
                   . "full_ale          = '$full_ale', "
                   . "late              = '$late', "
                   . "age               = '$age' "
                   . "WHERE person_id   = $person_id";
              if (mysql_query($sql)) {
                echo("<p>Entry updated.</p> \n\n");
              } else {
                echo("<p>Error updating entry: " .
                     mysql_error() . "</p> \n\n");
              }
              foreach($_POST['qty'] as $key => $quantity)
              {
                if ($quantity > 0)
                {
                  if ($_POST['style_select'][$key] != "")
                  {
                    $style = $_POST['style_select'][$key];
                    $size  = $_POST['size_select'][$key];

                    $sql = " SELECT shirt_id
                             FROM   ale_shirts
                             WHERE  style = '$style'
                             AND    size  = '$size' 
                             ORDER BY shirt_id ";
                    $result = mysql_query($sql);
                    while ( $row = mysql_fetch_array($result) ) 
                    {
                      $shirt_id = $row['shirt_id'];
                    }
                    mysql_free_result($result);
              
                    mysql_query(" INSERT INTO ale_shirt_orders 
                                  SET person_id = '$person_id', 
                                      shirt_id  = '$shirt_id', 
                                      quantity  = '$quantity' ");
                  }
                }
              }
              display_attendees($team_id, 1);
              add_person();
            break;
          case "delete_person":
              if ($_POST['person_id'] > 0)
              {
                $person_id = $_POST['person_id'];
                $sql = "DELETE FROM ale_person WHERE person_id='$person_id'";
                if (mysql_query($sql)) 
                {
                  echo("<p>Entry deleted.</p> \n\n");
                } else {
                  echo("<p>Error deleting entry: " .
                       mysql_error() . "</p> \n\n");
                }
                mysql_query(" DELETE FROM ale_shirt_orders 
                              WHERE person_id = '$person_id' ");
              } else {
                echo "<p>Don't mess around!</p> \n\n";
              }
              display_attendees($team_id, 1);
              add_person();
            break;
          case "add_team":
              $team_id                   = $_POST['team_id'];
              $team_name                 = $_POST['team_name'];
              $amount_paid               = $_POST['amount_paid'];
              $tour_restrictions         = $_POST['tour_restrictions'];
              $contact                   = $_POST['contact'];
              $password                  = $_POST['password'];
              $slashed_team_name         = addslashes($team_name);
              $slashed_tour_restrictions = addslashes($tour_restrictions);
              $slashed_contact           = addslashes($contact);

              $sql = "INSERT ale_team SET "
                   . "team_name         = '$slashed_team_name', "
                   . "amount_paid       = '$amount_paid', "
                   . "tour_restrictions = '$slashed_tour_restrictions', "
                   . "contact           = '$slashed_contact', "
                   . "password          = '$password' ";
          
              if (mysql_query($sql)) {
                echo("<p>Entry updated.</p> \n\n");
              } else {
                echo("<p>Error updating entry: " .
                     mysql_error() . "</p> \n\n");
              }
              $team_id = mysql_insert_id();
              display_teams();
              add_team();
            break;
          case "update_team":
              $team_id                   = $_POST['team_id'];
              $team_name                 = $_POST['team_name'];
              $amount_paid               = $_POST['amount_paid'];
              $tour_restrictions         = $_POST['tour_restrictions'];
              $contact                   = $_POST['contact'];
              $password                  = $_POST['password'];
              $slashed_team_name         = addslashes($team_name);
              $slashed_tour_restrictions = addslashes($tour_restrictions);
              $slashed_contact           = addslashes($contact);

              $sql = "UPDATE ale_team SET "
                   . "team_name         = '$slashed_team_name', "
                   . "amount_paid       = '$amount_paid', "
                   . "tour_restrictions = '$slashed_tour_restrictions', "
                   . "contact           = '$slashed_contact', "
                   . "password          = '$password' "
                   . "WHERE team_id     = $team_id";
          
              if (mysql_query($sql)) {
                echo("<p>Entry updated.</p> \n\n");
              } else {
                echo("<p>Error updating entry: " .
                     mysql_error() . "</p> \n\n");
              }
              display_teams();
              add_team();
            break;
          case "recalc":
              mysql_query("UPDATE ale_person SET late='0' WHERE team_id=$team_id");
              foreach($_POST['late_check'] as $key => $value)
              {
                mysql_query("UPDATE ale_person SET late='1' WHERE person_id='$key'");
              }
              foreach($_POST['full_ale_check'] as $key => $value)
              {
                mysql_query("UPDATE ale_person SET full_ale='$value' WHERE person_id='$key'");
              }
              display_attendees($team_id, 1);
              add_person();
            break;
          case "display_team":
              echo "<p>Display Team</p> \n\n";
              display_attendees($team_id, 0);
              add_person();
            break;
        endswitch;
      }
    } else {
      display_teams();
      add_team();
    }
  } else {

    if (isset($_GET['team_id']))
    {
      if (isset($_GET['mode']))
      {
        if ( $_GET['mode'] == 'add_team' || 
             $_GET['mode'] == 'update_team' )
        {
          edit_team($_GET['team_id'], $_GET['mode']);
        } 
        elseif ( $_GET['mode'] == 'display_team' || 
                 $_GET['mode'] == 'recalc' )
        {
          display_attendees($_GET['team_id'], 1);
          add_person();
        }
      } elseif (isset($_GET['person_id'])) {
        display_registration_form($_GET['team_id'], $_GET['person_id'], 6);
      }
    } else {
      display_teams();
      add_team();
    }

  }
?>

<?php include "x-footer.php"; ?>

</body>
</html>
