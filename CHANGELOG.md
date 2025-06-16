## [2024-02-25] - Fixed Student Admission Mapping Issue

### 🔹 Changes:

- **Purpose:** Fix mapping issue in student admission.
- **Alter Table:** Changed `branch_id` column in `enroll` table.

### 🔄 Migration (Apply)

````sql
ALTER TABLE `enroll`
CHANGE `branch_id` `branch_id` INT(11) NOT NULL;


### ⏪ Rollback (Undo)
ALTER TABLE `enroll`
CHANGE `branch_id` `branch_id` TINYINT(4) NOT NULL;

## [2025-02-27] - Fixed Student Admission Mapping Issue
### 🔹 Changes:
- **Purpose:** Brnach not delete issue.
- **ALTER TABLE `book_branches` DROP FOREIGN KEY `book_branches_ibfk_2`;

### 🔄 Migration (Apply)
```sql
ALTER TABLE `book_branches` DROP FOREIGN KEY `book_branches_ibfk_2`;

-- 2. Add a new foreign key constraint with ON DELETE CASCADE
ALTER TABLE `book_branches`
ADD CONSTRAINT `book_branches_ibfk_2`
FOREIGN KEY (`branch_id`) REFERENCES `branch`(`id`)
ON DELETE CASCADE;

## [2025-02-28] - Fixed Student List Current Session


## [2025-03-08] - Remember Me Added
### 🔹 Changes:
- **Purpose:** Remember Me not working issue.

```sql
ALTER TABLE `login_credential` ADD COLUMN `remember_token` VARCHAR(64) NULL;


## [2025-03-12] - Implemented Attendance Report Fetching & Insertion
### 🔹 Changes:
- **Purpose:** Student Attendance details.
- **CREATE TABLE student_attendance_report (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    branch_id INT NOT NULL,
    exam_id INT NOT NULL,
    present_days INT NOT NULL DEFAULT 0,
    absent_days INT NOT NULL DEFAULT 0,
    total_days INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (student_id, branch_id, exam_id)
);`;


## [2025-04-30] - Implemented Notification System
### 🔹 Changes:
- **Purpose:** Notification System.
- **CREATE TABLE student_attendance_report (
    id INT PRIMARY KEY AUTO_INCREMENT,
   to_user_id INT NOT NULL,
   to_user_type INT NOT NULL COMMENT '1=SUPER ADMIN, 2=ADMIN, 3=TEACHER, 4=ACCOUNTANT, 5=LIBRARIAN, 6=PARENT, 7=STUDENT, etc.
   from_user_id INT DEFAULT NULL,
   from_user_type INT DEFAULT NULL,
   title VARCHAR(255) NOT NULL,
   description TEXT,
   link  VARCHAR(255),
   is_read TINYINT(1) DEFAULT 0,
   is_popup TINYINT(1) DEFAULT 1 COMMENT '1=show in bell icon',
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);`;


````
