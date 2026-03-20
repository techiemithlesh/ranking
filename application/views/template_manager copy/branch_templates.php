<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-bullhorn"></i> <?= translate('marketing_templates') ?>
        </h4>
    </header>

    <div class="panel-body">
        <?php if (empty($templates)): ?>
            <div class="alert alert-warning text-center">
                <?= translate('no_templates_found') ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($templates as $tpl): ?>
                    <div class="col-md-4 col-sm-6 mb-lg">
                        <div class="panel panel-bordered" style="height: 100%;">
                            <div class="panel-body">

                                <div class="thumbnail-container" style="border:1px solid #ddd; margin-bottom:10px; background: #000; border-radius: 4px; overflow: hidden; position: relative; height: 220px; display: flex; align-items: center; justify-content: center;">

                                    <div style="position: absolute; top: 10px; left: 10px; z-index: 5;">
                                        <?php if ($tpl['type'] === 'video'): ?>
                                            <span class="label label-danger" style="padding: 5px 8px; font-size: 10px; text-transform: uppercase;">
                                                <i class="fas fa-video"></i> Video
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-info" style="padding: 5px 8px; font-size: 10px; text-transform: uppercase;">
                                                <i class="fas fa-image"></i> Image
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($tpl['type'] === 'video'): ?>
                                        <div style="position: absolute; color: rgba(255,255,255,0.7); font-size: 35px; z-index: 2; pointer-events: none;">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($tpl['type'] === 'image'): ?>
                                        <img src="<?= base_url($tpl['file_path']) ?>"
                                            style="max-width:100%; max-height:100%; object-fit:contain;"
                                            alt="<?= html_escape($tpl['title']) ?>">
                                    <?php elseif ($tpl['type'] === 'video'): ?>
                                        <video style="max-width:100%; max-height:100%; object-fit:contain;">
                                            <source src="<?= base_url($tpl['file_path']) ?>#t=0.1" type="video/mp4">
                                        </video>
                                    <?php endif; ?>
                                </div>

                                <h5 class="text-weight-semibold" style="margin:0 0 15px 0; min-height: 2.4em; line-height: 1.2;">
                                    <?= html_escape($tpl['title']) ?>
                                </h5>

                                <div class="text-right">
                                    <?php if ($tpl['type'] == 'image') : ?>
                                        <a href="<?= base_url('Template_manager/preview/' . $tpl['id']) ?>" class="btn btn-info ml-2">
                                            <i class="fas fa-edit"></i> Preview
                                        </a>
                                    <?php elseif ($tpl['type'] == 'video') : ?>
                                        <a href="<?= base_url('Video_editor/preview/' . $tpl['id']) ?>" class="btn btn-info ml-2">
                                            <i class="fas fa-edit"></i> Preview
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($tpl['type'] == 'image') : ?>
                                        <a href="#"
                                            data-url="<?= base_url('Template_manager/download/' . $tpl['id']) ?>"
                                            class="btn btn-success btn-sm startRender">
                                            <i class="fas fa-download"></i> <?= translate('download') ?>
                                        </a>

                                    <?php elseif ($tpl['type'] == 'video'): ?>
                                        <a href="#"
                                            data-url="<?= base_url('Video_editor/download/' . $tpl['id']) ?>"
                                            class="btn btn-success btn-sm startRender">
                                            <i class="fas fa-download"></i> <?= translate('download') ?>
                                        </a>

                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- GENERATING OVERLAY -->
<div id="renderOverlay">
    <div class="render-box">
        <div class="spinner"></div>
        <h3 id="renderTitle">Preparing template...</h3>
        <p class="small text-muted">Personalising with your branch details</p>

        <div class="progressFake">
            <div class="bar"></div>
        </div>
    </div>
</div>

<style>
    #renderOverlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999999;
    }

    .render-box {
        width: 360px;
        background: #111;
        color: #fff;
        padding: 30px;
        border-radius: 14px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .6);
    }

    .spinner {
        width: 60px;
        height: 60px;
        border: 4px solid rgba(255, 255, 255, .2);
        border-top: 4px solid #00d4ff;
        border-radius: 50%;
        margin: auto;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    .progressFake {
        margin-top: 20px;
        height: 6px;
        background: rgba(255, 255, 255, .1);
        border-radius: 6px;
        overflow: hidden;
    }

    .progressFake .bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #00d4ff, #6a5cff);
        animation: fakeProgress 14s ease-in-out forwards;
    }

    @keyframes fakeProgress {
        0% {
            width: 5%
        }

        25% {
            width: 30%
        }

        50% {
            width: 55%
        }

        75% {
            width: 80%
        }

        100% {
            width: 95%
        }
    }
</style>

<script>
    const renderMessages = [
        "Preparing template...",
        "Applying branch branding...",
        "Rendering video...",
        "Optimising quality...",
        "Finalising export..."
    ];

    document.querySelectorAll(".startRender").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            startRender(this.dataset.url);
        });
    });

    function startRender(url) {
        const overlay = document.getElementById("renderOverlay");
        const title = document.getElementById("renderTitle");

        overlay.style.display = "flex";

        let i = 0;
        const msgLoop = setInterval(() => {
            title.innerText = renderMessages[i % renderMessages.length];
            i++;
        }, 2200);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("Server error");
                return response.blob().then(blob => ({
                    blob,
                    response
                }));
            })
            .then(({
                blob,
                response
            }) => {
                console.log("File ready for download.");

                clearInterval(msgLoop);

                const a = document.createElement("a");
                a.href = URL.createObjectURL(blob);

                // read filename from header
                let filename = "template.mp4";
                const cd = response.headers.get("Content-Disposition");
                if (cd && cd.includes("filename="))
                    filename = cd.split("filename=")[1].replace(/"/g, '');

                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();

                overlay.style.display = "none";
            })
            .catch(error => {
                overlay.style.display = "none";
                console.error("Failed to download file.", error);
                alert("Failed to generate file. Try again.");
            });
    }
</script>