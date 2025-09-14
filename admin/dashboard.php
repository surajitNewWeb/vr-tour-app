<?php
// admin/dashboard.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Dashboard";
redirectIfNotLoggedIn();

// Get stats for dashboard
$tours_count = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$scenes_count = $pdo->query("SELECT COUNT(*) FROM scenes")->fetchColumn();
$hotspots_count = $pdo->query("SELECT COUNT(*) FROM hotspots")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get recent tours
$recent_tours = $pdo->query("
    SELECT t.*, u.username as creator 
    FROM tours t 
    LEFT JOIN users u ON t.created_by = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent users
$recent_users = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get tours by status
$published_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE published = 1")->fetchColumn();
$draft_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE published = 0")->fetchColumn();

include '../includes/header.php';
?>

<style>
/* Enhanced Professional Dashboard Styles */
.dashboard-container {
    background: linear-gradient(135deg, #f8f9fc 0%, #f1f3f8 100%);
    min-height: 100vh;
    padding: 20px 0;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin: -20px -15px 30px -15px;
    padding: 40px 30px;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header .subtitle {
    opacity: 0.9;
    font-size: 1.1rem;
    margin-top: 8px;
}

.create-tour-btn {
    padding: 10px 20px;
    font-weight: 500;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    border: none;
    letter-spacing: 0.5px;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(238, 90, 36, 0.3);
    transition: all 0.3s ease;
}

.create-tour-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(238, 90, 36, 0.4);
    background: linear-gradient(45deg, #ee5a24, #ff6b6b);
}

/* Enhanced Stat Cards */
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
}

.stat-card.tours { --card-color: #667eea; --card-color-light: #f093fb; }
.stat-card.scenes { --card-color: #10ac84; --card-color-light: #1dd1a1; }
.stat-card.hotspots { --card-color: #3742fa; --card-color-light: #70a1ff; }
.stat-card.users { --card-color: #ff9ff3; --card-color-light: #feca57; }

.stat-card .card-body {
    padding: 30px;
    position: relative;
}

.stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
    opacity: 0.1;
}

.stat-icon i {
    font-size: 24px;
}

.stat-label {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6c757d;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2d3436;
    margin: 0;
    line-height: 1;
}

.stat-footer {
    background: rgba(0,0,0,0.02);
    padding: 15px 30px;
    border-top: 1px solid rgba(0,0,0,0.05);
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

/* Enhanced Content Cards */
.content-card {
    background: white;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card-header-enhanced {
    background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 20px 25px;
}

.card-header-enhanced h6 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #495057;
    margin: 0;
}

.view-all-link {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white !important;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.3s ease;
}

.view-all-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white !important;
}

/* Enhanced List Items */
.enhanced-list-item {
    border: none;
    padding: 20px 25px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.enhanced-list-item:hover {
    background: rgba(102, 126, 234, 0.02);
}

.enhanced-list-item:last-child {
    border-bottom: none;
}

.item-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.tour-icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.tour-icon i {
    color: white;
    font-size: 18px;
}

.item-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3436;
    margin-bottom: 4px;
}

.item-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
}

.status-badge-enhanced {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-success-enhanced {
    background: linear-gradient(45deg, #00b894, #00cec9);
    color: white;
    border: none;
}

.badge-draft-enhanced {
    background: linear-gradient(45deg, #636e72, #74b9ff);
    color: white;
    border: none;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 3px solid #e9ecef;
    object-fit: cover;
    margin-right: 15px;
}

/* Chart Card Enhancement */
.chart-card {
    background: white;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chart-container {
    padding: 25px;
    height: 350px;
    position: relative;
}

/* System Info Enhancement */
.system-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.system-info-item:last-child {
    border-bottom: none;
}

.system-info-label {
    font-weight: 600;
    color: #495057;
}

.system-info-value {
    font-weight: 500;
    color: #6c757d;
    font-family: 'Monaco', 'Menlo', monospace;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state p {
    font-size: 1.1rem;
    margin-bottom: 20px;
}

.empty-state a {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.empty-state a:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .page-header {
        padding: 30px 20px;
        margin: -20px -15px 20px -15px;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .stat-card .card-body {
        padding: 20px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
    }
    
    .stat-value {
        font-size: 2rem;
    }
}
</style>

<!-- Begin Page Content -->
<div class="container-fluid dashboard-container">
    <!-- Enhanced Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Dashboard</h1>
                <p class="subtitle mb-0">Welcome back! Here's what's happening with your VR tours.</p>
            </div>
            <a href="manage-tours.php" class="btn create-tour-btn d-none d-sm-inline-block">
                <i class="fas fa-plus fa-sm mr-2"></i>Create New Tour
            </a>
        </div>
    </div>

    <!-- Enhanced Stats Row -->
    <div class="row">
        <!-- Tours Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card tours">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-map-marked"></i>
                    </div>
                    <div class="stat-label">Total Tours</div>
                    <div class="stat-value"><?php echo $tours_count; ?></div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-check-circle text-success mr-1"></i>
                    <?php echo $published_tours; ?> Published
                    <span class="mx-2">•</span>
                    <i class="fas fa-edit text-warning mr-1"></i>
                    <?php echo $draft_tours; ?> Draft
                </div>
            </div>
        </div>

        <!-- Scenes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card scenes">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="stat-label">Total Scenes</div>
                    <div class="stat-value"><?php echo $scenes_count; ?></div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-eye text-info mr-1"></i>
                    360° panoramic views
                </div>
            </div>
        </div>

        <!-- Hotspots Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card hotspots">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dot-circle"></i>
                    </div>
                    <div class="stat-label">Total Hotspots</div>
                    <div class="stat-value"><?php echo $hotspots_count; ?></div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-mouse-pointer text-primary mr-1"></i>
                    Interactive elements
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card users">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo $users_count; ?></div>
                </div>
                <div class="stat-footer">
                    <i class="fas fa-user-plus text-success mr-1"></i>
                    Registered members
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Content Row -->
    <div class="row">
        <!-- Recent Tours -->
        <div class="col-xl-6 col-lg-6">
            <div class="card content-card mb-4">
                <div class="card-header card-header-enhanced d-flex justify-content-between align-items-center">
                    <h6>Recent Tours</h6>
                    <a href="manage-tours.php" class="view-all-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_tours) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_tours as $tour): ?>
                                <div class="list-group-item enhanced-list-item d-flex align-items-center">
                                    <div class="item-icon tour-icon">
                                        <i class="fas fa-map-marked"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="item-title"><?php echo htmlspecialchars($tour['title']); ?></div>
                                        <div class="item-subtitle">
                                            <i class="fas fa-user text-muted mr-1"></i>
                                            <?php echo htmlspecialchars($tour['creator'] ?? 'Admin'); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-calendar text-muted mr-1"></i>
                                            <?php echo date('M j, Y', strtotime($tour['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="status-badge-enhanced <?php echo $tour['published'] ? 'badge-success-enhanced' : 'badge-draft-enhanced'; ?>">
                                        <?php echo $tour['published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-map-marked"></i>
                            <p>No tours created yet</p>
                            <a href="manage-tours.php">Create your first tour</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-xl-6 col-lg-6">
            <div class="card content-card mb-4">
                <div class="card-header card-header-enhanced d-flex justify-content-between align-items-center">
                    <h6>Recent Users</h6>
                    <a href="manage-users.php" class="view-all-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_users) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_users as $user): ?>
                                <div class="list-group-item enhanced-list-item d-flex align-items-center">
                                    <img class="user-avatar" 
                                         src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=667eea&color=fff&size=45" 
                                         alt="<?php echo htmlspecialchars($user['username']); ?>">
                                    <div class="flex-grow-1">
                                        <div class="item-title"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="item-subtitle">
                                            <i class="fas fa-envelope text-muted mr-1"></i>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-calendar text-muted mr-1"></i>
                                            Joined <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No users registered yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Bottom Row -->
    <div class="row">
        <!-- Tours Status Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card chart-card mb-4">
                <div class="card-header card-header-enhanced">
                    <h6>Tours Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="toursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced System Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card content-card mb-4">
                <div class="card-header card-header-enhanced">
                    <h6>System Information</h6>
                </div>
                <div class="card-body">
                    <div class="system-info-item">
                        <span class="system-info-label">PHP Version</span>
                        <span class="system-info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Server Software</span>
                        <span class="system-info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Database</span>
                        <span class="system-info-value">MySQL</span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">VR Framework</span>
                        <span class="system-info-value">A-Frame</span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Admin User</span>
                        <span class="system-info-value"><?php echo $_SESSION['admin_username']; ?></span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Login Time</span>
                        <span class="system-info-value"><?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<script>
// Enhanced Tours Chart with better styling
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('toursChart').getContext('2d');
    
    // Create gradient backgrounds
    var gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
    gradient1.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
    gradient1.addColorStop(1, 'rgba(102, 126, 234, 0.1)');
    
    var gradient2 = ctx.createLinearGradient(0, 0, 0, 400);
    gradient2.addColorStop(0, 'rgba(108, 117, 125, 0.8)');
    gradient2.addColorStop(1, 'rgba(108, 117, 125, 0.1)');
    
    var gradient3 = ctx.createLinearGradient(0, 0, 0, 400);
    gradient3.addColorStop(0, 'rgba(16, 172, 132, 0.8)');
    gradient3.addColorStop(1, 'rgba(16, 172, 132, 0.1)');
    
    var gradient4 = ctx.createLinearGradient(0, 0, 0, 400);
    gradient4.addColorStop(0, 'rgba(55, 66, 250, 0.8)');
    gradient4.addColorStop(1, 'rgba(55, 66, 250, 0.1)');
    
    var toursChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Published Tours', 'Draft Tours', 'Total Scenes', 'Total Hotspots'],
            datasets: [{
                label: 'Count',
                data: [
                    <?php echo $published_tours; ?>,
                    <?php echo $draft_tours; ?>,
                    <?php echo $scenes_count; ?>,
                    <?php echo $hotspots_count; ?>
                ],
                backgroundColor: [gradient1, gradient2, gradient3, gradient4],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(16, 172, 132, 1)',
                    'rgba(55, 66, 250, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#6c757d',
                        font: {
                            family: "'Nunito', sans-serif"
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        borderColor: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#6c757d',
                        font: {
                            family: "'Nunito', sans-serif",
                            weight: '600'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
            },
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 8,
                    titleFont: {
                        family: "'Nunito', sans-serif",
                        weight: '600'
                    },
                    bodyFont: {
                        family: "'Nunito', sans-serif"
                    }
                }
            }
        }
    });
});
</script>

<?php
include '../includes/footer.php';
?>