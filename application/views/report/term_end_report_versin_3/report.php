<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Report</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/reports/term_end_report_versin_3/report8.css'); ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="report-container">
    <!-- Header Section -->
    <div class="header">
      <div class="logo">
        <img src="your-logo.png" alt="School Logo">
      </div>
      <div class="school-details">
        <h1>Oakland Valley School</h1>
        <p>8243, Wild Rose Circle, Buffalo, NY 14224</p>
        <h2>Term End Report (2021-2022)</h2>
		</div>
    </div>

    <!-- Student Details Section -->
    <div class="student-details">
      <table>
        <tr>
          <td><strong>Name:</strong> Carel Matthew</td>
          <td><strong>Roll Number:</strong> 1</td>
          <td><strong>Class:</strong> IG Year 1</td>
          <td><strong>Attendance:</strong> 59/60</td>
        </tr>
      </table>
    </div>

    <!-- Grades and Skills Section -->
    <div class="grades-skills">
      <div class="grades-table">
        <table>
          <thead>
            <tr>
              <th>Subject</th>
              <th>Grade</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>English</td>
              <td>A*</td>
            </tr>
            <tr>
              <td>Economics</td>
              <td>A*</td>
            </tr>
            <tr>
              <td>Mathematics</td>
              <td>A</td>
            </tr>
            <tr>
              <td>Chemistry</td>
              <td>A*</td>
            </tr>
            <tr>
              <td>Biology</td>
              <td>A*</td>
            </tr>
            <tr>
              <td>ICT</td>
              <td>A*</td>
            </tr>
            <tr>
              <td>Physics</td>
              <td>A*</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="skills-activities">
        <div class="skills-table">
          <table>
            <thead>
              <tr>
                <th>Skill</th>
                <th>Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Communication</td>
                <td>A</td>
              </tr>
              <tr>
                <td>Teamwork</td>
                <td>A*</td>
              </tr>
              <tr>
                <td>Problem Solving</td>
                <td>A</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="activities-table">
          <table>
            <thead>
              <tr>
                <th>Activity</th>
                <th>Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sports</td>
                <td>A*</td>
              </tr>
              <tr>
                <td>Music</td>
                <td>A</td>
              </tr>
              <tr>
                <td>Art</td>
                <td>A*</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Report Chart Section -->
    <div class="report-chart">
      <canvas id="gradesChart"></canvas>
    </div>

    <!-- Class Teacher Comments Section -->
    <div class="class-teacher-comments">
      <table>
        <thead>
          <tr>
            <th>Class Teacher Comments</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Carel has consistently performed well throughout the term. Excellent work!</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Grade Boundaries Section -->
    <div class="grade-boundaries">
      <table>
        <thead>
          <tr>
            <th>Grade</th>
            <th>Percentage</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>A*</td>
            <td>90-100%</td>
          </tr>
          <tr>
            <td>A</td>
            <td>80-89%</td>
          </tr>
          <tr>
            <td>B</td>
            <td>70-79%</td>
          </tr>
          <tr>
            <td>C</td>
            <td>60-69%</td>
          </tr>
          <tr>
            <td>D</td>
            <td>50-59%</td>
          </tr>
          <tr>
            <td>E</td>
            <td>40-49%</td>
          </tr>
          <tr>
            <td>F</td>
            <td>Below 40%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('gradesChart').getContext('2d');
    const gradesChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['English', 'Economics', 'Mathematics', 'Chemistry', 'Biology', 'ICT', 'Physics'],
        datasets: [{
          label: 'Grades',
          data: [95, 94, 88, 96, 97, 93, 95],
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>