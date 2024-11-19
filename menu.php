<nav>
    <ul>
        <li class="<?php echo ($page_name == 'index' or $page_name == '') ? 'active' : ''; ?>">
            <a href="index.php">Home</a>
        </li>
        <li class="<?php echo $page_name == 'user-sync' ? 'active' : ''; ?>">
            <a href="user-sync.php">Sync user</a>
        </li>
        <li class="<?php echo $page_name == 'export' ? 'active' : ''; ?>">
            <a href="export.php">Export</a>
        </li>
        <li class="<?php echo $page_name == 'info' ? 'active' : ''; ?>">
            <a href="info.php">Info</a>
        </li>
        <li class="<?php echo $page_name == 'configuration' ? 'active' : ''; ?>">
            <a href="configuration.php">Configuration</a>
        </li>


    </ul>
</nav>
<hr>