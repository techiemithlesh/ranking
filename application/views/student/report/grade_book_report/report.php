<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Report Card</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/reports/grade_book_report/report6.css'); ?>">
</head>
<body>
  <div class="report-card">
    <!-- Header -->
    <div class="header">
      <img src="your-logo.png" alt="School Logo" class="logo">
      <div class="school-info">
        <h1>Oakland Valley School</h1>
        <p>8243, Wild Rose Circle, Buffalo, NY 14224</p>
        <h2>GRADE BOOK 2020 - 2021</h2>
      </div>
    </div>

    <!-- Student Details -->
    <div class="student-details">
      <table>
        <tr>
          <td><strong>Name:</strong> Bonnie Bort</td>
          <td><strong>Class:</strong> 1 A</td>
          <td><strong>Roll Number:</strong> 2</td>
        </tr>
        <tr>
          <td><strong>No. of Working Days:</strong> 50.0</td>
          <td><strong>No. of Present Days:</strong> 40.0</td>
          <td><strong>Weight (kg):</strong> 22</td>
        </tr>
        <tr>
          <td><strong>Height (cm):</strong> 32</td>
        </tr>
      </table>
    </div>

    <!-- Subject Skills -->
    <div class="subject-skills">
      <table>
        <thead>
          <tr>
            <th>Subject</th>
            <th>Skill</th>
            <th>Q1</th>
            <th>Q2</th>
            <th>Q3</th>
            <th>Q4</th>
          </tr>
        </thead>
        <tbody>
          <!-- English -->
          <tr>
            <td rowspan="4">English</td>
            <td>PHONICS SPELLING AND VOCABULARY</td>
            <td class="grade green">A</td>
            <td class="grade yellow">B</td>
            <td class="grade red">C</td>
            <td class="grade red">D</td>
          </tr>
          <tr>
            <td>READING</td>
            <td class="grade red">E</td>
            <td class="grade yellow">D</td>
            <td class="grade green">B</td>
            <td class="grade yellow">C</td>
          </tr>
          <tr>
            <td>WRITING</td>
            <td class="grade green">A</td>
            <td class="grade yellow">C</td>
            <td class="grade red">D</td>
            <td class="grade green">B</td>
          </tr>
          <tr>
            <td>LISTENING</td>
            <td class="grade yellow">E</td>
            <td class="grade green">B</td>
            <td class="grade yellow">C</td>
            <td class="grade red">D</td>
          </tr>

          <!-- Mathematics -->
          <tr>
            <td rowspan="3">Mathematics</td>
            <td>ARITHMETIC OPERATIONS</td>
            <td class="grade green">A</td>
            <td class="grade green">A</td>
            <td class="grade yellow">B</td>
            <td class="grade red">C</td>
          </tr>
          <tr>
            <td>GEOMETRY</td>
            <td class="grade red">D</td>
            <td class="grade yellow">C</td>
            <td class="grade green">B</td>
            <td class="grade green">A</td>
          </tr>
          <tr>
            <td>MEASUREMENTS</td>
            <td class="grade yellow">C</td>
            <td class="grade red">D</td>
            <td class="grade green">A</td>
            <td class="grade yellow">B</td>
          </tr>

          <!-- Science -->
          <tr>
            <td rowspan="3">Science</td>
            <td>EXPERIMENTAL METHODS</td>
            <td class="grade yellow">B</td>
            <td class="grade green">A</td>
            <td class="grade yellow">C</td>
            <td class="grade green">A</td>
          </tr>
          <tr>
            <td>CONCEPTUAL UNDERSTANDING</td>
            <td class="grade red">D</td>
            <td class="grade yellow">C</td>
            <td class="grade green">B</td>
            <td class="grade yellow">C</td>
          </tr>
          <tr>
            <td>APPLICATION</td>
            <td class="grade yellow">C</td>
            <td class="grade red">D</td>
            <td class="grade green">A</td>
            <td class="grade green">A</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
