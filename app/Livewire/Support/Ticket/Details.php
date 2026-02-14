<?php

namespace App\Livewire\Support\Ticket;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\IssueCategory;

class Details extends Component
{
    public $ticket;
    public $ticketId;
    public $newStatus = '';
    public $note = '';

    public function mount($ticket)
    {
        $this->ticket = Ticket::where('ticket_number', $ticket)->with(['category', 'subCategory', 'user', 'order'])->firstOrFail();
        $this->newStatus = $this->ticket->status;
        $this->note = $this->ticket->note;
    }

    public function render()
    {
        return view('livewire.admin.tickets.details', [
            'ticket' => $this->ticket
        ])->layout('components.layouts.support-dashboard');
    }

    public function updateStatus()
    {
        $this->ticket->update(['status' => $this->newStatus]);
        $this->dispatch('success', 'Ticket status updated successfully!');
    }

    public function updateStatusAndNotes()
    {
        try {
            $this->ticket->update([
                'status' => $this->newStatus,
                'note' => $this->note
            ]);

            // إرسال رسالة نجاح
            session()->flash('success', 'تم تحديث حالة التذكرة والملاحظات بنجاح!');

        } catch (\Exception $e) {
            // إرسال رسالة خطأ
            session()->flash('error', 'حدث خطأ أثناء تحديث التذكرة: ' . $e->getMessage());
        }
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'open' => 'badge badge-success',
            'pending' => 'badge badge-warning',
            'closed' => 'badge badge-secondary',
            default => 'badge badge-info'
        };
    }
}
