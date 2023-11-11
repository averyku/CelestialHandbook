<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 11th
    Updated: 2023 November 11th

****************/

session_start();
require('connect.php');
define('CATEGORY_TABLE', 'object_categories');
define('CAT_TO_OBJ_TABLE', 'category_to_object');
define('OBJECT_TABLE_NAME', 'celestial_objects');


// Select all objects sorted by name
$category_query = 'SELECT * FROM ' . CATEGORY_TABLE . ' ORDER BY category_name ASC';
$category_statement = $db->prepare($category_query);
$category_statement->execute();


$object_statement = "";
if ($_GET
    && !empty($_GET['category_id'])
    && filter_input(INPUT_GET,'category_id',FILTER_VALIDATE_INT))
{
    $object_query = '
    SELECT obj.object_name, obj.object_id , cat.category_name
    FROM ' . CAT_TO_OBJ_TABLE . ' cto
        JOIN ' . OBJECT_TABLE_NAME . ' obj ON cto.object_id = obj.object_id
        JOIN ' . CATEGORY_TABLE . ' cat ON cto.category_id = cat.category_id
    WHERE cto.category_id = :category_id
    ORDER BY obj.object_name ASC';
    $object_statement = $db->prepare($object_query);
    $object_statement->bindValue(':category_id', $_GET['category_id']);
    $object_statement->execute();
}


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
            <?php require('nav.php'); ?>
        </nav>

        <br><br><br>

        <!-- List of All Categories -->
        <h2>Select a Category</h2>
        <ul id="catrgory_list">
            <?php while ($category = $category_statement->fetch()): ?>
                <li>
                    <a href='objectsByCategory.php?category_id=<?= $category['category_id'] ?>'><?= $category['category_name'] ?></a>
                </li>
            <?php endwhile ?>
        </ul>

        <!-- List All Objects from the category-->
        <?php if (!empty($object_statement)): ?>
            <main>
                <?php $titleDisplayed = false; ?>
                <ul>
                    <?php while ($object = $object_statement->fetch()): ?>
                        <?php if (!$titleDisplayed): ?>
                            <li>All <?= $object['category_name']  ?> Objects</li>
                            <?php $titleDisplayed = true; ?>
                        <?php endif ?>
                        <li>
                            <a href='fullObjectPage.php?id=<?= $object['object_id'] ?>'><?= $object['object_name'] ?></a>
                        </li>
                    <?php endwhile ?>
                </ul>
            </main>
        <?php endif ?>
        
        <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
    </div>
</body>
</html>

