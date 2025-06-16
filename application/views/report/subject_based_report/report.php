<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Report</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/reports/subject_based_report/report3.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Report Container -->
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <h1>Spring Valley School</h1>
            <h2>TERM END EXAM</h2>
            <p>Progress Report</p>
            <img src="https://via.placeholder.com/80" alt="QR Code">
        </div>

        <!-- Student Details Table -->
        <table class="details-table">
            <tr>
                <th>Subject</th>
                <td>English</td>
                <th>Student Name</th>
                <td>Andy Richards</td>
            </tr>
            <tr>
                <th>Grade</th>
                <td>10</td>
                <th>Division</th>
                <td>Secondary</td>
            </tr>
            <tr>
                <th>Date</th>
                <td colspan="3">9-9-2021</td>
            </tr>
        </table>

        <!-- Marks Table -->
        <table class="marks-table">
            <tr>
                <th>#</th>
                <th>Topic Name</th>
                <th>Max Marks</th>
                <th>Marks Obtained</th>
                <th>Overall %</th>
            </tr>
            <tr>
                <td>1</td>
                <td>FA1 - English</td>
                <td>20</td>
                <td>5</td>
                <td>25.00</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Term End - English</td>
                <td>20</td>
                <td>15</td>
                <td>75.00</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Unit Test - English</td>
                <td>20</td>
                <td>11</td>
                <td>55.00</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Notebook - English</td>
                <td>20</td>
                <td>8</td>
                <td>40.00</td>
            </tr>
            <tr>
                <td>5</td>
                <td>Term End - English</td>
                <td>100</td>
                <td>83</td>
                <td>83.00</td>
            </tr>
            <tr>
                <td>6</td>
                <td>Summative - English</td>
                <td>100</td>
                <td>75</td>
                <td>75.00</td>
            </tr>
            <tr>
                <td>7</td>
                <td>PT - English</td>
                <td>20</td>
                <td>15</td>
                <td>75.00</td>
            </tr>
        </table>

        <!-- Chart -->
        <div class="chart-container">
            <canvas id="marksChart" width="400" height="200"></canvas>
        </div>

        <!-- Overall Effort Key Table -->
        <table class="effort-key">
            <tr>
                <th colspan="2">Overall Effort Key</th>
            </tr>
            <tr>
                <td>***</td>
                <td>Consistently</td>
            </tr>
            <tr>
                <td>**</td>
                <td>Sometimes</td>
            </tr>
            <tr>
                <td>*</td>
                <td>Rarely</td>
            </tr>
        </table>

        <!-- Assessment Key -->
        <div class="legend-container">
            <div class="legend">
                <h3>Assessment Key</h3>
                <table>
                    <tr><td>A</td><td>85% - 100%</td></tr>
                    <tr><td>B</td><td>80% - 85%</td></tr>
                    <tr><td>C</td><td>70% - 80%</td></tr>
                    <tr><td>D</td><td>55% - 70%</td></tr>
                    <tr><td>E</td><td>40% - 55%</td></tr>
                    <tr><td>U</td><td>0% - 40%</td></tr>
                </table>
            </div>
        </div>

        <!-- Special Comments -->
        <div class="special-comments">
            <h3>Special Comments:</h3>
            <p>Andy enjoys participating in class discussions and group activities. He is able to grasp concepts in class and presents neat work with steady improvement.</p>
        </div>
    </div>

    <!-- Chart Script -->
    <script>
        const ctx = document.getElementById('marksChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['FA1', 'Term End 1', 'Unit Test', 'Notebook', 'Term End 2', 'Summative', 'PT'],
                datasets: [{
                    label: 'Obtained Marks',
                    data: [25, 75, 55, 40, 83, 75, 75],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>
</html>
