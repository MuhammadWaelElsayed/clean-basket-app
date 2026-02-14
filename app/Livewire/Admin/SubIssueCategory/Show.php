<?php

namespace App\Livewire\Admin\SubIssueCategory;

use Livewire\Component;
use App\Models\IssueCategory;
use App\Models\SubIssueCategory;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = '';
    public $editId = '';
    public $editName = '';
    public $newName = '';
    public $newNameAr = '';
    public $selectedCategory = null;

    public $listeners = ['updateSubCategory' => 'updateSubCategory', 'deleteSubCategory' => 'deleteSubCategory', 'createSubCategory' => 'createSubCategory'];

    public function mount($category_id = null)
    {
        abort_unless(auth()->user()->can('list_ticket'), 403);

        if ($category_id) {
            $this->category_id = $category_id;
            $this->selectedCategory = IssueCategory::find($category_id);
        }
    }

    public function render()
    {
        $query = SubIssueCategory::with('mainCategory')->orderBy('id', 'desc');

        // فلترة حسب التصنيف الرئيسي
        if ($this->category_id) {
            $query->where('issue_category_id', $this->category_id);
        }

        // فلترة حسب البحث
        if ($this->search !== '') {
            $query->where('name', 'LIKE', '%' . $this->search . '%')
            ->orWhere('name_ar', 'LIKE', '%' . $this->search . '%');
        }

        $subCategories = $query->paginate(15);
        $categories = IssueCategory::all();

        return view('livewire.admin.sub-issue-categories.index', [
            'subCategories' => $subCategories,
            'categories' => $categories
        ])->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->category_id = '';
    }

    public function updateSubCategory($id, $name, $nameAr)
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editNameAr' => 'required|string|max:255'
        ]);

        $subCategory = SubIssueCategory::findOrFail($id);
        $subCategory->update(['name' => $name, 'name_ar' => $nameAr]);

        $this->dispatch('success', 'Sub Category updated successfully!');
    }

    public function deleteSubCategory($id)
    {
        $subCategory = SubIssueCategory::findOrFail($id);

        // تحقق من وجود تذاكر مرتبطة
        if ($subCategory->tickets()->count() > 0) {
            $this->dispatch('error', 'Cannot delete sub category with associated tickets!');
            return;
        }

        $subCategory->delete();
        $this->dispatch('success', 'Sub Category deleted successfully!');
    }

    public function createSubCategory($name = null, $nameAr = null)
    {
        if ($name === null && $nameAr === null) {
            $name = $this->newName;
            $nameAr = $this->newNameAr;
        }

        $this->validate([
            'newName' => 'required|string|max:255',
            'newNameAr' => 'required|string|max:255',
            'category_id' => 'required|exists:issue_categories,id'
        ]);

        SubIssueCategory::create([
            'name' => $name,
            'name_ar' => $nameAr,
            'issue_category_id' => $this->category_id
        ]);

        $this->newName = '';
        $this->newNameAr = '';
         $this->dispatch('success', 'Sub Category created successfully!');
    }

    public function exportData()
    {
        $query = SubIssueCategory::with('mainCategory')->orderBy('id', 'desc');

        if ($this->category_id) {
            $query->where('issue_category_id', $this->category_id);
        }

        if ($this->search !== '') {
            $query->where('name', 'LIKE', '%' . $this->search . '%')
            ->orWhere('name_ar', 'LIKE', '%' . $this->search . '%');
        }

        $data = $query->get();

        $columns = ['ID', 'Name', 'Name Ar', 'Main Category', 'Created At'];

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($columns);

        foreach ($data as $row) {
            $single = [
                $row->id,
                $row->name,
                $row->name_ar,
                $row->mainCategory ? $row->mainCategory->name_ar : 'N/A',
                $row->mainCategory ? $row->mainCategory->name : 'N/A',
                $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A'
            ];
            $csv->insertOne($single);
        }

        $filename = 'sub_categories_' . date('Y-m-d') . '.csv';
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
}
