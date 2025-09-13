// assets/js/vr-tour.js

class VRTour {
    constructor() {
        this.currentScene = 0;
        this.scenes = tourData.scenes;
        this.isVRMode = false;
        this.isFullscreen = false;
        this.isInfoPanelOpen = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadScene(0);
        this.setupHotspots();
        this.checkFavorites();
        
        // Hide loading spinner when scene is loaded
        const sky = document.querySelector('#main-sky');
        sky.addEventListener('loaded', () => {
            this.hideLoading();
        });
        
        // Handle A-Frame scene load
        const scene = document.querySelector('a-scene');
        scene.addEventListener('loaded', () => {
            console.log('A-Frame scene loaded');
            this.initializeVR();
        });
    }
    
    setupEventListeners() {
        // VR toggle
        document.getElementById('vr-toggle').addEventListener('click', () => {
            this.toggleVRMode();
        });
        
        // Fullscreen toggle
        document.getElementById('fullscreen-toggle').addEventListener('click', () => {
            this.toggleFullscreen();
        });
        
        // Scene navigation
        document.getElementById('scene-nav').addEventListener('click', () => {
            this.toggleNavigationPanel();
        });
        
        // Info toggle
        document.getElementById('info-toggle').addEventListener('click', () => {
            this.toggleInfoPanel();
        });
        
        // Close panel
        document.getElementById('close-panel').addEventListener('click', () => {
            this.toggleInfoPanel();
        });
        
        // Scene items click
        document.querySelectorAll('.scene-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const sceneId = parseInt(item.dataset.sceneId);
                const sceneIndex = this.scenes.findIndex(s => s.id === sceneId);
                if (sceneIndex !== -1) {
                    this.loadScene(sceneIndex);
                }
            });
        });
        
        // Favorite button
        const favoriteBtn = document.getElementById('favorite-btn');
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', () => {
                this.toggleFavorite();
            });
        }
        
        // Share button
        document.getElementById('share-btn').addEventListener('click', () => {
            this.shareTour();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });
        
        // Handle VR mode changes
        window.addEventListener('vrdisplaypresentchange', (e) => {
            this.isVRMode = e.detail.isPresenting;
            this.onVRModeChange();
        });
        
        // Handle fullscreen changes
        document.addEventListener('fullscreenchange', () => {
            this.isFullscreen = !!document.fullscreenElement;
        });
    }
    
    initializeVR() {
        // Add hotspots to the current scene
        this.addHotspotsToScene(this.currentScene);
        
        // Set up scene navigation
        this.setupSceneNavigation();
        
        // Initialize VR button text
        const vrButton = document.querySelector('.a-enter-vr-button');
        if (vrButton) {
            vrButton.textContent = 'Enter VR';
        }
    }
    
    loadScene(index) {
        if (index < 0 || index >= this.scenes.length) return;
        
        this.showLoading();
        this.currentScene = index;
        
        const scene = this.scenes[index];
        const sky = document.querySelector('#main-sky');
        
        // Update sky texture
        sky.setAttribute('src', `#scene-${scene.id}`);
        
        // Update progress
        this.updateProgress();
        
        // Update URL hash
        window.location.hash = `scene-${index + 1}`;
        
        // Save progress if user is logged in
        if (userLoggedIn) {
            this.saveProgress(scene.id);
        }
        
        // Remove existing hotspots
        this.removeHotspots();
        
        // Add hotspots for new scene
        this.addHotspotsToScene(index);
        
        // Hide loading when texture is loaded
        sky.addEventListener('loaded', () => {
            this.hideLoading();
        }, { once: true });
    }
    
    addHotspotsToScene(sceneIndex) {
        const scene = this.scenes[sceneIndex];
        
        // In a real implementation, you would fetch hotspots from the server
        // For now, we'll create some demo hotspots
        this.createDemoHotspots(scene.id);
    }
    
    createDemoHotspots(sceneId) {
        // Demo hotspots - in production, these would come from the database
        const hotspots = [
            { type: 'info', position: { x: 0, y: 1, z: -3 }, title: 'Welcome', content: 'Welcome to this amazing VR tour!' },
            { type: 'navigation', position: { x: 2, y: 1, z: -2 }, target: 'next', title: 'Next Scene' },
            { type: 'media', position: { x: -2, y: 1, z: -2 }, content: 'demo-video', title: 'Watch Video' }
        ];
        
        hotspots.forEach((hotspot, index) => {
            this.createHotspot(hotspot, sceneId, index);
        });
    }
    
    createHotspot(hotspot, sceneId, index) {
        const entity = document.createElement('a-entity');
        entity.setAttribute('class', 'hotspot clickable');
        entity.setAttribute('data-type', hotspot.type);
        entity.setAttribute('data-target', hotspot.target || '');
        entity.setAttribute('data-content', hotspot.content || '');
        entity.setAttribute('position', `${hotspot.position.x} ${hotspot.position.y} ${hotspot.position.z}`);
        
        // Create hotspot visual
        const hotspotVisual = document.createElement('a-entity');
        hotspotVisual.setAttribute('geometry', 'primitive: circle; radius: 0.2');
        hotspotVisual.setAttribute('material', 'color: #0080e5; shader: flat; transparent: true; opacity: 0.8');
        hotspotVisual.setAttribute('animation', 'property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate');
        
        // Add icon based on type
        const icon = document.createElement('a-entity');
        icon.setAttribute('text', `value: ${this.getHotspotIcon(hotspot.type)}; color: white; align: center; width: 0.4`);
        icon.setAttribute('position', '0 0 0.01');
        
        hotspotVisual.appendChild(icon);
        entity.appendChild(hotspotVisual);
        
        // Add click event
        entity.addEventListener('click', () => {
            this.handleHotspotClick(hotspot);
        });
        
        // Add mouseenter/mouseleave events for desktop
        entity.addEventListener('mouseenter', () => {
            this.showHotspotLabel(hotspot.title);
        });
        
        entity.addEventListener('mouseleave', () => {
            this.hideHotspotLabel();
        });
        
        document.querySelector('a-scene').appendChild(entity);
    }
    
    getHotspotIcon(type) {
        const icons = {
            'info': 'ℹ️',
            'navigation': '➡️',
            'media': '🎬'
        };
        return icons[type] || '🔘';
    }
    
    handleHotspotClick(hotspot) {
        switch (hotspot.type) {
            case 'info':
                this.showInfoPopup(hotspot.content);
                break;
            case 'navigation':
                if (hotspot.target === 'next') {
                    this.nextScene();
                } else if (hotspot.target === 'prev') {
                    this.previousScene();
                }
                break;
            case 'media':
                this.playMedia(hotspot.content);
                break;
        }
    }
    
    showHotspotLabel(text) {
        // Create or update hotspot label
        let label = document.querySelector('#hotspot-label');
        if (!label) {
            label = document.createElement('a-entity');
            label.setAttribute('id', 'hotspot-label');
            label.setAttribute('position', '0 2.5 -2');
            label.setAttribute('text', `value: ${text}; color: white; align: center; width: 1`);
            document.querySelector('a-scene').appendChild(label);
        } else {
            label.setAttribute('text', 'value', text);
        }
    }
    
    hideHotspotLabel() {
        const label = document.querySelector('#hotspot-label');
        if (label) {
            label.parentNode.removeChild(label);
        }
    }
    
    removeHotspots() {
        const hotspots = document.querySelectorAll('.hotspot');
        hotspots.forEach(hotspot => {
            hotspot.parentNode.removeChild(hotspot);
        });
    }
    
    nextScene() {
        if (this.currentScene < this.scenes.length - 1) {
            this.loadScene(this.currentScene + 1);
        }
    }
    
    previousScene() {
        if (this.currentScene > 0) {
            this.loadScene(this.currentScene - 1);
        }
    }
    
    updateProgress() {
        // Update progress bar
        const progress = ((this.currentScene + 1) / this.scenes.length) * 100;
        document.querySelector('.progress-fill').style.width = `${progress}%`;
        
        // Update progress text
        document.querySelector('.progress-text').textContent = 
            `Scene ${this.currentScene + 1} of ${this.scenes.length}`;
    }
    
    toggleVRMode() {
        const vrButton = document.querySelector('.a-enter-vr-button');
        if (vrButton) {
            vrButton.click();
        }
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error('Error attempting to enable fullscreen:', err);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }
    
    toggleNavigationPanel() {
        const panel = document.querySelector('#navigation-panel');
        const isVisible = panel.getAttribute('visible');
        panel.setAttribute('visible', !isVisible);
    }
    
    toggleInfoPanel() {
        const panel = document.querySelector('.tour-info-panel');
        panel.classList.toggle('active');
        this.isInfoPanelOpen = panel.classList.contains('active');
    }
    
    showInfoPopup(content) {
        // Create info popup
        const popup = document.createElement('a-entity');
        popup.setAttribute('id', 'info-popup');
        popup.setAttribute('position', '0 1.5 -1.5');
        popup.setAttribute('geometry', 'primitive: plane; width: 1.5; height: 1');
        popup.setAttribute('material', 'color: #000; opacity: 0.8; transparent: true');
        
        const text = document.createElement('a-entity');
        text.setAttribute('text', `value: ${content}; color: white; align: center; width: 1.3; wrap-count: 20`);
        text.setAttribute('position', '0 0 0.01');
        
        popup.appendChild(text);
        document.querySelector('a-scene').appendChild(popup);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 5000);
    }
    
    playMedia(mediaId) {
        console.log('Playing media:', mediaId);
        // Implementation for media playback
        // This would vary based on your media handling setup
    }
    
    setupSceneNavigation() {
        // Add event listeners to scene buttons
        const sceneButtons = document.querySelectorAll('.scene-button');
        sceneButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                this.loadScene(index);
                document.querySelector('#navigation-panel').setAttribute('visible', false);
            });
        });
    }
    
    handleKeyboardShortcuts(e) {
        // Prevent shortcuts when typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        switch (e.key) {
            case 'ArrowRight':
                e.preventDefault();
                this.nextScene();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                this.previousScene();
                break;
            case ' ':
                e.preventDefault();
                this.toggleVRMode();
                break;
            case 'f':
                e.preventDefault();
                this.toggleFullscreen();
                break;
            case 'i':
                e.preventDefault();
                this.toggleInfoPanel();
                break;
            case 'n':
                e.preventDefault();
                this.toggleNavigationPanel();
                break;
            case 'Escape':
                if (this.isInfoPanelOpen) {
                    e.preventDefault();
                    this.toggleInfoPanel();
                }
                break;
        }
    }
    
    onVRModeChange() {
        // Adjust UI based on VR mode
        const controls = document.querySelector('.vr-controls');
        if (this.isVRMode) {
            controls.style.opacity = '0';
        } else {
            controls.style.opacity = '1';
        }
    }
    
    showLoading() {
        document.getElementById('loading-indicator').setAttribute('visible', true);
    }
    
    hideLoading() {
        document.getElementById('loading-indicator').setAttribute('visible', false);
    }
    
    async checkFavorites() {
        if (!userLoggedIn) return;
        
        try {
            const response = await fetch('../api/check-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tour_id: tourData.id
                })
            });
            
            const data = await response.json();
            if (data.is_favorite) {
                this.updateFavoriteButton(true);
            }
        } catch (error) {
            console.error('Error checking favorites:', error);
        }
    }
    
    async toggleFavorite() {
        if (!userLoggedIn) {
            showToast('Please log in to add favorites', 'error');
            return;
        }
        
        const button = document.getElementById('favorite-btn');
        const isCurrentlyFavorite = button.classList.contains('favorited');
        
        try {
            const response = await fetch('../api/toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tour_id: tourData.id,
                    action: isCurrentlyFavorite ? 'remove' : 'add'
                })
            });
            
            const data = await response.json();
            if (data.success) {
                this.updateFavoriteButton(!isCurrentlyFavorite);
                showToast(
                    isCurrentlyFavorite ? 'Removed from favorites' : 'Added to favorites', 
                    'success'
                );
            } else {
                showToast('Error updating favorites', 'error');
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            showToast('Error updating favorites', 'error');
        }
    }
    
    updateFavoriteButton(isFavorite) {
        const button = document.getElementById('favorite-btn');
        const icon = button.querySelector('i');
        
        if (isFavorite) {
            button.classList.add('favorited');
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.innerHTML = '<i class="fas fa-heart"></i> Remove from Favorites';
        } else {
            button.classList.remove('favorited');
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.innerHTML = '<i class="far fa-heart"></i> Add to Favorites';
        }
    }
    
    async saveProgress(sceneId) {
        try {
            await fetch('../api/save-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tour_id: tourData.id,
                    scene_id: sceneId
                })
            });
        } catch (error) {
            console.error('Error saving progress:', error);
        }
    }
    
    shareTour() {
        if (navigator.share) {
            navigator.share({
                title: tourData.title,
                text: 'Check out this amazing VR tour!',
                url: window.location.href
            }).catch(console.error);
        } else {
            // Fallback for browsers that don't support Web Share API
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast('Link copied to clipboard!', 'success');
            }).catch(() => {
                prompt('Copy this link to share:', window.location.href);
            });
        }
    }
}

// Initialize the VR tour when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Wait for A-Frame to be ready
    if (typeof AFRAME !== 'undefined') {
        window.vrTour = new VRTour();
    } else {
        window.addEventListener('aframe-loaded', () => {
            window.vrTour = new VRTour();
        });
    }
});

// Custom event for A-Frame loaded
if (typeof AFRAME !== 'undefined') {
    window.dispatchEvent(new Event('aframe-loaded'));
}