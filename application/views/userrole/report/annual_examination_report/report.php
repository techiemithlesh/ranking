<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>
    <link rel="stylesheet" href="<?= base_url('assets/reports/annual_examination_report/report.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    .print-button {
        float: right;
        background-color: #637dc2;
        padding: 7px 15px;
        border-radius: 9px;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .print-button:hover {
        background-color: #4c63a8;
    }

    /* Signature section styles */
    .signatures-section {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-bottom: 20px;
    }

    .signature {
        text-align: center;
        width: 30%;
    }

    .signature img {
        width: 150px;
        margin-bottom: 10px;
    }

    .signature p {
        margin: 0;
        padding: 5px 0;
    }

    /* Print specific styles */
    @media print {
        .print-button {
            display: none !important;
        }

        .report-container {
            width: 100%;
            margin: 5px;
            padding: 5px;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }


        .signatures-section {
            page-break-inside: avoid;
            margin-top: 20px;
            padding-bottom: 20px;
        }

        .signature {
            padding: 0 10px;
            break-inside: avoid;
        }

        .signature p {
            margin-top: 5px;
            padding: 0;
            font-size: 14px;
            line-height: 1.2;
        }

        /* Ensure last sections don't break */
        .comments-section,
        .graph-section {
            page-break-inside: avoid;
        }
    }
</style>


<body>
    <div class="report-container">
        <div class="print-button" onclick="window.print();">
            <i class="icons icon-feed"></i>Print
        </div>
        <div id="printDiv">
            <!-- Header Section -->
            <header class="report-header">
                <div class="school-logo">
                <img src="<?= !empty($branchData['logo']) ? base_url($branchData['logo']) : base_url('assets/reports/school_logo.jpg') ?>" />
                </div>

                <div class="school-info">
                    <h1><?= $branchData["name"]; ?></h1>
                    <p><?php if (isset($branchData["address"]) && $branchData["address"] != '') { ?><?= $branchData["address"]; ?>,<?php } ?>
                        <?php if (isset($branchData["city"]) && $branchData["city"] != '') { ?>    <?= $branchData["city"]; ?>,<?php } ?>
                        <?php if (isset($branchData["state"]) && $branchData["state"] != '') { ?>
                            <?= $branchData["state"]; ?><?php } ?></p>
                </div>
                <div class="qr-code">
                    <img src="<?= base_url('uploads/images/student/' . $studentMpped['student_photo']); ?>"
                        alt="Student Photo" style="width:120px">

                </div>
            </header>

            <!-- Title Section -->
            <section class="report-title">
                <h2><?= $examName["exam_name"]; ?></h2>
            </section>

            <!-- Student Information Section -->
            <section class="student-info">
                <p><strong>Student Name:</strong> <?= $studentMpped["fullname"]; ?></p>
                <p><strong>Class:</strong> <?= $studentMpped["class_name"]; ?></p>
                <p><strong>Section:</strong> <?= trim($studentMpped["section_name"], $studentMpped["class_name"]); ?>
                </p>
                <p><strong>Roll No:</strong> <?= $studentMpped["register_no"]; ?></p>
                <p><strong>Attendance:</strong> A: <?= $absentDays; ?> P: <?= $presentDays; ?></p>
            </section>

            <!-- Marks Table Section -->
            <section class="marks-table">
                <table>
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Subject Name</th>
                            <th>Total Marks</th>
                            <th>Obtained Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalGetMark = array_sum(array_column($subjects, "obtainMark"));
                        $totalExamMark = array_sum(array_column($subjects, "full_mark"));
                        $labels = "'" . implode("','", array_column($subjects, "subject_name")) . "'";
                        $obtainedmark = implode(",", array_column($subjects, "obtainMark"));
                        $totalmark = implode(",", array_column($subjects, "full_mark"));
                        $classaverage = implode(",", array_map(function($subject) use ($class_average) {
                            return isset($class_average[$subject['subject_id']]) ? $class_average[$subject['subject_id']] : 0;
                        }, $subjects));


                        foreach ($subjects as $key => $mark) {
                            ?>
                            <tr>
                                <td><?= $key + 1; ?></td>
                                <td><?= $mark["subject_name"]; ?></td>
                                <td><?= $mark["full_mark"]; ?></td>
                                <td><?= $mark["obtainMark"]; ?></td>
                            </tr>
                            <?php
                        }
                        ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th><?php echo $totalExamMark; ?></th>
                            <th><?php echo $totalGetMark; ?></th>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <!-- Graph Section -->
            <section class="graph-section">
                <h3>Result Analysis</h3>
                <canvas id="resultGraph"></canvas>
            </section>

            <!-- Teacher's Comments Section -->
            <section class="comments-section">
                <h4>Class Teacher's Comments:</h4>
                <?php
                foreach($subjects as $key => $mark){
                    ?>
                    <an><?= $mark['subject_name'] ?> - <span><?= $mark['remarks'] ?? 'NA' ?></span></p>
                   <?php 
                }
               
                ?>
            </section>

            <!-- Signatures Section -->
            <section class="signatures-section">
                <div class="signature">
                    <p style="text-align:center">
                    
                        <!-- <img src="<?php echo base_url('assets/reports/singeture.png'); ?>"
                            alt="" style="width:150px;" /> -->
                    
                        
                    </p>
                    <p>Principal</p>
                </div>
                
                <div class="signature">
                    <p style="text-align:center">

                    <?php if ($teacherData['signature']): ?>
                        <img src="<?= base_url('uploads/images/staff/' . $teacherData['signature']) ?>"
                            alt="Princial Signature" style="width:120px">
                    <?php else: ?>
                        <img src="<?php echo base_url('assets/reports/singeture.png'); ?>"
                            alt="" style="width:150px;" />
                    <?php endif; ?>  
                    </p>
                    <p><?= $teacherData['name'] ?? '' ?></p>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Data for the graph
        const labels = [<?php echo $labels; ?>];
        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Obtained Marks',
                    data: [<?php echo $obtainedmark; ?>],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Marks',
                    data: [<?php echo $totalmark; ?>],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Class Average',
                    data: [<?php echo $classaverage; ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ]
        };

        // Configuration for the graph
        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Performance Analysis by Subject'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Render the chart
        const resultGraph = new Chart(
            document.getElementById('resultGraph'),
            config
        );
    </script>
</body>

</html>