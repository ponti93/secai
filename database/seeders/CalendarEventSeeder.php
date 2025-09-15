<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class CalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        // Create events for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample calendar events
        $events = [
            [
                'title' => 'Team Meeting',
                'description' => 'Weekly team standup to discuss progress and upcoming tasks.',
                'start_time' => Carbon::now()->addDays(1)->setTime(9, 0),
                'end_time' => Carbon::now()->addDays(1)->setTime(10, 0),
                'location' => 'Conference Room A',
                'attendees' => json_encode(['John Smith', 'Sarah Johnson', 'Mike Davis']),
                'status' => 'confirmed',
                'all_day' => false,
            ],
            [
                'title' => 'Client Presentation',
                'description' => 'Present quarterly results to key client stakeholders.',
                'start_time' => Carbon::now()->addDays(3)->setTime(14, 0),
                'end_time' => Carbon::now()->addDays(3)->setTime(15, 30),
                'location' => 'Zoom Meeting',
                'attendees' => json_encode(['Client: Robert Brown', 'Jennifer Lee', 'Our Team: ' . $user->name]),
                'status' => 'confirmed',
                'all_day' => false,
            ],
            [
                'title' => 'Project Deadline',
                'description' => 'Final submission deadline for Q4 project deliverables.',
                'start_time' => Carbon::now()->addDays(7)->setTime(17, 0),
                'end_time' => Carbon::now()->addDays(7)->setTime(17, 0),
                'location' => null,
                'attendees' => json_encode(['Project Team']),
                'status' => 'confirmed',
                'all_day' => true,
            ],
            [
                'title' => 'Completed: All-Hands Meeting',
                'description' => 'Monthly company-wide meeting to share updates and celebrate achievements.',
                'start_time' => Carbon::now()->subDays(2)->setTime(10, 0),
                'end_time' => Carbon::now()->subDays(2)->setTime(11, 0),
                'location' => 'Main Auditorium',
                'attendees' => json_encode(['All Employees']),
                'status' => 'confirmed',
                'all_day' => false,
            ],
            [
                'title' => 'In Progress: Technical Review',
                'description' => 'Review current technical architecture and discuss improvements.',
                'start_time' => Carbon::now()->setTime(13, 0),
                'end_time' => Carbon::now()->setTime(14, 30),
                'location' => 'Tech Conference Room',
                'attendees' => json_encode(['Tech Team: ' . $user->name, 'Lead Architects', 'Senior Developers']),
                'status' => 'tentative',
                'all_day' => false,
            ],
            [
                'title' => 'Cancelled: Vendor Meeting',
                'description' => 'Meeting with potential vendor cancelled due to scheduling conflicts.',
                'start_time' => Carbon::now()->addDays(2)->setTime(11, 0),
                'end_time' => Carbon::now()->addDays(2)->setTime(12, 0),
                'location' => 'Meeting Room B',
                'attendees' => json_encode(['Vendor Rep', $user->name, 'Procurement Team']),
                'status' => 'cancelled',
                'all_day' => false,
            ],
            [
                'title' => 'Strategy Planning Session',
                'description' => 'Annual strategy planning session to define goals for the upcoming year.',
                'start_time' => Carbon::now()->addDays(10)->setTime(9, 0),
                'end_time' => Carbon::now()->addDays(10)->setTime(17, 0),
                'location' => 'Off-site Venue',
                'attendees' => json_encode(['Executive Team', 'Department Heads', $user->name]),
                'status' => 'confirmed',
                'all_day' => false,
            ],
            [
                'title' => 'Holiday - Company Retreat',
                'description' => 'Annual company retreat for team building and relaxation.',
                'start_time' => Carbon::now()->addDays(15)->setTime(0, 0),
                'end_time' => Carbon::now()->addDays(17)->setTime(23, 59),
                'location' => 'Resort Location',
                'attendees' => json_encode(['All Employees']),
                'status' => 'confirmed',
                'all_day' => true,
            ]
        ];

        foreach ($events as $eventData) {
            CalendarEvent::create([
                'user_id' => $user->id,
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'start_time' => $eventData['start_time'],
                'end_time' => $eventData['end_time'],
                'location' => $eventData['location'],
                'attendees' => $eventData['attendees'],
                'status' => $eventData['status'],
                'all_day' => $eventData['all_day'],
            ]);
        }

        $this->command->info('Sample calendar events created successfully!');
    }
}