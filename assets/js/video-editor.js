document.addEventListener("DOMContentLoaded", () => {
  const video        = document.getElementById("videoPlayer");
  const overlayLayer = document.getElementById("overlay-layer");
  const settingsPanel= document.getElementById("overlay-settings");
  const editorCanvas = document.getElementById("editor-canvas");
  const loader       = document.getElementById("editor-loader");
  const mainContainer= document.getElementById("editor-container-main");

  let selectedOverlay = null;
  const overlayElements = {};
  let isInitialized = false;

  if (!video) {
    console.error("Video element #videoPlayer not found!");
    if (loader) loader.innerHTML = "<p class='text-danger'>Error: Video element missing.</p>";
    return;
  }

  /* ═══════════════════════════════════════════════════
     1. AUTO-FIT TEXT  (mirrors image editor behaviour)
     Finds largest font-size where text fits inside the
     overlay box without overflowing in either dimension.
  ═══════════════════════════════════════════════════ */
  function autoFitText(el, boxW, boxH) {
    if (!el || boxW <= 0 || boxH <= 0) return;
    const MIN = 8;
    const MAX = Math.max(MIN, Math.floor(Math.max(boxH * 0.85, boxW * 0.3)));
    let size  = MAX;
    el.style.width      = boxW + "px";
    el.style.maxWidth   = boxW + "px";
    el.style.whiteSpace = "normal";
    el.style.wordBreak  = "break-word";
    el.style.overflow   = "hidden";
    el.style.fontSize   = size + "px";
    while (size > MIN && (el.scrollWidth > boxW + 1 || el.scrollHeight > boxH + 1)) {
      size--;
      el.style.fontSize = size + "px";
    }
  }

  /* Re-fit text for ALL visible text overlays.
     Called after resize / syncOverlayLayerSize. */
  function reflowAllText() {
    overlays.forEach((o) => {
      if (o.overlay_type === "logo") return;
      const el = overlayElements[o.id];
      if (!el) return;
      const content = el.querySelector(".overlay-content");
      if (!content) return;
      requestAnimationFrame(() => {
        autoFitText(content, el.clientWidth, el.clientHeight);
      });
    });
  }

  /* ═══════════════════════════════════════════════════
     2. LAYOUT & OVERLAY LAYER SYNC
  ═══════════════════════════════════════════════════ */
  function initEditor() {
    if (isInitialized) return;
    if (video.videoWidth === 0 || video.readyState < 2) {
      setTimeout(initEditor, 100);
      return;
    }

    mainContainer.style.display = "block";
    loader.style.display = "none";

    syncOverlayLayerSize();

    if (Array.isArray(overlays) && overlays.length > 0) {
      overlays.forEach((o) => {
        // settings may be double-encoded JSON string when loaded from DB
        if (typeof o.settings === "string") {
          try { o.settings = JSON.parse(o.settings); } catch(e) { o.settings = {}; }
        }
        if (!o.settings) o.settings = {};
        createOverlayElement(o);
      });
    }

    isInitialized = true;
    setTimeout(() => { syncOverlayLayerSize(); reflowAllText(); }, 150);
  }

  function syncOverlayLayerSize() {
    if (!video || !overlayLayer || !editorCanvas) return;
    const videoRect  = video.getBoundingClientRect();
    const canvasRect = editorCanvas.getBoundingClientRect();
    overlayLayer.style.width  = videoRect.width  + "px";
    overlayLayer.style.height = videoRect.height + "px";
    overlayLayer.style.left   = (videoRect.left - canvasRect.left) + "px";
    overlayLayer.style.top    = (videoRect.top  - canvasRect.top)  + "px";
    overlayLayer.style.pointerEvents = "none";
  }

  video.addEventListener("loadedmetadata", initEditor);
  video.addEventListener("canplay", initEditor);
  if (video.readyState >= 2) initEditor();

  window.addEventListener("resize", () => {
    syncOverlayLayerSize();
    reflowAllText();
  });

  /* ═══════════════════════════════════════════════════
     3. OVERLAY CREATION
  ═══════════════════════════════════════════════════ */
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
    o.x      = parseFloat(o.x)      || 0;
    o.y      = parseFloat(o.y)      || 0;
    o.width  = parseFloat(o.width)  || 0.2;
    o.height = parseFloat(o.height) || 0.1;

    const el = document.createElement("div");
    el.className = "overlay-item";
    el.id = "el_" + o.id;
    el.style.position      = "absolute";
    el.style.pointerEvents = "auto";

    const content = document.createElement("div");
    content.className       = "overlay-content";
    content.style.width     = "100%";
    content.style.height    = "100%";
    content.style.pointerEvents = "none";
    content.style.display   = "flex";
    content.style.alignItems    = "center";
    content.style.justifyContent = "center";
    content.style.overflow  = "hidden";
    content.style.whiteSpace    = "normal";
    content.style.wordBreak     = "break-word";

    const delBtn = document.createElement("div");
    delBtn.className  = "delete-overlay";
    delBtn.innerHTML  = '<i class="fas fa-times"></i>';
    delBtn.onclick = (e) => { e.stopPropagation(); deleteOverlay(o.id); };

    if (o.overlay_type === "logo") {
      const img = document.createElement("img");
      img.src = (typeof DEMO_LOGO !== "undefined") ? DEMO_LOGO : "";
      img.style.cssText = "width:100%;height:100%;object-fit:contain;";
      content.appendChild(img);
    } else {
      const textKey      = o.settings?.text_key || o.variable || "text";
      content.textContent = `{{${textKey.toUpperCase()}}}`;
    }

    el.appendChild(content);
    el.appendChild(delBtn);

    if (!o.settings) o.settings = {};
    if (!o.settings.bg) {
      o.settings.bg = { enabled: false, color: "#000000", padding: 5, radius: 4 };
    }
    // Ensure logo defaults
    if (o.overlay_type === "logo") {
      o.settings = Object.assign({
        radius: 0, opacity: 1, objectFit: "contain",
        borderWidth: 0, borderColor: "#ffffff", borderStyle: "solid",
        shadow: "none", filter: "none"
      }, o.settings);
    }

    applyPosition(el, o);
    applyStyles(el, o.settings);
    if (o.overlay_type === "logo") applyLogoStyles(el, o.settings);

    overlayLayer.appendChild(el);
    overlayElements[o.id] = el;

    el.addEventListener("mousedown", (e) => {
      e.stopPropagation();
      selectOverlay(o.id);
    });

    // Auto-fit text AFTER element is painted and has real dimensions
    if (o.overlay_type !== "logo") {
      requestAnimationFrame(() => {
        autoFitText(content, el.clientWidth, el.clientHeight);
      });
    }

    requestAnimationFrame(() => initInteract(el, o));
  }

  /* ═══════════════════════════════════════════════════
     4. INTERACT — drag + resize
  ═══════════════════════════════════════════════════ */
  function initInteract(el, overlay) {
    interact(el)
      .draggable({
        modifiers: [
          interact.modifiers.restrictRect({ restriction: "parent", endOnly: false }),
        ],
        listeners: {
          move(event) {
            const display = overlayLayer.getBoundingClientRect();
            overlay.x += event.dx / display.width;
            overlay.y += event.dy / display.height;
            overlay.x  = Math.max(0, Math.min(1 - overlay.width,  overlay.x));
            overlay.y  = Math.max(0, Math.min(1 - overlay.height, overlay.y));
            el.style.left = overlay.x * 100 + "%";
            el.style.top  = overlay.y * 100 + "%";
          },
        },
      })
      .resizable({
        edges: { left: true, right: true, bottom: true, top: true },
        modifiers: [
          interact.modifiers.restrictSize({ min: { width: 30, height: 20 } }),
          interact.modifiers.restrictEdges({ outer: "parent" }),
        ],
        listeners: {
          move(event) {
            const display = overlayLayer.getBoundingClientRect();
            overlay.width  = Math.max(0.05, event.rect.width  / display.width);
            overlay.height = Math.max(0.05, event.rect.height / display.height);
            overlay.x     += event.deltaRect.left / display.width;
            overlay.y     += event.deltaRect.top  / display.height;

            el.style.width  = overlay.width  * 100 + "%";
            el.style.height = overlay.height * 100 + "%";
            el.style.left   = overlay.x      * 100 + "%";
            el.style.top    = overlay.y      * 100 + "%";

            // Re-fit text live during resize
            if (overlay.overlay_type !== "logo") {
              const content = el.querySelector(".overlay-content");
              if (content) autoFitText(content, el.clientWidth, el.clientHeight);
            }
          },
        },
      });
  }

  function applyPosition(el, o) {
    el.style.left   = (parseFloat(o.x)      || 0)   * 100 + "%";
    el.style.top    = (parseFloat(o.y)      || 0)   * 100 + "%";
    el.style.width  = (parseFloat(o.width)  || 0.2) * 100 + "%";
    el.style.height = (parseFloat(o.height) || 0.1) * 100 + "%";
  }

  function applyStyles(el, s) {
    if (!s) return;
    el.style.color = s.color || "#ffffff";
    // font-size intentionally NOT set here — autoFitText() handles it
    if (s.bg && s.bg.enabled === true) {
      el.style.background   = s.bg.color   || "rgba(0,0,0,0.5)";
      el.style.padding      = (s.bg.padding || 5) + "px";
      el.style.borderRadius = (s.bg.radius  || 4) + "px";
    } else {
      el.style.background   = "transparent";
      el.style.padding      = "0px";
      el.style.borderRadius = "0px";
    }
  }

  /* Apply logo image styles to the <img> inside the overlay */
  function applyLogoStyles(el, s) {
    const img = el.querySelector("img");
    if (!img || !s) return;
    img.style.borderRadius = (s.radius     || 0)         + "px";
    img.style.opacity      =  s.opacity    !== undefined ? s.opacity : 1;
    img.style.objectFit    =  s.objectFit  || "contain";
    img.style.boxShadow    =  s.shadow     || "none";
    img.style.filter       =  s.filter     || "none";
    img.style.border       = (+s.borderWidth > 0)
      ? `${s.borderWidth}px ${s.borderStyle || "solid"} ${s.borderColor || "#ffffff"}`
      : "none";
  }

  /* ═══════════════════════════════════════════════════
     5. SETTINGS PANEL
     FIX: Show current video time alongside In/Out inputs
     so user always knows where the playhead is relative
     to the overlay's saved timing.
  ═══════════════════════════════════════════════════ */
  function selectOverlay(id) {
    selectedOverlay = overlays.find((o) => o.id == id);
    document.querySelectorAll(".overlay-item")
      .forEach((item) => item.classList.remove("selected"));
    overlayElements[id]?.classList.add("selected");
    renderSettings();
    if (!selectedOverlay) return;
    const elSelected = overlayElements[id];
    if (selectedOverlay.overlay_type === "logo") {
      showSidebarSection("logo");
      bindLogoControls(selectedOverlay, elSelected);
    } else {
      showSidebarSection("text");
      bindTextControls(selectedOverlay, elSelected);
    }
  }

  /* Show logo OR text sidebar section, hide the other */
  function showSidebarSection(type) {
    const logoSection = document.getElementById("logoStyleSection");
    const textSection = document.getElementById("textStyleSection");
    if (logoSection) logoSection.style.display = type === "logo" ? "block" : "none";
    if (textSection) textSection.style.display = type === "text" ? "block" : "none";
  }

  function formatTime(s) {
    // Format seconds as M:SS.s  e.g. 65.3 → "1:05.3"
    const m  = Math.floor(s / 60);
    const ss = (s % 60).toFixed(1).padStart(4, "0");
    return m + ":" + ss;
  }

  function renderSettings() {
    if (!selectedOverlay) {
      settingsPanel.innerHTML =
        '<p class="text-muted text-center mt-3">Select an overlay to edit</p>';
      return;
    }

    const startVal = Number(selectedOverlay.start_time).toFixed(1);
    const endVal   = Number(selectedOverlay.end_time).toFixed(1);
    const duration = video.duration || 0;

    // Build time tick marks — one every second, max 10 visible labels
    const tickCount  = Math.floor(duration);
    const step       = tickCount <= 10 ? 1 : tickCount <= 30 ? 5 : tickCount <= 60 ? 10 : 15;
    let ticksHTML    = "";
    for (let t = 0; t <= duration; t += step) {
      const pct = (t / duration) * 100;
      ticksHTML += `
        <div style="position:absolute;left:${pct}%;transform:translateX(-50%);
                    display:flex;flex-direction:column;align-items:center;top:0;">
          <div style="width:1px;height:6px;background:#aaa;"></div>
          <span style="font-size:9px;color:#888;white-space:nowrap;margin-top:1px;">${t}s</span>
        </div>`;
    }

    settingsPanel.innerHTML = `
      <div class="p-2 border rounded bg-light mb-2">

        <div class="d-flex justify-content-between align-items-center mb-1">
          <strong style="font-size:0.82rem;"><i class="fas fa-clock text-muted"></i> Visibility Timing</strong>
          <span id="currentTimeBadge" style="font-size:11px;background:#333;color:#fff;
                border-radius:4px;padding:2px 6px;font-family:monospace;">
            ▶ ${formatTime(video.currentTime)}
          </span>
        </div>

        <!-- ── TIMELINE RULER ── -->
        <div style="position:relative;margin-bottom:22px;padding-top:2px;">

          <!-- Track background -->
          <div style="position:relative;height:10px;background:#ddd;border-radius:5px;overflow:visible;" id="miniTimeline">

            <!-- Green active range -->
            <div id="miniRange"
                 style="position:absolute;height:100%;background:#28a745;border-radius:5px;top:0;"></div>

            <!-- Red playhead line -->
            <div id="miniPlayhead"
                 style="position:absolute;width:2px;height:18px;background:#dc3545;
                        top:-4px;border-radius:1px;z-index:2;"></div>

            <!-- IN handle (green triangle) -->
            <div id="handleIn"
                 style="position:absolute;width:12px;height:12px;background:#28a745;
                        border-radius:2px;top:-1px;transform:translateX(-50%);
                        cursor:ew-resize;z-index:3;" title="Drag to move In point"></div>

            <!-- OUT handle (red triangle) -->
            <div id="handleOut"
                 style="position:absolute;width:12px;height:12px;background:#dc3545;
                        border-radius:2px;top:-1px;transform:translateX(-50%);
                        cursor:ew-resize;z-index:3;" title="Drag to move Out point"></div>
          </div>

          <!-- Tick marks row -->
          <div style="position:relative;height:18px;margin-top:2px;">
            ${ticksHTML}
          </div>
        </div>

        <!-- ── IN / OUT inputs side by side ── -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">

          <div>
            <div style="font-size:11px;color:#555;margin-bottom:3px;">
              <span style="display:inline-block;width:10px;height:10px;
                           background:#28a745;border-radius:2px;margin-right:3px;"></span>
              In (sec)
            </div>
            <div style="display:flex;gap:3px;">
              <input type="number" step="0.1" min="0" id="startTime"
                     value="${startVal}"
                     style="width:100%;padding:4px 6px;font-size:13px;font-weight:600;
                            border:2px solid #28a745;border-radius:4px;text-align:center;">
              <button onclick="setCurrentTime('start')"
                      style="padding:4px 8px;background:#28a745;color:#fff;border:none;
                             border-radius:4px;font-size:11px;cursor:pointer;white-space:nowrap;">
                Now
              </button>
            </div>
          </div>

          <div>
            <div style="font-size:11px;color:#555;margin-bottom:3px;">
              <span style="display:inline-block;width:10px;height:10px;
                           background:#dc3545;border-radius:2px;margin-right:3px;"></span>
              Out (sec)
            </div>
            <div style="display:flex;gap:3px;">
              <input type="number" step="0.1" min="0" id="endTime"
                     value="${endVal}"
                     style="width:100%;padding:4px 6px;font-size:13px;font-weight:600;
                            border:2px solid #dc3545;border-radius:4px;text-align:center;">
              <button onclick="setCurrentTime('end')"
                      style="padding:4px 8px;background:#dc3545;color:#fff;border:none;
                             border-radius:4px;font-size:11px;cursor:pointer;white-space:nowrap;">
                Now
              </button>
            </div>
          </div>

        </div>
        <p style="font-size:10px;color:#999;margin-top:6px;margin-bottom:0;">
          Press <strong>Now</strong> or drag the handles on the timeline.
        </p>
      </div>
    `;

    // Wire number inputs — capture THIS overlay in closure, not selectedOverlay reference
    const thisOverlay = selectedOverlay; // snapshot — prevents stale reference bug
    document.getElementById("startTime").onchange = (e) => {
      thisOverlay.start_time = Math.max(0, Math.min(duration, parseFloat(e.target.value) || 0));
      updateMiniTimeline();
    };
    document.getElementById("endTime").onchange = (e) => {
      thisOverlay.end_time = Math.max(0, Math.min(duration, parseFloat(e.target.value) || 0));
      updateMiniTimeline();
    };

    // Wire draggable IN handle
    const trackEl   = document.getElementById("miniTimeline");
    const handleIn  = document.getElementById("handleIn");
    const handleOut = document.getElementById("handleOut");

    function dragHandle(handle, onDrag) {
      handle.addEventListener("mousedown", (e) => {
        e.preventDefault();
        const onMove = (mv) => {
          const rect = trackEl.getBoundingClientRect();
          const pct  = Math.max(0, Math.min(1, (mv.clientX - rect.left) / rect.width));
          onDrag(pct * duration);
          updateMiniTimeline();
        };
        const onUp = () => {
          window.removeEventListener("mousemove", onMove);
          window.removeEventListener("mouseup", onUp);
        };
        window.addEventListener("mousemove", onMove);
        window.addEventListener("mouseup",   onUp);
      });
    }

    dragHandle(handleIn, (t) => {
      thisOverlay.start_time = parseFloat(Math.min(t, thisOverlay.end_time - 0.1).toFixed(1));
      const si = document.getElementById("startTime");
      if (si) si.value = thisOverlay.start_time.toFixed(1);
    });

    dragHandle(handleOut, (t) => {
      thisOverlay.end_time = parseFloat(Math.max(t, thisOverlay.start_time + 0.1).toFixed(1));
      const ei = document.getElementById("endTime");
      if (ei) ei.value = thisOverlay.end_time.toFixed(1);
    });

    updateMiniTimeline();
  }

  /* Update mini timeline bar, handles, playhead, badge */
  function updateMiniTimeline() {
    if (!selectedOverlay) return;
    const duration    = video.duration || 1;
    const currentTime = video.currentTime;
    const startPct    = Math.max(0, Math.min(100, (selectedOverlay.start_time / duration) * 100));
    const endPct      = Math.max(0, Math.min(100, (selectedOverlay.end_time   / duration) * 100));
    const nowPct      = Math.max(0, Math.min(100, (currentTime / duration) * 100));

    // Badge
    const badge = document.getElementById("currentTimeBadge");
    if (badge) badge.textContent = "▶ " + formatTime(currentTime);

    // Green range bar
    const range = document.getElementById("miniRange");
    if (range) {
      range.style.left  = startPct + "%";
      range.style.width = (endPct - startPct) + "%";
    }

    // Red playhead
    const playhead = document.getElementById("miniPlayhead");
    if (playhead) playhead.style.left = nowPct + "%";

    // IN handle (green)
    const handleIn = document.getElementById("handleIn");
    if (handleIn) handleIn.style.left = startPct + "%";

    // OUT handle (red)
    const handleOut = document.getElementById("handleOut");
    if (handleOut) handleOut.style.left = endPct + "%";

    // Highlight inputs when playhead is within active range
    const inRange    = currentTime >= selectedOverlay.start_time &&
                       currentTime <= selectedOverlay.end_time;
    const startInput = document.getElementById("startTime");
    const endInput   = document.getElementById("endTime");
    if (startInput) startInput.style.background = inRange ? "#d4edda" : "#fff";
    if (endInput)   endInput.style.background   = inRange ? "#d4edda" : "#fff";
  }

  /* ═══════════════════════════════════════════════════
     6. VIDEO TIMEUPDATE — visibility + timeline sync
  ═══════════════════════════════════════════════════ */
  video.addEventListener("timeupdate", () => {
    const t = video.currentTime;

    // In editor mode: show ALL overlays always so designer can see & select them.
    // Visibility timing only affects the final video — not the editor preview.
    // (Comment out the display toggle below if you want live preview behaviour.)
    overlays.forEach((o) => {
      const el = overlayElements[o.id];
      if (!el) return;
      // Always visible in editor — designer needs to see all overlays
      el.style.display = "flex";
      // Dim overlays that are outside their time range so designer
      // can still see them but knows they're "inactive" at this moment
      const inRange = (t >= o.start_time && t <= o.end_time);
      el.style.opacity = inRange ? "1" : "0.35";
    });

    // Keep settings panel timeline indicator in sync
    updateMiniTimeline();
  });

  /* Global — used by onclick="setCurrentTime(...)" in renderSettings HTML */
  window.setCurrentTime = (type) => {
    if (!selectedOverlay) return;
    const now = parseFloat(video.currentTime.toFixed(1));
    if (type === "start") {
      selectedOverlay.start_time = now;
      const inp = document.getElementById("startTime");
      if (inp) inp.value = now.toFixed(1);
    } else {
      selectedOverlay.end_time = now;
      const inp = document.getElementById("endTime");
      if (inp) inp.value = now.toFixed(1);
    }
    updateMiniTimeline();
  };

  /* ═══════════════════════════════════════════════════
     7. TEXT STYLE CONTROLS (sidebar)
  ═══════════════════════════════════════════════════ */
  function bindTextControls(o, el) {
    if (!o.settings.bg) {
      o.settings.bg = { enabled: false, color: "#000000", padding: 5, radius: 4 };
    }
    const bgEnabled = document.getElementById("bgEnabled");
    const colorInp  = document.getElementById("textColor");
    const bgInp     = document.getElementById("bgColor");
    const padInp    = document.getElementById("padding");
    const radInp    = document.getElementById("radius");
    if (!bgEnabled) return;
    bgEnabled.checked = o.settings.bg.enabled === true;
    colorInp.value    = o.settings.color       || "#ffffff";
    bgInp.value       = o.settings.bg.color    || "#000000";
    padInp.value      = o.settings.bg.padding  || 5;
    radInp.value      = o.settings.bg.radius   || 4;
    function refresh() {
      applyStyles(el, o.settings);
      const content = el.querySelector(".overlay-content");
      if (content) requestAnimationFrame(() =>
        autoFitText(content, el.clientWidth, el.clientHeight));
    }
    bgEnabled.onchange = (e) => { o.settings.bg.enabled = e.target.checked; refresh(); };
    colorInp.oninput   = (e) => { o.settings.color          = e.target.value; refresh(); };
    bgInp.oninput      = (e) => { o.settings.bg.color       = e.target.value; refresh(); };
    padInp.oninput     = (e) => { o.settings.bg.padding     = parseInt(e.target.value); refresh(); };
    radInp.oninput     = (e) => { o.settings.bg.radius      = parseInt(e.target.value); refresh(); };
  }

  function bindLogoControls(o, el) {
    // Ensure logo settings defaults
    o.settings = Object.assign({
      radius: 0, opacity: 1, objectFit: "contain",
      borderWidth: 0, borderColor: "#ffffff", borderStyle: "solid",
      shadow: "none", filter: "none"
    }, o.settings);

    const get = (id) => document.getElementById(id);

    const setVal = (id, val) => { const e = get(id); if (e) e.value = val; };
    const setTxt = (id, txt) => { const e = get(id); if (e) e.textContent = txt; };

    setVal("logoRadius",      o.settings.radius);
    setTxt("logoRadiusVal",   o.settings.radius + "px");
    setVal("logoOpacity",     o.settings.opacity);
    setTxt("logoOpacityVal",  Math.round(o.settings.opacity * 100) + "%");
    setVal("logoObjectFit",   o.settings.objectFit);
    setVal("logoBorderWidth", o.settings.borderWidth);
    setVal("logoBorderColor", o.settings.borderColor);
    setVal("logoBorderStyle", o.settings.borderStyle);
    setVal("logoShadow",      o.settings.shadow);
    setVal("logoFilter",      o.settings.filter);

    function refresh() { applyLogoStyles(el, o.settings); }

    const wire = (id, key, parse) => {
      const inp = get(id);
      if (!inp) return;
      inp.oninput = inp.onchange = (e) => {
        o.settings[key] = parse ? parse(e.target.value) : e.target.value;
        refresh();
        // update display labels
        if (id === "logoRadius")  setTxt("logoRadiusVal",  o.settings.radius + "px");
        if (id === "logoOpacity") setTxt("logoOpacityVal", Math.round(o.settings.opacity * 100) + "%");
      };
    };

    wire("logoRadius",      "radius",      parseFloat);
    wire("logoOpacity",     "opacity",     parseFloat);
    wire("logoObjectFit",   "objectFit",   null);
    wire("logoBorderWidth", "borderWidth", parseInt);
    wire("logoBorderColor", "borderColor", null);
    wire("logoBorderStyle", "borderStyle", null);
    wire("logoShadow",      "shadow",      null);
    wire("logoFilter",      "filter",      null);

    refresh();
  }

  /* ═══════════════════════════════════════════════════
     8. ADD OVERLAY BUTTONS
  ═══════════════════════════════════════════════════ */
  document.querySelectorAll(".add-overlay").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type      = btn.dataset.type;
      const id        = "ov_" + Date.now();
      const startTime = parseFloat(video.currentTime.toFixed(1));

      const isLogo = type === "logo";
      const overlay = {
        id,
        overlay_type: isLogo ? "logo" : "text",
        variable: isLogo ? null : type,   // branch_name / branch_address / branch_contact
        settings: {
          text_key: isLogo ? null : type,  // used by backend to map branch data
          color: "#ffffff",
          bg: { enabled: false, color: "#000000", padding: 5, radius: 4 },
          ...(isLogo ? {
            radius: 0, opacity: 1, objectFit: "contain",
            borderWidth: 0, borderColor: "#ffffff", borderStyle: "solid",
            shadow: "none", filter: "none"
          } : {})
        },
        x: 0.1, y: 0.1, width: 0.3, height: 0.1,
        start_time: startTime,
        end_time:   parseFloat((startTime + 5).toFixed(1)),
      };

      overlays.push(overlay);
      createOverlayElement(overlay);
      selectOverlay(id);
    });
  });
});

/* ═══════════════════════════════════════════════════
   9. SAVE
═══════════════════════════════════════════════════ */
$(document).on("click", "#saveTemplateBtn", function () {
  const btn = $(this);
  $.ajax({
    url:      base_url + "Video_editor/save_overlays",
    method:   "POST",
    data:     { template_id: btn.data("template-id"), overlays: JSON.stringify(overlays) },
    dataType: "json",
    success:  (res) => swal(
      res.status === "success" ? "Success" : "Error",
      res.message,
      res.status
    ),
    error: () => swal("Error", "Server connection failed", "error"),
  });
});