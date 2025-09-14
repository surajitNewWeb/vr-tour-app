<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    
#wrapper {
    display: flex;
}

#content-wrapper {
    width: 100%;
    overflow-x: hidden;
}
    /* Enhanced Professional Sidebar Styles */
    .sidebar {
        background: linear-gradient(180deg, #1e3c72 0%, #2a5298 50%, #667eea 100%) !important;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
    }

    .sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.03"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.02"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.04"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
    }

    /* Enhanced Brand Section */
    .sidebar-brand {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 0 !important;
        padding: 25px 20px !important;
        text-decoration: none !important;
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
    }

    .sidebar-brand:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        transform: translateY(-2px);
    }

    .sidebar-brand-icon {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3);
        transition: all 0.3s ease;
    }

    .sidebar-brand:hover .sidebar-brand-icon {
        transform: rotate(-5deg) scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
    }

    .sidebar-brand-icon i {
        color: white;
        font-size: 20px;
    }

    .sidebar-brand-text {
        color: white !important;
        font-weight: 700 !important;
        font-size: 1.2rem !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
    }

    /* Enhanced Dividers */
    .sidebar-divider {
        border-color: rgba(255, 255, 255, 0.15) !important;
        margin: 20px 0 !important;
        position: relative;
        z-index: 2;
    }

    .sidebar-divider.my-0 {
        margin: 0 !important;
    }

    /* Enhanced Section Headings */
    .sidebar-heading {
        color: rgba(255, 255, 255, 0.7) !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 1.5px !important;
        padding: 0 20px 10px 20px !important;
        margin-top: 10px !important;
        position: relative;
        z-index: 2;
    }

    .sidebar-heading::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 20px;
        right: 20px;
        height: 2px;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.3), transparent);
        border-radius: 1px;
    }

    /* Enhanced Navigation Items */
    .sidebar .nav-item {
        margin: 5px 15px;
        position: relative;
        z-index: 2;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        padding: 15px 20px !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }

    .sidebar .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .sidebar .nav-link:hover::before {
        left: 100%;
    }

    .sidebar .nav-link:hover {
        color: white !important;
        background: rgba(255, 255, 255, 0.1) !important;
        transform: translateX(8px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Enhanced Active State */
    .sidebar .nav-item.active .nav-link {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1)) !important;
        color: white !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        transform: translateX(5px);
        border-left: 4px solid #ff6b6b;
    }

    .sidebar .nav-item.active .nav-link::after {
        content: '';
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: #ff6b6b;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(255, 107, 107, 0.6);
    }

    /* Enhanced Icons */
    .sidebar .nav-link i {
        margin-right: 15px !important;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover i,
    .sidebar .nav-item.active .nav-link i {
        transform: scale(1.1);
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }

    /* Enhanced Toggle Button */
    #sidebarToggle {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 2px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        transition: all 0.3s ease !important;
        position: relative;
        z-index: 2;
        margin-bottom: 20px;
    }

    #sidebarToggle:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    #sidebarToggle:focus {
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3) !important;
    }

    /* Navigation Item Hover Effects with Different Colors */
    .sidebar .nav-item:nth-child(3) .nav-link:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(102, 126, 234, 0.1)) !important;
    }

    .sidebar .nav-item:nth-child(6) .nav-link:hover {
        background: linear-gradient(135deg, rgba(16, 172, 132, 0.3), rgba(16, 172, 132, 0.1)) !important;
    }

    .sidebar .nav-item:nth-child(7) .nav-link:hover {
        background: linear-gradient(135deg, rgba(55, 66, 250, 0.3), rgba(55, 66, 250, 0.1)) !important;
    }

    .sidebar .nav-item:nth-child(8) .nav-link:hover {
        background: linear-gradient(135deg, rgba(255, 159, 243, 0.3), rgba(255, 159, 243, 0.1)) !important;
    }

    .sidebar .nav-item:nth-child(9) .nav-link:hover {
        background: linear-gradient(135deg, rgba(254, 202, 87, 0.3), rgba(254, 202, 87, 0.1)) !important;
    }

    .sidebar .nav-item:nth-child(10) .nav-link:hover {
        background: linear-gradient(135deg, rgba(255, 107, 107, 0.3), rgba(255, 107, 107, 0.1)) !important;
    }

    /* Responsive Enhancements */
    @media (max-width: 768px) {
        .sidebar {
            box-shadow: none;
        }

        .sidebar-brand {
            padding: 20px 15px !important;
        }

        .sidebar-brand-text {
            font-size: 1.1rem !important;
        }

        .sidebar .nav-item {
            margin: 3px 10px;
        }

        .sidebar .nav-link {
            padding: 12px 15px !important;
        }
    }

    /* Collapsed Sidebar State */
    .sidebar.toggled .sidebar-brand-text {
        display: none;
    }

    .sidebar.toggled .nav-link span {
        display: none;
    }

    .sidebar.toggled .sidebar-heading {
        display: none;
    }

    .sidebar.toggled .nav-link {
        text-align: center;
        padding: 15px 10px !important;
    }

    .sidebar.toggled .nav-link i {
        margin-right: 0 !important;
    }

    .sidebar.toggled .nav-item.active .nav-link::after {
        display: none;
    }

    /* Tooltip for collapsed sidebar */
    @media (min-width: 768px) {
        .sidebar.toggled .nav-link {
            position: relative;
        }

        .sidebar.toggled .nav-link:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            margin-left: 15px;
            opacity: 0;
            animation: fadeInTooltip 0.3s ease forwards;
        }

        @keyframes fadeInTooltip {
            to {
                opacity: 1;
            }
        }
    }

    /* Scrollbar Styling for Sidebar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>

<!-- Enhanced Sidebar -->
<ul class="sidebar navbar-nav">
    <!-- Enhanced Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-vr-cardboard"></i>
        </div>
        <div class="sidebar-brand-text">VR Tour Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php" data-tooltip="Dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Tours -->
    <li class="nav-item <?php echo $current_page == 'manage-tours.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-tours.php" data-tooltip="Tours">
            <i class="fas fa-fw fa-map-marked"></i>
            <span>Tours</span>
        </a>
    </li>

    <!-- Nav Item - Scenes -->
    <li class="nav-item <?php echo $current_page == 'manage-scenes.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-scenes.php" data-tooltip="Scenes">
            <i class="fas fa-fw fa-image"></i>
            <span>Scenes</span>
        </a>
    </li>

    <!-- Nav Item - Hotspots -->
    <li class="nav-item <?php echo $current_page == 'manage-hotspots.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-hotspots.php" data-tooltip="Hotspots">
            <i class="fas fa-fw fa-dot-circle"></i>
            <span>Hotspots</span>
        </a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item <?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-users.php" data-tooltip="Users">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span>
        </a>
    </li>

    <!-- Nav Item - Reviews -->
    <li class="nav-item <?php echo $current_page == 'manage-reviews.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="manage-reviews.php" data-tooltip="Reviews">
            <i class="fas fa-fw fa-star"></i>
            <span>Reviews</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Enhanced Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Enhanced Sidebar -->