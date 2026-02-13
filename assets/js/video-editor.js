document.addEventListener("DOMContentLoaded", () => {
  const video = document.getElementById("videoPlayer");
  const overlayLayer = document.getElementById("overlay-layer");
  const settingsPanel = document.getElementById("overlay-settings");

  let selectedOverlay = null;
  const overlayElements = {};

  /* ---------------- HELPERS ---------------- */

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

  function initInteract(el, overlay) {
    interact(el)
      .draggable({
        listeners: {
          move(event) {
            const rect = overlayLayer.getBoundingClientRect();
            overlay.x = (parseFloat(overlay.x) || 0) + event.dx / rect.width;
            overlay.y = (parseFloat(overlay.y) || 0) + event.dy / rect.height;
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
            overlay.width = event.rect.width / rect.width;
            overlay.height = event.rect.height / rect.height;
            overlay.x =
              (parseFloat(overlay.x) || 0) + event.deltaRect.left / rect.width;
            overlay.y =
              (parseFloat(overlay.y) || 0) + event.deltaRect.top / rect.height;
            el.style.width = overlay.width * 100 + "%";
            el.style.height = overlay.height * 100 + "%";
            el.style.left = overlay.x * 100 + "%";
            el.style.top = overlay.y * 100 + "%";
          },
        },
      });
  }

  function applyPosition(el, o) {
    el.style.left = parseFloat(o.x) * 100 + "%";
    el.style.top = parseFloat(o.y) * 100 + "%";
    el.style.width = parseFloat(o.width) * 100 + "%";
    el.style.height = parseFloat(o.height) * 100 + "%";
  }

  function applyStyles(el, s) {
    if (!s) return;
    el.style.color = s.color || "#ffffff";
    el.style.background = s.background || "rgba(0,0,0,0.5)";
    el.style.padding = (s.padding || 5) + "px";
    el.style.borderRadius = (s.border_radius || 4) + "px";
    el.style.fontSize = (s.font_size || 18) + "px";
  }

  function selectOverlay(id) {
    selectedOverlay = overlays.find((o) => o.id == id);
    document
      .querySelectorAll(".overlay-item")
      .forEach((el) => el.classList.remove("selected"));
    overlayElements[id]?.classList.add("selected");
    renderSettings();
    bindTextControls(selectedOverlay, overlayElements[id]);
  }

  function renderSettings() {
    if (!selectedOverlay) {
      settingsPanel.innerHTML =
        '<p class="text-muted text-center">Select an overlay</p>';
      return;
    }

    settingsPanel.innerHTML = `
    <div class="style-group p-2 border rounded bg-light mb-3">
        <h6 class="mb-2" style="font-size: 0.85rem; font-weight: bold;">
            <i class="fas fa-clock text-muted"></i> Visibility Timing
        </h6>
        
        <div class="row no-gutters align-items-center video-timing-container">
            <div class="col-6 pr-1">
                <label class="mb-0 text-muted" style="font-size: 0.7rem;">In</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.1" class="form-control text-center border-success" 
                           value="${Number(selectedOverlay.start_time).toFixed(1)}" id="startTime">
                    <div class="input-group-append">
                        <button class="btn btn-success btn-sm px-2" 
                                onclick="setCurrentTime('start')" title="Set Start">Now</button>
                    </div>
                </div>
            </div>

            <div class="col-6 pl-1">
                <label class="mb-0 text-muted" style="font-size: 0.7rem;">Out</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.1" class="form-control text-center border-danger" 
                           value="${Number(selectedOverlay.end_time).toFixed(1)}" id="endTime">
                    <div class="input-group-append">
                        <button class="btn btn-danger btn-sm px-2" 
                                onclick="setCurrentTime('end')" title="Set End">Now</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-2 border-top pt-1">
            <small class="text-muted" style="font-size: 0.65rem; font-style: italic;">
                Tip: Pause video and click "Now" to sync timings.
            </small>
        </div>
    </div>
    
    ${
      selectedOverlay.overlay_type !== "logo"
        ? `
    <div class="style-group p-2 border rounded shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="mb-0" style="font-size: 0.85rem; font-weight: bold;">
                <i class="fas fa-font text-info"></i> Font Size
            </h6>
            <span class="badge badge-info" id="fontSizeBadge">${selectedOverlay.settings.font_size || 18}px</span>
        </div>
        <input type="range" min="10" max="80" 
               value="${selectedOverlay.settings.font_size || 18}" 
               class="custom-range w-100" id="fontSizeRange">
    </div>`
        : ""
    }
`;

    document.getElementById("startTime").onchange = (e) => {
      selectedOverlay.start_time = parseFloat(e.target.value);
    };
    document.getElementById("endTime").onchange = (e) => {
      selectedOverlay.end_time = parseFloat(e.target.value);
    };

    const fRange = document.getElementById("fontSizeRange");
    if (fRange) {
      fRange.oninput = (e) => {
        selectedOverlay.settings.font_size = e.target.value;
        overlayElements[selectedOverlay.id].style.fontSize =
          e.target.value + "px";
      };
    }
  }

  // Global helper for the "Now" buttons
  window.setCurrentTime = (type) => {
    if (!selectedOverlay) return;
    const now = video.currentTime;
    if (type === "start") {
      selectedOverlay.start_time = now;
      document.getElementById("startTime").value = now.toFixed(1);
    } else {
      selectedOverlay.end_time = now;
      document.getElementById("endTime").value = now.toFixed(1);
    }
  };

  function bindTextControls(o, el) {
    const colorInp = document.getElementById("textColor");
    const bgInp = document.getElementById("bgColor");
    const padInp = document.getElementById("padding");
    const radInp = document.getElementById("radius");

    colorInp.value = o.settings.color || "#ffffff";
    bgInp.value = o.settings.background || "#000000";
    padInp.value = o.settings.padding || 5;
    radInp.value = o.settings.border_radius || 4;

    colorInp.oninput = (e) => {
      o.settings.color = e.target.value;
      el.style.color = e.target.value;
    };
    bgInp.oninput = (e) => {
      o.settings.background = e.target.value;
      el.style.background = e.target.value;
    };
    padInp.oninput = (e) => {
      o.settings.padding = e.target.value;
      el.style.padding = e.target.value + "px";
    };
    radInp.oninput = (e) => {
      o.settings.border_radius = e.target.value;
      el.style.borderRadius = e.target.value + "px";
    };
  }

  /* ---------------- INIT & EVENTS ---------------- */

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
      // Visible if time is within range OR if it is currently being edited
      const isEditing = selectedOverlay && selectedOverlay.id == o.id;
      el.style.display =
        isEditing || (t >= o.start_time && t <= o.end_time) ? "block" : "none";
    });
  });

  document.querySelectorAll(".add-overlay").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      const id = "ov_" + Date.now();

      // SMART SNAP: Use current video time as start point
      const startTime = video.currentTime;
      const duration = 5; // Default 5 seconds visibility
      const endTime = Math.min(
        startTime + duration,
        video.duration || startTime + duration,
      );

      let overlay = {
        id: id,
        overlay_type: type === "logo" ? "logo" : "text",
        settings: {
          text_key: type !== "logo" ? type : null,
          font_size: 18,
          color: "#ffffff",
          background: "rgba(0,0,0,0.5)",
          padding: 5,
          border_radius: 4,
          align: "center",
        },
        x: 0.1,
        y: 0.1,
        width: 0.25,
        height: 0.1,
        start_time: startTime,
        end_time: endTime,
      };
      overlays.push(overlay);
      createOverlayElement(overlay);
      selectOverlay(id);
    });
  });
});

/* ---------------- SAVE DATA ---------------- */
$("#saveTemplateBtn").on("click", function () {
  const btn = $(this);
  const templateId = $(this).data("template-id");

  btn
    .prop("disabled", true)
    .html('<i class="fas fa-spinner fa-spin"></i> Saving...');

  console.log("Saving overlays:", overlays);

  console.log("Final overlays data to be sent:", JSON.stringify(overlays));

  $.ajax({
    url: base_url + "Video_editor/save_overlays",
    method: "POST",
    data: {
      template_id: templateId,
      overlays: JSON.stringify(overlays), // This saves the whole structure including settings
    },
    dataType: "json",
    success: function (res) {
      if (res.status === "success") {
        swal("Success", res.message, "success");
      } else {
        swal("Error", res.message, "error");
      }
    },
    error: function () {
      swal("Error", "Server connection failed", "error");
    },
    complete: function () {
      btn
        .prop("disabled", false)
        .html('<i class="fas fa-save"></i> Save Template');
    },
  });
});
