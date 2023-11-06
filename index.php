<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 3rd
    Updated: 2023 November 6th

****************/

session_start();
require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');
// define('DATE_FORMAT', 'F d, Y, h:ia');


// Select all posts from newest to oldest
$query = 'SELECT * FROM ' . OBJECT_TABLE_NAME . ' ORDER BY object_name DESC';
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
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div id="content">
        <h1><a href="index.php">The Celestial Handbook</a></h1>

        <div id="login_box">
            <?php require('loginModule.php'); ?>
        </div>

        <br><br>

        <nav>
            <ul>
                <li class="selectedTab"><a href="index.php">Home</a></li>
                <li class="selectedTab"><a href="https://www.asc-csa.gc.ca/eng/">CSA</a></li>
                <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                    <li><a href="manageUsers.php">Manage Users</a></li>
                <?php endif ?>
            </ul>
        </nav>

        <br><br><br>
        <main>
            <?php while ($row = $statement->fetch()): ?>
            <section>
                <h2>Name: <?= $row['object_name'] ?></h2>
                <p>Name (scientific): <?= $row['object_scientific_name'] ?></p>
                <p>ID: <?= $row['object_id'] ?></p>
                <p>Mass (kg): <?= $row['object_mass_kg'] ?></p>
                <p>Radius (km): <?= $row['object_radius_km'] ?></p>
                <p>Distance From Earth (km): <?= $row['object_distance_from_earth'] ?></p>
                <p>Distance From Sun (km): <?= $row['object_distance_from_sun'] ?></p>
                <p>Velocity (km/s): <?= $row['object_velocity_kms'] ?></p>
                <p>Surface Temperature (k): <?= $row['object_surface_temperature_k'] ?></p>
                <p>Atmosphere: <?= $row['object_has_atmosphere'] ?></p>
                <p>link: <?= $row['object_media'] ?></p>
                <img src='<?= $row['object_media']  ?>' />
                <br><br><br><br><br><br>
            </section>
            <?php endwhile ?>
        </main>
        
        <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
    </div>
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