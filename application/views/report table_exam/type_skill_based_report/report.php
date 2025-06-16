<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Report Card</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/reports/type_skill_based_report/report4.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="<?php echo base_url('assets/reports/school_logo.jpg'); ?>" alt="School Logo"  style="width:120px">
            <div class="school-info">
                <h1><?php echo $branchData->name;?></h1>
                <p><?php echo $branchData->address;?>, <?php echo $branchData->city;?>, <?php echo $branchData->state;?></p>
            </div>
            <div class="qr-code"></div>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            <h2>Mount Nemrut Report Card (<?php echo $currentYear;?>-<?php echo $currentYear+1;?>)</h2>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <p><strong>Student Name:</strong> <?php echo $studentData->first_name;?> <?php echo $studentData->last_name;?></p>
            <p><strong>Roll No:</strong> <?php echo $studentData->register_no;?></p>
            <p><strong>Class:</strong> <?php echo $studentMpped->class_name; ?></p>
            <p><strong>Attendance:</strong> A: <?php echo $absent;?> P: <?php echo $present;?> T: <?php echo $totalSchoolDay;?></p>
        </div>

        <!-- Marks Table -->
        <table>
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Subject Name</th>
                    <th>Class Test 1</th>
                    <th>Unit Test 1</th>
                    <th>Total Marks</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>English</td>
                    <td>11</td>
                    <td>50</td>
                    <td>61</td>
                    <td>A</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Spanish</td>
                    <td>18</td>
                    <td>30</td>
                    <td>48</td>
                    <td>C</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>German</td>
                    <td>12</td>
                    <td>37</td>
                    <td>49</td>
                    <td>B</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Mathematics</td>
                    <td>14</td>
                    <td>48</td>
                    <td>62</td>
                    <td>A</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Science</td>
                    <td>18</td>
                    <td>47</td>
                    <td>65</td>
                    <td>A*</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Social Studies</td>
                    <td>16</td>
                    <td>50</td>
                    <td>66</td>
                    <td>A*</td>
                </tr>
            </tbody>
        </table>

        <!-- Graph Section -->
        <div class="graph-section">
            <h3>Performance Chart</h3>
            <canvas id="performanceChart" width="400" height="200"></canvas>
        </div>

        <!-- Teacher Comments in Table -->
        <h3>Class Teacher's Comments</h3>
        <table class="teacher-comments">
            <tr>
                <td>
                    Alex enjoys participating in class discussions and group activities. He is able to read familiar words. He completes assignments on time and makes steady progress in writing simple sentences with appropriate punctuation marks.
                </td>
            </tr>
        </table>

        <!-- Grade Boundaries -->
        <div class="grade-boundaries">
            <p><strong>Grade Boundaries:</strong> A*: 100-90, A: 89-80, B: 79-70, C: 69-60</p>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['English', 'Spanish', 'German', 'Mathematics', 'Science', 'Social Studies'],
                datasets: [{
                    label: 'Total Marks',
                    data: [61, 48, 49, 62, 65, 66],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 70
                    }
                }
            }
        });
    </script>
</body>
</html>
