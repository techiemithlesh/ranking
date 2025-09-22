<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#333; margin: 20px; position: relative; }
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
            z-index: -1000;
        }
        .header-bar {
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .header-bar table { width: 100%; }
        .header-bar td { vertical-align: middle; text-align: center; }
        .header-bar img { height: 50px; }
        .header-bar h1 { margin: 0; font-size: 20px; }
        .student-info {
            border: 1px solid #444;
            padding: 10px;
            margin: 15px 0;
            border-radius: 6px;
            background: #f9f9f9;
        }
        .student-info p { margin: 4px 0; font-size: 13px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #2980b9;
            color: white;
        }
        tr:nth-child(even) { background: #f2f2f2; }
        .grade-pass { color: green; font-weight: bold; font-size: 14px; }
        .grade-fail { color: red; font-weight: bold; font-size: 14px; }
        .result-box {
            margin-top: 20px;
            padding: 12px;
            border: 2px solid #2980b9;
            border-radius: 6px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            background: #ecf6fc;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            padding: 20px;
            font-size: 12px;
        }
        .footer {
            text-align:center;
            margin-top:20px;
            font-size:11px;
            color:#666;
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
                    <img src="<?= base_url('uploads/logo.jpg'); ?>" alt="School Logo">
                </td>
                <td>
                    <h1>FutureCampus - Exam Report Card</h1>
                </td>
                <td style="width:80px;"></td>
            </tr>
        </table>
    </div>

    <!-- Student & Exam Info -->
    <div class="student-info">
        <p><strong>Student Name:</strong> <?= $student['name']; ?></p>
        <p><strong>Roll No:</strong> <?= $student['roll_no']; ?></p>
        <p><strong>Class:</strong> <?= $student['class']; ?></p>
        <p><strong>Exam:</strong> <?= $exam['name']; ?></p>
        <p><strong>Exam Date:</strong> <?= $exam['date']; ?></p>
        <p><strong>Time Taken:</strong> <?= isset($report['time_taken']) ? $report['time_taken'] : 'N/A'; ?></p>
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
            <td><?= $report['total_question']; ?></td>
            <td><?= $report['total_answered']; ?></td>
            <td><?= $report['correct_ans']; ?></td>
            <td><?= $report['wrong_ans']; ?></td>
            <td><?= $report['total_marks']; ?></td>
            <td><?= $report['total_obtain_marks']; ?></td>
            <td><?= round(($report['total_obtain_marks'] / max(1, $report['total_marks'])) * 100, 2); ?>%</td>
            <td><?= $report['total_neg_marks']; ?></td>
        </tr>
    </table>

    <!-- Result Highlight -->
    <div class="result-box">
        Final Result: <?= 70 ?>% — 
        <?= ($percentage >= 40 ? '<span class="grade-pass">PASS</span>' : '<span class="grade-fail">FAIL</span>'); ?>
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>_________________<br>Class Teacher</td>
            <td>_________________<br>Principal</td>
            <td>_________________<br>Student</td>
        </tr>
    </table>

    <div class="footer">
        <p>Generated on <?= date('d M Y H:i'); ?> by FutureCampus</p>
    </div>

</body>
</html>
