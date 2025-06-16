<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/reports/annual_examination_report/report.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<?php //echo '<pre>'; print_r($studentData);?>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <header class="report-header">
            <div class="school-logo">
                <img src="<?php echo base_url('assets/reports/school_logo.jpg'); ?>" alt="School Logo"  style="width:120px">
            </div>
			
            <div class="school-info">
                <h1><?php echo $branchData->name;?></h1>
                <p><?php echo $branchData->address;?>, <?php echo $branchData->city;?>, <?php echo $branchData->state;?></p>
            </div>
            <div class="qr-code">
                <img src="<?php echo base_url('assets/reports/school_qr_code.svg'); ?>" alt="QR Code" style="width:120px">
            </div>
        </header>

        <!-- Title Section -->
        <section class="report-title">
            <h2>ANNUAL EXAMINATION</h2>
        </section>

        <!-- Student Information Section -->
        <section class="student-info">
            <p><strong>Student Name:</strong> <?php echo $studentData->first_name;?> <?php echo $studentData->last_name;?></p>
            <p><strong>Class:</strong> <?php echo $studentMpped->class_name; ?></p>
            <p><strong>Roll No:</strong> <?php echo $studentData->register_no;?></p>
            <p><strong>Attendance:</strong> A: <?php echo $absent;?> P: <?php echo $present;?> T: <?php echo $totalSchoolDay;?></p>
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
                    $totalGetMark = 0;
                    $totalExamMark = 0;
                    $labels = '';
                    $obtainedmark = '';
                    $totalmark = '';
                    $classaverage = '';
                    $i=1;
                    foreach($marks as $mark){
                        $marksData = $mark->mark_distribution;
                        
                        $marksData = json_decode($marksData,true);
                        $marksData = $marksData[$mark->hall_id];
                        $totalGetMark = $totalGetMark + $marksData['pass_mark'];
                        $totalExamMark = $totalExamMark + $marksData['full_mark'];
                        if($i==1)
                        {
                            $labels = '"'.$mark->name.'"';
                            $obtainedmark = $marksData['pass_mark'];
                            $totalmark = $marksData['full_mark'];
                            $classaverage = rand('11','99');
                        }else{
                            $labels = $labels.', "'.$mark->name.'"';
                            $obtainedmark = $obtainedmark.', '.$marksData['pass_mark'];
                            $totalmark = $totalmark.', '.$marksData['full_mark'];
                            $classaverage = $classaverage.', '.rand('11','99');
                        }
                    ?>
                    <tr>
                        <td><?php echo $i;?></td>
                        <td><?php echo $mark->name;?></td>
                        <td><?php echo $marksData['full_mark'];?></td>
                        <td><?php echo $marksData['pass_mark'];?></td>
                    </tr>
                    <?php $i++;} ?>
                    
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th><?php echo $totalExamMark;?></th>
                        <th><?php echo $totalGetMark;?></th>
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
            <p>Alex enjoys participating in class discussions and in group activities. He is able to read familiar words. He completes his assignments on time. He is able to grasp the concepts taught in class with repetition and guidance. He presents neat work and is making steady progress in writing simple sentences with appropriate punctuation marks.</p>
        </section>

        <!-- Signatures Section -->
        <section class="signatures-section">
            <div class="signature">
                <p style="text-align:center"><img src="<?php echo base_url('assets/reports/singeture.png'); ?>" alt="" style="width:150px;"/></p>
                <p>Principal</p>
            </div>
            <div class="signature">
                <p style="text-align:center"><img src="<?php echo base_url('assets/reports/singeture.png'); ?>" alt="" style="width:150px;"/></p>
                <p>HOD</p>
            </div>
            <div class="signature">
                <p style="text-align:center"><img src="<?php echo base_url('assets/reports/singeture.png'); ?>" alt="" style="width:150px;"/></p>
                <p>Homeroom Teacher</p>
            </div>
        </section>
    </div>

    <script>
        // Data for the graph
        const labels = [<?php echo $labels;?>];
        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Obtained Marks',
                    data: [<?php echo $obtainedmark;?>],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Marks',
                    data: [<?php echo $totalmark;?>],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Class Average',
                    data: [<?php echo $classaverage;?>],
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
