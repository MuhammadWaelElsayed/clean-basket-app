<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminManagement extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $selectedRoles = [];

    public $editingAdminId = null;
    public $editingName = '';
    public $editingEmail = '';
    public $editingPassword = '';
    public $editingSelectedRoles = [];

    public function render()
    {
        $admins = Admin::with('roles')->get();
        $roles = Role::all();
        return view('livewire.admin.admin-management', compact('admins', 'roles'))->layout('components.layouts.admin-dashboard');
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:6',
            'selectedRoles' => 'array',
        ]);

        $admin = Admin::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        if (!empty($this->selectedRoles)) {
            $admin->syncRoles($this->selectedRoles);
        }

        $this->reset(['name', 'email', 'password', 'selectedRoles']);
        $this->dispatch('success', 'Admin created successfully!');
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $this->editingAdminId = $id;
        $this->editingName = $admin->name ?? '';
        $this->editingEmail = $admin->email;
        $this->editingPassword = '';
        $this->editingSelectedRoles = $admin->roles->pluck('name')->toArray();
    }

    public function update()
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingEmail' => 'required|email|unique:admin_users,email,' . $this->editingAdminId,
            'editingPassword' => 'nullable|min:6',
            'editingSelectedRoles' => 'array',
        ]);

        $admin = Admin::findOrFail($this->editingAdminId);
        $admin->name = $this->editingName;
        $admin->email = $this->editingEmail;
        if ($this->editingPassword) {
            $admin->password = Hash::make($this->editingPassword);
        }
        $admin->save();

        $admin->syncRoles($this->editingSelectedRoles);

        $this->editingAdminId = null;
        $this->dispatch('success', 'Admin updated successfully!');
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);
        if ($admin->hasRole('super_admin')) {
            $this->dispatch('error', 'Cannot delete super admin!');
            return;
        }
        if ($admin->id == session('admin')?->id) {
            $this->dispatch('error', 'Cannot delete yourself!');
            return;
        }
        $admin->delete();
        $this->dispatch('success', 'Admin deleted successfully!');
    }
}
