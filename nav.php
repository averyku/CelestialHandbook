<?php

/*******w******** 
    
    Name: Avery Kuboth
    Description: WEBD-2013 Project - Celestial Handbook
    Date: 2023 November 11th
    Updated: 2023 November 11th

****************/


?>

<!-- Display Navigation Elements -->
<ul>
    <li><a href="index.php">All Celestial Objects</a></li>
    <li><a href="objectsByCategory.php">All Object Categories</a></li>
    <li><a href="https://www.asc-csa.gc.ca/eng/">Canadian Space Agency</a></li>
    <?php if($_SESSION['login_status'] === 'loggedin' && $_SESSION['login_account']['user_is_admin']): ?>
        <li><a href="manageUsers.php">Manage Users</a></li>
    <?php endif ?>
</ul>