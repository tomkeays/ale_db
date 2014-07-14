<?php

  function calc_price($age, $late, $attending)
  {  
    $config = get_config();

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



  function calc_price($age, $late, $full_ale)
  {
    $age_cutoff              = 18;

    $full_ale_price          = 58;
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

