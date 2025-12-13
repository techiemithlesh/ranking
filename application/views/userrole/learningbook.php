<style>
    .book-wrapper {
        position: relative;
        display: inline-block;
    }

    .book-blur {
        filter: blur(8px);
        opacity: 0.6;
    }

    .book-loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 45px;
        height: 45px;
        border: 5px solid #ddd;
        border-top-color: #3498db;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        z-index: 20;
        display: none;
    }

    @keyframes spin {
        to {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }
</style>

<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-cloud-upload-alt"></i> <?= translate('My_learning_book') ?></h4>
    </header>
    <div class="panel-body">

        <div class="container">
            <div class="row my-6">
                <?php foreach ($booklist as $row): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card shadow-sm h-100 d-flex flex-column">
                            <div class="card-header bg-primary text-white text-center">
                                <h5 class="card-title mb-2">
                                    <?php echo $row['title'] ?>
                                </h5>
                            </div>
                            <div class="text-center my-3">
                                <div class="book-wrapper">
                                    <div class="book-loader"></div>

                                    <a href="<?php echo $row['book_url']; ?>" target="_blank">
                                        <img id="book-image-<?php echo $row['id']; ?>"
                                            src="<?php echo base_url($row['book_img']); ?>"
                                            loading="lazy"
                                            class="img-fluid img-thumbnail book-blur"
                                            style="height: 350px; object-fit: cover;">
                                    </a>
                                </div>
                            </div>


                            <div class="card-footer mt-auto">
                                <div class="btn-group d-flex justify-content-end">
                                    <a href="<?php echo $row['book_url'] ?>" target="_blank"
                                        class="btn btn-sm btn-primary text-center" style="margin-top: 12px;"
                                        data-toggle="tooltip" data-original-title="<?= translate('Open') ?>">
                                        <i class="fas fa-external-link-alt"></i> <?= translate('Open') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {

        $.ajax({
            url: '<?= base_url() . '/' . 'userrole/getStudentPhotoAndNameById' ?>',
            type: 'GET',
            dataType: "json",
            success: function(response) {

                if (response.status !== 'success') return;

                const studentData = response.data;

                const studentPhotoImage = new Image();
                studentPhotoImage.crossOrigin = "anonymous";

                let isDefaultPhoto = !studentData.photo ||
                    studentData.photo.trim() === "" || ["default.png", "default.jpg", "default.jpeg", "defualt.png"].includes(studentData.photo.toLowerCase());

                studentPhotoImage.src = isDefaultPhoto ?
                    "<?= base_url('assets/images/default.png') ?>" :
                    `<?= base_url('uploads/images/student/') ?>${studentData.photo}`;

                studentPhotoImage.onload = function() {

                    $('img[id^="book-image-"]').each(function(index, imageElement) {

                        const bookImage = new Image();
                        bookImage.crossOrigin = "anonymous";

                        bookImage.onload = function() {

                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            ctx.imageSmoothingEnabled = true;

                            canvas.width = this.width;
                            canvas.height = this.height;

                            // 1) Draw template
                            ctx.drawImage(bookImage, 0, 0);

                            const w = canvas.width;
                            const h = canvas.height;

                            const imgData = ctx.getImageData(0, 0, w, h);
                            const data = imgData.data;

                            const ALPHA_LIMIT = 80;

                            const circleCandidates = [];
                            const nameCandidates = [];

                            // 2) Collect transparent-ish pixels
                            for (let y = 0; y < h; y += 3) {
                                for (let x = 0; x < w; x += 3) {
                                    const i = (y * w + x) * 4;
                                    if (data[i + 3] <= ALPHA_LIMIT) {
                                        if (y < h * 0.6) circleCandidates.push({
                                            x,
                                            y
                                        });
                                        else nameCandidates.push({
                                            x,
                                            y
                                        });
                                    }
                                }
                            }

                            if (!circleCandidates.length || !nameCandidates.length) {
                                // console.warn('Could not detect circle or name area for', imageElement);
                                return;
                            }

                            function boundingBox(pixels) {
                                let minX = Infinity,
                                    maxX = -Infinity;
                                let minY = Infinity,
                                    maxY = -Infinity;
                                pixels.forEach(p => {
                                    if (p.x < minX) minX = p.x;
                                    if (p.x > maxX) maxX = p.x;
                                    if (p.y < minY) minY = p.y;
                                    if (p.y > maxY) maxY = p.y;
                                });
                                return {
                                    x1: minX,
                                    y1: minY,
                                    x2: maxX,
                                    y2: maxY,
                                    w: maxX - minX,
                                    h: maxY - minY
                                };
                            }

                            // 3) REFINE CIRCLE
                            const circleRowCount = new Array(h).fill(0);
                            const circleColCount = new Array(w).fill(0);

                            circleCandidates.forEach(p => {
                                circleRowCount[p.y]++;
                                circleColCount[p.x]++;
                            });

                            const maxCircleRow = Math.max(...circleRowCount);
                            const maxCircleCol = Math.max(...circleColCount);
                            const rowCircleThresh = maxCircleRow * 0.6;
                            const colCircleThresh = maxCircleCol * 0.6;

                            const circlePixelsRefined = circleCandidates.filter(p =>
                                circleRowCount[p.y] >= rowCircleThresh &&
                                circleColCount[p.x] >= colCircleThresh
                            );

                            const finalCirclePixels = circlePixelsRefined.length ? circlePixelsRefined : circleCandidates;

                            const cb = boundingBox(finalCirclePixels);

                            const circle = {
                                cx: cb.x1 + cb.w / 2,
                                cy: cb.y1 + cb.h / 2,
                                r: Math.min(cb.w, cb.h) / 2
                            };

                            // 4) REFINE NAME RECT
                            const nameRowCount = new Array(h).fill(0);
                            nameCandidates.forEach(p => nameRowCount[p.y]++);

                            const maxNameRow = Math.max(...nameRowCount);
                            const rowNameThresh = maxNameRow * 0.6;

                            const namePixelsRefined = nameCandidates.filter(p =>
                                nameRowCount[p.y] >= rowNameThresh
                            );

                            const finalNamePixels = namePixelsRefined.length ? namePixelsRefined : nameCandidates;

                            const nb = boundingBox(finalNamePixels);

                            const nameRect = {
                                x: nb.x1,
                                y: nb.y1,
                                w: nb.w,
                                h: nb.h
                            };

                            // 5) Draw student photo ONCE (global image)
                            const diameter = circle.r * 2;
                            const sw = studentPhotoImage.width;
                            const sh = studentPhotoImage.height;

                            const scale = Math.max(diameter / sw, diameter / sh);
                            const cropW = diameter / scale;
                            const cropH = diameter / scale;

                            const sx = (sw - cropW) / 2;
                            const sy = (sh - cropH) / 2;

                            ctx.save();
                            ctx.beginPath();
                            ctx.arc(circle.cx, circle.cy, circle.r, 0, Math.PI * 2);
                            ctx.clip();

                            ctx.drawImage(
                                studentPhotoImage,
                                sx, sy, cropW, cropH,
                                circle.cx - circle.r,
                                circle.cy - circle.r,
                                diameter,
                                diameter
                            );
                            ctx.restore();

                            // 6) Draw student name
                            const fullName = `${studentData.first_name} ${studentData.last_name}`.trim();
                            ctx.textAlign = "center";
                            ctx.textBaseline = "middle";
                            ctx.fillStyle = "#000";

                            let fontSize = nameRect.h * 0.6;

                            function setFont(size) {
                                ctx.font = `${Math.floor(size)}px "Comic Sans MS","Baloo Bhai 2",Arial`;
                            }

                            setFont(fontSize);

                            while (ctx.measureText(fullName).width > nameRect.w * 0.9 && fontSize > nameRect.h * 0.3) {
                                fontSize -= 2;
                                setFont(fontSize);
                            }

                            ctx.fillText(
                                fullName,
                                nameRect.x + nameRect.w / 2,
                                nameRect.y + nameRect.h / 2
                            );

                            // Set final image
                            imageElement.src = canvas.toDataURL("image/png");
                            // Remove loader + blur
                            $(imageElement).removeClass("book-blur");
                            $(imageElement).siblings(".book-loader").hide();

                        }; // bookImage.onload

                        bookImage.src = $(imageElement).attr('src');

                    }); // each loop

                }; // studentPhotoImage.onload

            }
        });

    });
</script>