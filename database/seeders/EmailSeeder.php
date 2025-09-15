<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Email;

class EmailSeeder extends Seeder
{
    public function run(): void
    {
        // Create emails for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample emails
        $emails = [
            [
                'subject' => 'Project Update - Q4 Planning',
                'from_email' => $user->email,
                'to_email' => 'team@company.com',
                'cc_emails' => 'manager@company.com',
                'content' => 'Hi Team,

I wanted to provide an update on our Q4 planning progress. We have successfully completed the initial phase and are now moving into the implementation stage.

Key highlights:
- Budget approved for all planned initiatives
- Timeline is on track
- Team resources allocated

Please let me know if you have any questions.

Best regards,
' . $user->name,
                'status' => 'sent',
                'is_read' => true,
                'is_important' => true,
            ],
            [
                'subject' => 'Meeting Reminder - Client Presentation',
                'from_email' => $user->email,
                'to_email' => 'client@example.com',
                'content' => 'Dear Client,

This is a friendly reminder about our upcoming presentation scheduled for tomorrow at 2:00 PM.

Agenda:
1. Project overview
2. Timeline discussion
3. Budget review
4. Q&A session

Please confirm your attendance.

Best regards,
' . $user->name,
                'status' => 'sent',
                'is_read' => true,
                'is_important' => false,
            ],
            [
                'subject' => 'Draft: Weekly Report Template',
                'from_email' => $user->email,
                'to_email' => 'reports@company.com',
                'content' => 'Hi,

I am working on a new weekly report template that will help streamline our reporting process. This is still a draft and needs some refinement.

Key sections to include:
- Project status
- Budget updates
- Risk assessment
- Next week priorities

I will finalize this by end of week.

Thanks,
' . $user->name,
                'status' => 'draft',
                'is_read' => false,
                'is_important' => false,
            ],
            [
                'subject' => 'Follow up on Invoice #12345',
                'from_email' => $user->email,
                'to_email' => 'billing@vendor.com',
                'content' => 'Hello,

I am following up on invoice #12345 which was submitted on October 15th. The payment was supposed to be processed within 30 days, but I have not received confirmation yet.

Could you please check the status and let me know when I can expect the payment?

Invoice details:
- Amount: $2,500.00
- Due date: November 14th
- Reference: INV-12345

Thank you for your attention to this matter.

Best regards,
' . $user->name,
                'status' => 'sent',
                'is_read' => true,
                'is_important' => true,
            ],
            [
                'subject' => 'Draft: Holiday Schedule Announcement',
                'from_email' => $user->email,
                'to_email' => 'all@company.com',
                'content' => 'Dear Team,

I wanted to share the holiday schedule for the upcoming season. Please review and let me know if you have any conflicts.

Holiday Schedule:
- December 24th: Early closure (2 PM)
- December 25th: Closed
- December 26th: Closed
- January 1st: Closed

Please plan your work accordingly and ensure all critical tasks are completed before the holidays.

Happy holidays!
' . $user->name,
                'status' => 'draft',
                'is_read' => false,
                'is_important' => false,
            ],
            [
                'subject' => 'Thank you for the meeting',
                'from_email' => $user->email,
                'to_email' => 'partner@business.com',
                'content' => 'Dear Partner,

Thank you for taking the time to meet with us yesterday. It was a productive discussion and I believe we have a solid foundation for our collaboration.

As discussed, I will send over the detailed proposal by end of this week. Please don\'t hesitate to reach out if you have any questions in the meantime.

Looking forward to working together.

Best regards,
' . $user->name,
                'status' => 'sent',
                'is_read' => true,
                'is_important' => false,
            ]
        ];

        foreach ($emails as $emailData) {
            Email::create([
                'user_id' => $user->id,
                'subject' => $emailData['subject'],
                'from_email' => $emailData['from_email'],
                'to_email' => $emailData['to_email'],
                'cc_emails' => $emailData['cc_emails'] ?? null,
                'content' => $emailData['content'],
                'status' => $emailData['status'],
                'is_read' => $emailData['is_read'],
                'is_important' => $emailData['is_important'],
            ]);
        }

        $this->command->info('Sample emails created successfully!');
    }
}