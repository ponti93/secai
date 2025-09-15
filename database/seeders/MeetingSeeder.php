<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Meeting;
use Carbon\Carbon;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        // Create meetings for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample meetings
        $meetings = [
            [
                'title' => 'Weekly Team Standup',
                'description' => 'Daily standup meeting to discuss progress, blockers, and upcoming tasks.',
                'start_time' => Carbon::now()->addDays(1)->setTime(9, 0),
                'end_time' => Carbon::now()->addDays(1)->setTime(9, 30),
                'location' => 'Conference Room A',
                'participants' => json_encode(['John Smith', 'Sarah Johnson', 'Mike Davis', 'Lisa Wilson']),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Client Presentation - Q4 Results',
                'description' => 'Present quarterly results and discuss next quarter objectives with key client stakeholders.',
                'start_time' => Carbon::now()->addDays(3)->setTime(14, 0),
                'end_time' => Carbon::now()->addDays(3)->setTime(15, 30),
                'location' => 'Zoom Meeting',
                'meeting_link' => 'https://zoom.us/j/123456789',
                'participants' => json_encode(['Client: Robert Brown', 'Jennifer Lee', 'Our Team: ' . $user->name, 'Sarah Johnson']),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Project Kickoff - New Initiative',
                'description' => 'Kickoff meeting for the new customer engagement platform project. Discuss scope, timeline, and resource allocation.',
                'start_time' => Carbon::now()->addDays(5)->setTime(10, 0),
                'end_time' => Carbon::now()->addDays(5)->setTime(11, 30),
                'location' => 'Main Conference Room',
                'participants' => json_encode(['Project Team: ' . $user->name, 'Sarah Johnson', 'Mike Davis', 'Lisa Wilson', 'Tom Anderson']),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Budget Review Meeting',
                'description' => 'Review current budget status and discuss allocation for next quarter. Address any budget concerns and approve new expenditures.',
                'start_time' => Carbon::now()->addDays(7)->setTime(15, 0),
                'end_time' => Carbon::now()->addDays(7)->setTime(16, 0),
                'location' => 'Executive Conference Room',
                'participants' => json_encode(['Finance Team: ' . $user->name, 'CFO', 'Department Heads']),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Completed: Monthly All-Hands Meeting',
                'description' => 'Monthly company-wide meeting to share updates, celebrate achievements, and discuss company direction.',
                'start_time' => Carbon::now()->subDays(2)->setTime(10, 0),
                'end_time' => Carbon::now()->subDays(2)->setTime(11, 0),
                'location' => 'Main Auditorium',
                'participants' => json_encode(['All Employees']),
                'status' => 'completed',
            ],
            [
                'title' => 'In Progress: Technical Architecture Review',
                'description' => 'Review current technical architecture and discuss improvements for scalability and performance.',
                'start_time' => Carbon::now()->setTime(13, 0),
                'end_time' => Carbon::now()->setTime(14, 30),
                'location' => 'Tech Conference Room',
                'participants' => json_encode(['Tech Team: ' . $user->name, 'Lead Architects', 'Senior Developers']),
                'status' => 'in_progress',
            ],
            [
                'title' => 'Cancelled: Vendor Meeting',
                'description' => 'Meeting with potential vendor to discuss partnership opportunities. Cancelled due to scheduling conflicts.',
                'start_time' => Carbon::now()->addDays(2)->setTime(11, 0),
                'end_time' => Carbon::now()->addDays(2)->setTime(12, 0),
                'location' => 'Meeting Room B',
                'participants' => json_encode(['Vendor Rep', $user->name, 'Procurement Team']),
                'status' => 'cancelled',
            ],
            [
                'title' => 'Strategy Planning Session',
                'description' => 'Annual strategy planning session to define goals and objectives for the upcoming year.',
                'start_time' => Carbon::now()->addDays(10)->setTime(9, 0),
                'end_time' => Carbon::now()->addDays(10)->setTime(17, 0),
                'location' => 'Off-site Venue',
                'participants' => json_encode(['Executive Team', 'Department Heads', $user->name]),
                'status' => 'scheduled',
            ]
        ];

        foreach ($meetings as $meetingData) {
            Meeting::create([
                'user_id' => $user->id,
                'title' => $meetingData['title'],
                'description' => $meetingData['description'],
                'start_time' => $meetingData['start_time'],
                'end_time' => $meetingData['end_time'],
                'location' => $meetingData['location'],
                'meeting_link' => $meetingData['meeting_link'] ?? null,
                'participants' => $meetingData['participants'],
                'status' => $meetingData['status'],
            ]);
        }

        $this->command->info('Sample meetings created successfully!');
    }
}