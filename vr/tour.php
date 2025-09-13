<?php
// vr/tour.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

// Check if tour ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../tours.php');
    exit;
}

$tour_id = intval($_GET['id']);

// Get tour details using PDO directly
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.username as creator_name,
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           (SELECT COUNT(*) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as review_count
    FROM tours t 
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.id = :id AND t.published = 1
");
$stmt->execute([':id' => $tour_id]);
$tour = $stmt->fetch();

if (!$tour) {
    header('Location: ../tours.php');
    exit;
}

// Get scenes for this tour
$stmt = $pdo->prepare("
    SELECT s.*, 
           (SELECT COUNT(*) FROM hotspots h WHERE h.scene_id = s.id) as hotspot_count
    FROM scenes s 
    WHERE s.tour_id = :tour_id 
    ORDER BY s.id
");
$stmt->execute([':tour_id' => $tour_id]);
$scenes = $stmt->fetchAll();

if (empty($scenes)) {
    header('Location: ../tours.php');
    exit;
}

// Get hotspots for each scene
foreach ($scenes as &$scene) {
    $stmt = $pdo->prepare("
        SELECT h.*, ts.name as target_scene_name
        FROM hotspots h 
        LEFT JOIN scenes ts ON h.target_scene_id = ts.id
        WHERE h.scene_id = :scene_id
    ");
    $stmt->execute([':scene_id' => $scene['id']]);
    $scene['hotspots'] = $stmt->fetchAll();
}
unset($scene); // Break the reference

// Get first scene for initial view
$first_scene = $scenes[0];

// Update user progress
if (isUserLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    // Check if progress exists
    $stmt = $pdo->prepare("
        SELECT id FROM progress 
        WHERE user_id = :user_id AND tour_id = :tour_id
    ");
    $stmt->execute([':user_id' => $user_id, ':tour_id' => $tour_id]);
    $progress = $stmt->fetch();
    
    if ($progress) {
        // Update existing progress
        $stmt = $pdo->prepare("
            UPDATE progress 
            SET last_scene_id = :scene_id, updated_at = NOW() 
            WHERE id = :progress_id
        ");
        $stmt->execute([':scene_id' => $first_scene['id'], ':progress_id' => $progress['id']]);
    } else {
        // Create new progress
        $stmt = $pdo->prepare("
            INSERT INTO progress (user_id, tour_id, last_scene_id, updated_at) 
            VALUES (:user_id, :tour_id, :scene_id, NOW())
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':tour_id' => $tour_id,
            ':scene_id' => $first_scene['id']
        ]);
    }
}

$page_title = $tour['title'] . " - VR Tour";
$body_class = "vr-tour-page";

include '../includes/user-header.php';
?>

<style>/* VR Tour Page Styles */
/* VR Tour Specific Styles */
.vr-tour-page {
    overflow: hidden;
    height: 100vh;
}

.vr-tour-container {
    position: relative;
    width: 100%;
    height: 100vh;
    background: #000;
}

.vr-scene {
    width: 100%;
    height: 100%;
}

.vr-scene a-scene {
    width: 100%;
    height: 100%;
}

/* VR Controls */
.vr-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.7);
    padding: 15px;
    border-radius: 25px;
    backdrop-filter: blur(10px);
}

.control-group {
    display: flex;
    gap: 10px;
}

.control-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 18px;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
    transform: scale(1.1);
}

.control-btn:active {
    transform: scale(0.95);
}

.progress-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    width: 200px;
}

.progress-text {
    color: white;
    font-size: 14px;
    font-weight: 500;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0080e5, #00c4ff);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Tour Info Panel */
.tour-info-panel {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 350px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    backdrop-filter: blur(10px);
    transform: translateX(calc(100% + 20px));
    transition: transform 0.3s ease;
}

.tour-info-panel.active {
    transform: translateX(0);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.panel-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.panel-content {
    padding: 20px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

.tour-meta {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.meta-item i {
    font-size: 1.5rem;
    color: #0080e5;
    margin-bottom: 5px;
}

.meta-item span {
    font-size: 0.9rem;
    color: #666;
}

.tour-description {
    margin-bottom: 20px;
}

.tour-description p {
    color: #666;
    line-height: 1.6;
}

.tour-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tour-actions .btn {
    flex: 1;
}

.scene-list h3 {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.2rem;
}

.scene-items {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.scene-item {
    display: flex;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.scene-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.scene-thumb {
    width: 60px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.scene-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scene-thumb-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0080e5, #00c4ff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.scene-info {
    flex: 1;
}

.scene-info h4 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #333;
}

.scene-info p {
    margin: 0 0 5px 0;
    font-size: 0.8rem;
    color: #666;
    line-height: 1.4;
}

.scene-meta {
    font-size: 0.75rem;
    color: #888;
}

/* Loading Spinner */
.loading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(10px);
}

.spinner-content {
    text-align: center;
    color: white;
}

.spinner-icon {
    font-size: 3rem;
    color: #0080e5;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .vr-controls {
        bottom: 10px;
        padding: 10px;
    }
    
    .control-btn {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .progress-group {
        width: 150px;
    }
    
    .tour-info-panel {
        width: calc(100% - 40px);
        right: 20px;
        left: 20px;
        transform: translateY(calc(100% + 20px));
    }
    
    .tour-info-panel.active {
        transform: translateY(0);
    }
    
    .tour-meta {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* VR Mode Adjustments */
.a-enter-vr-button {
    background: linear-gradient(135deg, #0080e5, #00c4ff) !important;
    border: none !important;
    border-radius: 25px !important;
    font-family: inherit !important;
}

.a-enter-vr-button:hover {
    transform: scale(1.05) !important;
    box-shadow: 0 5px 15px rgba(0, 128, 229, 0.4) !important;
}
.vr-tour-page {
    margin: 0;
    padding: 0;
    overflow: hidden;
    background: #000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Main VR Container */
.vr-tour-container {
    position: relative;
    width: 100%;
    height: 100vh;
    background: #000;
    overflow: hidden;
}

/* VR Scene Wrapper */
.vr-scene {
    position: relative;
    width: 100%;
    height: 100vh;
    background: radial-gradient(circle at center, #1a1a2e 0%, #000 100%);
    overflow: hidden;
}

.vr-scene a-scene {
    width: 100% !important;
    height: 100% !important;
}

/* VR Controls */
.vr-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 20px;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    padding: 15px 25px;
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    transition: all 0.3s ease;
}

.vr-controls:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: translateX(-50%) translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
}

.control-group {
    display: flex;
    gap: 10px;
}

.control-btn {
    width: 48px;
    height: 48px;
    border: none;
    border-radius: 50%;
    background: linear-gradient(135deg, #0080e5 0%, #0066cc 100%);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.control-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.control-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 128, 229, 0.4);
    background: linear-gradient(135deg, #0099ff 0%, #0080e5 100%);
}

.control-btn:hover::before {
    left: 100%;
}

.control-btn:active {
    transform: translateY(0) scale(0.98);
}

.progress-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    margin-left: 20px;
    padding-left: 20px;
    border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.progress-text {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.progress-bar {
    width: 120px;
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0080e5, #00bcd4);
    border-radius: 3px;
    transition: width 0.5s ease;
    box-shadow: 0 0 10px rgba(0, 128, 229, 0.5);
}

/* Hotspot Information Panel */
.hotspot-info-panel {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 350px;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
    z-index: 1000;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.4s ease;
    overflow: hidden;
}

.hotspot-info-panel.active {
    transform: translateX(0);
    opacity: 1;
}

.hotspot-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #0080e5 0%, #0066cc 100%);
    color: white;
}

.hotspot-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.hotspot-content {
    padding: 20px;
}

.hotspot-content p {
    color: #fff;
    line-height: 1.6;
    margin: 0;
    font-size: 15px;
}

/* Tour Info Panel */
.tour-info-panel {
    position: absolute;
    top: 0;
    left: 0;
    width: 400px;
    height: 100vh;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.4s ease;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #0080e5 transparent;
}

.tour-info-panel::-webkit-scrollbar {
    width: 6px;
}

.tour-info-panel::-webkit-scrollbar-track {
    background: transparent;
}

.tour-info-panel::-webkit-scrollbar-thumb {
    background: #0080e5;
    border-radius: 3px;
}

.tour-info-panel.active {
    transform: translateX(0);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px;
    background: linear-gradient(135deg, #0080e5 0%, #0066cc 100%);
    color: white;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.panel-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
}

.panel-content {
    padding: 25px;
}

/* Tour Meta Information */
.tour-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 25px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    font-size: 14px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.meta-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.meta-item i {
    color: #0080e5;
    font-size: 16px;
}

/* Tour Description */
.tour-description {
    margin-bottom: 25px;
}

.tour-description p {
    color: #ccc;
    line-height: 1.6;
    font-size: 15px;
    margin: 0;
}

/* Tour Actions */
.tour-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
}

.btn {
    padding: 12px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.btn-outline {
    background: transparent;
    color: #0080e5;
    border: 2px solid #0080e5;
}

.btn-outline:hover {
    background: #0080e5;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 128, 229, 0.3);
}

/* Scene List */
.scene-list h3 {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #0080e5;
}

.scene-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.scene-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.scene-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.scene-thumb {
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.scene-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.scene-item:hover .scene-thumb img {
    transform: scale(1.1);
}

.scene-thumb-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 24px;
}

.scene-info h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.scene-info p {
    color: #ccc;
    font-size: 14px;
    line-height: 1.4;
    margin: 0 0 10px 0;
}

.scene-meta {
    display: flex;
    gap: 15px;
}

.scene-meta span {
    color: #0080e5;
    font-size: 12px;
    font-weight: 500;
}

/* Hotspot Guide */
.hotspot-guide {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.hotspot-guide h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 15px 0;
}

.hotspot-types {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.hotspot-type-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.hotspot-icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.hotspot-label {
    color: #ccc;
    font-size: 14px;
}

/* Loading Animation */
@keyframes pulse {
    0% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
    100% {
        opacity: 0.6;
    }
}

.loading {
    animation: pulse 1.5s infinite;
}

/* VR Mode Specific Styles */
@media (max-width: 768px) {
    .vr-controls {
        bottom: 10px;
        padding: 10px 20px;
        gap: 15px;
    }
    
    .control-btn {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .progress-group {
        margin-left: 15px;
        padding-left: 15px;
    }
    
    .progress-bar {
        width: 100px;
    }
    
    .tour-info-panel {
        width: 90%;
        max-width: 350px;
    }
    
    .hotspot-info-panel {
        width: 300px;
        top: 10px;
        right: 10px;
    }
    
    .panel-header, .hotspot-header {
        padding: 15px;
    }
    
    .panel-content, .hotspot-content {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .tour-info-panel {
        width: 100%;
    }
    
    .hotspot-info-panel {
        width: calc(100% - 20px);
        right: 10px;
    }
    
    .vr-controls {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    
    .progress-group {
        margin-left: 0;
        padding-left: 0;
        border-left: none;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 10px;
    }
    
    .tour-meta {
        grid-template-columns: 1fr;
    }
    
    .tour-actions {
        flex-direction: column;
    }
}

/* Accessibility Improvements */
.control-btn:focus,
.btn:focus,
.scene-item:focus,
.close-btn:focus {
    outline: 2px solid #0080e5;
    outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .vr-controls {
        background: rgba(0, 0, 0, 0.95);
        border: 2px solid #fff;
    }
    
    .tour-info-panel,
    .hotspot-info-panel {
        background: rgba(0, 0, 0, 0.98);
        border: 1px solid #fff;
    }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .control-btn:hover,
    .scene-item:hover,
    .meta-item:hover {
        transform: none;
    }
}

/* Print styles */
@media print {
    .vr-tour-container {
        display: none;
    }
}</style>

<div class="vr-tour-container">
    <!-- VR Scene -->
    <div id="vr-scene" class="vr-scene">
        <a-scene vr-mode-ui="enabled: true" loading-screen="dotsColor: #0080e5; backgroundColor: #000" 
                 cursor="rayOrigin: mouse" raycaster="objects: .clickable" embedded>
            
            <!-- Assets -->
            <a-assets>
                <img id="sky-texture" src="../assets/panoramas/uploads/<?php echo $first_scene['panorama']; ?>" crossorigin="anonymous">
                <?php foreach ($scenes as $scene): ?>
                    <img id="scene-<?php echo $scene['id']; ?>" src="../assets/panoramas/uploads/<?php echo $scene['panorama']; ?>" crossorigin="anonymous">
                <?php endforeach; ?>
                
                <!-- Hotspot icons -->
                <img id="info-icon" src="../assets/images/hotspots/info.png" crossorigin="anonymous">
                <img id="navigation-icon" src="../assets/images/hotspots/navigation.png" crossorigin="anonymous">
                <img id="media-icon" src="../assets/images/hotspots/media.png" crossorigin="anonymous">
                
                <!-- Audio assets -->
                <audio id="click-sound" src="../assets/audio/click.mp3" preload="auto"></audio>
                <audio id="transition-sound" src="../assets/audio/transition.mp3" preload="auto"></audio>
            </a-assets>
            
            <!-- Default Sky -->
            <a-sky id="main-sky" src="#sky-texture" rotation="0 -90 0"></a-sky>
            
            <!-- Camera Rig -->
            <a-entity id="camera-rig" movement-controls="speed: 0.1" position="0 0 0">
                <a-entity id="camera" camera="active: true" look-controls="pointerLockEnabled: true" 
                         position="0 1.6 0" wasd-controls="acceleration: 20">
                    <a-cursor id="cursor" animation__click="property: scale; startEvents: click; from: 0.1 0.1 0.1; to: 1 1 1; dur: 150"
                             animation__fusing="property: scale; startEvents: fusing; from: 1 1 1; to: 0.1 0.1 0.1; dur: 1500"
                             animation__mouseleave="property: scale; startEvents: mouseleave; from: 0.1 0.1 0.1; to: 1 1 1; dur: 500"
                             raycaster="far: 20; interval: 1000" fuse="true" fuse-timeout="500">
                    </a-cursor>
                </a-entity>
            </a-entity>
            
            <!-- Environment -->
            <a-entity environment="preset: default; ground: hills; groundYScale: 5; dressingAmount: 100"></a-entity>
            
            <!-- Loading Indicator -->
            <a-entity id="loading-indicator" position="0 1.5 -1" visible="true">
                <a-circle color="#000000" opacity="0.7" radius="0.3"></a-circle>
                <a-text value="Loading..." align="center" color="#FFF" position="0 0 0.01"></a-text>
                <a-ring color="#0080e5" radius-inner="0.2" radius-outer="0.3" theta-start="0" theta-length="0" id="loading-ring">
                    <a-animation attribute="theta-length" from="0" to="360" dur="2000" repeat="indefinite"></a-animation>
                </a-ring>
            </a-entity>
            
            <!-- UI Elements -->
            <a-entity id="vr-ui" position="0 0 0">
                <!-- Navigation Panel -->
                <a-entity id="navigation-panel" position="0 2 -3" visible="false">
                    <a-plane color="#000" opacity="0.8" width="2" height="1.5"></a-plane>
                    <a-text value="Scenes" color="#FFF" align="center" position="0 0.6 0.01" width="1.8"></a-text>
                    
                    <?php foreach ($scenes as $index => $scene): ?>
                        <a-entity class="scene-button clickable" data-scene-id="<?php echo $scene['id']; ?>" 
                                 position="<?php echo (-0.7 + ($index % 3) * 0.7); ?> <?php echo (0.2 - floor($index / 3) * 0.4); ?> 0.01">
                            <a-plane color="#0080e5" width="0.6" height="0.3"></a-plane>
                            <a-text value="Scene <?php echo $index + 1; ?>" color="#FFF" align="center" position="0 0 0.01" width="0.5"></a-text>
                        </a-entity>
                    <?php endforeach; ?>
                </a-entity>
                
                <!-- Info Panel -->
                <a-entity id="info-panel" position="0 0 -2" visible="false">
                    <a-plane color="#000" opacity="0.8" width="1.5" height="1"></a-plane>
                    <a-text value="<?php echo htmlspecialchars($tour['title']); ?>" color="#FFF" align="center" 
                           position="0 0.3 0.01" width="1.3" wrap-count="20"></a-text>
                    <a-text value="Scenes: <?php echo count($scenes); ?>" color="#CCC" align="center" 
                           position="0 0 0.01" width="1.3"></a-text>
                    <a-text value="Click to explore" color="#CCC" align="center" 
                           position="0 -0.3 0.01" width="1.3"></a-text>
                </a-entity>
            </a-entity>
            
            <!-- Hotspots - Dynamically added based on database -->
            <?php foreach ($first_scene['hotspots'] as $hotspot): ?>
                <a-entity class="hotspot clickable"
                         data-type="<?php echo $hotspot['type']; ?>"
                         data-target="<?php echo $hotspot['target_scene_id'] ?? ''; ?>"
                         data-content="<?php echo htmlspecialchars($hotspot['content'] ?? ''); ?>"
                         position="<?php echo $hotspot['x'] ?? 0; ?> <?php echo $hotspot['y'] ?? 1; ?> <?php echo $hotspot['z'] ?? -2; ?>">
                    
                    <a-circle radius="0.2" color="#0080e5" opacity="0.8"
                             animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate">
                    </a-circle>
                    
                    <a-text value="<?php 
                        switch($hotspot['type']) {
                            case 'navigation': echo '➡️'; break;
                            case 'info': echo 'ℹ️'; break;
                            case 'media': echo '🎬'; break;
                            default: echo '🔘';
                        }
                    ?>" align="center" color="#FFF" position="0 0 0.01" width="0.3"></a-text>
                    
                    <?php if (!empty($hotspot['title'])): ?>
                        <a-text value="<?php echo htmlspecialchars($hotspot['title']); ?>" 
                               color="#FFF" align="center" position="0 0.3 0.01" width="1.0"></a-text>
                    <?php endif; ?>
                </a-entity>
            <?php endforeach; ?>
            
        </a-scene>
    </div>
    
    <!-- VR Controls -->
    <div class="vr-controls">
        <div class="control-group">
            <button id="vr-toggle" class="control-btn" title="Enter VR Mode">
                <i class="fas fa-vr-cardboard"></i>
            </button>
            <button id="fullscreen-toggle" class="control-btn" title="Fullscreen">
                <i class="fas fa-expand"></i>
            </button>
            <button id="scene-nav" class="control-btn" title="Scene Navigation">
                <i class="fas fa-map"></i>
            </button>
            <button id="info-toggle" class="control-btn" title="Tour Information">
                <i class="fas fa-info"></i>
            </button>
        </div>
        
        <div class="progress-group">
            <div class="progress-text">Scene 1 of <?php echo count($scenes); ?></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo (1/count($scenes))*100; ?>%"></div>
            </div>
        </div>
    </div>
    
    <!-- Hotspot Information Display -->
    <div id="hotspot-info" class="hotspot-info-panel">
        <div class="hotspot-header">
            <h4 id="hotspot-title">Point of Interest</h4>
            <button id="close-hotspot" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hotspot-content">
            <p id="hotspot-description">Information about this point of interest will appear here.</p>
        </div>
    </div>
    
    <!-- Tour Info Panel (non-VR) -->
    <div class="tour-info-panel">
        <div class="panel-header">
            <h2><?php echo htmlspecialchars($tour['title']); ?></h2>
            <button id="close-panel" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="panel-content">
            <div class="tour-meta">
                <div class="meta-item">
                    <i class="fas fa-layer-group"></i>
                    <span><?php echo count($scenes); ?> scenes</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>~<?php echo ceil(count($scenes) * 2); ?> min</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($tour['creator_name'] ?? 'Admin'); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-location-dot"></i>
                    <span><?php echo count($first_scene['hotspots']); ?> hotspots in this scene</span>
                </div>
            </div>
            
            <div class="tour-description">
                <p><?php echo htmlspecialchars($tour['description']); ?></p>
            </div>
            
            <div class="tour-actions">
                <?php if (isUserLoggedIn()): ?>
                    <button id="favorite-btn" class="btn btn-outline" data-tour-id="<?php echo $tour_id; ?>">
                        <i class="far fa-heart"></i> Add to Favorites
                    </button>
                <?php endif; ?>
                <button class="btn btn-outline" id="share-btn">
                    <i class="fas fa-share-alt"></i> Share
                </button>
            </div>
            
            <div class="scene-list">
                <h3>Scenes in this tour</h3>
                <div class="scene-items">
                    <?php foreach ($scenes as $index => $scene): ?>
                        <div class="scene-item" data-scene-id="<?php echo $scene['id']; ?>">
                            <div class="scene-thumb">
                                <?php if ($scene['panorama']): ?>
                                    <img src="../assets/panoramas/uploads/<?php echo $scene['panorama']; ?>" alt="<?php echo htmlspecialchars($scene['name']); ?>">
                                <?php else: ?>
                                    <div class="scene-thumb-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="scene-info">
                                <h4><?php echo htmlspecialchars($scene['name']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($scene['description'], 0, 100)); ?>...</p>
                                <div class="scene-meta">
                                    <span><?php echo $scene['hotspot_count']; ?> hotspots</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Hotspot Guide -->
            <div class="hotspot-guide">
                <h4>Hotspot Guide</h4>
                <div class="hotspot-types">
                    <div class="hotspot-type-item">
                        <span class="hotspot-icon">➡️</span>
                        <span class="hotspot-label">Navigation - Move to another scene</span>
                    </div>
                    <div class="hotspot-type-item">
                        <span class="hotspot-icon">ℹ️</span>
                        <span class="hotspot-label">Information - Learn about this area</span>
                    </div>
                    <div class="hotspot-type-item">
                        <span class="hotspot-icon">🎬</span>
                        <span class="hotspot-label">Media - Watch videos or listen to audio</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global tour data for JavaScript
const tourData = {
    id: <?php echo $tour_id; ?>,
    title: "<?php echo addslashes($tour['title']); ?>",
    scenes: <?php echo json_encode($scenes); ?>,
    currentScene: 0
};

// Check if user is logged in
const userLoggedIn = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;
</script>

<?php
include '../includes/user-footer.php';
?>