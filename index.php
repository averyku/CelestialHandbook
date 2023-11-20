<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 3rd
    Updated: 2023 November 18th

****************/

session_start();
require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');


// Select all objects sorted by name
$query = 'SELECT * FROM ' . OBJECT_TABLE_NAME . ' ORDER BY object_name ASC';
$statement = $db->prepare($query);
$statement->execute();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Celestial Handbook</title>
</head>
<body>
    <!-- Header -->
    <header>
        <div id="header_top">
            <h1><a href="index.php">The Celestial Handbook</a></h1>

            <!-- Login / Out Panel -->
            <div id="login_module">
                <?php require('loginModule.php'); ?>
            </div>
        </div>

        <!-- Navigation -->
        <nav>
            <?php require('nav.php'); ?>
        </nav>
    </header>

    <br><br><br>
    
    <main id="main">
        <!-- List of All Objects -->
        <h2>All Objects</h2>
        <ul id="main_list">
            <!-- Admins have the ability to create new object -->
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <a href="modifyObject.php">
                    <li id="new_object_li">New Object</li>
                </a>
            <?php endif ?>

            <!-- Display a link to each object -->
            <?php while ($row = $statement->fetch()): ?>
                <a href='fullObjectPage.php?id=<?= $row['object_id'] ?>#celestial_object'>
                    <li><?= $row['object_name'] ?></li>
                </a>
            <?php endwhile ?>
        </ul>
    </main>
    
    <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
</body>
</html>



<!-- 

INSERT INTO `celestial_objects`(
`object_id`, 
`object_name`, 
`object_scientific_name`, 
`object_mass_kg`, 
`object_radius_km`, 
`object_distance_from_earth`, 
`object_distance_from_sun`, 
`object_velocity_kms`, 
`object_surface_temperature_k`, 
`object_has_atmosphere`, 
`object_media`) 
VALUES (
'9001',
'The Sun',
'Sol',
'[value-4]',
'[value-5]',
'[value-6]',
'[value-7]',
'[value-8]',
'[value-9]',
'[value-10]',
'[value-11]') 

-->