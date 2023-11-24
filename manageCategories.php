<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 17th
    Updated: 2023 November 24th

****************/

session_start();
require('connect.php');
require('globalFunctions.php');
define('CAT_TO_OBJ_TABLE', 'category_to_object');  

// Redirect the user to index
function redirect()
{ 
    header("Location: index.php");
    die();
}


// Redirect if needed
if(!isAdmin())
    redirect();

if(!$_POST)
    redirect();


// Remove Category from an Object
if(!empty($_POST['remove_category']))
{
    if (empty($_POST['category_id'])
    || !filter_input(INPUT_POST,'category_id',FILTER_VALIDATE_INT)
    || empty($_POST['object_id'])
    || !filter_input(INPUT_POST,'object_id',FILTER_VALIDATE_INT))
        redirect();
    else
    {
        // Update database
        $query = "DELETE FROM ".CAT_TO_OBJ_TABLE." WHERE category_id=:category_id AND object_id=:object_id LIMIT 1";
        $statement = $db->prepare($query);
        $statement->bindValue(':category_id', $_POST['category_id']);
        $statement->bindValue(':object_id', $_POST['object_id']);
        $statement->execute();

        // Return to the Object's page
        header("Location: fullObjectPage.php?id=". $_POST['object_id'] ."#celestial_object");
        die();
    }
}
// Add Category to an Object
elseif(!empty($_POST['add_category']))
{
    if (empty($_POST['category_id'])
    || !filter_input(INPUT_POST,'category_id',FILTER_VALIDATE_INT)
    || empty($_POST['object_id'])
    || !filter_input(INPUT_POST,'object_id',FILTER_VALIDATE_INT))
        redirect();
    else
    {
        // Search for an existing record
        $query = "SELECT * FROM ".CAT_TO_OBJ_TABLE." WHERE category_id=:category_id AND object_id=:object_id";
        $statement = $db->prepare($query);
        $statement->bindValue(':category_id', $_POST['category_id']);
        $statement->bindValue(':object_id', $_POST['object_id']);
        $statement->execute();

        if ($statement->rowCount() > 0)
        {
            // Return to the Object's page
            header("Location: fullObjectPage.php?id=". $_POST['object_id'] ."#celestial_object");
            die();
        }

        // Add new record associating the object and category
        $query = "INSERT INTO ".CAT_TO_OBJ_TABLE." (category_id, object_id) VALUES (:category_id, :object_id)";
        $statement = $db->prepare($query);
        $statement->bindValue(':category_id', $_POST['category_id']);
        $statement->bindValue(':object_id', $_POST['object_id']);
        $statement->execute();

        // Return to the Object's page
        header("Location: fullObjectPage.php?id=". $_POST['object_id'] ."#celestial_object");
        die();
    }
}


?>