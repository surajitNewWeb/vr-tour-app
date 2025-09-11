// assets/js/vr.js

document.addEventListener("DOMContentLoaded", () => {
  const panorama = document.getElementById("panorama");
  const hotspotContainer = document.getElementById("hotspots");
  const loading = document.getElementById("loading");
  const favBtn = document.getElementById("favoriteBtn");

  async function loadScene(sceneId) {
    loading.style.display = "block";

    try {
      const response = await fetch(`scene.php?id=${sceneId}`);
      const data = await response.json();

      if (data.error) {
        alert(data.error);
        return;
      }

      // ✅ Fix: correct panorama path
      panorama.setAttribute("src", `../assets/panoramas/${data.scene.panorama}`);

      // Clear old hotspots
      hotspotContainer.innerHTML = "";

      // Add hotspots dynamically
      data.hotspots.forEach(h => {
        const hotspot = document.createElement("a-entity");
        hotspot.setAttribute("geometry", { primitive: "sphere", radius: 0.5 });
        hotspot.setAttribute("material", { color: "#FF4500", opacity: 0.8 });
        hotspot.setAttribute("position", `${h.x} ${h.y} ${h.z}`);
        hotspot.setAttribute("look-at", "[camera]");

        if (h.type === "navigation" && h.target_scene_id) {
          hotspot.addEventListener("click", () => {
            currentSceneId = h.target_scene_id;
            loadScene(h.target_scene_id);
          });
        } else if (h.type === "info") {
          hotspot.addEventListener("click", () => {
            alert(`${h.title}\n${h.content}`);
          });
        } else if (h.type === "media") {
          hotspot.addEventListener("click", () => {
            window.open(h.content, "_blank");
          });
        }

        hotspotContainer.appendChild(hotspot);
      });
    } catch (err) {
      console.error(err);
      alert("Failed to load scene.");
    } finally {
      loading.style.display = "none";
    }
  }

  // Favorite toggle
  if (favBtn) {
    favBtn.addEventListener("click", async () => {
      const formData = new FormData();
      formData.append("tour_id", TOUR_ID);

      const res = await fetch("../ajax/favorite.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        favBtn.textContent = data.favorited ? "★ Favorited" : "☆ Favorite";
        favBtn.classList.toggle("active", data.favorited);
      }
    });
  }

  // Load initial scene
  if (typeof currentSceneId !== "undefined") {
    loadScene(currentSceneId);
  }
});
