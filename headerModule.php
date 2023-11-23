<?php
/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 23rd
    Updated: 2023 November 23rd

****************/
?>


<header>

    <!-- Main Title -->
    <h1><a href="index.php">The Celestial Handbook</a></h1>

    <!-- Login / Out Panel -->
    <div id="login_module">
        <?php require('loginModule.php'); ?>
    </div>

    <!-- Navigation -->
    <nav>
        <ul>
            <li onclick="window.location.href = 'index.php#main'">All Objects</li>
            <?php if($_SESSION['login_status'] === 'loggedin'): ?>
                <li onclick="window.location.href = 'sortedObjects.php#main'">Sorted Objects</li>
            <?php endif ?>
            <li onclick="window.location.href = 'objectsByCategory.php#main'">All Categories</li>
            <li onclick="window.location.href = 'http://www.asc-csa.gc.ca/eng/'">Canadian Space Agency</li>
            <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
                <li onclick="window.location.href = 'manageUsers.php'">Manage Users</li>
            <?php endif ?>
        </ul>
    </nav>

</header>
