<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = [
                'emails' => [
                    'total' => $user->emails()->count(),
                    'unread' => $user->emails()->unread()->count(),
                    'today' => $user->emails()->whereDate('created_at', today())->count(),
                    'trend' => 'up',
                    'percentage' => 12,
                ],
                'meetings' => [
                    'total' => $user->meetings()->count(),
                    'today' => $user->meetings()->today()->count(),
                    'upcoming' => $user->meetings()->upcoming()->count(),
                    'trend' => 'up',
                    'percentage' => 8,
                ],
                'documents' => [
                    'total' => $user->documents()->count(),
                    'drafts' => $user->documents()->byStatus('draft')->count(),
                    'pending' => $user->documents()->byStatus('review')->count(),
                    'trend' => 'up',
                    'percentage' => 15,
                ],
                'inventory' => [
                    'total' => $user->inventory()->count(),
                    'low_stock' => $user->inventory()->lowStock()->count(),
                    'out_of_stock' => $user->inventory()->where('quantity', 0)->count(),
                    'trend' => 'down',
                    'percentage' => 5,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $activities = [];

            // Recent emails
            $recentEmails = $user->emails()
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentEmails as $email) {
                $activities[] = [
                    'id' => $email->id,
                    'type' => 'email',
                    'title' => 'Email: ' . $email->subject,
                    'description' => substr($email->content, 0, 100) . '...',
                    'timestamp' => $email->created_at->diffForHumans(),
                    'icon' => 'email',
                ];
            }

            // Recent meetings
            $recentMeetings = $user->meetings()
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentMeetings as $meeting) {
                $activities[] = [
                    'id' => $meeting->id,
                    'type' => 'meeting',
                    'title' => 'Meeting: ' . $meeting->title,
                    'description' => $meeting->description ? substr($meeting->description, 0, 100) . '...' : 'No description',
                    'timestamp' => $meeting->created_at->diffForHumans(),
                    'icon' => 'meeting',
                ];
            }

            // Recent documents
            $recentDocuments = $user->documents()
                ->latest()
                ->limit(5)
                ->get();

            foreach ($recentDocuments as $document) {
                $activities[] = [
                    'id' => $document->id,
                    'type' => 'document',
                    'title' => 'Document: ' . $document->title,
                    'description' => substr($document->content, 0, 100) . '...',
                    'timestamp' => $document->created_at->diffForHumans(),
                    'icon' => 'document',
                ];
            }

            // Sort by timestamp
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return response()->json([
                'success' => true,
                'data' => array_slice($activities, 0, 10)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quick actions
     */
    public function getQuickActions(Request $request): JsonResponse
    {
        try {
            $quickActions = [
                [
                    'id' => 'compose-email',
                    'title' => 'Compose Email',
                    'description' => 'Draft with AI assistance',
                    'icon' => 'email',
                    'url' => '/emails/compose',
                    'color' => 'primary',
                ],
                [
                    'id' => 'schedule-meeting',
                    'title' => 'Schedule Meeting',
                    'description' => 'Create with Meet link',
                    'icon' => 'calendar',
                    'url' => '/calendar/create',
                    'color' => 'success',
                ],
                [
                    'id' => 'upload-meeting',
                    'title' => 'Upload Meeting',
                    'description' => 'Audio transcription',
                    'icon' => 'upload',
                    'url' => '/meetings/upload',
                    'color' => 'info',
                ],
                [
                    'id' => 'new-document',
                    'title' => 'New Document',
                    'description' => 'AI-assisted drafting',
                    'icon' => 'document',
                    'url' => '/documents/create',
                    'color' => 'warning',
                ],
                [
                    'id' => 'add-expense',
                    'title' => 'Add Expense',
                    'description' => 'Track with AI categorization',
                    'icon' => 'receipt',
                    'url' => '/expenses/create',
                    'color' => 'secondary',
                ],
                [
                    'id' => 'manage-inventory',
                    'title' => 'Manage Inventory',
                    'description' => 'AI-powered suggestions',
                    'icon' => 'inventory',
                    'url' => '/inventory',
                    'color' => 'dark',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $quickActions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quick actions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
