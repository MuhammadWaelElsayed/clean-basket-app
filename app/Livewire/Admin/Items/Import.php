<?php
namespace App\Livewire\Admin\Items;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ItemsImport;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Component
{
    use WithFileUploads;

    public $file;

    public function render()
    {
        return view('livewire.admin.items.import')
            ->layout('components.layouts.admin-dashboard');
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new ItemsImport, $this->file->getRealPath());

            $this->dispatch('success', 'Items imported successfully!');
            return redirect()->route('admin.items');

        } catch (\Exception $e) {
            $this->dispatch('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
