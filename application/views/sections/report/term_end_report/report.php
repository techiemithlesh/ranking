<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report Card</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/reports/term_end_report/report5.css'); ?>">
</head>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo">
                <img src="logo.png" alt="School Logo">
            </div>
            <div class="institute-details">
                <h1>Oakland Valley School</h1>
                <p>8243, Wild Rose Circle, Buffalo, NY 14224</p>
                <h2>Term End Report</h2>
            </div>
            <div class="report-date">
                <p>12-10-2020</p>
            </div>
        </div>

        <!-- Student Information Section -->
        <div class="student-info">
            <p><strong>Student Name:</strong> Carel Matthew</p>
            <p><strong>Class:</strong> 2A</p>
            <p><strong>Roll No:</strong> 1</p>
            <p><strong>Attendance:</strong> A: 71 | P: 8 | T: 79</p>
        </div>

        <!-- Scholastic Areas Table -->
        <table class="table scholastic-areas">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>FA1</th>
                    <th>FA2</th>
                    <th>FA3</th>
                    <th>SA1</th>
                    <th>Total</th>
                    <th>FA1</th>
                    <th>FA2</th>
                    <th>FA3</th>
                    <th>SA1</th>
                    <th>Total</th>
                    <th>Overall</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>History</td>
                    <td>60.0</td>
                    <td>77.69</td>
                    <td>58.0</td>
                    <td>70.0</td>
                    <td>67.91</td>
                    <td>60.0</td>
                    <td>77.69</td>
                    <td>58.0</td>
                    <td>70.0</td>
                    <td>67.91</td>
                    <td>67.91</td>
                </tr>
                <tr>
                    <td>English</td>
                    <td>79.0</td>
                    <td>44.5</td>
                    <td>90.0</td>
                    <td>66.95</td>
                    <td>66.95</td>
                    <td>79.0</td>
                    <td>44.5</td>
                    <td>90.0</td>
                    <td>66.95</td>
                    <td>66.95</td>
                    <td>66.95</td>
                </tr>
                <tr>
                    <td>Mathematics</td>
                    <td>86.0</td>
                    <td>36.5</td>
                    <td>96.0</td>
                    <td>44.0</td>
                    <td>65.55</td>
                    <td>86.0</td>
                    <td>36.5</td>
                    <td>96.0</td>
                    <td>44.0</td>
                    <td>65.55</td>
                    <td>65.55</td>
                </tr>
                <tr>
                    <td>Social Studies</td>
                    <td>78.0</td>
                    <td>45.5</td>
                    <td>88.0</td>
                    <td>50.0</td>
                    <td>70.25</td>
                    <td>78.0</td>
                    <td>45.5</td>
                    <td>88.0</td>
                    <td>50.0</td>
                    <td>70.25</td>
                    <td>70.25</td>
                </tr>
                <tr>
                    <td>Physics</td>
                    <td>92.0</td>
                    <td>40.5</td>
                    <td>99.0</td>
                    <td>48.0</td>
                    <td>70.75</td>
                    <td>92.0</td>
                    <td>40.5</td>
                    <td>99.0</td>
                    <td>48.0</td>
                    <td>70.75</td>
                    <td>70.75</td>
                </tr>
                <tr>
                    <td>Economics</td>
                    <td>85.0</td>
                    <td>38.5</td>
                    <td>94.0</td>
                    <td>46.0</td>
                    <td>66.75</td>
                    <td>85.0</td>
                    <td>38.5</td>
                    <td>94.0</td>
                    <td>46.0</td>
                    <td>66.75</td>
                    <td>66.75</td>
                </tr>

                <!-- Add more subjects as per the picture -->
            </tbody>
        </table>

        <!-- Co-Scholastic Areas Table -->
        <table class="table co-scholastic-areas">
            <thead>
                <tr>
                    <th>Social Skills</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Work Skills</td>
                    <td>B</td>
                </tr>
                <tr>
                    <td>Art and Craft</td>
                    <td>A</td>
                </tr>
                <tr>
                    <td>Physical Education</td>
                    <td>B</td>
                </tr>
            </tbody>
        </table>

        <!-- Teacher Comments Table -->
        <table class="table teacher-comments">
            <thead>
                <tr>
                    <th>Class Teacher's Comments</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Carel is a spontaneous learner and shows keen interest in group activities. 
                        He shows great enthusiasm in class discussions and is able to read familiar words with ease. 
                        He has a quest to learn new words and is able to write simple sentences. 
                        He takes pride in completing his assignments on time. 
                        He grasps the grammar concepts taught in class.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Grade Boundaries Table -->
        <table class="table grade-boundaries">
            <thead>
                <tr>
                    <th>Grade Boundaries</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A+: 100-91 | A: 90-81 | B: 80-71 | C: 70-61 | D: Below 60</td>
                </tr>
            </tbody>
        </table>

        <!-- Promotion Table -->
        <table class="table promotion">
            <thead>
                <tr>
                    <th>Promoted To</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Promoted to Class: 3</td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures Section -->
        <div class="signatures">
            <p>Richard O <br> <strong>Principal</strong></p>
            <p>William Peter <br> <strong>HOD</strong></p>
            <p>Julia Fred <br> <strong>Homeroom Teacher</strong></p>
        </div>
    </div>
</body>
</html>
