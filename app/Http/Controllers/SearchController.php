<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\CalendarEvent;
use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Perform global search across all modules
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $userId = Auth::id();
        
        if (empty($query)) {
            return response()->json([
                'emails' => [],
                'documents' => [],
                'meetings' => [],
                'events' => [],
                'expenses' => [],
                'inventory' => []
            ]);
        }

        $results = [
            'emails' => $this->searchEmails($query, $userId),
            'documents' => $this->searchDocuments($query, $userId),
            'meetings' => $this->searchMeetings($query, $userId),
            'events' => $this->searchEvents($query, $userId),
            'expenses' => $this->searchExpenses($query, $userId),
            'inventory' => $this->searchInventory($query, $userId)
        ];

        return response()->json($results);
    }

    /**
     * Search emails
     */
    private function searchEmails($query, $userId)
    {
        return Email::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('from_email', 'like', "%{$query}%")
                  ->orWhere('to_email', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'subject', 'from_email', 'to_email', 'created_at'])
            ->map(function($email) {
                return [
                    'id' => $email->id,
                    'title' => $email->subject,
                    'subtitle' => "From: {$email->from_email}",
                    'url' => route('emails.show', $email),
                    'type' => 'email',
                    'date' => $email->created_at->format('M j, Y')
                ];
            });
    }

    /**
     * Search documents
     */
    private function searchDocuments($query, $userId)
    {
        return Document::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'type', 'created_at'])
            ->map(function($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'subtitle' => ucfirst($document->type),
                    'url' => route('documents.show', $document),
                    'type' => 'document',
                    'date' => $document->created_at->format('M j, Y')
                ];
            });
    }

    /**
     * Search meetings
     */
    private function searchMeetings($query, $userId)
    {
        return Meeting::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'start_time', 'location'])
            ->map(function($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'subtitle' => $meeting->location ? "📍 {$meeting->location}" : "📅 " . $meeting->start_time->format('M j, Y g:i A'),
                    'url' => route('meetings.show', $meeting),
                    'type' => 'meeting',
                    'date' => $meeting->start_time->format('M j, Y')
                ];
            });
    }

    /**
     * Search calendar events
     */
    private function searchEvents($query, $userId)
    {
        return CalendarEvent::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            })
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'start_time', 'location'])
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'subtitle' => $event->location ? "📍 {$event->location}" : "📅 " . $event->start_time->format('M j, Y g:i A'),
                    'url' => route('calendar.show', $event),
                    'type' => 'event',
                    'date' => $event->start_time->format('M j, Y')
                ];
            });
    }

    /**
     * Search expenses
     */
    private function searchExpenses($query, $userId)
    {
        return Expense::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('merchant', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'description', 'amount', 'category', 'created_at'])
            ->map(function($expense) {
                return [
                    'id' => $expense->id,
                    'title' => $expense->description,
                    'subtitle' => "💰 $" . number_format($expense->amount, 2) . " - " . ucfirst($expense->category),
                    'url' => route('expenses.show', $expense),
                    'type' => 'expense',
                    'date' => $expense->created_at->format('M j, Y')
                ];
            });
    }

    /**
     * Search inventory
     */
    private function searchInventory($query, $userId)
    {
        return Inventory::where('user_id', $userId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'quantity', 'category', 'created_at'])
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'subtitle' => "📦 Qty: {$item->quantity} - " . ucfirst($item->category),
                    'url' => route('inventory.show', $item),
                    'type' => 'inventory',
                    'date' => $item->created_at->format('M j, Y')
                ];
            });
    }
}