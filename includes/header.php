<?php
// includes/header.php
if (!isset($page_title)) {
    $page_title = 'VR Tour Admin Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    
    <style>
        /* Enhanced Professional Header/Topbar Styles */
        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08) !important;
            padding: 0.75rem 1.5rem !important;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        /* Enhanced Sidebar Toggle Button */
        .topbar #sidebarToggle {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            color: white !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 10px !important;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .topbar #sidebarToggle:hover {
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
            background: linear-gradient(135deg, #764ba2, #667eea) !important;
        }

        .topbar #sidebarToggle i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .topbar #sidebarToggle:hover i {
            transform: rotate(90deg);
        }

        /* Enhanced Topbar Navbar */
        .topbar .navbar-nav {
            align-items: center;
        }

        /* Enhanced Notification Items */
        .topbar .nav-item {
            margin: 0 8px;
        }

        .topbar .nav-link {
            position: relative;
            background: rgba(102, 126, 234, 0.08) !important;
            border-radius: 12px !important;
            padding: 12px 15px !important;
            color: #495057 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 2px solid transparent;
            backdrop-filter: blur(10px);
        }

        .topbar .nav-link:hover {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .topbar .nav-link i {
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .topbar .nav-link:hover i {
            transform: scale(1.1);
        }

        /* Enhanced Badge Counter */
        .badge-counter {
            position: absolute !important;
            top: -8px !important;
            right: -8px !important;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
            color: white !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            padding: 4px 7px !important;
            border-radius: 12px !important;
            min-width: 20px !important;
            text-align: center !important;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4) !important;
            border: 2px solid white !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Enhanced Topbar Divider */
        .topbar-divider {
            width: 2px !important;
            height: 40px !important;
            background: linear-gradient(180deg, transparent, rgba(0, 0, 0, 0.1), transparent) !important;
            margin: 0 20px !important;
            border-radius: 1px;
        }

        /* Enhanced User Dropdown */
        .topbar .nav-item.dropdown .nav-link {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)) !important;
            border: 2px solid rgba(102, 126, 234, 0.2);
            padding: 8px 20px !important;
            border-radius: 25px !important;
        }

        .topbar .nav-item.dropdown .nav-link:hover {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .topbar .nav-item.dropdown .nav-link span {
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            margin-right: 12px !important;
            color: #495057;
            transition: color 0.3s ease;
        }

        .topbar .nav-item.dropdown .nav-link:hover span {
            color: white !important;
        }

        /* Enhanced Profile Image */
        .img-profile {
            width: 40px !important;
            height: 40px !important;
            border: 3px solid rgba(102, 126, 234, 0.3) !important;
            transition: all 0.3s ease !important;
            object-fit: cover;
        }

        .topbar .nav-item.dropdown .nav-link:hover .img-profile {
            border-color: rgba(255, 255, 255, 0.6) !important;
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        /* Enhanced Dropdown Menu */
        .dropdown-menu {
            border: none !important;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15) !important;
            border-radius: 20px !important;
            padding: 15px 0 !important;
            margin-top: 15px !important;
            background: white !important;
            backdrop-filter: blur(20px);
            min-width: 220px !important;
        }

        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 30px;
            width: 16px;
            height: 16px;
            background: white;
            transform: rotate(45deg);
            border-radius: 3px;
            box-shadow: -3px -3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Dropdown Items */
        .dropdown-item {
            padding: 12px 25px !important;
            font-weight: 500 !important;
            color: #495057 !important;
            transition: all 0.3s ease !important;
            border-radius: 0 !important;
            position: relative;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05)) !important;
            color: #667eea !important;
            padding-left: 35px !important;
        }

        .dropdown-item i {
            margin-right: 12px !important;
            width: 18px;
            text-align: center;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .dropdown-item:hover i {
            color: #667eea !important;
            transform: scale(1.1);
        }

        /* Special styling for logout */
        .dropdown-item:last-child {
            margin-top: 8px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            padding-top: 20px !important;
        }

        .dropdown-item:last-child:hover {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(238, 90, 36, 0.05)) !important;
            color: #ff6b6b !important;
        }

        .dropdown-item:last-child:hover i {
            color: #ff6b6b !important;
        }

        /* Enhanced Dropdown Divider */
        .dropdown-divider {
            margin: 15px 25px !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            opacity: 1 !important;
        }

        /* Enhanced Animations */
        .animated--grow-in {
            animation: growIn 0.3s ease-out;
        }

        @keyframes growIn {
            0% {
                opacity: 0;
                transform: scale(0.9) translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Notification specific colors */
        .topbar .nav-item:nth-child(1) .nav-link:hover {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
        }

        .topbar .nav-item:nth-child(2) .nav-link:hover {
            background: linear-gradient(135deg, #10ac84, #1dd1a1) !important;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .topbar {
                padding: 0.5rem 1rem !important;
            }
            
            .topbar .nav-item {
                margin: 0 4px;
            }
            
            .topbar .nav-link {
                padding: 10px 12px !important;
            }
            
            .topbar-divider {
                display: none !important;
            }
            
            .dropdown-menu {
                min-width: 200px !important;
                margin-top: 10px !important;
            }
        }

        /* Content Wrapper Enhancements */
        #content-wrapper {
            background: linear-gradient(135deg, #f8f9fc 0%, #f1f3f8 100%);
            min-height: 100vh;
        }

        #wrapper {
            overflow-x: hidden;
        }

        /* Enhanced focus states for accessibility */
        .topbar .nav-link:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3) !important;
        }

        .dropdown-item:focus {
            outline: none;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05)) !important;
            color: #667eea !important;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Enhanced Topbar -->
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top">
                    <!-- Enhanced Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggle" class="btn btn-link d-md-none rounded-circle">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Enhanced Topbar Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Enhanced Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Enhanced Counter - Alerts -->
                                <span class="badge badge-counter">3+</span>
                            </a>
                        </li>

                        <!-- Enhanced Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Enhanced Counter - Messages -->
                                <span class="badge badge-counter">7</span>
                            </a>
                        </li>

                        <!-- Enhanced Topbar Divider -->
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Enhanced Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-none d-lg-inline"><?php echo $_SESSION['admin_username']; ?></span>
                                <img class="img-profile rounded-circle" 
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_username']); ?>&background=667eea&color=fff&size=40">
                            </a>
                            <!-- Enhanced Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-fw"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-fw"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-fw"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-fw"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Enhanced Topbar -->