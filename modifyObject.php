<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 18th
    Updated: 2023 November 21st

****************/

session_start();

// Requires ImageResize Library
require $_SERVER['DOCUMENT_ROOT'] . '/Library_ImageResize/lib/ImageResize.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Library_ImageResize/lib/ImageResizeException.php';
use \Gumlet\ImageResize;

require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');
define('CAT_TO_OBJ_TABLE', 'category_to_object');
define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/gif']);
define('IMAGE_FOLDER', 'images');
define('IMAGE_WIDTH', 400);
define('DISTANCE_MEASURED_FROM_OPTIONS', ["Earth","Sun","Milky Way","Planet"]);
$new = true;


// Redirect if not logged in or unauthorized
if($_SESSION['login_status'] !== 'loggedin' || !$_SESSION['login_account']['user_is_admin'])
{
    header("Location: index.php");
    die();
}


// Redirect the user to this page, but without any GET/POST data (to create a new object)
function redirect()
{ 
    header("Location: modifyObject.php");
    die();
}


// Processes the image, upload it to the server, and save path to database
function uploadImage($image)
{
    echo "Starting Upload";
    $image_new = new ImageResize($image['tmp_name']);
    $image_new->resizeToWidth(IMAGE_WIDTH);
    $url_new = 
        IMAGE_FOLDER . 
        DIRECTORY_SEPARATOR . 
        substr_replace(
            $image['name'], 
            ('_' . $_SESSION['login_account']['user_name'] . '_' . rand(100000,999999)), 
            strrpos($image['name'],'.'), 
            0);
    $image_new->save($url_new);
    return $url_new;
}


// Return the file path, full or relative to the current directory
function getFilePath($original_filename, $fullUrl) 
{
    $current_folder = dirname(__FILE__);
    $path_segments = [$current_folder, IMAGE_FOLDER, basename($original_filename)];
    if (!$fullUrl)
        array_shift($path_segments);
    return join(DIRECTORY_SEPARATOR, $path_segments);
}


// Delete an object's image
function deleteObjectImage($database, $object_id)
{
    $query = 'SELECT object_media FROM ' . OBJECT_TABLE_NAME . ' WHERE object_id=:object_id LIMIT 1';
    $statement = $database->prepare($query);
    $statement->bindValue(':object_id', $object_id);
    $statement->execute();
    $object = $statement->fetch();
    unlink($object['object_media']);
}


// Loads object's information
function getObjectInfo($database, $id)
{
    $query = 'SELECT * FROM ' . OBJECT_TABLE_NAME . ' WHERE object_id=:id LIMIT 1';
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $id);
    $statement->execute();

    return $statement->fetch();
}


// Delete an object from the database
function deleteObject($database)
{
    // Delete an image if there is any
    deleteObjectImage($database, $_GET['id']);

    // Delete the object
    $query = "DELETE FROM " . OBJECT_TABLE_NAME . " WHERE object_id=:id LIMIT 1";
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $_GET['id']);
    $statement->execute();

    // Delete all category relationships assigned to the object
    $query = "DELETE FROM " . CAT_TO_OBJ_TABLE . " WHERE object_id=:id";
    $statement = $database->prepare($query);
    $statement->bindValue(':id', $_GET['id']);
    $statement->execute();

    // Redirect to the list of all objects
    header("Location: index.php#main");
    die();
}

// Edit the object in the database
function editObject($database)
{
    // Ensure data is valid (ID was already validated)
    if (empty($_POST['name'])
    || empty($_POST['location'])
    || empty($_POST['mass'])
    || empty($_POST['radius'])
    || empty($_POST['radius_unit'])
    || empty($_POST['distance'])
    || empty($_POST['distance_unit'])
    || empty($_POST['distance_from'])
    )
    redirect();

    // Initalize the variables
    $object_name = "";
    $object_scientific_name = null;
    $object_location = "";
    $object_mass_kg = 0;
    $object_radius = 0;
    $object_radius_unit = "";
    $object_distance = 0;
    $object_distance_unit = "";
    $object_distance_from = "";
    $object_description = null;
    $object_media = null;

    // Validation and Sanatization
    $object_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_radius_unit = filter_input(INPUT_POST, 'radius_unit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_distance_unit = filter_input(INPUT_POST, 'distance_unit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($_POST['scientific_name']))
        $object_scientific_name = filter_input(INPUT_POST, 'scientific_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_distance_from = filter_input(INPUT_POST, 'distance_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (!in_array($object_distance_from, DISTANCE_MEASURED_FROM_OPTIONS))
        redirect();

    if (!filter_input(INPUT_POST, 'mass', FILTER_VALIDATE_FLOAT))
        redirect();

    if (!filter_input(INPUT_POST, 'radius', FILTER_VALIDATE_FLOAT))
        redirect();

    if (!filter_input(INPUT_POST, 'distance', FILTER_VALIDATE_FLOAT))
        redirect();

    // Set double / floats
    $object_mass_kg = $_POST['mass'];
    $object_radius = $_POST['radius'];
    $object_distance = $_POST['distance'];

    // Delete existing image
    if (!empty($_POST['delete_image']))
        deleteObjectImage($database, $_POST['id']);

    // Add an image
    if (!empty($_FILES['image']) 
        && $_FILES['image']['error'] === 0 
        && empty($_POST['delete_image'])
        && in_array($_FILES['image']['type'], ALLOWED_IMAGE_MIMES))
    {
        // Delete any existing image
        deleteObjectImage($database, $_POST['id']);

        // Upload the new image and set the object's path
        $object_media = uploadImage($_FILES['image']);
    }

    // Update the object in the database
    $query = "UPDATE ".OBJECT_TABLE_NAME." SET 
            object_name=:object_name, 
            object_scientific_name=:object_scientific_name, 
            object_mass_kg=:object_mass_kg, 
            object_radius=:object_radius,
            object_radius_unit=:object_radius_unit,
            object_distance=:object_distance,
            object_distance_from=:object_distance_from,
            object_distance_unit=:object_distance_unit,
            object_location=:object_location,
            object_description=:object_description,
            object_media=:object_media
        WHERE object_id = :object_id
        LIMIT 1
        ";
    $statement = $database->prepare($query);
    $statement->bindValue(':object_id', $_POST['id']);
    $statement->bindValue(':object_name', $object_name);
    $statement->bindValue(':object_scientific_name', $object_scientific_name);
    $statement->bindValue(':object_mass_kg', $object_mass_kg);
    $statement->bindValue(':object_radius', $object_radius);
    $statement->bindValue(':object_radius_unit', $object_radius_unit);
    $statement->bindValue(':object_distance', $object_distance);
    $statement->bindValue(':object_distance_from', $object_distance_from);
    $statement->bindValue(':object_distance_unit', $object_distance_unit);
    $statement->bindValue(':object_location', $object_location);
    $statement->bindValue(':object_description', $object_description);
    $statement->bindValue(':object_media', $object_media);

    $statement->execute();

    // Redirect to the full page for the object that was updated
    header("Location: fullObjectPage.php?id=".$_POST['id']."#celestial_object");
    die();
}


// Add a new object to the database
function createObject($database)
{
    // Ensure data is valid
    if (empty($_POST['name'])
        || empty($_POST['location'])
        || empty($_POST['mass'])
        || empty($_POST['radius'])
        || empty($_POST['radius_unit'])
        || empty($_POST['distance'])
        || empty($_POST['distance_unit'])
        || empty($_POST['distance_from'])
    )
        redirect();

    // Initalize the variables
    $object_name = "";
    $object_scientific_name = null;
    $object_location = "";
    $object_mass_kg = 0;
    $object_radius = 0;
    $object_radius_unit = "";
    $object_distance = 0;
    $object_distance_unit = "";
    $object_distance_from = "";
    $object_description = null;
    $object_media = null;

    // Validation and Sanatization
    $object_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_radius_unit = filter_input(INPUT_POST, 'radius_unit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_distance_unit = filter_input(INPUT_POST, 'distance_unit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (!empty($_POST['scientific_name']))
        $object_scientific_name = filter_input(INPUT_POST, 'scientific_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $object_distance_from = filter_input(INPUT_POST, 'distance_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (!in_array($object_distance_from, DISTANCE_MEASURED_FROM_OPTIONS))
        redirect();

    if (!filter_input(INPUT_POST, 'mass', FILTER_VALIDATE_FLOAT))
        redirect();

    if (!filter_input(INPUT_POST, 'radius', FILTER_VALIDATE_FLOAT))
        redirect();

    if (!filter_input(INPUT_POST, 'distance', FILTER_VALIDATE_FLOAT))
        redirect();

    // Set double / floats
    $object_mass_kg = $_POST['mass'];
    $object_radius = $_POST['radius'];
    $object_distance = $_POST['distance'];

    // Add an image
    if (!empty($_FILES['image']) 
    && $_FILES['image']['error'] === 0 
    && in_array($_FILES['image']['type'], ALLOWED_IMAGE_MIMES))
    {
        // Delete any existing image
        deleteObjectImage($database, $_POST['id']);

        // Upload the new image and set the object's path
        $object_media = uploadImage($_FILES['image']);
    }
    
    // Create new entry using the data
    $query = "INSERT INTO ".OBJECT_TABLE_NAME." (
            object_name, 
            object_scientific_name, 
            object_mass_kg, 
            object_radius,
            object_radius_unit,
            object_distance,
            object_distance_from,
            object_distance_unit,
            object_location,
            object_description,
            object_media
        ) 
        VALUES (
            :object_name, 
            :object_scientific_name, 
            :object_mass_kg, 
            :object_radius,
            :object_radius_unit,
            :object_distance,
            :object_distance_from,
            :object_distance_unit,
            :object_location,
            :object_description,
            :object_media
        )";
    $statement = $database->prepare($query);
    $statement->bindValue(':object_name', $object_name);
    $statement->bindValue(':object_scientific_name', $object_scientific_name);
    $statement->bindValue(':object_mass_kg', $object_mass_kg);
    $statement->bindValue(':object_radius', $object_radius);
    $statement->bindValue(':object_radius_unit', $object_radius_unit);
    $statement->bindValue(':object_distance', $object_distance);
    $statement->bindValue(':object_distance_from', $object_distance_from);
    $statement->bindValue(':object_distance_unit', $object_distance_unit);
    $statement->bindValue(':object_location', $object_location);
    $statement->bindValue(':object_description', $object_description);
    $statement->bindValue(':object_media', $object_media);

    $statement->execute();

    // Redirect to the list of all objects
    header("Location: index.php#main");
    die();
}


// Determine what the page should do.
if($_GET)
{
    if (!empty($_GET['id']) && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
    {
        if (!empty($_GET['delete']))
        {
            deleteObject($db);
        }
        elseif (!empty($_GET['edit']))
        {
            $object = getObjectInfo($db, $_GET['id']);
            $new = false;
        }
    }
    else
        redirect();
}
elseif($_POST)
{
    if (empty($_POST['update']))
        redirect();

    if ($_POST['update'] === "Create")
        createObject($db);
    elseif ($_POST['update'] === "Update" && !empty($_POST['id']) && filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT))
        editObject($db);
    else
        redirect();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Manage Users</title>
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

    <main id="modify">

        <!-- Create / Edit Object Form -->
        <h2> <?= $new ? "Create New Object":"Edit Object" ?></h2>
        <form method='post' action='modifyObject.php' enctype="multipart/form-data">
            <input type="hidden" name="id" value=<?= $new ? 'new' : $object['object_id'] ?>>
            <label for='name'>Name:</label>
            <input id='name' name='name' type="text" value='<?= $new ? '' : $object['object_name'] ?>'><br> 
            <label for='scientific_name'>Scientific Name (optional):</label>
            <input id='scientific_name' name='scientific_name' type="text" value='<?= $new ? '' : $object['object_scientific_name'] ?>'><br> 
            <label for='location'>Location:</label>
            <input id='location' name='location' type="text" value='<?= $new ? '' : $object['object_location'] ?>'><br> 
            <label for='mass'>Mass (kg):</label>
            <input id='mass' name='mass' type="text" value='<?= $new ? '' : $object['object_mass_kg'] ?>'><br> 
            <label for='radius'>Radius:</label>
            <input id='radius' name='radius' type="text" value='<?= $new ? '' : $object['object_radius'] ?>'><br> 
            <label for='radius_unit'>Radius Unit of Measurement (ie km):</label>
            <input id='radius_unit' name='radius_unit' type="text" value='<?= $new ? '' : $object['object_radius_unit'] ?>'><br> 
            <label for='distance'>Distance:</label>
            <input id='distance' name='distance' type="text" value='<?= $new ? '' : $object['object_distance'] ?>'><br> 
            <label for='distance_unit'>Distance Unit of Measurement (ie km):</label>
            <input id='distance_unit' name='distance_unit' type="text" value='<?= $new ? '' : $object['object_distance_unit'] ?>'><br> 
            <label for='distance_from'>Distance Measured From:</label>
            <select id="distance_from" name="distance_from">

                <!-- Add options for the select, corresponding to the database enum -->
                <!-- If editing an exisiting object, add 'selected' to the correct option tag -->
                <?php for ($i=0; $i < count(DISTANCE_MEASURED_FROM_OPTIONS); $i++): ?>
                    <option 
                        value="<?= DISTANCE_MEASURED_FROM_OPTIONS[$i] ?>" 
                        <?php if (!$new): ?>
                            <?= DISTANCE_MEASURED_FROM_OPTIONS[$i] === $object['object_distance_from'] ? 'selected':'' ?>
                        <?php endif ?>
                    >
                        <?= DISTANCE_MEASURED_FROM_OPTIONS[$i] ?>
                    </option>
                <?php endfor ?>
            </select>
            <label for='description'>Description:</label>
            <textarea id='description' name='description'><?= $new ? '' : $object['object_description'] ?></textarea><br> 

            <!-- Image Upload / Removal -->
            <label for="image"><?= empty($object['object_media']) ? "Upload":"Replace" ?> Image:</label>
            <input type="file" name="image" id="image">
            <?php if (!$new && !empty($object['object_media'])): ?>
                <label for="delete_image">Check to Remove Image:</label>
                <input type="checkbox" name="delete_image" id="delete_image">
            <?php endif ?>

            <input id="update" name='update' type="submit" value="<?= $new ? 'Create' : 'Update' ?>">
        </form>
    </main>
</body>
</html>