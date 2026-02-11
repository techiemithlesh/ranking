document.addEventListener("DOMContentLoaded", () => {
  const video = document.getElementById("videoPlayer");
  const overlayLayer = document.getElementById("overlay-layer");
  const settingsPanel = document.getElementById("overlay-settings");

  let selectedOverlay = null;
  const overlayElements = {};

  /* ---------------- HELPERS ---------------- */

  function getPlaceholder(variable) {
    return `{{${variable}}}`;
  }

  function createOverlayElement(o) {
    const el = document.createElement("div");
    el.className = "overlay-item";
    el.id = "el_" + o.id; // Unique DOM ID
    el.dataset.id = o.id;

    applyPosition(el, o);

    // Styling
    const s = o.styles || {};
    el.style.background = s.background || "#c05959";
    el.style.padding = (s.padding || 5) + "px";
    el.style.borderRadius = (s.borderRadius || 4) + "px";
    el.style.display = "block"; // Always visible for now

    if (o.overlay_type === "logo") {
      const img = document.createElement("img");
      img.src = DEMO_LOGO;
      img.style.width = "100%";
      img.style.height = "100%";
      img.style.objectFit = "contain";
      el.appendChild(img);
    } else {
      el.innerText = `{{${o.variable}}}`;
      el.style.color = s.color || "#ffffff";
    }

    // Append to DOM before initializing Interact
    overlayLayer.appendChild(el);
    overlayElements[o.id] = el;

    // Selection logic
    el.addEventListener("mousedown", () => selectOverlay(o.id));

    // Initialize Interact.js immediately
    initInteract(el, o);
  }

  function initInteract(el, overlay) {
    console.log("Initializing Interact for:", el.id);

    interact(el)
      .draggable({
        listeners: {
          move(event) {
            const rect = overlayLayer.getBoundingClientRect();

            // Calculate movement as percentage of container size
            overlay.x += event.dx / rect.width;
            overlay.y += event.dy / rect.height;

            // Apply new position
            el.style.left = overlay.x * 100 + "%";
            el.style.top = overlay.y * 100 + "%";
          },
        },
      })
      .resizable({
        edges: { left: true, right: true, bottom: true, top: true },
        listeners: {
          move(event) {
            const rect = overlayLayer.getBoundingClientRect();

            // Update dimensions
            overlay.width = event.rect.width / rect.width;
            overlay.height = event.rect.height / rect.height;

            // Update position (critical for top/left handles)
            overlay.x += event.deltaRect.left / rect.width;
            overlay.y += event.deltaRect.top / rect.height;

            el.style.width = overlay.width * 100 + "%";
            el.style.height = overlay.height * 100 + "%";
            el.style.left = overlay.x * 100 + "%";
            el.style.top = overlay.y * 100 + "%";
          },
        },
      });
  }

  function applyPosition(el, o) {
    el.style.left = o.x * 100 + "%";
    el.style.top = o.y * 100 + "%";
    el.style.width = o.width * 100 + "%";
    el.style.height = o.height * 100 + "%";
  }

  function selectOverlay(id) {
    selectedOverlay = overlays.find((o) => o.id === id);

    document
      .querySelectorAll(".overlay-item")
      .forEach((el) => el.classList.remove("selected"));

    overlayElements[id]?.classList.add("selected");

    renderSettings();
    bindTextControls(selectedOverlay, overlayElements[id]);
  }

  function renderSettings() {
    if (!selectedOverlay) {
      settingsPanel.innerHTML = "<p>Select an overlay</p>";
      return;
    }

    settingsPanel.innerHTML = `
      <label>Start Time (sec)</label>
      <input type="number" step="0.1" value="${selectedOverlay.start_time}" id="startTime">

      <label>End Time (sec)</label>
      <input type="number" step="0.1" value="${selectedOverlay.end_time}" id="endTime">
    `;

    document.getElementById("startTime").onchange = (e) => {
      selectedOverlay.start_time = parseFloat(e.target.value);
    };

    document.getElementById("endTime").onchange = (e) => {
      selectedOverlay.end_time = parseFloat(e.target.value);
    };
  }

  /* ---------------- INIT EXISTING ---------------- */

  overlays.forEach(createOverlayElement);

  /* ---------------- VIDEO TIME SYNC ---------------- */

  video.addEventListener("timeupdate", () => {
    const t = video.currentTime;
    overlays.forEach((o) => {
      const el = overlayElements[o.id];
      if (!el) return;

      // If it's the selected one, keep it visible so we can edit it
      if (selectedOverlay && selectedOverlay.id === o.id) {
        el.style.display = "block";
      } else {
        el.style.display =
          t >= o.start_time && t <= o.end_time ? "block" : "none";
      }
    });
  });

  /* ---------------- ADD OVERLAY ---------------- */

  document.querySelectorAll(".add-overlay").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      const id = "ov_" + Date.now(); // String ID for consistency

      let overlay = {
        id: id,
        overlay_type: type === "logo" ? "logo" : "text",
        variable: type !== "logo" ? type : null,
        x: 0.1,
        y: 0.1,
        width: 0.3,
        height: type === "logo" ? 0.2 : 0.08,
        start_time: 0,
        end_time: video.duration || 10,
        styles: {
          // Ensure styles object exists
          color: "#ffffff",
          background: "rgba(0,0,0,0.5)",
          padding: 5,
          borderRadius: 4,
        },
      };

      overlays.push(overlay);
      createOverlayElement(overlay);

      // Force display so user can see it immediately to drag it
      overlayElements[id].style.display = "block";
      selectOverlay(id);
    });
  });

  /* ---------------- INTERACT JS ---------------- */

  function enableInteract(el, overlay) {
    interact(el)
      .draggable({
        listeners: {
          move(event) {
            const rect = overlayLayer.getBoundingClientRect();

            // Update internal data (percentages)
            overlay.x += event.dx / rect.width;
            overlay.y += event.dy / rect.height;

            // Update UI
            applyPosition(el, overlay);
          },
        },
      })
      .resizable({
        edges: { left: true, right: true, bottom: true, top: true },
        listeners: {
          move(event) {
            const rect = overlayLayer.getBoundingClientRect();

            // Update width/height percentages
            overlay.width = event.rect.width / rect.width;
            overlay.height = event.rect.height / rect.height;

            // Update x/y if resizing from top or left
            overlay.x += event.deltaRect.left / rect.width;
            overlay.y += event.deltaRect.top / rect.height;

            applyPosition(el, overlay);
          },
        },
      });
  }

  function bindTextControls(o, el) {
    if (!o.styles) o.styles = {}; // Safety check

    const colorInp = document.getElementById("textColor");
    const bgInp = document.getElementById("bgColor");
    const padInp = document.getElementById("padding");
    const radInp = document.getElementById("radius");

    // Sync UI values to current overlay
    colorInp.value = o.styles.color || "#ffffff";
    bgInp.value = o.styles.background || "#000000";
    padInp.value = o.styles.padding || 0;
    radInp.value = o.styles.borderRadius || 0;

    colorInp.oninput = (e) => {
      o.styles.color = e.target.value;
      el.style.color = e.target.value;
    };
    bgInp.oninput = (e) => {
      o.styles.background = e.target.value;
      el.style.background = e.target.value;
    };
    padInp.oninput = (e) => {
      o.styles.padding = e.target.value;
      el.style.padding = e.target.value + "px";
    };
    radInp.oninput = (e) => {
      o.styles.borderRadius = e.target.value;
      el.style.borderRadius = e.target.value + "px";
    };
  }
});
