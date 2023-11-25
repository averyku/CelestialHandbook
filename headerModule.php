<?php
/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 23rd
    Updated: 2023 November 25th

****************/
?>


<header>

    <!-- Main Title -->
    <h1><a href="index.php">The Celestial Handbook</a></h1>

    <!-- Login / Out Panel -->
    <?php require('loginModule.php'); ?>

    <!-- Navigation -->
    <nav>
        <ul>
            <li onclick="window.location.href = 'index.php#main'">All Objects</li>
            <?php if(isLoggedIn()): ?>
                <li onclick="window.location.href = 'sortedObjects.php#main'">Sorted Objects</li>
            <?php endif ?>
            <li onclick="window.location.href = 'objectsByCategory.php#main'">All Categories</li>
            <li onclick="window.location.href = 'http://www.asc-csa.gc.ca/eng/'">Canadian Space Agency</li>
            <?php if(isAdmin()): ?>
                <li onclick="window.location.href = 'manageUsers.php'">Manage Users</li>
            <?php endif ?>
        </ul>
        <form method='get' id="search_bar" action='searchPage.php'>
            <input id='search_query' name='search' type="text" value=''>
            <input id="search_submit" type="submit" value="Search &#x1F50D;">
        </form>
    </nav>

</header>
