<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterAction = 'all';
    public $filterUserId = 'all';

    public $showDetailModal = false;
    public $selectedLog = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openDetailModal($id)
    {
        $this->selectedLog = ActivityLog::with('user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function clearOldLogs()
    {
        $count = ActivityLog::where('created_at', '<', now()->subDays(30))->count();
        ActivityLog::where('created_at', '<', now()->subDays(30))->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Cleared Audit Logs',
            'subject_type' => ActivityLog::class,
            'subject_id' => 0,
            'description' => "Purged {$count} audit log records older than 30 days.",
        ]);

        session()->flash('message', "{$count} old activity log records purged.");
    }

    public function render()
    {
        $query = ActivityLog::with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('action', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', '%' . $this->search . '%')->orWhere('login_id', 'LIKE', '%' . $this->search . '%'));
            });
        }

        if ($this->filterAction !== 'all') {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterUserId !== 'all') {
            $query->where('user_id', $this->filterUserId);
        }

        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $users = User::whereIn('id', ActivityLog::select('user_id')->distinct())->get();

        return view('livewire.admin.activity-log-management', [
            'logs' => $query->orderBy('created_at', 'desc')->paginate(15),
            'actions' => $actions,
            'users' => $users,
        ])->layout('layouts.admin', ['title' => 'System Audit Trail & Activity Logs', 'subtitle' => 'Immutable audit logging feed tracking administrative and faculty actions']);
    }
}
