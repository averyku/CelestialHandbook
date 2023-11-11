<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 11th
    Updated: 2023 November 11th

****************/

define('DB_DSN','mysql:host=sql312.byethost32.com;dbname=b32_34772106_serverside;charset=utf8');
define('DB_USER','b32_34772106');
define('DB_PASS','fullStack1001');     

// Get the PDO object, or die trying (and display an error)
try 
{
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
} 
catch (PDOException $e) 
{
    print "Error: " . $e->getMessage();
    die(); 
}



 ?>