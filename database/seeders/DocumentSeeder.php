<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Create documents for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample documents
        $documents = [
            [
                'title' => 'Q4 Project Report',
                'type' => 'report',
                'content' => 'Executive Summary

This report provides a comprehensive overview of our Q4 project performance and achievements. The quarter has been marked by significant progress across all key initiatives.

Key Achievements:
- Successfully completed 95% of planned deliverables
- Exceeded budget efficiency targets by 12%
- Maintained 98% client satisfaction rating
- Implemented 3 new process improvements

Financial Performance:
- Total project cost: $125,000
- Budget variance: -$8,500 (under budget)
- ROI: 145%

Recommendations:
1. Continue current project management approach
2. Expand team capacity for Q1 initiatives
3. Implement additional automation tools

Next Steps:
- Begin Q1 planning phase
- Conduct team performance review
- Update project templates based on lessons learned

Prepared by: ' . $user->name . '
Date: ' . now()->format('F j, Y'),
                'status' => 'published',
            ],
            [
                'title' => 'Client Meeting Minutes - December 15',
                'type' => 'minutes',
                'content' => 'Meeting Minutes

Date: December 15, 2024
Time: 2:00 PM - 3:30 PM
Location: Conference Room A / Zoom
Attendees: John Smith (Client), Sarah Johnson (Project Manager), Mike Davis (Technical Lead)

Agenda Items:

1. Project Status Update
   - Current phase: 75% complete
   - On track for January 15th delivery
   - No major blockers identified

2. Budget Review
   - Remaining budget: $25,000
   - No additional funds required
   - Cost savings identified in testing phase

3. Next Milestone
   - Beta testing begins January 5th
   - Client feedback session scheduled for January 20th
   - Final delivery: January 30th

Action Items:
- Sarah: Prepare beta testing documentation
- Mike: Complete integration testing by January 3rd
- John: Review and approve test cases

Next Meeting: January 10th, 2024 at 2:00 PM

Minutes prepared by: ' . $user->name,
                'status' => 'published',
            ],
            [
                'title' => 'Partnership Proposal - TechCorp',
                'type' => 'proposal',
                'content' => 'Partnership Proposal

To: TechCorp Leadership Team
From: ' . $user->name . '
Date: ' . now()->format('F j, Y') . '

Executive Summary

We propose a strategic partnership between our organizations to leverage complementary strengths and create mutual value in the technology services market.

Partnership Benefits:

For TechCorp:
- Access to our specialized development team
- Reduced time-to-market for new products
- Shared R&D costs and risks
- Expanded market reach

For Our Organization:
- Access to TechCorp\'s established client base
- Technology platform integration opportunities
- Brand recognition and credibility
- Revenue sharing opportunities

Proposed Structure:
- 12-month initial agreement with renewal options
- Joint project development initiatives
- Shared marketing and sales efforts
- Regular strategic review meetings

Financial Terms:
- 60/40 revenue split on joint projects
- Shared development costs
- Performance-based bonuses

Timeline:
- Proposal review: 2 weeks
- Due diligence: 4 weeks
- Contract negotiation: 2 weeks
- Implementation: 6 weeks

We believe this partnership will create significant value for both organizations and look forward to discussing this opportunity further.

Best regards,
' . $user->name,
                'status' => 'draft',
            ],
            [
                'title' => 'Letter of Recommendation - Sarah Johnson',
                'type' => 'letter',
                'content' => 'Letter of Recommendation

To Whom It May Concern:

I am writing to highly recommend Sarah Johnson for any position she may be seeking. I have had the pleasure of working with Sarah for the past three years in her role as Senior Project Manager.

Sarah\'s Qualifications:

Professional Excellence:
- Consistently delivers projects on time and within budget
- Excellent communication and leadership skills
- Strong problem-solving and analytical abilities
- Proven track record of managing complex, multi-stakeholder projects

Leadership Qualities:
- Natural ability to motivate and guide team members
- Effective conflict resolution skills
- Strategic thinking and planning capabilities
- Adaptable to changing business requirements

Specific Achievements:
- Led 15+ successful project implementations
- Improved team efficiency by 25%
- Maintained 99% client satisfaction rating
- Mentored 5 junior project managers

Personal Attributes:
- Highly reliable and trustworthy
- Strong work ethic and dedication
- Collaborative and team-oriented
- Continuous learner and self-improver

I have no hesitation in recommending Sarah for any position that requires strong project management skills, leadership abilities, and professional excellence. She would be a valuable asset to any organization.

Please feel free to contact me if you need any additional information.

Sincerely,
' . $user->name . '
Project Director
Phone: (555) 123-4567
Email: ' . $user->email,
                'status' => 'published',
            ],
            [
                'title' => 'Draft: Annual Budget Planning',
                'type' => 'report',
                'content' => 'Annual Budget Planning - Draft

This is a preliminary draft of our annual budget planning document. Key areas to be finalized:

Department Budgets:
- IT Infrastructure: $150,000
- Personnel: $800,000
- Marketing: $75,000
- Operations: $200,000
- Research & Development: $300,000

Capital Expenditures:
- New server equipment: $50,000
- Office renovation: $25,000
- Software licenses: $30,000

Revenue Projections:
- Q1: $500,000
- Q2: $550,000
- Q3: $600,000
- Q4: $650,000

Key Assumptions:
- 15% growth in client base
- 5% increase in average project value
- Stable market conditions
- No major economic disruptions

Risk Factors:
- Economic uncertainty
- Increased competition
- Technology changes
- Regulatory changes

This draft needs further review and approval from the executive team.

Prepared by: ' . $user->name . '
Status: Draft - Under Review',
                'status' => 'draft',
            ]
        ];

        foreach ($documents as $documentData) {
            Document::create([
                'user_id' => $user->id,
                'title' => $documentData['title'],
                'type' => $documentData['type'],
                'content' => $documentData['content'],
                'status' => $documentData['status'],
            ]);
        }

        $this->command->info('Sample documents created successfully!');
    }
}