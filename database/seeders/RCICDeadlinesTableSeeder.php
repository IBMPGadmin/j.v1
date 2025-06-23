<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RCICDeadline;

class RCICDeadlinesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deadlines = [
            [
                'title' => 'Express Entry Profile Expiry',
                'category' => 'Express Entry',
                'type' => 'Profile Management',
                'description' => 'Express Entry profiles expire after 12 months. Must be renewed or updated before expiry.',
                'days_before' => 30,
                'status' => 'active'
            ],
            [
                'title' => 'LMIA Work Permit Extension',
                'category' => 'Work Permits',
                'type' => 'Application Deadline',
                'description' => 'Submit work permit extension application before current permit expires.',
                'days_before' => 60,
                'status' => 'active'
            ],
            [
                'title' => 'Study Permit Extension',
                'category' => 'Study Permits',
                'type' => 'Application Deadline',
                'description' => 'Submit study permit extension application before current permit expires.',
                'days_before' => 90,
                'status' => 'active'
            ],
            [
                'title' => 'PR Card Renewal',
                'category' => 'Permanent Residence',
                'type' => 'Document Renewal',
                'description' => 'Submit PR card renewal application before current card expires.',
                'days_before' => 180,
                'status' => 'active'
            ],
            [
                'title' => 'Language Test Results Validity',
                'category' => 'Document Validity',
                'type' => 'Test Results',
                'description' => 'IELTS/CELPIP results are valid for 2 years from test date.',
                'days_before' => 60,
                'status' => 'active'
            ],
            [
                'title' => 'Educational Credential Assessment (ECA)',
                'category' => 'Document Validity',
                'type' => 'Assessment Validity',
                'description' => 'ECA reports are typically valid for 5 years.',
                'days_before' => 180,
                'status' => 'active'
            ],
            [
                'title' => 'Biometrics Validity Period',
                'category' => 'Document Validity',
                'type' => 'Biometrics',
                'description' => 'Biometrics are valid for 10 years for temporary residents.',
                'days_before' => 90,
                'status' => 'active'
            ],
            [
                'title' => 'Police Certificate Validity',
                'category' => 'Document Validity',
                'type' => 'Background Check',
                'description' => 'Police certificates are generally valid for 6 months.',
                'days_before' => 45,
                'status' => 'active'
            ],
            [
                'title' => 'Medical Exam Validity',
                'category' => 'Document Validity',
                'type' => 'Medical',
                'description' => 'Immigration medical exams are valid for 12 months.',
                'days_before' => 60,
                'status' => 'active'
            ],
            [
                'title' => 'PGWP Application Deadline',
                'category' => 'Work Permits',
                'type' => 'Post-Graduation',
                'description' => 'Must apply for PGWP within 180 days of receiving final marks.',
                'days_before' => 30,
                'status' => 'active'
            ],
            [
                'title' => 'Spousal Sponsorship Updates',
                'category' => 'Family Sponsorship',
                'type' => 'Application Updates',
                'description' => 'Required updates for sponsor and applicant information.',
                'days_before' => 30,
                'status' => 'active'
            ],
            [
                'title' => 'Provincial Nominee Acceptance',
                'category' => 'Provincial Programs',
                'type' => 'Nomination Deadline',
                'description' => 'Accept provincial nomination and apply within given timeframe.',
                'days_before' => 15,
                'status' => 'active'
            ],
            [
                'title' => 'CEC Experience Verification',
                'category' => 'Express Entry',
                'type' => 'Document Verification',
                'description' => 'Verify Canadian work experience documentation before submission.',
                'days_before' => 45,
                'status' => 'active'
            ],
            [
                'title' => 'FSW Points Calculation Review',
                'category' => 'Express Entry',
                'type' => 'Points Assessment',
                'description' => 'Regular review of Federal Skilled Worker points calculation.',
                'days_before' => 30,
                'status' => 'active'
            ],
            [
                'title' => 'NOC Code Updates Review',
                'category' => 'Document Maintenance',
                'type' => 'Classification Update',
                'description' => 'Review and update NOC codes based on IRCC changes.',
                'days_before' => 90,
                'status' => 'active'
            ]
        ];

        foreach ($deadlines as $deadline) {
            RCICDeadline::create($deadline);
        }
    }
}
