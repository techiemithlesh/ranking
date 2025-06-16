<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill-Based Report</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/reports/skill_based_report/report.css'); ?>">
</head>
<body>
    <div class="report-container">
        <!-- Header -->
        <header>
            <div class="school-logo">
        
            <img src="<?= !empty($branchData->logo) ? base_url($branchData->logo) : base_url('assets/reports/school_logo.jpg') ?>" alt="<?=$branchData->name ?>"  style="width:100px"/>
            </div>
            <div class="school-name">
                <h1><?php echo $branchData->name;?></h1>
                <h2>Skill Based Report</h2>
            </div>
        </header>

        <!-- Student Details -->
        <div class="student-details">
            <div class="detail">
                <strong>Student Name :</strong> <?php echo $studentData->fullname;?>
            </div>
            <div class="detail">
                <strong>Roll No :</strong> <?php echo $studentMpped->register_no;?>
            </div>
            <div class="detail">
                <strong>Class :</strong> <?php echo $studentMpped->class_name; ?>
            </div>
            <div class="detail">
                <strong>Attendance :</strong> A: <?php echo $absent ?? '';?> P: <?php echo $present ?? '';?> T: <?php echo $totalSchoolDay ?? '';?>
            </div>
        </div>

        <!-- English Section -->
        <section class="subject-section">
            <h3 class="subject-title">English</h3>
            <table>
                <thead>
                    <tr>
                        <th>PHONICS SPELLING AND VOCABULARY</th>
                        <th>Mastered</th>
                        <th>Progressing</th>
                        <th>Beginning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Able to pronounce words correctly</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Uses effective strategies for learning new spellings and corrects misspelt words</td>
                        <td></td>
                        <td>✔</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>READING</th>
                        <th>Mastered</th>
                        <th>Progressing</th>
                        <th>Beginning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Uses knowledge of punctuation to read with fluency understanding and expression.</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Reads grade appropriate text independently and clearly</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>WRITING</th>
                        <th>Mastered</th>
                        <th>Progressing</th>
                        <th>Beginning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Writes in appropriate sequences to display understanding</td>
                        <td></td>
                        <td></td>
                        <td>✔</td>
                    </tr>
                    <tr>
                        <td>Presents neat and legible work</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Uses appropriate punctuation marks and grade appropriate grammar concepts</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- German Section -->
        <section class="subject-section">
            <h3 class="subject-title">German</h3>
            <table>
                <thead>
                    <tr>
                        <th>PHONICS SPELLING AND VOCABULARY</th>
                        <th>Mastered</th>
                        <th>Progressing</th>
                        <th>Beginning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Able to pronounce words correctly</td>
                        <td>✔</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Uses effective strategies for learning new spellings and corrects misspelt words</td>
                        <td></td>
                        <td>✔</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
