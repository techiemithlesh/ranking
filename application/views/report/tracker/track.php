<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Tracker</title>
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
                    <img
                        src="<?= !empty($branchData['logo']) ? base_url($branchData['logo']) : base_url('assets/reports/school_logo.jpg') ?>" />

                </div>

                <div class="school-info">
                    <h1><?= $branchData["name"]; ?></h1>
                    <p><?php if (isset($branchData["address"]) && $branchData["address"] != '') { ?><?= $branchData["address"]; ?>,<?php } ?>
                        <?php if (isset($branchData["city"]) && $branchData["city"] != '') { ?>
                            <?= $branchData["city"]; ?>,<?php } ?>
                        <?php if (isset($branchData["state"]) && $branchData["state"] != '') { ?>
                            <?= $branchData["state"]; ?><?php } ?>
                    </p>
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

            </section>

            <!-- Marks Table Section -->
            <section class="marks-table">
                <table>
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Subject Name</th>
                            <th>Obtained Marks</th>
                            <th>Total Marks</th>
                            <th>Exam Name</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNumber = 1;
                        foreach ($progress as $item) {
                            ?>
                            <tr>
                                <td><?php echo $serialNumber++; ?></td>
                                <td><?php echo $item['subject_name']; ?></td>
                                <td><?php echo $item['marks_obtained']; ?></td>
                                <td><?php echo $item['full_mark']; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td>
                                    <?php
                                    if ($item['full_mark'] > 0) {
                                        $percentage = ($item['marks_obtained'] / $item['full_mark']) * 100;
                                        echo number_format($percentage, 2) . '%';
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>

                    </tbody>

                </table>
            </section>

            <!-- Graph Section -->
            <section class="graph-section">
                <h3>Result Analysis</h3>
                <canvas id="resultGraph"></canvas>
            </section>

        </div>
    </div>

    <script>
        // PHP variables to JavaScript (Replace with your actual PHP values)
        const examNames = <?php echo json_encode(array_column($progress, 'name')); ?>;
        const obtainedMarks = <?php echo json_encode(array_column($progress, 'marks_obtained')); ?>;
        const totalMarks = <?php echo json_encode(array_column($progress, 'full_mark')); ?>;
        const classAverages = <?php echo json_encode($class_average); ?>;

        // Calculate percentages
        const percentages = obtainedMarks.map((marks, index) => (marks / totalMarks[index]) * 100);

        const data = {
            labels: examNames,
            datasets: [
                {
                    label: 'Percentage',
                    data: percentages,
                    type: 'line',
                    borderColor: 'rgba(75, 192, 192, 1)',  // Teal
                    borderWidth: 3,
                    fill: false,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                    pointRadius: 6
                },
                {
                    label: 'Class Average',
                    data: Object.values(classAverages),
                    type: 'line',
                    borderColor: 'rgba(255, 165, 0, 1)',  // Orange
                    borderWidth: 3,
                    fill: false,
                    pointBackgroundColor: 'rgba(255, 165, 0, 1)',
                    pointRadius: 6
                }
            ]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Progress Tracker: Exam Performance',
                        font: {
                            size: 18 
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            font: {
                                size: 14
                            }
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Exam Name',
                            font: {
                                size: 14
                            }
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        };

        // Render chart
        new Chart(document.getElementById('resultGraph'), config);
    </script>


</body>

</html>