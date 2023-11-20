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
    // Get the objects of the selected category
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

    // Query for the name of the category
    $category_info_query = 'SELECT * FROM ' . CATEGORY_TABLE . ' WHERE category_id = :category_id LIMIT 1';
    $category_info_statement = $db->prepare($category_info_query);
    $category_info_statement->bindValue(':category_id', $_GET['category_id']);
    $category_info_statement->execute();
    $cat_info = $category_info_statement->fetch();
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

    <!-- List of All Categories -->
    <main id="main">
        <h2>All Categories</h2>
        <ul id="main_list">
            <!-- Admins have the ability to create new object -->
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <a href="modifyCategory.php">
                    <li id="new_object_li">New Category</li>
                </a>
                <!-- Admins have the ability to edit/delete the selected object -->
                <?php if(!empty($cat_info)): ?>
                    <a href="modifyCategory.php?edit=true&id=<?= $cat_info['category_id']  ?>">
                        <li id="edit_category_li">Edit <?= $cat_info['category_name']  ?> Category</li>
                    </a>
                    <a href="modifyCategory.php?delete=true&id=<?= $cat_info['category_id']  ?>">
                        <li id="delete_category_li">Delete <?= $cat_info['category_name']  ?> Category</li>
                    </a>
                <?php endif ?>
            <?php endif ?>

            <!-- Display all categories -->
            <?php while ($category = $category_statement->fetch()): ?>
                <a href='objectsByCategory.php?category_id=<?= $category['category_id'] ?>#sub_list'>
                    <li><?= $category['category_name'] ?></li>
                </a>
            <?php endwhile ?>
        </ul>

        <!-- List All Objects from the category-->
        <?php if (!empty($object_statement)): ?>
                <?php $titleDisplayed = false; ?>
                <ul id="sub_list">
                    <?php while ($object = $object_statement->fetch()): ?>

                        <!-- Display the new headding before list of objects -->
                        <?php if (!$titleDisplayed): ?>
                            <h3>All <?= $object['category_name']  ?> Objects</h3>
                            <?php $titleDisplayed = true; ?>
                        <?php endif ?>

                        <!-- Display a link to each object -->
                        <a href='fullObjectPage.php?id=<?= $object['object_id'] ?>#celestial_object'>
                            <li><?= $object['object_name'] ?></li>
                        </a>
                    <?php endwhile ?>
                </ul>
        <?php endif ?>
    </main>
    <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
</body>
</html>

