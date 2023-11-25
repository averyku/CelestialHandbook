<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 3rd
    Updated: 2023 November 23rd

****************/


if (!defined('USER_TABLE_NAME'))
    define('USER_TABLE_NAME', 'users');


// Start a session and initalize session variables
if (empty($_SESSION['login_status']))
{
    $_SESSION['login_status'] = 'guest';
    $_SESSION['login_account'] = '';
}


// The error that should be displayed, if any
$error = array("type"=>"none", "message"=>"none");


// Check for POSTed info
if ($_POST)
{
    if(!empty($_POST['create-account']))
    {
        // Registration Submitted

        // Check for empty fields
        if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password']))
            $error = array("type"=>"bad", "message"=>"All fields are required");
        else
        {
            // Check that both passwords match
            if ($_POST['password'] !== $_POST['confirm_password'])
                $error = array("type"=>"bad", "message"=>"Passwords do not match");
            else
            {
                // Check for valid email address
                if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))
                    $error = array("type"=>"bad", "message"=>"Invalid Email Address");
                else
                {
                    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                    $password_hash = password_hash($_POST['password'],PASSWORD_DEFAULT);

                    // Ensure password has was sucessful            
                    if($password_hash === false)
                        $error = array("type"=>"bad", "message"=>"Password Hashing Failed");
                    else
                    {
                        // Count the accounts with that username
                        $login_query = 'SELECT count(*) FROM ' . USER_TABLE_NAME . ' WHERE user_name LIKE :username';
                        $login_statement = $db->prepare($login_query);
                        $login_statement->bindValue(':username', $username);
                        $login_statement->execute();
            
                        // Check for existing account with that username
                        if ($login_statement->fetchColumn() > 0)
                            $error = array("type"=>"bad", "message"=>"That Username is already taken");
                        else
                        {
                            // Create the account
                            $login_query = 'INSERT INTO '.USER_TABLE_NAME.' (user_name, user_email, user_pass, user_is_admin) VALUES (:username, :email, :password_hash, 0)';
                            $login_statement = $db->prepare($login_query);
                            $login_statement->bindValue(':username', $username);
                            $login_statement->bindValue(':email', $email);
                            $login_statement->bindValue(':password_hash', $password_hash);
                            $login_statement->execute();
                            $error = array("type"=>"good", "message"=>"Account Created");

                            // Ensures the login page is displayed instead
                            $_POST['create-account'] = null;
                        }
                    }
                }
            }
        }
    }
    elseif(!empty($_POST['login']))
    {
        // Login Attempt
        if(empty($_POST['username']) || empty($_POST['password']))
            $error = array("type"=>"bad", "message"=>"You must enter a Username and Password");
        else
        {
            // Sanitize
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Find the account in the database
            $login_query = 'SELECT * FROM ' . USER_TABLE_NAME . ' WHERE user_name LIKE :username LIMIT 1';
            $login_statement = $db->prepare($login_query);
            $login_statement->bindValue(':username', $username);
            $login_statement->execute();
            $account = $login_statement->fetch();

            // Confirm if any account was found, and if the password matches
            if(empty($account['user_id']) || !password_verify($_POST['password'], $account['user_pass']))
                $error = array("type"=>"bad", "message"=>"Incorrect Username or Password");
            else
            {
                // Set the session to logged in and save the account information
                $error = array("type"=>"good", "message"=>"Sucessful Login");
                $_SESSION['login_status'] = 'loggedin';
                $_SESSION['login_account'] = $account;
            }
        }
    }
    elseif(!empty($_POST['logout']))
    {
        // User logged out
        $_SESSION['login_status'] = 'guest';
        $_SESSION['login_account'] = '';
        
        header("Location: index.php");
    }
}
?>


<div id="login_module">

    <!-- User is logged in -->
    <?php if(isLoggedIn()): ?>
        <?php if ($error['type'] !== "none"): ?>
            <p class="<?= ($error['type'] === "bad") ? "login_error":"login_success" ?>"><?=$error['message']?></p>
        <?php endif ?>
        <form id="loginModule_loggedIn" method='post' action='#'>
            <p>Account: <?= $_SESSION['login_account']['user_name'] ?></p>
            <input id="logout" name='logout' type="submit" value="Logout">
        </form>

    <!-- User is registering a new account (possibly failed previous registration) -->
    <?php elseif(!empty($_POST['register']) || !empty($_POST['create-account'])): ?>
        <form id="loginModule_loggedOut" method='post' action='#'>
            <?php if ($error['type'] !== "none"): ?>
                <p class="<?= ($error['type'] === "bad") ? "login_error":"login_success" ?>"><?=$error['message']?></p>
            <?php endif ?>
            <p>Register an Account</p>
            <label for='username'>Username:</label>
            <input id='username' name='username'><br> 
            <label for='email'>Email Address:</label>
            <input id='email' type="email" name='email'><br> 
            <label for='password'>Password:</label>
            <input id='password' type='password' name='password'><br> 
            <label for='confirm_password'>Confirm Password:</label>
            <input id='confirm_password' type='password' name='confirm_password'><br> 
            <input id="create-account" name='create-account' type="submit" value="Create Account">
        </form>

    <!-- User is a guest (not signed in) -->
    <?php else: ?>
        <form id="loginModule_loggedOut" method='post' action='#'>
            <?php if ($error['type'] !== "none"): ?>
                <p class="<?= ($error['type'] === "bad") ? "login_error":"login_success" ?>"><?=$error['message']?></p>
            <?php endif ?>
            <p>Login to Your Account</p>
            <label for='username'>Username:</label>
            <input id='username' name='username'><br>  
            <label for='password'>Password:</label>
            <input id='password' type ='password' name='password'><br> 
            <div>
                <input id="login" name='login' type="submit" value="Login">
                <input id="register" name='register' type="submit" value="Register">
            </div>
        </form>
    <?php endif ?>

</div>
