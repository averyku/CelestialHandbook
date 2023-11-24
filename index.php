<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 3rd
    Updated: 2023 November 24th

****************/

session_start();
require('connect.php');
require('globalFunctions.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');


// Select all objects sorted by name
$query = 'SELECT object_id, object_name FROM ' . OBJECT_TABLE_NAME . ' ORDER BY object_name ASC';
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
    <title>All Objects</title>
</head>
<body>

    <!-- Header -->
    <?php require('headerModule.php'); ?>
    
    <main id="main">
        <!-- List of All Objects -->
        <h2>All Objects</h2>
        <ul id="main_list">
            <!-- Admins have the ability to create new object -->
            <?php if(isAdmin()): ?>
                <li id="new_object_li" onclick="window.location.href = 'modifyObject.php'">New Object</li>
            <?php endif ?>

            <!-- Display a link to each object -->
            <?php while ($row = $statement->fetch()): ?>
                    <li onclick="window.location.href = 'fullObjectPage.php?id=<?= $row['object_id'] ?>#celestial_object'"><?= $row['object_name'] ?></li>
            <?php endwhile ?>
        </ul>
    </main>
    
    <!-- Footer -->
    <?php require('footerModule.php'); ?>

</body>
</html>
