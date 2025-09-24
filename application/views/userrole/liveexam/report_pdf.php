<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 35%;
            left: 15%;
            width: 70%;
            text-align: center;
            opacity: 0.08;
            font-size: 80px;
            color: #000;
            transform: rotate(-30deg);
            z-index: 0; /* ✅ safe value */
        }

        .header-bar {
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .header-bar table {
            width: 100%;
        }

        .header-bar td {
            vertical-align: middle;
            text-align: center;
        }

        .header-bar img {
            height: 50px;
        }

        .header-bar h2 {
            margin: 0;
            font-size: 20px;
        }

        .student-info {
            border: 1px solid #444;
            padding: 10px;
            margin: 15px 0;
            border-radius: 6px;
            background: #f9f9f9;
        }

        .student-info p {
            margin: 4px 0;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #2980b9;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .grade-pass {
            color: green;
            font-weight: bold;
            font-size: 14px;
        }

        .grade-fail {
            color: red;
            font-weight: bold;
            font-size: 14px;
        }

        .result-box {
            margin-top: 15px;
            padding: 10px;
            border: 2px solid #2980b9;
            border-radius: 6px;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            background: #ecf6fc;
        }

        .chart-box {
            text-align: center;
            margin-top: 15px;
        }

        .chart-box img {
            width: 220px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 11px;
            color: #666;
        }

        .qr-code {
            position: absolute;
            bottom: 40px;
            right: 40px;
            text-align: center;
        }

        .qr-code img {
            width: 90px;
        }
    </style>
</head>

<body>

    <!-- Watermark -->
    <div class="watermark">FutureCampus</div>

    <!-- Header -->
    <div class="header-bar">
        <table>
            <tr>
                <td style="width:80px; text-align:left;">
                    <!-- <img src="<?= !empty($branchData['logo']) ? base_url(html_escape($branchData['logo'])) : base_url('assets/reports/school_logo.jpg') ?>"
                        alt="School Logo"> -->
                </td>
                <td>
                    <h2><?= html_escape($branchData['school_name']); ?> - Exam Report Card</h2>
                </td>
                <td style="width:80px;"></td>
            </tr>
        </table>
    </div>

    <!-- Student & Exam Info -->
    <div class="student-info">
        <p><strong>Student Name:</strong> <?= html_escape($student['first_name'] . ' ' . $student['last_name']); ?></p>
        <p><strong>Roll No:</strong> <?= html_escape($student['roll']); ?></p>
        <p><strong>Class:</strong> <?= html_escape($student['class_name']); ?></p>
        <p><strong>Section:</strong> <?= html_escape($student['section_name']); ?></p>
        <p><strong>Exam:</strong> <?= html_escape($report['exam_name']); ?></p>
        <p><strong>Exam Date:</strong> <?= html_escape($report['exam_date']); ?></p>
        <p><strong>Time Taken:</strong> <?= !empty($report['time_taken']) ? html_escape($report['time_taken']) : 'N/A'; ?></p>
        <p><strong>Rank:</strong> <?= html_escape($report['rank']); ?> / <?= html_escape($report['total_students']); ?></p>
    </div>

    <!-- Performance Table -->
    <table>
        <tr>
            <th>Total Questions</th>
            <th>Attempted</th>
            <th>Correct</th>
            <th>Wrong</th>
            <th>Total Marks</th>
            <th>Obtained Marks</th>
            <th>Percentage</th>
            <th>Negative Marks</th>
        </tr>
        <tr>
            <td><?= (int) $report['total_question']; ?></td>
            <td><?= (int) $report['total_answered']; ?></td>
            <td><?= (int) $report['correct_ans']; ?></td>
            <td><?= (int) $report['wrong_ans']; ?></td>
            <td><?= (float) $report['total_marks']; ?></td>
            <td><?= (float) $report['total_obtain_marks']; ?></td>
            <td><?= (float) $report['percentage']; ?>%</td>
            <td><?= (float) $report['total_neg_marks']; ?></td>
        </tr>
    </table>

    <!-- Chart -->
    <div class="chart-box">
        <img src="<?= html_escape($chart_url); ?>"><br>
        <small>Performance Breakdown</small>
    </div>

    <!-- Result Highlight -->
    <div class="result-box">
        Final Result: <?= (float) $report['percentage']; ?>% —
        <?= ($report['result_status'] === 'Pass'
            ? '<span class="grade-pass">PASS</span>'
            : '<span class="grade-fail">FAIL</span>'); ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on <?= date('d M Y H:i'); ?> by FutureCampus</p>
    </div>

    <!-- QR Code bottom-right -->
    <!-- <div class="qr-code">
        <img src="<?= html_escape($qr_code); ?>"><br>
        <small>Scan to Verify Report</small>
    </div> -->

</body>

</html>
