<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title" style="margin:0;">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('template-manager') ?>" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">

            <!-- LEFT : CANVAS -->
            <div class="col-md-8">
                <div style="border:1px solid #ddd;background:#fafafa;padding:10px;overflow:auto;">
                    <div id="stage" style="position:relative;display:inline-block;">
                        <img id="baseImg"
                             src="<?= base_url($template['file_path']) ?>"
                             style="display:block;max-width:100%;">
                        <div id="overlayLayer" style="position:absolute;left:0;top:0;"></div>
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label style="margin-right:12px;">
                        <input type="checkbox" id="toggleSafeArea" checked> Safe Area
                    </label>

                    <button id="btnAdd" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Placement
                    </button>

                    <button id="btnSave" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Save All
                    </button>

                    <span id="msg" style="margin-left:10px;"></span>
                </div>

                <p class="text-muted" style="margin-top:8px;">
                    Drag / resize logo placements. Positions are stored safely using ratios.
                </p>
            </div>

            <!-- RIGHT : TOOLS -->
            <div class="col-md-4">
                <div class="panel" style="border:1px solid #eee;">

                    <div id="settingsPanel">
                        <div class="panel-heading"><strong>Placements</strong></div>

                        <div class="panel-body">
                            <div id="list"
                                 style="border:1px solid #eee;border-radius:6px;max-height:200px;overflow:auto;"></div>

                            <div style="margin:10px 0;">
                                <button id="btnUp" class="btn btn-default btn-xs">⬆</button>
                                <button id="btnDown" class="btn btn-default btn-xs">⬇</button>
                            </div>

                            <hr>

                            <strong>Selected Placement</strong>

                            <div class="form-group">
                                <label>Background</label>
                                <select id="bgEnabled" class="form-control">
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Color</label>
                                <input type="text" id="bgColor" class="form-control" value="#ffffff">
                            </div>

                            <div class="form-group">
                                <label>Padding</label>
                                <input type="number" id="bgPadding" class="form-control" min="0" max="80">
                            </div>

                            <div class="form-group">
                                <label>Radius</label>
                                <input type="number" id="bgRadius" class="form-control" min="0" max="80">
                            </div>

                            <button id="btnDuplicate" class="btn btn-default btn-sm">
                                <i class="fas fa-clone"></i> Duplicate
                            </button>

                            <button id="btnDelete" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>

                    <div id="emptyHint" class="text-muted" style="padding:15px;">
                        Select a placement to edit its settings
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
.overlay-box{
    position:absolute;
    border:2px dashed #0d6efd;
    background:rgba(13,110,253,.08);
    box-sizing:border-box;
    cursor:move;
}
.overlay-box.selected{border-style:solid;}
.overlay-box .demo{
    width:100%;height:100%;
    object-fit:contain;
    pointer-events:none;
}
.safe-area{
    position:absolute;
    border:2px dashed rgba(255,0,0,.4);
    pointer-events:none;
}
.list-item{
    padding:8px 10px;
    border-bottom:1px solid #eee;
    cursor:pointer;
}
.list-item.active{background:#eef5ff;}
</style>

<script>
(function(){

const saveUrl = "<?= base_url('Template_manager/saveOverlays/'.$template['id']) ?>";
const demoLogo = "<?= base_url('assets/images/logo.jpg') ?>";

const overlayLayer = document.getElementById('overlayLayer');
const baseImg = document.getElementById('baseImg');
const listEl = document.getElementById('list');
const msg = document.getElementById('msg');

const bgEnabled = document.getElementById('bgEnabled');
const bgColor = document.getElementById('bgColor');
const bgPadding = document.getElementById('bgPadding');
const bgRadius = document.getElementById('bgRadius');

const btnUp = document.getElementById('btnUp');
const btnDown = document.getElementById('btnDown');
const btnDuplicate = document.getElementById('btnDuplicate');
const btnDelete = document.getElementById('btnDelete');
const btnAdd = document.getElementById('btnAdd');
const toggleSafeArea = document.getElementById('toggleSafeArea');

const settingsPanel = document.getElementById('settingsPanel');
const emptyHint = document.getElementById('emptyHint');

let overlays = <?= json_encode($overlays, JSON_UNESCAPED_UNICODE) ?> || [];
let selectedIndex = 0;
let safeAreaEl = null;

/* ---------- utils ---------- */
const clamp = (n,min,max)=>Math.max(min,Math.min(n,max));
const pxToRatio = (v,t)=>t? v/t:0;
const ratioToPx = (v,t)=>v<=1? v*t:v;

function defaultOverlay(){
    return {
        x:overlayLayer.clientWidth*0.05,
        y:overlayLayer.clientHeight*0.05,
        width:overlayLayer.clientWidth*0.2,
        height:overlayLayer.clientWidth*0.2,
        settings:{bg:{enabled:true,color:'#fff',padding:12,radius:16}}
    };
}

/* ---------- safe area ---------- */
function renderSafeArea(){
    if(!toggleSafeArea.checked){
        if(safeAreaEl) safeAreaEl.remove();
        safeAreaEl=null; return;
    }
    if(!safeAreaEl){
        safeAreaEl=document.createElement('div');
        safeAreaEl.className='safe-area';
        overlayLayer.appendChild(safeAreaEl);
    }
    const m=.05;
    safeAreaEl.style.left=(overlayLayer.clientWidth*m)+'px';
    safeAreaEl.style.top=(overlayLayer.clientHeight*m)+'px';
    safeAreaEl.style.width=(overlayLayer.clientWidth*(1-m*2))+'px';
    safeAreaEl.style.height=(overlayLayer.clientHeight*(1-m*2))+'px';
}

/* ---------- init ---------- */
function initOverlays(){
    overlays=overlays.map(o=>{
        let s={};
        try{s=o.settings?JSON.parse(o.settings):{}}catch(e){}
        return{
            x:ratioToPx(o.x||.05,overlayLayer.clientWidth),
            y:ratioToPx(o.y||.05,overlayLayer.clientHeight),
            width:ratioToPx(o.width||.2,overlayLayer.clientWidth),
            height:ratioToPx(o.height||.2,overlayLayer.clientHeight),
            settings:Object.assign({bg:{enabled:true,color:'#fff',padding:12,radius:16}},s)
        };
    });
    if(!overlays.length) overlays.push(defaultOverlay());
}

/* ---------- tools ---------- */
function updateToolVisibility(){
    const has = overlays[selectedIndex];
    settingsPanel.style.display = has?'block':'none';
    emptyHint.style.display = has?'none':'block';
}

function loadSettings(){
    const bg=overlays[selectedIndex].settings.bg;
    bgEnabled.value=bg.enabled?'1':'0';
    bgColor.value=bg.color;
    bgPadding.value=bg.padding;
    bgRadius.value=bg.radius;
}

function applySettings(){
    const bg=overlays[selectedIndex].settings.bg;
    bg.enabled=bgEnabled.value==='1';
    bg.color=bgColor.value;
    bg.padding=+bgPadding.value;
    bg.radius=+bgRadius.value;
}

/* ---------- render ---------- */
function render(){
    overlayLayer.querySelectorAll('.overlay-box').forEach(el=>{
        try{interact(el).unset();}catch(e){}
    });
    overlayLayer.innerHTML='';
    listEl.innerHTML='';
    renderSafeArea();

    overlays.forEach((ov,i)=>{
        const li=document.createElement('div');
        li.className='list-item'+(i===selectedIndex?' active':'');
        li.textContent='Layer '+(i+1);
        li.onclick=()=>{selectedIndex=i;render();};
        listEl.appendChild(li);

        const box=document.createElement('div');
        box.className='overlay-box'+(i===selectedIndex?' selected':'');
        Object.assign(box.style,{
            left:ov.x+'px',top:ov.y+'px',
            width:ov.width+'px',height:ov.height+'px',
            background:ov.settings.bg.enabled?ov.settings.bg.color:'rgba(13,110,253,.08)',
            borderRadius:ov.settings.bg.radius+'px'
        });

        const img=document.createElement('img');
        img.src=demoLogo; img.className='demo';
        box.appendChild(img);
        overlayLayer.appendChild(box);

        interact(box).draggable({
            listeners:{move:e=>{
                ov.x=clamp(ov.x+e.dx,0,overlayLayer.clientWidth-ov.width);
                ov.y=clamp(ov.y+e.dy,0,overlayLayer.clientHeight-ov.height);
                box.style.left=ov.x+'px';
                box.style.top=ov.y+'px';
            }}
        }).resizable({
            edges:{right:true,bottom:true},
            listeners:{move:e=>{
                ov.width=clamp(e.rect.width,40,overlayLayer.clientWidth-ov.x);
                ov.height=clamp(e.rect.height,40,overlayLayer.clientHeight-ov.y);
                box.style.width=ov.width+'px';
                box.style.height=ov.height+'px';
            }}
        });
    });

    loadSettings();
    updateToolVisibility();
}

/* ---------- buttons ---------- */
btnAdd.onclick=()=>{overlays.push(defaultOverlay());selectedIndex=overlays.length-1;render();};
btnDuplicate.onclick=()=>{const c=JSON.parse(JSON.stringify(overlays[selectedIndex]));c.x+=10;c.y+=10;overlays.push(c);selectedIndex=overlays.length-1;render();};
btnDelete.onclick=()=>{if(overlays.length<=1)return alert('At least one placement required');overlays.splice(selectedIndex,1);selectedIndex=Math.max(0,selectedIndex-1);render();};
btnUp.onclick=()=>{if(selectedIndex<=0)return;[overlays[selectedIndex],overlays[selectedIndex-1]]=[overlays[selectedIndex-1],overlays[selectedIndex]];selectedIndex--;render();};
btnDown.onclick=()=>{if(selectedIndex>=overlays.length-1)return;[overlays[selectedIndex],overlays[selectedIndex+1]]=[overlays[selectedIndex+1],overlays[selectedIndex]];selectedIndex++;render();};
[bgEnabled,bgColor,bgPadding,bgRadius].forEach(el=>el.oninput=()=>{applySettings();render();});

document.getElementById('btnSave').onclick=()=>{
    const w=overlayLayer.clientWidth,h=overlayLayer.clientHeight;
    const payload=overlays.map(o=>({x:pxToRatio(o.x,w),y:pxToRatio(o.y,h),width:pxToRatio(o.width,w),height:pxToRatio(o.height,h),settings:o.settings}));
    const fd=new FormData();
    fd.append('overlays',JSON.stringify(payload));
    fd.append("<?= $this->security->get_csrf_token_name(); ?>","<?= $this->security->get_csrf_hash(); ?>");
    fetch(saveUrl,{method:'POST',body:fd})
        .then(r=>r.json())
        .then(j=>msg.innerHTML=j.status==='success'?'✅ Saved':'❌ '+j.message);
};

/* ---------- boot ---------- */
baseImg.onload=()=>{
    overlayLayer.style.width=baseImg.clientWidth+'px';
    overlayLayer.style.height=baseImg.clientHeight+'px';
    initOverlays(); render();
};
if(baseImg.complete) baseImg.onload();

})();
</script>
