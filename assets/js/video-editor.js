document.addEventListener("DOMContentLoaded", () => {
    const video = document.getElementById("videoPlayer");
    const overlayLayer = document.getElementById("overlay-layer");
    const settingsPanel = document.getElementById("overlay-settings");

    let selectedOverlay = null;
    const overlayElements = {};

    /**
     * Delete an overlay from the stage and the data array
     */
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
                
                // Use loose equality (==) to match string IDs with numeric DB IDs
                const index = overlays.findIndex((o) => o.id == id);
                if (index > -1) overlays.splice(index, 1);
                
                delete overlayElements[id];
                selectedOverlay = null;
                renderSettings();
            }
        });
    }

    /**
     * Create the DOM element for an overlay
     */
    function createOverlayElement(o) {
        const el = document.createElement("div");
        el.className = "overlay-item";
        el.id = "el_" + o.id;
        el.dataset.id = o.id;

        // Ensure settings object exists
        if (!o.settings) {
            o.settings = {
                text_key: o.variable || o.overlay_type,
                font_size: 18,
                color: "#ffffff",
                background: "rgba(0,0,0,0.5)",
                padding: 5,
                border_radius: 4,
                align: "center"
            };
        }

        // 1. CONTENT WRAPPER (Prevents overwriting the delete button)
        const content = document.createElement("div");
        content.className = "overlay-content";
        content.style.width = "100%";
        content.style.height = "100%";
        content.style.display = "flex";
        content.style.alignItems = "center";
        content.style.justifyContent = "center";
        content.style.pointerEvents = "none"; 

        // 2. DELETE BUTTON
        const delBtn = document.createElement("div");
        delBtn.className = "delete-overlay";
        delBtn.innerHTML = '<i class="fas fa-times"></i>';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            deleteOverlay(o.id);
        };

        // 3. RENDER CONTENT BASED ON TYPE
        if (o.overlay_type === "logo") {
            const img = document.createElement("img");
            img.src = DEMO_LOGO;
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "contain";
            content.appendChild(img);
        } else {
            // Map the placeholder using text_key from settings
            const textKey = o.settings.text_key || "text";
            content.innerText = `{{${textKey}}}`;
            el.style.fontSize = (o.settings.font_size || 18) + "px";
            el.style.textAlign = o.settings.align || "center";
        }

        el.appendChild(content);
        el.appendChild(delBtn);

        // 4. APPLY INITIAL POSITION & STYLES
        applyPosition(el, o);
        el.style.color = o.settings.color || "#ffffff";
        el.style.background = o.settings.background || "rgba(0,0,0,0.5)";
        el.style.padding = (o.settings.padding || 5) + "px";
        el.style.borderRadius = (o.settings.border_radius || 4) + "px";

        overlayLayer.appendChild(el);
        overlayElements[o.id] = el;

        // Selection Listener
        el.addEventListener("mousedown", (e) => {
            e.stopPropagation();
            selectOverlay(o.id);
        });

        // Initialize Drag/Resize
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
                        el.style.left = (overlay.x * 100) + "%";
                        el.style.top = (overlay.y * 100) + "%";
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
                        overlay.x = (parseFloat(overlay.x) || 0) + event.deltaRect.left / rect.width;
                        overlay.y = (parseFloat(overlay.y) || 0) + event.deltaRect.top / rect.height;

                        el.style.width = (overlay.width * 100) + "%";
                        el.style.height = (overlay.height * 100) + "%";
                        el.style.left = (overlay.x * 100) + "%";
                        el.style.top = (overlay.y * 100) + "%";
                    },
                },
            });
    }

    function applyPosition(el, o) {
        el.style.left = (parseFloat(o.x) * 100) + "%";
        el.style.top = (parseFloat(o.y) * 100) + "%";
        el.style.width = (parseFloat(o.width) * 100) + "%";
        el.style.height = (parseFloat(o.height) * 100) + "%";
    }

    function selectOverlay(id) {
        selectedOverlay = overlays.find((o) => o.id == id);
        document.querySelectorAll(".overlay-item").forEach((el) => el.classList.remove("selected"));
        overlayElements[id]?.classList.add("selected");
        renderSettings();
        bindTextControls(selectedOverlay, overlayElements[id]);
    }

    function renderSettings() {
        if (!selectedOverlay) {
            settingsPanel.innerHTML = '<p class="text-muted text-center">Select an overlay to edit timing</p>';
            return;
        }

        const isText = selectedOverlay.overlay_type !== "logo";

        settingsPanel.innerHTML = `
            <div class="style-group">
                <h6><i class="fas fa-clock"></i> Timing</h6>
                <label>Start (sec)</label>
                <input type="number" step="0.1" class="form-control mb-2" value="${selectedOverlay.start_time}" id="startTime">
                <label>End (sec)</label>
                <input type="number" step="0.1" class="form-control mb-2" value="${selectedOverlay.end_time}" id="endTime">
            </div>
            ${isText ? `
            <div class="style-group mt-3">
                <h6><i class="fas fa-font"></i> Text Properties</h6>
                <label>Font Size</label>
                <input type="range" min="10" max="100" value="${selectedOverlay.settings.font_size || 18}" class="custom-range" id="fontSizeRange">
            </div>` : ''}
        `;

        // Bind Time events
        document.getElementById("startTime").onchange = (e) => { selectedOverlay.start_time = parseFloat(e.target.value); };
        document.getElementById("endTime").onchange = (e) => { selectedOverlay.end_time = parseFloat(e.target.value); };
        
        // Bind Font Range if it exists
        const fontRange = document.getElementById("fontSizeRange");
        if(fontRange) {
            fontRange.oninput = (e) => {
                selectedOverlay.settings.font_size = e.target.value;
                overlayElements[selectedOverlay.id].style.fontSize = e.target.value + "px";
            };
        }
    }

    function bindTextControls(o, el) {
        const colorInp = document.getElementById("textColor");
        const bgInp = document.getElementById("bgColor");
        const padInp = document.getElementById("padding");
        const radInp = document.getElementById("radius");

        // Sync values to UI
        colorInp.value = o.settings.color || "#ffffff";
        bgInp.value = o.settings.background || "#000000";
        padInp.value = o.settings.padding || 5;
        radInp.value = o.settings.border_radius || 4;

        colorInp.oninput = (e) => { o.settings.color = e.target.value; el.style.color = e.target.value; };
        bgInp.oninput = (e) => { o.settings.background = e.target.value; el.style.background = e.target.value; };
        padInp.oninput = (e) => { o.settings.padding = e.target.value; el.style.padding = e.target.value + "px"; };
        radInp.oninput = (e) => { o.settings.border_radius = e.target.value; el.style.borderRadius = e.target.value + "px"; };
    }

    /* ---------------- INITIALIZATION ---------------- */

    // Load Existing from DB
    if (Array.isArray(overlays)) {
        overlays.forEach((o) => {
            // Fix: ensure the settings object is parsed if coming from DB as string
            if (typeof o.settings === 'string') o.settings = JSON.parse(o.settings);
            createOverlayElement(o);
        });
    }

    // Playback Sync
    video.addEventListener("timeupdate", () => {
        const t = video.currentTime;
        overlays.forEach((o) => {
            const el = overlayElements[o.id];
            if (!el) return;
            if (selectedOverlay && selectedOverlay.id == o.id) {
                el.style.display = "block";
            } else {
                el.style.display = (t >= o.start_time && t <= o.end_time) ? "block" : "none";
            }
        });
    });

    // Add Overlay Logic
    document.querySelectorAll(".add-overlay").forEach((btn) => {
        btn.addEventListener("click", () => {
            const type = btn.dataset.type;
            const id = "ov_" + Date.now();
            let overlay = {
                id: id,
                overlay_type: type === "logo" ? "logo" : "text",
                settings: {
                    text_key: type !== "logo" ? type : null, // branch_name, branch_address etc.
                    font_size: 18,
                    color: "#ffffff",
                    background: "rgba(0,0,0,0.5)",
                    padding: 5,
                    border_radius: 4,
                    align: "center"
                },
                x: 0.1, y: 0.1, width: 0.25, height: 0.1,
                start_time: 0,
                end_time: video.duration || 10
            };
            console.log("Adding overlay:", overlay);
            overlays.push(overlay);
            console.log("Current overlays array:", overlays);
            createOverlayElement(overlay);
            selectOverlay(id);
        });
    });
});

/* ---------------- SAVE DATA ---------------- */
$("#saveTemplateBtn").on("click", function () {
    const btn = $(this);
    const templateId = $(this).data("template-id");

    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    console.log("Saving overlays:", overlays);

    console.log("Final overlays data to be sent:", JSON.stringify(overlays));

    $.ajax({
        url: base_url + "Video_editor/save_overlays",
        method: "POST",
        data: {
            template_id: templateId,
            overlays: JSON.stringify(overlays) // This saves the whole structure including settings
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
            btn.prop("disabled", false).html('<i class="fas fa-save"></i> Save Template');
        }
    });
});