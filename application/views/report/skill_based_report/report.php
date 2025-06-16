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

            <div class="info_container">
                <div class="info_box">
                    <div class="branch_logo" style="display: flex; align-items: center; gap: 30px;">
                        <img src="<?= !empty($branchData->logo) ? base_url($branchData->logo) : base_url('assets/reports/school_logo.jpg') ?>"
                            style="height: 100px; width: 100px;" alt="<?= $branchData->name ?>" />
                        <span style="font-size: 24px; font-weight: bold;"><?= $branchData->name ?></span>
                    </div>
                    <h2 class="student_name"
                        style="background-color: #f0f0f0; padding: 10px; border-radius: 5px; margin-top: 10px; text-align: center;">
                        <?=translate($studentMpped->fullname) ?? 'NA' ?>
                    </h2>
                     <img src="<?= base_url('uploads/images/student/' . $studentMpped->student_photo); ?>"
                     class="student_image" alt="<?=$studentMpped->fullname ?>" style="width:120px">

                    <table class="info_table">
                        <tr>
                            <td>Class</td>
                            <td><?=$studentMpped->class_name ?? 'NA' ?> </td>
                        </tr>
                        <tr>
                            <td>Section</td>
                            <td><?=$studentMpped->section_name ?? 'NA' ?> </td>
                        </tr>
                        <tr>
                            <td>Roll no.</td>
                            <td><?=$studentMpped->register_no ?? 'NA' ?> </td>
                        </tr>
                        <tr>
                            <td>DOB</td>
                            <td><?= date('d-m-Y', strtotime($studentMpped->birthday)) ?></td>
                        </tr>
                        <tr>
                            <td>Exam</td>
                            <td><?= $examName["exam_name"]; ?></td>
                        </tr>
                        <tr>
                            <td>Total days</td>
                            <td><?= $attendance['total_days'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Present days</td>
                            <td><?= $attendance['present_days'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <td>Absent days</td>
                            <td><?= $attendance['absent_days'] ?? 0 ?></td>
                        </tr>
                    </table>
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