<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 4th
    Updated: 2023 November 11th

****************/

session_start();
require('connect.php');
define('USER_TABLE_NAME', 'users');

// Redirect if not logged in or unauthorized
if($_SESSION['login_status'] !== 'loggedin' || !$_SESSION['login_account']['user_is_admin'])
{
    header("Location: index.php");
    die();
}

// Get records of all users
$user_query = 'SELECT * FROM ' . USER_TABLE_NAME;
$user_statement = $db->prepare($user_query);
$user_statement->execute();


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

    <div id="admin_panel">
        <!-- Table of All Users -->
        <input type="submit" value="Create New User" onclick="window.location.href = 'editUser.php'">
        <table>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Admin</th>
                <th>Username</th>
                <th>Email Address</th>
            </tr>
            <?php while ($row = $user_statement->fetch()):?>
                <tr>
                    <td>
                        <?php if($row['user_id'] === $_SESSION['login_account']['user_id']): ?>
                            Your Account
                        <?php else: ?>
                        <a href='editUser.php?id=<?= $row['user_id'] ?>'>Edit</a> / <a href='editUser.php?id=<?= $row['user_id'] ?>&delete=true'>Delete</a>
                        <?php endif ?>
                    </td>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= $row['user_is_admin'] ? "Yes":"No" ?></td>
                    <td><?= $row['user_name'] ?></td>
                    <td><?= $row['user_email'] ?></td>
                </tr>
            <?php endwhile ?>
        </table>
    </div>
</body>
</html>