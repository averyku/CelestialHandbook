<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 6th
    Updated: 2023 November 6th

****************/

session_start();
require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');
define('QUESTION_TABLE_NAME', 'questions');
define('USER_TABLE_NAME', 'users');


// Redirect the user to the index page
function redirect()
{ 
    header("Location: index.php");
    die();
}

// Redirect if no ID was included or ID was not a valid int
if(!$_GET || empty($_GET['id']) || !filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
    redirect();

// Select the specified object
$query = 'SELECT * FROM ' . OBJECT_TABLE_NAME . ' WHERE object_id LIKE :id';
$statement = $db->prepare($query);
$statement->bindValue(':id', $_GET['id']);
$statement->execute();

// Redirect if no rows or multiple rows were found
if ($statement->rowCount() < 1 || 1 > $statement->rowCount())
    redirect();

// Store the object data
$object = $statement->fetch();

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
    <div id="content">

        <!-- Header -->
        <h1><a href="index.php">The Celestial Handbook</a></h1>

        <!-- Login / Out Panel -->
        <div id="login_module">
            <?php require('loginModule.php'); ?>
        </div>

        <br><br>

        <!-- Navigation -->
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

        <!-- All Information About An Object -->
        <main>
            <section>
                <h2>Name: <?= $object['object_name'] ?></h2>
                <p>Name (scientific): <?= $object['object_scientific_name'] ?></p>
                <p>ID: <?= $object['object_id'] ?></p>
                <p>Mass (kg): <?= $object['object_mass_kg'] ?></p>
                <p>Radius (km): <?= $object['object_radius_km'] ?></p>
                <p>Distance From Earth (km): <?= $object['object_distance_from_earth'] ?></p>
                <p>Distance From Sun (km): <?= $object['object_distance_from_sun'] ?></p>
                <p>Velocity (km/s): <?= $object['object_velocity_kms'] ?></p>
                <p>Surface Temperature (k): <?= $object['object_surface_temperature_k'] ?></p>
                <p>Atmosphere: <?= $object['object_has_atmosphere'] ?></p>
                <?php if(!empty($object['object_media'])): ?>
                    <p>link: <?= $object['object_media'] ?></p>
                    <img src='<?= $object['object_media'] ?>' width="500" height="300" style="object-fit: contain;" />
                <?php endif ?>  
                <br><br><br><br><br><br>
            </section>

            <!-- Questions -->
            <div id="question_module">
                <?php require('questionModule.php'); ?>
            </div>
        </main>
    
        <br><br><br>
        <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
    </div>
</body>
</html>
