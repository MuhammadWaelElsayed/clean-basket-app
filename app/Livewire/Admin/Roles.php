<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    public $name = '';
    public $editingRoleId = null;
    public $editingName = '';

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.roles', compact('roles'))->layout('components.layouts.admin-dashboard');
    }

    public function create()
    {
        $this->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $this->name, 'guard_name' => 'admin']);
        $this->name = '';
        $this->dispatch('success', 'Role created successfully!');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->editingRoleId = $id;
        $this->editingName = $role->name;
    }

    public function update()
    {
        $this->validate(['editingName' => 'required|unique:roles,name,' . $this->editingRoleId]);
        $role = Role::findOrFail($this->editingRoleId);

        if ($role->name === 'super_admin' || $role->name === 'supervisor') {
            $this->dispatch('error', 'Cannot delete super_admin,supervisor role!');
            return;
        }

        $role->name = $this->editingName;
        $role->save();
        $this->editingRoleId = null;
        $this->dispatch('success', 'Role updated successfully!');
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'super_admin' || $role->name === 'supervisor') {
            $this->dispatch('error', 'Cannot delete super_admin,supervisor role!');
            return;
        }
        $role->delete();
        $this->dispatch('success', 'Role deleted successfully!');
    }
}
