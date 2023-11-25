<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 4th
    Updated: 2023 November 24th

****************/

session_start();
require('connect.php');
require('globalFunctions.php');
define('USER_TABLE_NAME', 'users');

// Redirect if not logged in or unauthorized
if(!isAdmin())
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
    <?php require('headerModule.php'); ?>

    <div id="admin_panel">
        <!-- Table of All Users -->
        <input type="submit" value="Create New User" onclick="window.location.href = 'editUser.php'">
        <table>
            <tr>
                <th></th>
                <th></th>
                <th>ID</th>
                <th>Admin</th>
                <th>Username</th>
                <th>Email Address</th>
            </tr>
            <?php while ($row = $user_statement->fetch()):?>
                <tr>
                    <td>
                        <!-- Do not provide option for a user to edit/delete their own account -->
                        <?php if($row['user_id'] === $_SESSION['login_account']['user_id']): ?>
                            </td>
                            <td>
                        <?php else: ?>
                                <a href='editUser.php?id=<?= $row['user_id'] ?>'><b>Edit</b></a>
                            </td>
                            <td>
                                <a href='editUser.php?id=<?= $row['user_id'] ?>&delete=true'><b>Delete</b></a>
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

    <!-- Footer -->
    <?php require('footerModule.php'); ?>
</body>
</html>