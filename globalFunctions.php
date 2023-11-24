<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 24th
    Updated: 2023 November 24th

****************/


// Formats a double/float to be more human readable
function formatDouble($value)
{
    if (strstr(strval($value),"E+"))
        return str_replace("E+"," x 10<sup>",strval($value))."</sup>";
    if (strstr(strval($value),"E-"))
        return str_replace("E-"," x 10<sup>-",strval($value))."</sup>";
    if ($value >= 1000)
        return number_format($value,0,".",",");

    return $value;
}


// Checks if the user is logged in
function isLoggedIn()
{
    return $_SESSION['login_status'] === 'loggedin';
}


// Checks if the user is logged in as an admin
function isAdmin()
{
    return $_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin'];
}


?>