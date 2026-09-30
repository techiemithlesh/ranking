<?php
$imageCount = 0;
$videoCount = 0;
foreach ($templates as $tpl) {
    $tpl['type'] === 'video' ? $videoCount++ : $imageCount++;
}
$noLogoCount = count(array_filter($branches, fn($b) => !$b['has_logo']));
?>
<div class="dd-page">

    <section class="panel">
        <div class="panel-body dd-intro">
            <div>
                <h4 class="dd-title"><i class="fas fa-download"></i> <?= translate('download_design') ?></h4>
                <p class="text-muted">
                    Pick branches and templates, then click <strong>Download</strong>. Designs are generated in the background with each branch's logo, name, address and contact —
                    you can close this page and come back. Finished downloads stay available for 3 days.
                </p>
            </div>
        </div>
    </section>

    <!-- YOUR DOWNLOADS -->
    <section class="panel" id="jobsPanel" style="display:none;">
        <header class="panel-heading dd-step-head">
            <i class="fas fa-history"></i>
            <h4 class="panel-title">Your downloads</h4>
        </header>
        <div class="panel-body">
            <div class="alert alert-warning dd-worker-alert" id="workerAlert" style="display:none;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>The background worker isn't running</strong>, so queued downloads won't start.
                Ask your server admin to schedule this command every minute:
                <code>php <?= html_escape(str_replace('\\', '/', FCPATH)) ?>index.php design_worker run</code>
            </div>
            <div id="jobsList"></div>
        </div>
    </section>

    <?php if (empty($templates)): ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-circle"></i> <?= translate('no_templates_found') ?>.
            Only active templates with logo/text placements saved in the editor appear here.
        </div>
    <?php else: ?>

    <div class="row">
        <!-- STEP 1: BRANCHES -->
        <div class="col-md-4">
            <section class="panel dd-panel">
                <header class="panel-heading dd-step-head">
                    <span class="dd-step">1</span>
                    <h4 class="panel-title">Branches</h4>
                    <span class="dd-chip" id="branchSelectedChip">0 / <?= count($branches) ?></span>
                </header>
                <div class="panel-body dd-branch-body">
                    <div class="dd-search">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="branchSearch" placeholder="Search <?= count($branches) ?> branches..." autocomplete="off">
                    </div>
                    <div class="dd-toolbar">
                        <a href="javascript:void(0);" id="branchSelectShown">Select shown</a>
                        <a href="javascript:void(0);" id="branchClear">Clear</a>
                        <label class="dd-toggle">
                            <input type="checkbox" id="branchOnlySelected"> Selected only
                        </label>
                    </div>
                    <?php if ($noLogoCount): ?>
                        <div class="dd-note">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= $noLogoCount ?> branch<?= $noLogoCount > 1 ? 'es have' : ' has' ?> no logo uploaded; the default logo will be used.
                        </div>
                    <?php endif; ?>
                    <div class="dd-branch-list" id="branchList">
                        <?php foreach ($branches as $b): ?>
                            <label class="dd-branch" data-id="<?= (int)$b['id'] ?>" data-name="<?= html_escape(mb_strtolower($b['name'])) ?>">
                                <input type="checkbox" class="branch-check" value="<?= (int)$b['id'] ?>">
                                <span class="dd-branch-name"><?= html_escape($b['name']) ?></span>
                                <?php if (!$b['has_logo']): ?>
                                    <span class="dd-tag dd-tag-warn" title="No logo uploaded">no logo</span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        <div class="dd-empty" id="branchEmpty" style="display:none;">No branches match your search.</div>
                    </div>
                </div>
            </section>
        </div>

        <!-- STEP 2: TEMPLATES -->
        <div class="col-md-8">
            <section class="panel dd-panel">
                <header class="panel-heading dd-step-head">
                    <span class="dd-step">2</span>
                    <h4 class="panel-title">Templates</h4>
                    <span class="dd-chip" id="templateSelectedChip">0 / <?= count($templates) ?></span>
                </header>
                <div class="panel-body">
                    <div class="dd-template-bar">
                        <div class="btn-group btn-group-sm dd-tabs" role="group">
                            <button type="button" class="btn btn-default active" data-filter="">All (<?= count($templates) ?>)</button>
                            <?php if ($imageCount): ?>
                                <button type="button" class="btn btn-default" data-filter="image"><i class="fas fa-image"></i> Pamphlet (<?= $imageCount ?>)</button>
                            <?php endif; ?>
                            <?php if ($videoCount): ?>
                                <button type="button" class="btn btn-default" data-filter="video"><i class="fas fa-video"></i> Video (<?= $videoCount ?>)</button>
                            <?php endif; ?>
                        </div>
                        <label class="dd-toggle">
                            <input type="checkbox" id="templateSelectShown"> Select all shown
                        </label>
                    </div>

                    <div class="dd-template-grid" id="templateGrid">
                        <?php foreach ($templates as $tpl): ?>
                            <?php $singleUrl = $tpl['type'] === 'video'
                                ? base_url('Video_editor/download/' . $tpl['id'])
                                : base_url('Template_manager/download/' . $tpl['id']); ?>
                            <label class="dd-template" data-id="<?= (int)$tpl['id'] ?>" data-type="<?= html_escape($tpl['type']) ?>"
                                data-url="<?= $singleUrl ?>">
                                <input type="checkbox" class="template-check" value="<?= (int)$tpl['id'] ?>">
                                <span class="dd-thumb">
                                    <?php if ($tpl['type'] === 'video'): ?>
                                        <video preload="metadata" muted>
                                            <source src="<?= base_url($tpl['file_path']) ?>#t=0.1" type="video/mp4">
                                        </video>
                                        <i class="fas fa-play-circle dd-play"></i>
                                    <?php else: ?>
                                        <img src="<?= base_url($tpl['file_path']) ?>" loading="lazy" alt="<?= html_escape($tpl['title']) ?>">
                                    <?php endif; ?>
                                    <span class="dd-type dd-type-<?= $tpl['type'] ?>">
                                        <i class="fas fa-<?= $tpl['type'] === 'video' ? 'video' : 'image' ?>"></i> <?= $tpl['type'] === 'video' ? 'Video' : 'Image' ?>
                                    </span>
                                    <span class="dd-tick"><i class="fas fa-check"></i></span>
                                </span>
                                <span class="dd-template-title"><?= html_escape($tpl['title']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- SUMMARY / ACTION BAR -->
    <div class="dd-actionbar">
        <div class="dd-summary">
            <strong id="sumText">Select branches and templates</strong>
            <span class="text-muted" id="sumHint"></span>
        </div>
        <button type="button" class="btn btn-primary" id="startDownload" disabled>
            <i class="fas fa-download"></i> Generate Template
        </button>
    </div>

    <?php endif; ?>
</div>

<!-- SINGLE DESIGN OVERLAY (1 branch × 1 template downloads immediately) -->
<div id="renderOverlay">
    <div class="render-box">
        <div class="spinner"></div>
        <h3 id="renderTitle">Preparing template...</h3>
        <p class="small text-muted">Personalising with the selected branch details</p>
        <div class="progressFake"><div class="bar"></div></div>
    </div>
</div>

<style>
    .dd-intro { padding: 16px 20px; }
    .dd-title { margin: 0 0 4px; font-weight: 600; }
    .dd-intro p { margin: 0; }

    .dd-panel { height: calc(100% - 20px); }
    .dd-step-head { display: flex; align-items: center; gap: 10px; }
    .dd-step-head .panel-title { flex: 1; margin: 0; }
    .dd-step {
        width: 24px; height: 24px; border-radius: 50%;
        background: #4a6cf7; color: #fff; font-size: 12px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .dd-chip { background: rgba(74, 108, 247, .12); color: #4a6cf7; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }

    .dd-branch-body { padding: 12px; }
    .dd-search { position: relative; }
    .dd-search i { position: absolute; left: 11px; top: 11px; color: #999; }
    .dd-search input { padding-left: 32px; }
    .dd-toolbar { display: flex; align-items: center; gap: 14px; margin: 10px 2px; font-size: 13px; }
    .dd-toolbar .dd-toggle { margin-left: auto; }
    .dd-toggle { font-weight: normal; margin: 0; cursor: pointer; font-size: 13px; }
    .dd-note { font-size: 12px; color: #b7791f; background: #fffaf0; border: 1px solid #fbd38d; border-radius: 4px; padding: 6px 8px; margin-bottom: 8px; }

    .dd-branch-list { max-height: 460px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; }
    .dd-branch { display: flex; align-items: center; gap: 10px; padding: 8px 10px; margin: 0; font-weight: normal; cursor: pointer; border-bottom: 1px solid #f1f1f1; }
    .dd-branch:last-of-type { border-bottom: 0; }
    .dd-branch:hover { background: #f7f8fc; }
    .dd-branch.is-checked { background: rgba(74, 108, 247, .07); }
    .dd-branch input { margin: 0; }
    .dd-branch-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dd-tag { font-size: 10px; padding: 1px 6px; border-radius: 8px; text-transform: uppercase; }
    .dd-tag-warn { background: #fff4e5; color: #b7791f; }
    .dd-empty { padding: 20px; text-align: center; color: #999; }

    .dd-template-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
    .dd-template-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; max-height: 560px; overflow-y: auto; padding: 2px; }
    .dd-template { margin: 0; font-weight: normal; cursor: pointer; display: block; }
    .dd-template input { position: absolute; opacity: 0; pointer-events: none; }
    .dd-thumb { position: relative; display: flex; align-items: center; justify-content: center; height: 150px; background: #111; border-radius: 8px; overflow: hidden; border: 2px solid transparent; transition: border-color .15s, box-shadow .15s; }
    .dd-thumb img, .dd-thumb video { max-width: 100%; max-height: 100%; object-fit: contain; }
    .dd-template:hover .dd-thumb { border-color: #c3cdfb; }
    .dd-template.is-checked .dd-thumb { border-color: #4a6cf7; box-shadow: 0 0 0 3px rgba(74, 108, 247, .2); }
    .dd-play { position: absolute; color: rgba(255, 255, 255, .75); font-size: 32px; pointer-events: none; }
    .dd-type { position: absolute; top: 8px; left: 8px; font-size: 10px; padding: 3px 7px; border-radius: 4px; color: #fff; text-transform: uppercase; }
    .dd-type-image { background: #3182ce; }
    .dd-type-video { background: #e53e3e; }
    .dd-tick { position: absolute; top: 8px; right: 8px; width: 22px; height: 22px; border-radius: 50%; background: rgba(255, 255, 255, .85); color: transparent; font-size: 11px; display: flex; align-items: center; justify-content: center; border: 1px solid #ccc; }
    .dd-template.is-checked .dd-tick { background: #4a6cf7; color: #fff; border-color: #4a6cf7; }
    .dd-template-title { display: block; margin-top: 6px; font-size: 13px; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .dd-actionbar { position: sticky; bottom: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; box-shadow: 0 -4px 16px rgba(0, 0, 0, .06); }
    .dd-summary { display: flex; flex-direction: column; }
    .dd-summary .text-muted, .dd-summary .text-warning { font-size: 12px; }

    /* your downloads */
    .dd-worker-alert code { display: inline-block; margin-top: 4px; word-break: break-all; }
    .dd-job { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; }
    .dd-job:last-child { margin-bottom: 0; }
    .dd-job-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .dd-job-title { flex: 1; min-width: 200px; }
    .dd-job-title strong { display: block; }
    .dd-job-title small { color: #888; }
    .dd-badge { font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 10px; text-transform: uppercase; letter-spacing: .3px; }
    .dd-badge-pending { background: #edf2f7; color: #4a5568; }
    .dd-badge-processing, .dd-badge-packaging { background: #ebf4ff; color: #3182ce; }
    .dd-badge-done { background: #e6fffa; color: #2c7a7b; }
    .dd-badge-failed { background: #fff5f5; color: #c53030; }
    .dd-badge-partial { background: #fffaf0; color: #c05621; }
    .dd-badge-expired, .dd-badge-cancelled { background: #f7f7f7; color: #999; }
    .dd-job-bar { height: 8px; background: #edf2f7; border-radius: 6px; overflow: hidden; margin: 10px 0 6px; }
    .dd-job-bar span { display: block; height: 100%; background: linear-gradient(90deg, #00b4d8, #4a6cf7); transition: width .4s ease; }
    .dd-job-bar.is-live span { background-size: 40px 40px; background-image: linear-gradient(45deg, rgba(255,255,255,.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.2) 50%, rgba(255,255,255,.2) 75%, transparent 75%, transparent), linear-gradient(90deg, #00b4d8, #4a6cf7); animation: dd-stripes 1s linear infinite; }
    @keyframes dd-stripes { from { background-position: 40px 0, 0 0 } to { background-position: 0 0, 0 0 } }
    .dd-job-stats { display: flex; gap: 14px; flex-wrap: wrap; font-size: 12px; color: #666; }
    .dd-job-stats .is-bad { color: #c53030; }
    .dd-job-actions { display: flex; gap: 6px; }
    .dd-job-detail { margin-top: 10px; border-top: 1px solid #f1f1f1; padding-top: 8px; }
    .dd-job-detail-head { display: flex; justify-content: space-between; font-size: 12px; color: #888; margin-bottom: 4px; }
    .dd-job-detail-head label { font-weight: normal; margin: 0; cursor: pointer; }
    .dd-branch-progress { list-style: none; margin: 0; padding: 0; max-height: 240px; overflow-y: auto; }
    .dd-branch-progress li { display: flex; align-items: center; gap: 10px; padding: 5px 4px; font-size: 13px; border-bottom: 1px solid #f7f7f7; }
    .dd-branch-progress .b-icon { width: 16px; text-align: center; }
    .dd-branch-progress .b-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dd-branch-progress .b-count { color: #999; font-size: 12px; }
    .dd-branch-progress li.pending { color: #999; }
    .dd-branch-progress li.active .b-icon { color: #3182ce; }
    .dd-branch-progress li.done .b-icon { color: #38a169; }
    .dd-branch-progress li.partial .b-icon, .dd-branch-progress li.skipped .b-icon { color: #dd6b20; }
    .dd-branch-progress.problems-only li:not(.partial):not(.skipped) { display: none; }

    /* single design overlay */
    #renderOverlay { position: fixed; inset: 0; z-index: 999999; padding: 16px; background: rgba(0, 0, 0, .55); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; }
    .render-box { width: 360px; max-width: 100%; background: #111; color: #fff; padding: 30px; border-radius: 14px; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, .6); }
    .render-box .spinner { width: 60px; height: 60px; margin: auto; border-radius: 50%; border: 4px solid rgba(255, 255, 255, .2); border-top: 4px solid #00d4ff; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg) } }
    .progressFake { margin-top: 20px; height: 6px; background: rgba(255, 255, 255, .1); border-radius: 6px; overflow: hidden; }
    .progressFake .bar { height: 100%; width: 0%; background: linear-gradient(90deg, #00d4ff, #6a5cff); animation: fakeProgress 14s ease-in-out forwards; }
    @keyframes fakeProgress { 0% { width: 5% } 25% { width: 30% } 50% { width: 55% } 75% { width: 80% } 100% { width: 95% } }

    @media (max-width: 767px) {
        .dd-branch-list { max-height: 300px; }
        .dd-job-actions { width: 100%; }
    }
</style>

<script type="text/javascript">
(function() {
    const URLS = {
        create:   "<?= base_url('Template_manager/design_job_create') ?>",
        jobs:     "<?= base_url('Template_manager/design_jobs') ?>",
        branches: "<?= base_url('Template_manager/design_job_branches') ?>/",
        cancel:   "<?= base_url('Template_manager/design_job_cancel') ?>",
        retry:    "<?= base_url('Template_manager/design_job_retry') ?>",
        remove:   "<?= base_url('Template_manager/design_job_delete') ?>"
    };
    const CSRF = { name: "<?= $this->security->get_csrf_token_name() ?>", hash: "<?= $this->security->get_csrf_hash() ?>" };
    const ACTIVE = ['pending', 'processing', 'packaging'];
    const renderMessages = ["Preparing template...", "Applying branch branding...", "Rendering design...", "Optimising quality...", "Finalising export..."];

    const $branchRows = $('#branchList .dd-branch');
    const $templates = $('#templateGrid .dd-template');

    /* ── helpers ───────────────────────────────────────────── */
    const escapeHtml = s => $('<div>').text(s == null ? '' : s).html();
    const plural = (n, word, many) => n + ' ' + (n === 1 ? word : (many || word + 's'));

    function fmtTime(sec) {
        sec = Math.max(0, Math.round(sec));
        const h = Math.floor(sec / 3600), m = Math.floor((sec % 3600) / 60), s = sec % 60;
        if (h) return h + 'h ' + m + 'm';
        if (m) return m + 'm ' + String(s).padStart(2, '0') + 's';
        return s + 's';
    }

    function fmtBytes(b) {
        if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        return Math.max(1, Math.round(b / 1024)) + ' KB';
    }

    async function getJson(url) {
        try {
            const r = await fetch(url, { credentials: 'same-origin' });
            return await r.json();
        } catch (e) {
            return { status: 'error', message: 'Network or server error' };
        }
    }

    async function postJson(url, data) {
        const body = new URLSearchParams();
        body.append(CSRF.name, CSRF.hash);
        Object.keys(data).forEach(k => {
            [].concat(data[k]).forEach(v => body.append(Array.isArray(data[k]) ? k + '[]' : k, v));
        });
        try {
            const r = await fetch(url, { method: 'POST', body: body, credentials: 'same-origin' });
            return await r.json();
        } catch (e) {
            return { status: 'error', message: 'Network or server error' };
        }
    }

    /* ── selection ─────────────────────────────────────────── */
    const selectedBranchIds = () => $branchRows.find('.branch-check:checked').map(function() { return this.value; }).get();
    const selectedTemplates = () => $templates.filter('.is-checked').map(function() {
        return { id: this.dataset.id, type: this.dataset.type, url: this.dataset.url };
    }).get();

    function applyBranchFilter() {
        const q = ($('#branchSearch').val() || '').trim().toLowerCase();
        const onlySelected = $('#branchOnlySelected').is(':checked');
        let shown = 0;
        $branchRows.each(function() {
            const visible = (!q || this.dataset.name.indexOf(q) !== -1) && (!onlySelected || $(this).hasClass('is-checked'));
            this.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });
        $('#branchEmpty').toggle(shown === 0);
    }

    function refreshSummary() {
        const branches = selectedBranchIds().length;
        const tpls = selectedTemplates();
        const videos = tpls.filter(t => t.type === 'video').length;
        const total = branches * tpls.length;

        $('#branchSelectedChip').text(branches + ' / ' + $branchRows.length);
        $('#templateSelectedChip').text(tpls.length + ' / ' + $templates.length);

        const $shown = $templates.filter(':visible');
        $('#templateSelectShown').prop('checked', $shown.length > 0 && $shown.length === $shown.filter('.is-checked').length);

        if (!total) {
            $('#sumText').text(!branches ? 'Select at least one branch' : 'Select at least one template');
            $('#sumHint').attr('class', 'text-muted').text('');
            $('#startDownload').prop('disabled', true);
            return;
        }

        $('#sumText').text(plural(branches, 'branch', 'branches') + ' × ' + plural(tpls.length, 'template') + ' = ' + plural(total, 'design'));
        const videoRenders = branches * videos;
        if (total === 1) {
            $('#sumHint').attr('class', 'text-muted').text('Downloads right away as a single file');
        } else if (videoRenders > 20) {
            $('#sumHint').attr('class', 'text-warning').text(videoRenders + ' videos to render — this runs in the background and can take a while.');
        } else {
            $('#sumHint').attr('class', 'text-muted').text('Generated in the background as a ZIP with a folder per branch');
        }
        $('#startDownload').prop('disabled', false);
    }

    let searchTimer;
    $('#branchSearch').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyBranchFilter, 120);
    });
    $('#branchOnlySelected').on('change', applyBranchFilter);

    $('#branchList').on('change', '.branch-check', function() {
        $(this).closest('.dd-branch').toggleClass('is-checked', this.checked);
        refreshSummary();
    });

    $('#branchSelectShown').on('click', function() {
        $branchRows.filter(function() { return this.style.display !== 'none'; })
            .addClass('is-checked').find('.branch-check').prop('checked', true);
        refreshSummary();
    });

    $('#branchClear').on('click', function() {
        $branchRows.removeClass('is-checked').find('.branch-check').prop('checked', false);
        applyBranchFilter();
        refreshSummary();
    });

    $('#templateGrid').on('change', '.template-check', function() {
        $(this).closest('.dd-template').toggleClass('is-checked', this.checked);
        refreshSummary();
    });

    $('.dd-tabs .btn').on('click', function() {
        $('.dd-tabs .btn').removeClass('active');
        $(this).addClass('active');
        const filter = this.dataset.filter;
        $templates.each(function() { $(this).toggle(!filter || this.dataset.type === filter); });
        refreshSummary();
    });

    $('#templateSelectShown').on('change', function() {
        $templates.filter(':visible').toggleClass('is-checked', this.checked).find('.template-check').prop('checked', this.checked);
        refreshSummary();
    });

    $('#startDownload').on('click', async function() {
        const branchIds = selectedBranchIds();
        const tpls = selectedTemplates();
        if (!branchIds.length || !tpls.length) return;

        if (branchIds.length === 1 && tpls.length === 1) {
            startSingle(tpls[0].url + '?branch_id=' + encodeURIComponent(branchIds[0]));
            return;
        }

        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Queuing...');
        const res = await postJson(URLS.create, { branch_ids: branchIds, template_ids: tpls.map(t => t.id) });
        $btn.html('<i class="fas fa-download"></i> Download');
        refreshSummary();

        if (res.status !== 'success') {
            alert(res.message || 'Could not queue the download.');
            return;
        }
        openDetails.add(res.job_id);
        await loadJobs();
        $('html, body').animate({ scrollTop: $('#jobsPanel').offset().top - 70 }, 300);
    });

    /* ── single design: immediate download ─────────────────── */
    function startSingle(url) {
        const overlay = document.getElementById('renderOverlay');
        const title = document.getElementById('renderTitle');
        const bar = overlay.querySelector('.progressFake .bar');
        bar.style.animation = 'none';
        void bar.offsetWidth;
        bar.style.animation = '';
        overlay.style.display = 'flex';

        let i = 0;
        const msgLoop = setInterval(() => { title.innerText = renderMessages[i++ % renderMessages.length]; }, 2200);

        fetch(url, { credentials: 'same-origin' })
            .then(response => {
                const type = response.headers.get('Content-Type') || '';
                if (!response.ok || type.includes('text/html')) throw new Error('Server error');
                return response.blob().then(blob => ({ blob, response }));
            })
            .then(({ blob, response }) => {
                let filename = 'template';
                const cd = response.headers.get('Content-Disposition');
                if (cd && cd.includes('filename=')) filename = cd.split('filename=')[1].replace(/"/g, '');
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(a.href), 1000);
            })
            .catch(error => {
                console.error('Failed to download file.', error);
                alert('Failed to generate file. Try again.');
            })
            .finally(() => {
                clearInterval(msgLoop);
                overlay.style.display = 'none';
            });
    }

    /* ── your downloads (live from the server) ─────────────── */
    const openDetails = new Set();     // job ids with the branch list expanded
    const problemsOnly = new Set();
    let pollTimer = null;

    function statusLabel(job) {
        if (job.status === 'done' && job.cancel_requested) return ['done', 'Cancelled · partial ZIP'];
        if (job.status === 'done' && job.failed) return ['partial', 'Ready · ' + job.failed + ' failed'];
        if (job.cancel_requested && ACTIVE.includes(job.status)) return ['processing', 'Cancelling'];
        return ({
            pending:    ['pending', 'Queued'],
            processing: ['processing', 'Generating'],
            packaging:  ['packaging', 'Building ZIP'],
            done:       ['done', 'Ready'],
            failed:     ['failed', 'Failed'],
            expired:    ['expired', 'Expired']
        })[job.status] || ['pending', job.status];
    }

    function jobHtml(job) {
        const [badge, label] = statusLabel(job);
        const live = ACTIVE.includes(job.status);
        const stats = [
            job.done + ' / ' + job.total + ' done'
        ];
        if (job.failed) stats.push('<span class="is-bad">' + job.failed + ' failed</span>');
        if (job.cancelled) stats.push(job.cancelled + ' cancelled');
        if (job.status === 'processing' && job.eta_seconds != null) stats.push('about ' + fmtTime(job.eta_seconds) + ' left');
        if (job.status === 'pending') stats.push('waiting for the background worker');
        if (job.status === 'done' && job.zip_size != null) stats.push(plural(job.zip_count, 'file') + ' · ' + fmtBytes(job.zip_size));
        if (job.status === 'expired') stats.push('ZIP deleted after 3 days');
        if (job.error) stats.push('<span class="is-bad">' + escapeHtml(job.error) + '</span>');

        let actions = '';
        if (job.download_url) {
            actions += '<a class="btn btn-success btn-sm" href="' + job.download_url + '"><i class="fas fa-file-archive"></i> Download ZIP</a>';
        }
        if (job.retry === 'all') {
            actions += '<button type="button" class="btn btn-warning btn-sm js-retry" data-id="' + job.id + '" data-mode="all">' +
                '<i class="fas fa-redo"></i> Retry</button>';
        } else if (job.retry === 'failed') {
            actions += '<button type="button" class="btn btn-default btn-sm js-retry" data-id="' + job.id + '" data-mode="failed">' +
                '<i class="fas fa-redo"></i> Retry failed (' + (job.failed + job.cancelled) + ')</button>';
        }
        if (live && !job.cancel_requested && job.status !== 'packaging') {
            actions += '<button type="button" class="btn btn-default btn-sm js-cancel" data-id="' + job.id + '">Cancel</button>';
        }
        if (job.status !== 'expired') {
            actions += '<button type="button" class="btn btn-default btn-sm js-details" data-id="' + job.id + '">' +
                (openDetails.has(job.id) ? 'Hide branches' : 'Branches') + '</button>';
        }
        if (job.can_delete) {
            actions += '<button type="button" class="btn btn-default btn-sm js-delete" data-id="' + job.id + '" title="Remove from the list">' +
                '<i class="fas fa-trash-alt"></i></button>';
        }

        return '<div class="dd-job" id="job-' + job.id + '">' +
            '<div class="dd-job-head">' +
                '<div class="dd-job-title"><strong>' + plural(job.branch_count, 'branch', 'branches') + ' × ' + plural(job.template_count, 'template') +
                    ' = ' + plural(job.total, 'design') + '</strong><small>#' + job.id + ' · ' + escapeHtml(job.created_at) + '</small></div>' +
                '<span class="dd-badge dd-badge-' + badge + '">' + label + '</span>' +
                '<div class="dd-job-actions">' + actions + '</div>' +
            '</div>' +
            '<div class="dd-job-bar' + (live ? ' is-live' : '') + '"><span style="width:' + job.percent + '%"></span></div>' +
            '<div class="dd-job-stats"><span>' + job.percent + '%</span><span>' + stats.join('</span><span>') + '</span></div>' +
            (openDetails.has(job.id) ? '<div class="dd-job-detail" data-id="' + job.id + '">' +
                '<div class="dd-job-detail-head"><span>Progress by branch</span>' +
                '<label><input type="checkbox" class="js-problems" data-id="' + job.id + '"' + (problemsOnly.has(job.id) ? ' checked' : '') + '> Problems only</label></div>' +
                '<ul class="dd-branch-progress' + (problemsOnly.has(job.id) ? ' problems-only' : '') + '"><li class="pending">Loading...</li></ul>' +
            '</div>' : '') +
        '</div>';
    }

    function branchRowsHtml(branches) {
        return branches.map(b => {
            const finished = b.done + b.failed + b.cancelled;
            let cls = 'pending', icon = 'far fa-clock', count = finished + ' / ' + b.total;
            if (b.active) {
                cls = 'active'; icon = 'fas fa-spinner fa-spin';
            } else if (finished === b.total) {
                if (b.failed) { cls = 'partial'; icon = 'fas fa-exclamation-triangle'; count = b.done + ' / ' + b.total + ' ok'; }
                else if (b.cancelled) { cls = 'skipped'; icon = 'fas fa-ban'; count = b.done + ' / ' + b.total + ' (cancelled)'; }
                else { cls = 'done'; icon = 'fas fa-check'; }
            } else if (finished > 0) {
                cls = 'active'; icon = 'fas fa-circle-notch';
            }
            return '<li class="' + cls + '"><span class="b-icon"><i class="' + icon + '"></i></span>' +
                '<span class="b-name">' + escapeHtml(b.name) + '</span><span class="b-count">' + count + '</span></li>';
        }).join('');
    }

    async function loadDetails(jobId) {
        const res = await getJson(URLS.branches + jobId);
        const $list = $('.dd-job-detail[data-id="' + jobId + '"] .dd-branch-progress');
        if (!$list.length) return;
        const scroll = $list.scrollTop();
        $list.html(res.status === 'success' ? branchRowsHtml(res.branches) : '<li>' + escapeHtml(res.message) + '</li>');
        $list.scrollTop(scroll);
    }

    async function loadJobs() {
        clearTimeout(pollTimer);
        const res = await getJson(URLS.jobs);
        if (res.status !== 'success') {
            pollTimer = setTimeout(loadJobs, 10000);
            return;
        }

        const jobs = res.jobs || [];
        $('#jobsPanel').toggle(jobs.length > 0);
        $('#jobsList').html(jobs.map(jobHtml).join(''));

        const anyActive = jobs.some(j => ACTIVE.includes(j.status));
        $('#workerAlert').toggle(anyActive && !res.worker.alive);

        jobs.filter(j => openDetails.has(j.id)).forEach(j => loadDetails(j.id));

        // poll quickly while something is running, slowly otherwise
        pollTimer = setTimeout(loadJobs, anyActive ? 3000 : 30000);
    }

    $('#jobsList').on('click', '.js-details', function() {
        const id = +this.dataset.id;
        openDetails.has(id) ? openDetails.delete(id) : openDetails.add(id);
        loadJobs();
    });

    $('#jobsList').on('change', '.js-problems', function() {
        const id = +this.dataset.id;
        this.checked ? problemsOnly.add(id) : problemsOnly.delete(id);
        $(this).closest('.dd-job-detail').find('.dd-branch-progress').toggleClass('problems-only', this.checked);
    });

    $('#jobsList').on('click', '.js-cancel', async function() {
        if (!confirm('Stop this download? Designs already generated will still be zipped.')) return;
        $(this).prop('disabled', true);
        const res = await postJson(URLS.cancel, { job_id: this.dataset.id });
        if (res.status !== 'success') alert(res.message || 'Could not cancel.');
        loadJobs();
    });

    $('#jobsList').on('click', '.js-retry', async function() {
        const msg = this.dataset.mode === 'all'
            ? 'Generate this download again from the start?'
            : 'Try the failed designs again? They will be added to the existing ZIP.';
        if (!confirm(msg)) return;
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Retrying...');
        const res = await postJson(URLS.retry, { job_id: this.dataset.id });
        if (res.status !== 'success') alert(res.message || 'Could not retry.');
        loadJobs();
    });

    $('#jobsList').on('click', '.js-delete', async function() {
        if (!confirm('Delete this download from the list? Its ZIP file will be removed from the server.')) return;
        const id = +this.dataset.id;
        $(this).prop('disabled', true);
        const res = await postJson(URLS.remove, { job_id: id });
        if (res.status !== 'success') {
            alert(res.message || 'Could not delete.');
        } else {
            openDetails.delete(id);
            problemsOnly.delete(id);
        }
        loadJobs();
    });

    applyBranchFilter();
    refreshSummary();
    loadJobs();
})();
</script>
