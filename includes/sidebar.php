<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-vr-cardboard"></i>
        </div>
        <div class="sidebar-brand-text mx-3">VR Tour Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Tours -->
    <li class="nav-item <?php echo $current_page == 'manage-tours.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-tours.php">
            <i class="fas fa-fw fa-map-marked"></i>
            <span>Tours</span></a>
    </li>

    <!-- Nav Item - Scenes -->
    <li class="nav-item <?php echo $current_page == 'manage-scenes.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-scenes.php">
            <i class="fas fa-fw fa-image"></i>
            <span>Scenes</span></a>
    </li>

    <!-- Nav Item - Hotspots -->
    <li class="nav-item <?php echo $current_page == 'manage-hotspots.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-hotspots.php">
            <i class="fas fa-fw fa-dot-circle"></i>
            <span>Hotspots</span></a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item <?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-users.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span></a>
    </li>

    <!-- Nav Item - Reviews -->
    <li class="nav-item <?php echo $current_page == 'manage-reviews.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-reviews.php">
            <i class="fas fa-fw fa-star"></i>
            <span>Reviews</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->