document.addEventListener("DOMContentLoaded", () => {
  const video = document.getElementById("videoPlayer");
  const overlayLayer = document.getElementById("overlay-layer");
  const settingsPanel = document.getElementById("overlay-settings");
  const editorCanvas = document.getElementById("editor-canvas");

  // UI Elements for Loading State
  const loader = document.getElementById("editor-loader");
  const mainContainer = document.getElementById("editor-container-main");

  let selectedOverlay = null;
  const overlayElements = {};
  let isInitialized = false;

  if (!video) {
    console.error("Video element #videoPlayer not found!");
    if (loader)
      loader.innerHTML =
        "<p class='text-danger'>Error: Video element missing.</p>";
    return;
  }

  /* ---------------- 1. LAYOUT & ASPECT RATIO FIX ---------------- */

  function initEditor() {
    if (isInitialized) return;

    // Check if video dimensions are available
    if (video.videoWidth === 0 || video.readyState < 2) {
      setTimeout(initEditor, 100);
      return;
    }

    // 1. Show the container so we can calculate sizes
    mainContainer.style.display = "block";
    loader.style.display = "none";

    // 2. Size the overlay layer to match the video
    syncOverlayLayerSize();

    // 3. Render existing overlays
    if (window.overlays && Array.isArray(overlays)) {
      overlays.forEach((o) => {
        if (typeof o.settings === "string") o.settings = JSON.parse(o.settings);
        createOverlayElement(o);
      });
    }

    isInitialized = true;

    // Final sync after a short delay for layout stability
    setTimeout(syncOverlayLayerSize, 100);
  }

  function syncOverlayLayerSize() {
    if (!video || !overlayLayer || !editorCanvas) return;

    // Get the actual rendered size of the video inside the canvas
    const videoRect = video.getBoundingClientRect();
    const canvasRect = editorCanvas.getBoundingClientRect();

    // Match the overlay layer to the video's exact dimensions
    overlayLayer.style.width = videoRect.width + "px";
    overlayLayer.style.height = videoRect.height + "px";

    // Position the layer exactly over the centered video
    overlayLayer.style.left = videoRect.left - canvasRect.left + "px";
    overlayLayer.style.top = videoRect.top - canvasRect.top + "px";

    // Ensure layer allows clicks to children but not itself
    overlayLayer.style.pointerEvents = "none";
  }

  // TRIGGER INITIALIZATION
  video.addEventListener("loadedmetadata", initEditor);
  video.addEventListener("canplay", initEditor);
  // Fallback in case events already fired
  if (video.readyState >= 2) initEditor();

  window.addEventListener("resize", syncOverlayLayerSize);

  /* ---------------- 2. OVERLAY MANAGEMENT ---------------- */

  function deleteOverlay(id) {
    swal({
      title: "Are you sure?",
      text: "This overlay will be removed from the template.",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        const el = overlayElements[id];
        if (el) el.remove();
        const index = overlays.findIndex((o) => o.id == id);
        if (index > -1) overlays.splice(index, 1);
        delete overlayElements[id];
        selectedOverlay = null;
        renderSettings();
      }
    });
  }

  function createOverlayElement(o) {
    const el = document.createElement("div");
    el.className = "overlay-item";
    el.id = "el_" + o.id;
    el.style.position = "absolute";
    el.style.pointerEvents = "auto";

    const content = document.createElement("div");
    content.className = "overlay-content";
    content.style.pointerEvents = "none";

    const delBtn = document.createElement("div");
    delBtn.className = "delete-overlay";
    delBtn.innerHTML = '<i class="fas fa-times"></i>';

    delBtn.onclick = (e) => {
      e.stopPropagation();
      deleteOverlay(o.id);
    };

    if (o.overlay_type === "logo") {
      const img = document.createElement("img");
      img.src = DEMO_LOGO;
      img.style.width = "100%";
      img.style.height = "100%";
      img.style.objectFit = "contain";
      content.appendChild(img);
    } else {
      const textKey = o.settings?.text_key || o.variable || "text";
      content.innerText = `{{${textKey}}}`;
    }

    el.appendChild(content);
    el.appendChild(delBtn);

    if (!o.settings) o.settings = {};

    if (!o.settings.bg) {
      o.settings.bg = {
        enabled: false,
        color: "#000000",
        padding: 5,
        radius: 4,
      };
    }

    applyPosition(el, o);
    applyStyles(el, o.settings);

    overlayLayer.appendChild(el);
    overlayElements[o.id] = el;

    el.addEventListener("mousedown", (e) => {
      e.stopPropagation();
      selectOverlay(o.id);
    });

    initInteract(el, o);
  }

  /* ---------------- 3. UPDATED INTERACTIVITY (CONSTRAINED) ---------------- */

  function initInteract(el, overlay) {
    interact(el)
      .draggable({
        modifiers: [
          interact.modifiers.restrictRect({
            restriction: "parent", // Keeps it inside overlay-layer
            endOnly: false,
          }),
        ],
        listeners: {
          move(event) {
            const display = overlayLayer.getBoundingClientRect();

            const scaleX = video.videoWidth / display.width;
            const scaleY = video.videoHeight / display.height;

            overlay.x += (event.dx * scaleX) / video.videoWidth;
            overlay.y += (event.dy * scaleY) / video.videoHeight;

            overlay.x = Math.max(0, Math.min(1 - overlay.width, overlay.x));
            overlay.y = Math.max(0, Math.min(1 - overlay.height, overlay.y));

            el.style.left = overlay.x * 100 + "%";
            el.style.top = overlay.y * 100 + "%";
          },
        },
      })
      .resizable({
        edges: { left: true, right: true, bottom: true, top: true },
        modifiers: [
          interact.modifiers.restrictSize({
            min: { width: 30, height: 20 },
          }),
          interact.modifiers.restrictEdges({
            outer: "parent", // Prevents resizing outside video
          }),
        ],
        listeners: {
          move(event) {
            const display = overlayLayer.getBoundingClientRect();

            const scaleX = video.videoWidth / display.width;
            const scaleY = video.videoHeight / display.height;

            overlay.width = (event.rect.width * scaleX) / video.videoWidth;
            overlay.height = (event.rect.height * scaleY) / video.videoHeight;

            overlay.x += (event.deltaRect.left * scaleX) / video.videoWidth;
            overlay.y += (event.deltaRect.top * scaleY) / video.videoHeight;

            overlay.width = Math.max(0.05, overlay.width);
            overlay.height = Math.max(0.05, overlay.height);

            el.style.width = overlay.width * 100 + "%";
            el.style.height = overlay.height * 100 + "%";
            el.style.left = overlay.x * 100 + "%";
            el.style.top = overlay.y * 100 + "%";
          },
        },
      });
  }

  function applyPosition(el, o) {
    el.style.left = (parseFloat(o.x) || 0) * 100 + "%";
    el.style.top = (parseFloat(o.y) || 0) * 100 + "%";
    el.style.width = (parseFloat(o.width) || 0.2) * 100 + "%";
    el.style.height = (parseFloat(o.height) || 0.1) * 100 + "%";
  }

  function applyStyles(el, s) {
    if (!s) return;

    el.style.color = s.color || "#ffffff";
    el.style.fontSize = (s.font_size || 18) + "px";

    if (s.bg && s.bg.enabled === true) {
      el.style.background = s.bg.color || "rgba(0,0,0,0.5)";
      el.style.padding = (s.bg.padding || 5) + "px";
      el.style.borderRadius = (s.bg.radius || 4) + "px";
    } else {
      el.style.background = "transparent";
      el.style.padding = "0px";
      el.style.borderRadius = "0px";
    }

    el.style.display = "flex";
    el.style.alignItems = "center";
    el.style.justifyContent = "center";
  }

  /* ---------------- 4. UI SETTINGS PANEL ---------------- */

  function selectOverlay(id) {
    selectedOverlay = overlays.find((o) => o.id == id);
    document
      .querySelectorAll(".overlay-item")
      .forEach((item) => item.classList.remove("selected"));
    overlayElements[id]?.classList.add("selected");
    renderSettings();
    bindTextControls(selectedOverlay, overlayElements[id]);
  }

  function renderSettings() {
    if (!selectedOverlay) {
      settingsPanel.innerHTML =
        '<p class="text-muted text-center mt-3">Select an overlay to edit</p>';
      return;
    }

    settingsPanel.innerHTML = `
            <div class="style-group p-1 border rounded bg-light mb-1">
                <h6 class="mb-2" style="font-size: 1.2rem; font-weight: bold;">
                    <i class="fas fa-clock text-muted"></i> Visibility Timing
                </h6>
                <div class="row no-gutters align-items-center video-timing-container">
                    <div class="col-6 pr-1">
                        <label class="mb-0 text-muted" style="font-size: 0.9rem;">In</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.1" class="form-control text-center border-success" 
                                   value="${Number(selectedOverlay.start_time).toFixed(1)}" id="startTime">
                            <div class="input-group-append">
                                <button class="btn btn-success px-2" onclick="setCurrentTime('start')">Now</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 pl-1">
                        <label class="mb-0 text-muted" style="font-size: 0.9rem;">Out</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.1" class="form-control text-center border-danger" 
                                   value="${Number(selectedOverlay.end_time).toFixed(1)}" id="endTime">
                            <div class="input-group-append">
                                <button class="btn btn-danger px-2" onclick="setCurrentTime('end')">Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            ${
              selectedOverlay.overlay_type !== "logo"
                ? `
                  <div class="style-group p-2 border rounded bg-white">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                          <h6 class="mb-0" style="font-size: 0.85rem; font-weight: bold;">Font Size</h6>
                          <span class="badge badge-info" id="fontSizeBadge">${selectedOverlay.settings.font_size || 18}px</span>
                      </div>
                      <input type="range" min="10" max="80" value="${selectedOverlay.settings.font_size || 18}" 
                            class="custom-range w-100" id="fontSizeRange">
                  </div>`
                : ""
            }
        `;

    document.getElementById("startTime").onchange = (e) => {
      selectedOverlay.start_time = parseFloat(e.target.value) || 0;
    };

    document.getElementById("endTime").onchange = (e) => {
      selectedOverlay.end_time = parseFloat(e.target.value) || 0;
    };

    const fRange = document.getElementById("fontSizeRange");
    if (fRange) {
      fRange.oninput = (e) => {
        const val = e.target.value;
        selectedOverlay.settings.font_size = val;
        overlayElements[selectedOverlay.id].style.fontSize = val + "px";
        document.getElementById("fontSizeBadge").innerText = val + "px";
      };
    }
  }

  window.setCurrentTime = (type) => {
    if (!selectedOverlay) return;
    const now = video.currentTime;
    if (type === "start") {
      selectedOverlay.start_time = parseFloat(now.toFixed(1));
      document.getElementById("startTime").value = now.toFixed(1);
    } else {
      selectedOverlay.end_time = parseFloat(now.toFixed(1));
      document.getElementById("endTime").value = now.toFixed(1);
    }
  };

  function bindTextControls(o, el) {
    if (!o.settings.bg) {
      o.settings.bg = {
        enabled: false,
        color: "#000000",
        padding: 5,
        radius: 4,
      };
    }

    const bgEnabled = document.getElementById("bgEnabled");
    const colorInp = document.getElementById("textColor");
    const bgInp = document.getElementById("bgColor");
    const padInp = document.getElementById("padding");
    const radInp = document.getElementById("radius");

    // load values
    bgEnabled.checked = o.settings.bg.enabled === true;
    colorInp.value = o.settings.color || "#ffffff";
    bgInp.value = o.settings.bg.color || "#000000";
    padInp.value = o.settings.bg.padding || 5;
    radInp.value = o.settings.bg.radius || 4;

    function refresh() {
      applyStyles(el, o.settings);
    }

    // checkbox controls bg existence
    bgEnabled.onchange = (e) => {
      o.settings.bg.enabled = e.target.checked === true;
      refresh();
    };

    colorInp.oninput = (e) => {
      o.settings.color = e.target.value;
      refresh();
    };

    bgInp.oninput = (e) => {
      o.settings.bg.color = e.target.value;
      refresh();
    };

    padInp.oninput = (e) => {
      o.settings.bg.padding = parseInt(e.target.value);
      refresh();
    };

    radInp.oninput = (e) => {
      o.settings.bg.radius = parseInt(e.target.value);
      refresh();
    };
  }

  /* ---------------- 5. INIT & TIMELINE ---------------- */

  if (Array.isArray(overlays)) {
    overlays.forEach((o) => {
      if (typeof o.settings === "string") o.settings = JSON.parse(o.settings);
      createOverlayElement(o);
    });
  }

  video.addEventListener("timeupdate", () => {
    const t = video.currentTime;
    overlays.forEach((o) => {
      const el = overlayElements[o.id];
      if (!el) return;
      const isEditing = selectedOverlay && selectedOverlay.id == o.id;
      el.style.display =
        isEditing || (t >= o.start_time && t <= o.end_time) ? "flex" : "none";
    });
  });

  document.querySelectorAll(".add-overlay").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      //   console.log("Adding overlay of type:", type);
      const id = "ov_" + Date.now();
      const startTime = video.currentTime;

      let overlay = {
        id: id,
        overlay_type: type === "logo" ? "logo" : "text",
        settings: {
          text_key: type,
          font_size: 18,
          color: "#ffffff",
          bg: {
            enabled: false,
            color: "#000000",
            padding: 5,
            radius: 4,
          },
        },
        x: 0.1,
        y: 0.1,
        width: 0.2,
        height: 0.08,
        start_time: parseFloat(startTime.toFixed(1)),
        end_time: parseFloat((startTime + 5).toFixed(1)),
      };

      //   console.log("New overlay object:", overlay);

      overlays.push(overlay);
      createOverlayElement(overlay);
      selectOverlay(id);
    });
  });
});

/* ---------------- 6. SAVE ---------------- */
$(document).on("click", "#saveTemplateBtn", function () {
  const btn = $(this);
  $.ajax({
    url: base_url + "Video_editor/save_overlays",
    method: "POST",
    data: {
      template_id: btn.data("template-id"),
      overlays: JSON.stringify(overlays),
    },
    dataType: "json",
    success: (res) => {
      swal(
        res.status === "success" ? "Success" : "Error",
        res.message,
        res.status,
      );
    },
    error: () => swal("Error", "Server connection failed", "error"),
  });
});
