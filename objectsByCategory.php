<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 11th
    Updated: 2023 November 24th

****************/

session_start();
require('connect.php');
require('globalFunctions.php');
define('CATEGORY_TABLE', 'object_categories');
define('CAT_TO_OBJ_TABLE', 'category_to_object');
define('OBJECT_TABLE_NAME', 'celestial_objects');


// Select all categories sorted by name
$category_query = 'SELECT * FROM ' . CATEGORY_TABLE . ' ORDER BY category_name ASC';
$category_statement = $db->prepare($category_query);
$category_statement->execute();


if ($_GET
    && !empty($_GET['category_id'])
    && filter_input(INPUT_GET,'category_id',FILTER_VALIDATE_INT))
{
    // Get the name/id of the selected category
    $category_info_query = 'SELECT * FROM ' . CATEGORY_TABLE . ' WHERE category_id = :category_id LIMIT 1';
    $category_info_statement = $db->prepare($category_info_query);
    $category_info_statement->bindValue(':category_id', $_GET['category_id']);
    $category_info_statement->execute();
    $selected_category = $category_info_statement->fetch();

    // Get the objects of the selected category
    $object_query = '
    SELECT obj.object_name, obj.object_id
    FROM ' . CAT_TO_OBJ_TABLE . ' cto
        JOIN ' . OBJECT_TABLE_NAME . ' obj ON cto.object_id = obj.object_id
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
    <title>Categories</title>
</head>
<body>

    <!-- Header -->
    <?php require('headerModule.php'); ?>

    <!-- List of All Categories -->
    <main id="main">
        <h2>All Categories</h2>
        <ul id="main_list">
            <!-- Admins have the ability to create new object -->
            <?php if(isAdmin()): ?>
                <li id="new_object_li"
                onclick="window.location.href = 'modifyCategory.php'">
                New Category
                </li>
                <!-- Admins have the ability to edit/delete the selected object -->
                <?php if(!empty($selected_category)): ?>
                    <li id="edit_category_li"
                    onclick="window.location.href = 'modifyCategory.php?edit=true&id=<?= $selected_category['category_id']  ?>'">
                        Edit <?= $selected_category['category_name']  ?> Category
                    </li>
                    <li id="delete_category_li"
                    onclick="window.location.href = 'modifyCategory.php?delete=true&id=<?= $selected_category['category_id']  ?>'">Delete <?= $selected_category['category_name']  ?>
                         Category
                    </li>
                <?php endif ?>
            <?php endif ?>

            <!-- Display all categories -->
            <?php while ($category = $category_statement->fetch()): ?>
                <li onclick="window.location.href = 'objectsByCategory.php?category_id=<?= $category['category_id'] ?>#sub_list'">
                    <?= $category['category_name'] ?>
                </li>
            <?php endwhile ?>
        </ul>

        <!-- List All Objects from the category-->
        <?php if (!empty($object_statement) && $object_statement->rowCount() > 0): ?>
            <h3>All <?= $selected_category['category_name']  ?> Objects</h3>
            <ul id="sub_list">
                <?php while ($object = $object_statement->fetch()): ?>
                    <!-- Display a link to each object -->
                    <li onclick="window.location.href = 'fullObjectPage.php?id=<?= $object['object_id'] ?>#celestial_object'">
                        <?= $object['object_name'] ?>
                    </li>
                <?php endwhile ?>
            </ul>
        <!-- Category selected, but no objects found -->
        <?php elseif (!empty($selected_category)): ?>
            <h3>No <?= $selected_category['category_name']  ?> Objects Found</h3>
            <ul id="sub_list"></ul>
        <?php endif ?>
    </main>

    <!-- Footer -->
    <?php require('footerModule.php'); ?>

</body>
</html>

