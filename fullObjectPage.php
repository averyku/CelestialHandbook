<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 6th
    Updated: 2023 November 11th

****************/

session_start();
require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');
define('QUESTION_TABLE_NAME', 'questions');
define('USER_TABLE_NAME', 'users');
define('CAT_TO_OBJ_TABLE', 'category_to_object');
define('CATEGORY_TABLE', 'object_categories');


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

// Select all of the categories
$category_query = '
SELECT cat.category_name 
FROM ' . CAT_TO_OBJ_TABLE . ' cto
JOIN ' . CATEGORY_TABLE . ' cat ON cto.category_id = cat.category_id
WHERE cto.object_id LIKE :id';
$category_statement = $db->prepare($category_query);
$category_statement->bindValue(':id', $_GET['id']);
$category_statement->execute();

// Redirect if no rows or multiple rows of objects were found
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

        <!-- All Information About An Object -->
        <main>
            <section>
                <h2>Name: <?= $object['object_name'] ?></h2>
                <h3>Categories:</h3>
                <?php while ($category = $category_statement->fetch()): ?>
                    <div class="category"><?= $category['category_name'] ?></div>
                <?php endwhile ?>
                <br>
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
