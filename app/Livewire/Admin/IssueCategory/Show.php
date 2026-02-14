<?php

namespace App\Livewire\Admin\IssueCategory;

use Livewire\Component;
use App\Models\IssueCategory;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $search = '';
    public $cId = '';
    public $dId = '';
    public $editName = '';
    public $newName = '';
    public $newNameAr = '';
    public $showAddModal = false;

    public $listeners = ['updateCategory' => 'updateCategory', 'deleteCategory' => 'deleteCategory', 'createCategory' => 'createCategory'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_ticket'), 403);
        // يمكن إضافة أي تهيئة مطلوبة هنا
    }

    public function render()
    {
        $categories = IssueCategory::orderBy('id', 'desc');

        if ($this->search !== '') {
            $categories->where('name', 'LIKE', '%' . $this->search . '%')
            ->orWhere('name_ar', 'LIKE', '%' . $this->search . '%');
        }

        $categories = $categories->paginate(15);

        return view('livewire.admin.issue-categories.index', [
            'categories' => $categories
        ])->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter()
    {
        $this->search = '';
    }

    public function updateCategory($id, $name, $nameAr)
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editNameAr' => 'required|string|max:255'
        ]);

        $category = IssueCategory::findOrFail($id);
        $category->update(['name' => $name, 'name_ar' => $nameAr]);

        $this->dispatch('success', 'Category updated successfully!');
    }

    public function deleteCategory($id)
    {
        $category = IssueCategory::findOrFail($id);

        // تحقق من وجود تذاكر مرتبطة
        if ($category->tickets()->count() > 0) {
            $this->dispatch('error', 'Cannot delete category with associated tickets!');
            return;
        }

        $category->delete();
        $this->dispatch('success', 'Category deleted successfully!');
    }

    public function createCategory($name = null)
    {
        if ($name === null) {
            $name = $this->newName;
        }

        $this->validate([
            'newName' => 'required|string|max:255|unique:issue_categories,name',
            'newNameAr' => 'required|string|max:255|unique:issue_categories,name_ar'
        ]);

        IssueCategory::create(['name' => $name, 'name_ar' => $this->newNameAr]);
        $this->newName = '';
        $this->newNameAr = '';
        $this->showAddModal = false;
        $this->dispatch('success', 'Category created successfully!');
    }
}
