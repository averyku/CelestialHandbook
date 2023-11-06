<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 3rd
    Updated: 2023 November 3rd

****************/

define('DB_DSN','mysql:host=localhost;dbname=serverside;charset=utf8');
define('DB_USER','serveruser');
define('DB_PASS','gorgonzola7!');     

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