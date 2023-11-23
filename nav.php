<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 11th
    Updated: 2023 November 23rd

****************/


?>

<!-- Display Navigation Elements -->
<ul>
    <li onclick="window.location.href = 'index.php#main'">All Objects</li>
    <?php if($_SESSION['login_status'] === 'loggedin'): ?>
        <li onclick="window.location.href = 'sortedObjects.php'">Sorted Objects</li>
    <?php endif ?>
    <li onclick="window.location.href = 'objectsByCategory.php#main'">All Categories</li>
    <li onclick="window.location.href = 'http://www.asc-csa.gc.ca/eng/'">Canadian Space Agency</li>
    <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
        <li onclick="window.location.href = 'manageUsers.php'">Manage Users</li>
    <?php endif ?>
</ul>
