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
$new = true;


// Redirect if not logged in or unauthorized
if(!isAdmin())
{
    header("Location: index.php");
    die();
}


// Redirect the user to the manageUsers page
function redirect()
{ 
    header("Location: manageUsers.php");
    die();
}


// Create a new user record
function newUser($db)
{
    $username = "";
    $email = "";
    $admin = false;
    $password_hash = false;

    // Check for empty fields
    if (empty($_POST['username']) 
    || empty($_POST['email']) 
    || empty($_POST['password'])
    || empty($_POST['confirm_password']))
    {
        echo '<p class="edit_user_error">Missing Required Field</p>';
        return;
    }

    // Set username
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate Email
    if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))
    {
        echo '<p class="edit_user_error">Invalid Email Address</p>';
        return;
    }
    else
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Set Admin Privileges
    $admin = empty($_POST['admin']) ? false : true;

    // Verify both passwords match
    if($_POST['password'] !== $_POST['confirm_password'])
    {
        echo '<p class="edit_user_error">Passwords do not match</p>';
        return;
    }
    else
        $password_hash = password_hash($_POST['password'],PASSWORD_DEFAULT);

    // Ensure password hash was sucessful            
    if($password_hash === false)
    {
        echo '<p class="edit_user_error">Password Hashing Failed</p>';
        return;
    }
    
    // Add new user to database
    $edit_query = "INSERT INTO ".USER_TABLE_NAME." (user_name, user_email, user_pass, user_is_admin) VALUES (:username, :email, :password_hash, :admin)";
    $edit_statement = $db->prepare($edit_query);
    $edit_statement->bindValue(':username', $username);
    $edit_statement->bindValue(':email', $email);
    $edit_statement->bindValue(':password_hash', $password_hash);
    $edit_statement->bindValue(':admin', $admin);
    $edit_statement->execute();
    redirect();
}


// Delete the user from the database
function deleteUser($db)
{
    // Validate ID
    if(empty($_GET['id']) || !filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
        redirect();
        
    // Ensure user is not trying to delete their own account
    if($_GET['id'] == $_SESSION['login_account']['user_id'])
        redirect();

    // Delete user from database
    $query = "DELETE FROM ".USER_TABLE_NAME." WHERE user_id=:id LIMIT 1";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $_GET['id']);
    $statement->execute();
    redirect();
}


// Edits user's information
function updateUser($db)
{
    $id = "";
    $username = "";
    $email = "";
    $admin = false;
    $password_hash = false;

    // Check for empty fields
    if (empty($_POST['id']) 
    || empty($_POST['username'])
    || empty($_POST['email']))
    {
        echo '<p class="edit_user_error">Missing Required Field</p>';
        return;
    }
    
    // Validate ID
    if(!filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT))
    {
        echo '<p class="edit_user_error">Invalid ID</p>';
        return;
    }
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

    // Set username
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate Email
    if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))
    {
        echo '<p class="edit_user_error">Invalid Email Address</p>';
        return;
    }
    else
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Set Admin Privileges
    $admin = empty($_POST['admin']) ? false : true;

    // No Password Entered
    if (empty($_POST['password']) && empty($_POST['confirm_password']))
    {
        // Update without changing password
        $edit_query = 'UPDATE ' . USER_TABLE_NAME . ' SET user_name=:username, user_email=:email, user_is_admin=:admin WHERE user_id=:id';
        $edit_statement = $db->prepare($edit_query);
        $edit_statement->bindValue(':id', $id);
        $edit_statement->bindValue(':username', $username);
        $edit_statement->bindValue(':email', $email);
        $edit_statement->bindValue(':admin', $admin);
        $edit_statement->execute();

        // Redirect and die when finished
        redirect();
    }

    // Verify both passwords match
    if($_POST['password'] !== $_POST['confirm_password'])
    {
        echo '<p class="edit_user_error">Passwords do not match</p>';
        return;
    }

    // Hash the password
    $password_hash = password_hash($_POST['password'],PASSWORD_DEFAULT);

    // Ensure password hash was sucessful            
    if($password_hash === false)
    {
        echo '<p class="edit_user_error">Password Hashing Failed</p>';
        return;
    }

    // Update the account
    $edit_query = 'UPDATE ' . USER_TABLE_NAME . ' SET user_name=:username, user_email=:email, user_pass=:password_hash, user_is_admin=:admin WHERE user_id=:id';
    $edit_statement = $db->prepare($edit_query);
    $edit_statement->bindValue(':id', $id);
    $edit_statement->bindValue(':username', $username);
    $edit_statement->bindValue(':email', $email);
    $edit_statement->bindValue(':password_hash', $password_hash);
    $edit_statement->bindValue(':admin', $admin);
    $edit_statement->execute();

    // Redirect and die when finished
    redirect();
}


// Determine what the page should do.
if($_GET)
{
    // Delete
    if(!empty($_GET['delete']) && $_GET['delete'] == true)
        deleteUser($db);

    // Create a form to edit user instead
    if(!empty($_GET['id']) && filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT))
    {
        $new = false;

        // Get records of the specified user
        $query = 'SELECT * FROM ' . USER_TABLE_NAME . ' WHERE user_id=:id LIMIT 1';
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $_GET['id']);
        $statement->execute();

        $user = $statement->fetch();
    }
    // Invalid ID
    else 
        redirect();
}
elseif($_POST)
{
    // Create new user
    if (!empty($_POST['id']))
    {
        if ($_POST['id'] === 'new')
            newUser($db);
        else
            updateUser($db);
    }
    // ID not set
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
    <title>Edit User</title>
</head>
<body>

    <!-- Header -->
    <?php require('headerModule.php'); ?>

    <div id="admin_panel">
        <!-- Create / Edit User Form -->
        <h2> <?= $new ? "Create New Account":"Edit Account" ?></h2>
        <form method='post' action='editUser.php'>
            <input type="hidden" name="id" value=<?= $new ? 'new' : $user['user_id'] ?>>
            <label for='username'>Username:</label>
            <input id='username' name='username' type="text" value='<?= $new ? '' : $user['user_name'] ?>'><br> 
            <label for='email'>Email Address:</label>
            <input id='email' name='email' type="email" value='<?= $new ? '' : $user['user_email'] ?>'><br> 
            <label for='admin'>Admin Privileges:</label>
            <input id='admin' type='checkbox' name='admin' <?= $new ? '' : ($user['user_is_admin'] ? "Checked":"")?>><br> 
            <label for='password'>Password:</label>
            <input id='password' type='password' name='password'><br> 
            <label for='confirm_password'>Confirm Password:</label>
            <input id='confirm_password' type='password' name='confirm_password'><br> 
            <input id="update" name='update' type="submit" value="<?= $new ? 'Create' : 'Update' ?>">
        </form>
    </div>

    <!-- Footer -->
    <?php require('footerModule.php'); ?>
</body>
</html>