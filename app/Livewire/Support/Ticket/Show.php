<?php

namespace App\Livewire\Support\Ticket;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\IssueCategory;
use App\Models\User;
use Carbon\Carbon;
use League\Csv\Writer;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $category_id = '';
    public $daterange = '';
    public $export = false;
    public $tId = '';
    public $newStatus = '';

    public $listeners = ['updateTicketStatus' => 'updateStatus', 'deleteTicket' => 'deleteTicket', 'updateStatus' => 'updateStatus'];

    public function mount()
    {
        // يمكن إضافة أي تهيئة مطلوبة هنا
    }

    public function render()
    {
        $tickets = Ticket::with(['category', 'user'])
            ->orderBy('opened_at', 'desc');

        // فلترة حسب البحث
        if ($this->search !== '') {
            $tickets->where(function($query) {
                $query->where('ticket_number', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('description', 'LIKE', '%' . $this->search . '%')
                      ->orWhereHas('user', function($q) {
                          $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                            ->orWhere('email', 'LIKE', '%' . $this->search . '%');
                      });
            });
        }

        // فلترة حسب الحالة
        if ($this->status !== '') {
            $tickets->where('status', $this->status);
        }

        // فلترة حسب التصنيف
        if ($this->category_id !== '') {
            $tickets->where('issue_category_id', $this->category_id);
        }

        // فلترة حسب التاريخ
        if ($this->daterange !== '') {
            $dates = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            if (isset($dates[1])) {
                $endDate = date('Y-m-d', strtotime($dates[1]));
                $tickets->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
            }
        }

        $tickets = $tickets->paginate(15);
        $categories = IssueCategory::all();

        return view('livewire.admin.tickets.index', [
            'tickets' => $tickets,
            'categories' => $categories
        ])->layout('components.layouts.support-dashboard');
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->status = '';
        $this->category_id = '';
        $this->daterange = '';
    }

        public function updateStatus($id, $status)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $status]);

        $this->dispatch('success', 'Ticket status updated successfully!');
    }

    public function deleteTicket($id)
    {
        Ticket::findOrFail($id)->delete();
        $this->dispatch('success', 'Ticket deleted successfully!');
    }

    public function exportData()
    {
        $data = Ticket::with(['category', 'user'])
            ->orderBy('opened_at', 'desc');

        if ($this->search !== '') {
            $data->where(function($query) {
                $query->where('ticket_number', 'LIKE', '%' . $this->search . '%')
                      ->orWhere('description', 'LIKE', '%' . $this->search . '%')
                      ->orWhereHas('user', function($q) {
                          $q->where('first_name', 'LIKE', '%' . $this->search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $this->search . '%')
                            ->orWhere('email', 'LIKE', '%' . $this->search . '%');
                      });
            });
        }

        if ($this->status !== '') {
            $data->where('status', $this->status);
        }

        if ($this->category_id !== '') {
            $data->where('issue_category_id', $this->category_id);
        }

        if ($this->daterange !== '') {
            $dates = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            if (isset($dates[1])) {
                $endDate = date('Y-m-d', strtotime($dates[1]));
                $data->whereDate('created_at', '>=', $startDate)
                     ->whereDate('created_at', '<=', $endDate);
            }
        }

        $data = $data->get();

        $columns = ['Ticket Number', 'Category', 'User', 'Status', 'Description', 'Opened At', 'Created At'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($columns);

        foreach ($data as $row) {
            $single = [
                $row->ticket_number,
                $row->category ? $row->category->name : 'N/A',
                $row->category ? $row->category->name_ar : 'N/A',
                $row->user ? $row->user->first_name . ' ' . $row->user->last_name : 'N/A',
                ucfirst($row->status),
                $row->description ?: 'N/A',
                $row->opened_at ? $row->opened_at->format('Y-m-d H:i:s') : 'N/A',
                $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A'
            ];
            $csv->insertOne($single);
        }

        $filename = 'tickets_' . date('Y-m-d') . '.csv';
        $file = fopen('php://temp', 'w');
        fwrite($file, $csv->getContent());
        rewind($file);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, $headers);
    }

    public function gotoDetails($ticket_number)
    {
        return redirect('support/tickets/' . $ticket_number);
    }
}
