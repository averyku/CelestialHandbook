<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 23rd
    Updated: 2023 November 23rd

****************/


session_start();
require('connect.php');
define('OBJECT_TABLE_NAME', 'celestial_objects');
define('COLUMN_LOOKUP', array("Name"=>"object_name", "Mass"=>"object_mass_kg", "Location"=>"object_location"));


// Redirect if user not logged in
if($_SESSION['login_status'] !== 'loggedin')
{
    header("Location: index.php");
    die();
}


// Initalize session variable to keep track of sorting method
if (empty($_SESSION['sort']))
    $_SESSION['sort'] = array("column"=>"Mass","direction"=>"DESC");


// Updates the sorting parameters stored in the session (if valid GET was provided)
if ($_GET)
{
    if (!empty($_GET['sortBy']) && array_key_exists($_GET['sortBy'],COLUMN_LOOKUP))
        $_SESSION['sort']['column'] = $_GET['sortBy'];
    if (!empty($_GET['sortDirection']) && $_GET['sortDirection'] === "ASC" || $_GET['sortDirection'] === "DESC")
        $_SESSION['sort']['direction'] = $_GET['sortDirection'];
}


// Double flip session sort direction (Incase it is invalid)
$_SESSION['sort']['direction'] = flipDirection(flipDirection($_SESSION['sort']['direction']));
    

// Select all objects sorted appropriately 
$query = 'SELECT '.implode(", ",COLUMN_LOOKUP).' FROM '.OBJECT_TABLE_NAME.' ORDER BY '.COLUMN_LOOKUP[$_SESSION['sort']["column"]].' '.$_SESSION['sort']["direction"];
$statement = $db->prepare($query);
$statement->execute();


// Returns the opposite direction of the direction provided
function flipDirection($direction)
{
    if($direction === "ASC")
        return "DESC";
    elseif($direction === "DESC")
        return "ASC";
    else
        return "ASC";
}


// Formats a double/float to be more human readable
function formatDouble($value)
{
    if (strstr(strval($value),"E+"))
        return str_replace("E+"," x 10<sup>",strval($value))."</sup>";
    if (strstr(strval($value),"E-"))
        return str_replace("E-"," x 10<sup>-",strval($value))."</sup>";
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
    <title>Sorted Objects</title>
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
    
    <main id="main">
        <h2>All Objects Sorted By <?=$_SESSION['sort']['column']?></h2>
        <table id="sorted_object_table">

            <!-- Display Sorting Options / Table Headers -->
            <tr>
                <?php foreach (COLUMN_LOOKUP as $key => $value): ?>
                    <th>
                        <!-- Each header has a link with GET info that will update the sorting appropriately -->
                        <a href='sortedObjects.php?sortBy=<?=$key?>&sortDirection=<?=($key === $_SESSION['sort']['column']) ? flipDirection($_SESSION['sort']['direction']) : $_SESSION['sort']['direction'] ?>'>
                            <?=$key?>
                            <?php if ($key === $_SESSION['sort']['column']): ?>
                                <?= ($_SESSION['sort']['direction'] === "ASC") ? "&#9650":"&#9660" ?>
                            <?php endif ?>
                        </a>
                    </th>
                <?php endforeach ?>
            </tr>

            <!-- Display a row for each object -->
            <?php while ($row = $statement->fetch()): ?>    
                <tr>
                    <td>
                        <a href='fullObjectPage.php?id=<?= $row['object_id'] ?>#celestial_object'>
                            <?= $row['object_name'] ?>
                        </a>
                    </td>
                    <td><?= formatDouble($row['object_mass_kg']) ?></td>
                    <td><?= $row['object_location'] ?></td>
                </tr>
            <?php endwhile ?>
        </table>
    </main>
    
    <footer><p>Copywrong 2023 - No Rights Reserved</p></footer>
</body>
</html>
