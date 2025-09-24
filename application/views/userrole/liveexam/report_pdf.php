<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px;
            text-align: center;
        }
        th {
            background: #2980b9;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>

    <h1>Test Report PDF</h1>

    <p>This is a <strong>dummy report</strong> to verify PDF generation is working on Ubuntu server.</p>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Exam</th>
                <th>Marks</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Maya</td>
                <td>Live Exam Test</td>
                <td>7 / 8</td>
                <td>Pass</td>
            </tr>
            <tr>
                <td>Arjun</td>
                <td>Live Exam Test</td>
                <td>3 / 5</td>
                <td>Fail</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on <?= date('d M Y H:i'); ?>
    </div>

</body>
</html>
