<?php
defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Centralized menu structure.
 * Each item:
 * - `title`: language key or string
 * - `icon`: FontAwesome / Bootstrap icon class
 * - `url`: controller/method or full URL
 * - `roles`: which user types see this (e.g. ['student','parent'])
 * - `children`: optional array of sub-items
 */


$config['menus'] = [

    // MENU FOR STUDENT LOGIN
    'student' => [
        [
            'label' => 'My Learning Kit',
            'icon' => 'icon-book-open',
            'children' => [
                [
                    'label' => 'My Learning Book',
                    'icon' => 'fas fa-book-reader',
                    'url' => 'userrole/myLearningBook',
                    'children' => [
                        ['label' => 'Month - 1', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=1'],
                        ['label' => 'Month - 2', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=2'],
                        ['label' => 'Month - 3', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=3'],
                        ['label' => 'Month - 4', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=4'],
                        ['label' => 'Month - 5', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=5'],
                        ['label' => 'Month - 6', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=6'],
                        ['label' => 'Month - 7', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=7'],
                        ['label' => 'Month - 8', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=8'],
                        ['label' => 'Month - 9', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=9'],
                        ['label' => 'Month - 10', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=10'],
                        ['label' => 'Month - 11', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=11'],
                        ['label' => 'Month - 12', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myLearningBook?month=12'],
                    ]
                ],
                [
                    'label' => 'My Interactive Book',
                    'icon' => 'fas fa-tablet-alt',
                    'url' => 'userrole/myInteractiveBook',
                    'children' => [
                        ['label' => 'Month - 1', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=1'],
                        ['label' => 'Month - 2', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=2'],
                        ['label' => 'Month - 3', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=3'],
                        ['label' => 'Month - 4', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=4'],
                        ['label' => 'Month - 5', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=5'],
                        ['label' => 'Month - 6', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=6'],
                        ['label' => 'Month - 7', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=7'],
                        ['label' => 'Month - 8', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=8'],
                        ['label' => 'Month - 9', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=9'],
                        ['label' => 'Month - 10', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=10'],
                        ['label' => 'Month - 11', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=11'],
                        ['label' => 'Month - 12', 'icon' => 'far fa-calendar-alt', 'url' => 'userrole/myInteractiveBook?month=12'],
                    ]
                ],
                ['label' => 'Attachment Book', 'icon' => 'icons icon-cloud-upload', 'url' => 'userrole/attachments'],
                ['label' => 'My Homework', 'icon' => 'icon-note', 'url' => 'userrole/homework'],
                ['label' => 'Smart Library', 'icon' => 'icon-book-open', 'url' => 'userrole/digitalBook'],
                [
                    'label' => 'Library',
                    'icon' => 'icon-notebook',
                    'children' => [
                        ['label' => 'Book List', 'icon' => 'fas fa-book-open', 'url' => 'userrole/book'],
                        ['label' => 'Issued Book', 'icon' => 'fas fa-book-reader', 'url' => 'userrole/book_request'],
                    ]
                ],
            ]
        ],
        [
            'label' => 'Academic Master',
            'icon' => 'fas fa-user-graduate',
            'children' => [
                ['label' => 'Exam Schedule', 'icon' => 'icon-trophy', 'url' => 'userrole/exam_schedule'],
                ['label' => 'Class Schedule', 'icon' => 'fas fa-dna', 'url' => 'userrole/class_schedule'],
                ['label' => 'Subject', 'icon' => 'fas fa-book-reader', 'url' => 'userrole/subject'],
                ['label' => 'Attendance', 'icon' => 'icons icon-chart', 'url' => 'userrole/attendance'],
                ['label' => 'Events', 'icon' => 'icons icon-speech', 'url' => 'userrole/event'],
                ['label' => 'Online Exam', 'icon' => 'icon-screen-desktop', 'url' => 'userrole/online_exam'],
            ]
        ],
        ['label' => 'Live Classroom', 'icon' => 'fas fa-chalkboard-teacher', 'url' => 'userrole/live_class'],
        [
            'label' => 'My Progress',
            'icon' => 'icon-graph',
            'children' => [
                ['label' => 'Progress Report', 'icon' => 'fas fa-marker', 'url' => 'userrole/report_card'],
                ['label' => 'Smart Progress', 'icon' => 'fas fa-tasks', 'url' => 'userrole/progress'],
                ['label' => 'Progress Tracker', 'icon' => 'fas fa-chart-line', 'url' => 'userrole/my_progress'],
                ['label' => 'Skill Report', 'icon' => 'fas fa-clipboard-list', 'url' => 'userrole/skillBasedReport'],

                ['label' => 'Online Exam', 'icon' => 'fas fa-laptop-code', 'children' => [
                    ['label' => 'Smart Progress', 'icon' => 'fas fa-globe', 'url' => 'userrole/online_exam_progress'],
                    ['label' => 'Progress Tracker', 'icon' => 'fas fa-file-alt', 'url' => 'userrole/exam_progress_subjectwise'],
                ]]
            ]
        ],
        ['label' => 'My Gallery', 'icon' => 'fas fa-images', 'url' => 'userrole/my_gallery'],
        [
            'label' => 'Parents',
            'icon' => 'fas fa-user',
            'children' => [
                ['label' => 'Fees', 'icon' => 'icons icon-calculator', 'url' => 'userrole/invoice'],
                ['label' => 'Message', 'icon' => 'icons icon-envelope-open', 'url' => 'communication/mailbox/inbox']
            ]
        ],

    ],

    // MENU FOR PARENT LOGIN
    'parent' => [
        [
            'label' => 'My Children',
            'icon' => 'fas fa-user-friends',
            'url' => 'parents/my_children'
        ]
    ],

    // add 'teacher', 'admin', etc. as needed
];
