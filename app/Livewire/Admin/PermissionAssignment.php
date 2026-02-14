<?php

namespace App\Livewire\Admin;

use App\Console\Commands\AssignSuperAdminPermissions;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAssignment extends Component
{
    public $selectedRole = null;
    public $permissions = [];
    public $groupedPermissions = [];

    public $models = [
        'partner', 'driver', 'item', 'working_hours', 'order', 'external_driver',
        'customer', 'discount', 'service', 'city', 'page', 'banner', 'onboard', 'ticket'
    ];

    public $actions = ['list', 'view', 'create', 'update', 'delete'];

    public $specialPermissions = [];

    public function mount()
    {
       $this->specialPermissions = app()->make(AssignSuperAdminPermissions::class)->getSpecialPermissions();

       $this->groupedPermissions = [];

        foreach ($this->models as $model) {
            $this->groupedPermissions[$model] = [];
            foreach ($this->actions as $action) {
                $perm = "{$action}_{$model}";
                if (Permission::where('name', $perm)->exists()) {
                    $this->groupedPermissions[$model][] = $perm;
                }
            }
        }

        $this->groupedPermissions['special'] = [];
        foreach ($this->specialPermissions as $special) {
            if (Permission::where('name', $special)->exists()) {
                $this->groupedPermissions['special'][] = $special;
            }
        }
    }

    public function render()
    {
        $roles = Role::all();
        $roles = $roles->filter(function ($role) {
            return $role->name !== 'super_admin';
        });
        return view('livewire.admin.permission-assignment', compact('roles'))->layout('components.layouts.admin-dashboard');
    }

    public function updatedSelectedRole()
    {
        if ($this->selectedRole) {
            $role = Role::findOrFail($this->selectedRole);
            $this->permissions = $role->permissions->pluck('name')->toArray();
        } else {
            $this->permissions = [];
        }
    }

    public function assign()
    {
        $this->validate(['selectedRole' => 'required|exists:roles,id']);
        $role = Role::findOrFail($this->selectedRole);
        $role->syncPermissions($this->permissions);
        $this->dispatch('success', 'Permissions assigned successfully!');
    }
}
