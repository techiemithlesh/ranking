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

        <div class="print-button" onclick="window.print();">
            <i class="icons icon-feed"></i>Print
        </div>
        <!-- Header -->
        <div id="printDiv">
            <header>
                <div class="school-logo">
                    <img src="<?= !empty($branchData->logo) ? base_url($branchData->logo) : base_url('assets/reports/school_logo.jpg') ?>"
                        alt="<?= $branchData->name ?>" style="width:100px" />
                </div>
                <div class="school-name">
                    <h1><?php echo $branchData->name; ?></h1>
                    <h2>Skill Based Report</h2>
                </div>
            </header>

            <!-- Student Details -->
            <div class="student-details">
                <div class="detail">
                    <p><strong>Student Name:</strong> <?= $studentMpped->fullname ?></p>
                </div>
                <div class="detail">
                    <strong>Roll No :</strong> <?php echo $studentMpped->register_no; ?>
                </div>
                <div class="detail">
                    <strong>Class :</strong> <?php echo $studentMpped->class_name; ?>
                </div>
                <div class="detail">
                    <strong>Attendance :</strong> A: <?php echo $absent ?? ''; ?> P: <?php echo $present ?? ''; ?> T:
                    <?php echo $totalSchoolDay ?? ''; ?>
                </div>
            </div>

            <!-- Subject-wise Grouping -->
            <section class="subject-section">
                <?php
                $current_subject = "";
                $current_category = "";

                foreach ($assessments as $assessment):
                    // Print Subject Name if it's a new subject
                    if ($current_subject != $assessment['subject_name']) {
                        if ($current_subject != "") {
                            echo "</tbody></table>"; // Close previous table
                        }
                        $current_subject = $assessment['subject_name'];
                        echo "<h3 class='subject-title'>{$current_subject}</h3>";
                        $current_category = ""; // Reset category when subject changes
                    }

                    // Print Category Name if it's a new category
                    if ($current_category != $assessment['category_name']) {
                        if ($current_category != "") {
                            echo "</tbody></table>"; // Close previous table
                        }
                        $current_category = $assessment['category_name'];
                        // echo "<h4 class='category-header'>{$current_category}</h4>";
                        echo "<table>
                    <thead>
                        <tr>
                            <th>{$current_category}</th>
                            <th>Mastered</th>
                            <th>Progressing</th>
                            <th>Beginning</th>
                        </tr>
                    </thead>
                    <tbody>";
                    }
                    ?>

                    <!-- Print Criteria Row -->
                    <tr>
                        <td><?= $assessment['criteria_text'] ?></td>
                        <td><?= ($assessment['assessment_level'] == 'Mastered') ? '✔' : '' ?></td>
                        <td><?= ($assessment['assessment_level'] == 'Progressing') ? '✔' : '' ?></td>
                        <td><?= ($assessment['assessment_level'] == 'Beginning') ? '✔' : '' ?></td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
                </table> <!-- Close last opened table -->
            </section>

        </div>

    </div>
</body>

</html>