<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 6th
    Updated: 2023 November 18th

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

// Redirect if no rows or multiple rows of objects were found
if ($statement->rowCount() < 1 || 1 > $statement->rowCount())
    redirect();

// Store the object data
$object = $statement->fetch();

// Select all of the categories assigned to the object
$category_query = '
SELECT cat.category_name, cat.category_id
FROM ' . CAT_TO_OBJ_TABLE . ' cto
JOIN ' . CATEGORY_TABLE . ' cat ON cto.category_id = cat.category_id
WHERE cto.object_id LIKE :id';
$category_statement = $db->prepare($category_query);
$category_statement->bindValue(':id', $_GET['id']);
$category_statement->execute();

// Select all of the categories if the user is an admin
if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin'])
{
    $all_category_query = 'SELECT * FROM ' . CATEGORY_TABLE . ' ORDER BY category_name ASC';
    $all_category_statement = $db->prepare($all_category_query);
    $all_category_statement->execute();
}

// Formats a double/float to be more human readable
function formatDouble($value)
{
    if (strstr(strval($value),"E+"))
        return str_replace("E+"," x 10^",strval($value));
    if (strstr(strval($value),"E-"))
        return str_replace("E-"," x 10^-",strval($value));
    if ($value >= 1000)
        return number_format($value,0,".",",");

    return $value;
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

    <!-- All Information About An Object -->
    <main id="celestial_object">
        <!-- Name(s) -->
        <h2><?= $object['object_name'] ?></h2>
        <?php if(!empty($object['object_scientific_name'])): ?>
            <h3><?= $object['object_scientific_name'] ?></h3>
        <?php endif ?>
        <br>

        <!-- Testing Only -->
        <p>ID: <?= $object['object_id'] ?></p>
        
        <!-- Categories -->
        <div id="object_categories">
            <?php while ($category = $category_statement->fetch()): ?>
                <div class="category">
                    <?= $category['category_name'] ?>
                    <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                        <!-- Remove The Category -->
                        <form method='post' action='manageCategories.php'>
                            <input type="hidden" name="object_id" value="<?= $object['object_id'] ?>" ?>
                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>" ?>
                            <input id="remove_category" name='remove_category' type="submit" value="X">
                        </form>
                    <?php endif ?>
                </div>
            <?php endwhile ?>
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <!-- Add New Category -->
                <div class="category">
                    <form method='post' action='manageCategories.php'>
                        <input type="hidden" name="object_id" value="<?= $object['object_id'] ?>" ?>
                        <input id="add_category" name='add_category' type="submit" value="Add">
                        <select id="add_category_options" name="category_id">
                            <option value="invalid">-- Select a Category --</option>
                            <?php while ($category = $all_category_statement->fetch()): ?>
                                <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                            <?php endwhile ?>
                        </select>
                    </form>
                </div>
            <?php endif ?>
        </div>

        <br><br>

        <!-- Object Information -->
        <table>
            <tr>
                <th>Mass:</th>
                <td><?= formatDouble($object['object_mass_kg']) ?> kg</td>
            </tr>
            <tr>
                <th>Radius:</th>
                <td><?= formatDouble($object['object_radius']) ?> <?= $object['object_radius_unit'] ?></td>
            </tr>
            <tr>
                <th>Location:</th>
                <td><?= $object['object_location'] ?></td>
            </tr>
            <tr>
                <th>Distance from <?= $object['object_distance_from'] ?>:</th>
                <td><?= formatDouble($object['object_distance']) ?> <?= $object['object_distance_unit'] ?></td>
            </tr>
        </table>

        <br><br><br>
        
        <!-- Description (if applicable) -->
        <?php if(!empty($object['object_description'])): ?>
            <b>Description</b>
            <br>
            <p id="object_description"><?= $object['object_description'] ?></p>
            <br><br>
        <?php endif ?>

        <!-- Picture (if applicable) -->
        <?php if(!empty($object['object_media'])): ?>
            <p><b>link:</b> <?= $object['object_media'] ?></p>
            <img src='<?= $object['object_media'] ?>' />
            <br><br><br>
        <?php endif ?> 

        <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
            <div id="modify_post_buttons">
                <a href="modifyObject.php?edit=true&id=<?= $object['object_id'] ?>">Edit Object</a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="modifyObject.php?delete=true&id=<?= $object['object_id'] ?>">Delete Object</a>
            </div>
        <?php endif ?>
    </main>

    <!-- Questions -->
    <div id="question_module">
        <?php require('questionModule.php'); ?>
    </div>

    <br><br><br>
    <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
</body>
</html>
