<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 20th
    Updated: 2023 November 24th

****************/

session_start();
require('connect.php');
require('globalFunctions.php');
define('CATEGORY_TABLE', 'object_categories');
define('CAT_TO_OBJ_TABLE', 'category_to_object');
$new = true;


// Redirect if not logged in or unauthorized
if(!isAdmin())
{
    header("Location: index.php");
    die();
}


// Redirect the user to this page, but without any GET/POST data (to create a new object)
function redirect()
{ 
    header("Location: modifyCategory.php");
    die();
}


// Loads category information from DB
function getCategoryInfo($database, $id)
{
    $query = 'SELECT * FROM ' . CATEGORY_TABLE . ' WHERE category_id=:id LIMIT 1';
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $id);
    $statement->execute();

    return $statement->fetch();
}


// Delete the object from the database
function deleteCategory($database)
{
    // Delete the category
    $query = "DELETE FROM " . CATEGORY_TABLE . " WHERE category_id=:id LIMIT 1";
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $_GET['id']);
    $statement->execute();

    // Delete all object relationships assigned to the category
    $query = "DELETE FROM " . CAT_TO_OBJ_TABLE . " WHERE category_id=:id";
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $_GET['id']);
    $statement->execute();
}


// Edit the category in the database
function editCategory($database)
{
    // Ensure data is valid (ID was already validated)
    if (empty($_POST['name']))
        redirect();

    $category_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Update the object in the database
    $query = "UPDATE ".CATEGORY_TABLE." SET 
            category_name=:category_name
        WHERE category_id = :category_id
        LIMIT 1";
    $statement = $database->prepare($query);
    $statement->bindValue(':category_id', $_POST['id']);
    $statement->bindValue(':category_name', $category_name);
    $statement->execute();
}


// Add the category to the database
function createCategory($database)
{
    // Ensure data is valid
    if (empty($_POST['name']))
        redirect();

    $category_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Create new entry using the data
    $query = "INSERT INTO ".CATEGORY_TABLE." (
            category_name
        ) 
        VALUES (
            :category_name
        )";
    $statement = $database->prepare($query);
    $statement->bindValue(':category_name', $category_name);
    $statement->execute();
}


// Determine what the page should do.
if($_GET)
{
    if (!empty($_GET['id']) && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
    {
        if (!empty($_GET['delete']))
        {
            deleteCategory($db);

            // Redirect to the list of all categories
            header("Location: objectsByCategory.php#main");
            die();
        }
        elseif (!empty($_GET['edit']))
        {
            $category = getCategoryInfo($db, $_GET['id']);
            $new = false;
        }
    }
    else
    {
        // Redirect to the list of all categories
        header("Location: objectsByCategory.php#main");
        die();
    }
}
elseif($_POST)
{
    if (empty($_POST['update']))
        redirect();

    if ($_POST['update'] === "Create")
        createCategory($db);
    elseif ($_POST['update'] === "Update" && !empty($_POST['id']) && filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT))
        editCategory($db);
    else
        redirect();

    // Redirect to the full page for the object that was updated
    header("Location: objectsByCategory.php?category_id=".$_POST['id']."");
    die();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title><?= $new ? "Create New Category":"Edit Category" ?></title>
</head>
<body>

    <!-- Header -->
    <?php require('headerModule.php'); ?>

    <main id="modify">
        <!-- Create / Edit Category Form -->
        <h2><?= $new ? "Create New Category":"Edit Category" ?></h2>
        <form method='post' action='modifyCategory.php'>
            <input type="hidden" name="id" value=<?= $new ? 'new' : $category['category_id'] ?>>
            <label for='name'>Name:</label>
            <input id='name' name='name' type="text" value='<?= $new ? '' : $category['category_name'] ?>'><br> 
            <input id="update" name='update' type="submit" value="<?= $new ? 'Create' : 'Update' ?>">
        </form>
    </main>

    <!-- Footer -->
    <?php require('footerModule.php'); ?>
</body>
</html>