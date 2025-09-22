<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
{

    public function index()
    {
        // Dummy data
        $data = [
            'report' => [
                'total_question' => 50,
                'total_answered' => 45,
                'correct_ans' => 38,
                'wrong_ans' => 7,
                'total_marks' => 100,
                'total_obtain_marks' => 76,
                'total_neg_marks' => 2,
            ],
            'student' => [
                'name' => 'Rohit Sharma',
                'roll_no' => 'ST12345',
                'class' => '10 - A',
            ],
            'exam' => [
                'name' => 'Science Midterm',
                'date' => '15 Sep 2025',
            ]
        ];

        // Load view as HTML
        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // Generate PDF
        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        // Preview in browser (0 = preview, 1 = download)
        $this->pdf->stream("Dummy_ReportCard.pdf", array("Attachment" => 0));
    }
}
