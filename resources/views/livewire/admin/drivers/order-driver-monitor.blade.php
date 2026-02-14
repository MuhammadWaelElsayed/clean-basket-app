<div wire:poll.{{ $refreshInterval }}s="loadData">
    <div class="driver-monitor">
        {{-- Header --}}
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="title">
                    @if($order)
                        Order #{{ $order->order_code }} - Driver Monitor
                    @else
                        Driver Request Monitor - Dashboard
                    @endif
                </h1>

                <div class="header-actions">
                    {{-- Auto Refresh Toggle --}}
                    <button
                        wire:click="toggleAutoRefresh"
                        class="btn {{ $autoRefresh ? 'btn-success' : 'btn-secondary' }}"
                    >
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>{{ $autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}</span>
                    </button>

                    {{-- Manual Refresh --}}
                    <button wire:click="loadData" class="btn btn-primary">
                        Refresh Now
                    </button>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Cutoff Date Info --}}
        @if(!$order && isset($stats['cutoff_date']))
            <div class="card mb-4">
                <div class="alert" style="background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af; margin: 0;">
                    📊 Showing statistics for orders created on or after {{ $stats['cutoff_date'] }}
                </div>
            </div>
        @endif

        {{-- Statistics Cards --}}
        <div class="stats-grid">
            @if($order)
                {{-- Order-specific stats --}}
                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Order Status</p>
                            <p class="stat-value">{{ $stats['order_status'] ?? 'N/A' }}</p>
                        </div>
                        <div class="stat-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Pickup Requests</p>
                            <p class="stat-value">{{ $stats['pickup']['total'] ?? 0 }}</p>
                            <p class="stat-detail">
                                {{ $stats['pickup']['accepted'] ?? 0 }} accepted,
                                {{ $stats['pickup']['pending'] ?? 0 }} pending
                            </p>
                        </div>
                        <div class="stat-icon green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Delivery Requests</p>
                            <p class="stat-value">{{ $stats['delivery']['total'] ?? 0 }}</p>
                            <p class="stat-detail">
                                {{ $stats['delivery']['accepted'] ?? 0 }} accepted,
                                {{ $stats['delivery']['pending'] ?? 0 }} pending
                            </p>
                        </div>
                        <div class="stat-icon purple">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Drivers Assigned</p>
                            <p class="stat-driver">{{ $stats['pickup_driver'] }}</p>
                            <p class="stat-detail">Pickup</p>
                            <p class="stat-driver">{{ $stats['delivery_driver'] }}</p>
                            <p class="stat-detail">Delivery</p>
                        </div>
                        <div class="stat-icon yellow">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            @else
                {{-- Global stats --}}
                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Pending Pickup</p>
                            <p class="stat-value">{{ $stats['pending_pickup'] ?? 0 }}</p>
                        </div>
                        <div class="stat-icon orange">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Pending Delivery</p>
                            <p class="stat-value">{{ $stats['pending_delivery'] ?? 0 }}</p>
                        </div>
                        <div class="stat-icon red">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-content">
                        <div>
                            <p class="stat-label">Acceptance Rate</p>
                            <p class="stat-value">{{ $stats['acceptance_rate'] ?? 0 }}%</p>
                            <p class="stat-detail">Today</p>
                        </div>
                        <div class="stat-icon green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Upcoming Scheduled Jobs Section --}}
        @if(count($upcomingJobs) > 0)
            <div class="card mb-4">
                <h2 class="section-title">⏰ Upcoming Scheduled Jobs ({{ count($upcomingJobs) }})</h2>

                <div class="jobs-list jobs-list-scrollable">
                    @foreach($upcomingJobs as $job)
                        <div class="job-item scheduled">
                            <div class="job-content">
                                <div class="job-info">
                                    <div class="job-status-indicator">
                                        <div class="dot blue pulse-slow"></div>
                                    </div>

                                    <div>
                                        <h3 class="job-title">
                                            Order #{{ $job['order_code'] ?? 'N/A' }} - {{ $job['request_type'] }} Search
                                        </h3>
                                        <p class="job-detail">
                                            Status: <span class="bold status-scheduled">Scheduled</span>
                                        </p>
                                        <p class="job-detail scheduled-time">
                                            ⏰ Will start: <span class="bold">{{ $job['time_until_start'] }}</span>
                                            <span class="muted">({{ \Carbon\Carbon::parse($job['scheduled_at'])->format('M j, g:i A') }})</span>
                                        </p>
                                        <p class="job-time">
                                            Created: {{ \Carbon\Carbon::parse($job['created_at'])->diffForHumans() }}
                                        </p>
                                        @if($job['time_until_start_seconds'] < 300)
                                            <div class="alert-inline warning">
                                                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                Starting soon!
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="job-actions">
                                    {{--                                    <button--}}
                                    {{--                                        wire:click="cancelUpcomingJob('{{ $job['request_type'] }}')"--}}
                                    {{--                                        class="btn btn-danger btn-sm"--}}
                                    {{--                                        wire:confirm="Are you sure you want to cancel this scheduled job?"--}}
                                    {{--                                    >--}}
                                    {{--                                        ✕ Cancel--}}
                                    {{--                                    </button>--}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Active Jobs Section --}}
        @if(count($activeJobs) > 0)
            <div class="card mb-4">
                <h2 class="section-title">🔴 Active Driver Search Jobs ({{ count($activeJobs) }})</h2>

                <div class="jobs-list jobs-list-scrollable">
                    @foreach($activeJobs as $job)
                        <div class="job-item {{ in_array($job['status'], ['searching', 'waiting']) ? 'active' : '' }}">
                            <div class="job-content">
                                <div class="job-info">
                                    <div class="job-status-indicator">
                                        @if($job['status'] === 'searching' || $job['status'] === 'waiting')
                                            <div class="pulse-dot"></div>
                                        @elseif($job['status'] === 'expanding')
                                            <div class="dot orange"></div>
                                        @elseif($job['status'] === 'completed')
                                            <div class="dot blue"></div>
                                        @elseif($job['status'] === 'failed')
                                            <div class="dot red"></div>
                                        @else
                                            <div class="dot gray"></div>
                                        @endif
                                    </div>

                                    <div>
                                        <h3 class="job-title">
                                            Order #{{ $job['order_code'] ?? 'N/A' }} - {{ $job['request_type'] }} Search
                                        </h3>
                                        <p class="job-detail">
                                            Status: <span class="bold status-{{ strtolower($job['status']) }}">{{ ucfirst($job['status']) }}</span>
                                        </p>
                                        <p class="job-detail">
                                            Current Radius: <span class="bold">{{ $job['current_radius'] }}km</span>
                                            @if(isset($job['next_radius']))
                                                <span class="expanding-text">→ Expanding to {{ $job['next_radius'] }}km</span>
                                            @endif
                                        </p>
                                        <p class="job-detail">
                                            Attempt: <span class="bold">#{{ $job['attempt_number'] ?? 1 }}</span>
                                            @if(isset($job['drivers_found']))
                                                | <span class="success-text">Found: {{ $job['drivers_found'] }} drivers</span>
                                            @endif
                                            @if(isset($job['requests_sent']))
                                                | <span class="info-text">Sent: {{ $job['requests_sent'] }} requests</span>
                                            @endif
                                        </p>
                                        <p class="job-time">
                                            Last Activity: {{ \Carbon\Carbon::parse($job['last_activity'])->diffForHumans() }}
                                            @if(isset($job['next_check_at']))
                                                <span class="next-check">| Next check: {{ \Carbon\Carbon::parse($job['next_check_at'])->diffForHumans() }}</span>
                                            @endif
                                        </p>
                                        @if(isset($job['started_at']))
                                            <p class="job-time">
                                                ⏱ Running for: <span class="bold">{{ \Carbon\Carbon::parse($job['started_at'])->diffForHumans(null, true) }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="job-actions">
                                    @if($job['status'] !== 'completed')
                                        {{--                                        <button--}}
                                        {{--                                            wire:click="retryDriverSearch('{{ $job['request_type'] }}')"--}}
                                        {{--                                            class="btn btn-primary btn-sm"--}}
                                        {{--                                        >--}}
                                        {{--                                            🔄 Retry--}}
                                        {{--                                        </button>--}}
                                        {{--                                        <button--}}
                                        {{--                                            wire:click="cancelRequests('{{ $job['request_type'] }}')"--}}
                                        {{--                                            class="btn btn-danger btn-sm"--}}
                                        {{--                                            wire:confirm="Are you sure you want to cancel all pending requests?"--}}
                                        {{--                                        >--}}
                                        {{--                                            ✕ Cancel--}}
                                        {{--                                        </button>--}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            @if($order && count($upcomingJobs) === 0)
                <div class="card mb-4">
                    <div class="alert" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; margin: 0;">
                        ✓ No active or scheduled driver search jobs for this order
                    </div>
                </div>
            @endif
        @endif

        {{-- Radius Expansion History --}}
        @if($order && count($radiusHistory) > 0)
            <div class="card mb-4">
                <h2 class="section-title">📍 Search Radius History</h2>

                <div class="radius-history">
                    @foreach($radiusHistory as $history)
                        <div class="radius-item">
                            <div class="radius-value">{{ $history['estimated_radius'] }}km</div>
                            <div class="radius-count">{{ $history['count'] }} drivers</div>
                            <div class="radius-time">{{ $history['time'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="filters">
                <div class="filter-group">
                    <label class="filter-label">Filter by Type:</label>
                    <select wire:model.live="requestType" class="filter-select">
                        <option value="ALL">All Requests</option>
                        <option value="PICKUP">Pickup Only</option>
                        <option value="DELIVERY">Delivery Only</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Refresh Interval:</label>
                    <select wire:model.live="refreshInterval" class="filter-select">
                        <option value="3">3 seconds</option>
                        <option value="5">5 seconds</option>
                        <option value="10">10 seconds</option>
                        <option value="30">30 seconds</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Driver Requests Table --}}
        <div class="card">
            <h2 class="section-title">📋 Driver Requests</h2>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Response Time</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($driverRequests as $request)
                        <tr>
                            <td>
                                <div class="table-text-bold">{{ $request['order_code'] }}</div>
                            </td>
                            <td>
                                <div class="table-text-bold">{{ $request['driver_name'] }}</div>
                                <div class="table-text-muted">{{ $request['driver_phone'] }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $request['request_type'] === 'PICKUP' ? 'badge-success' : 'badge-purple' }}">
                                    {{ $request['request_type'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge
                                    @if($request['status'] === 'ACCEPTED') badge-success
                                    @elseif($request['status'] === 'PENDING') badge-warning
                                    @elseif($request['status'] === 'REJECTED') badge-danger
                                    @else badge-secondary
                                    @endif">
                                    {{ $request['status'] }}
                                </span>
                            </td>
                            <td class="table-text-muted">
                                {{ \Carbon\Carbon::parse($request['created_at'])->diffForHumans() }}
                            </td>
                            <td class="table-text-muted">
                                @if($request['time_to_respond'])
                                    {{ $request['time_to_respond'] }}s
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($request['status'] === 'PENDING' && $order)
                                    <button
                                        wire:click="manualAssign({{ $request['driver_id'] ?? 0 }}, '{{ $request['request_type'] }}')"
                                        class="btn-link"
                                        wire:confirm="Are you sure you want to manually assign this driver?"
                                    >
                                        Assign
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                No driver requests found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading class="loading-indicator">
            <div class="loading-content">
                <svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="spinner-circle" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="spinner-path" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Updating...</span>
            </div>
        </div>
    </div>

    <style>
        /* Reset & Base */
        * { box-sizing: border-box; }

        .driver-monitor {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1a202c;
            line-height: 1.5;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
        }

        .mb-4 {
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #1a202c;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 16px 0;
            color: #1a202c;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-link {
            background: none;
            border: none;
            color: #3b82f6;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
        }

        .btn-link:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .icon {
            width: 20px;
            height: 20px;
        }

        .icon-sm {
            width: 16px;
            height: 16px;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 16px;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .alert-inline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 8px;
        }

        .alert-inline.warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 4px 0;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }

        .stat-driver {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin: 8px 0 0 0;
        }

        .stat-detail {
            font-size: 12px;
            color: #6b7280;
            margin: 4px 0 0 0;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 32px;
            height: 32px;
        }

        .stat-icon.blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .stat-icon.green {
            background: #d1fae5;
            color: #065f46;
        }

        .stat-icon.purple {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .stat-icon.yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .stat-icon.orange {
            background: #fed7aa;
            color: #9a3412;
        }

        .stat-icon.red {
            background: #fecaca;
            color: #991b1b;
        }

        /* Jobs List with Scroll */
        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .jobs-list-scrollable {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 8px;
        }

        /* Custom Scrollbar */
        .jobs-list-scrollable::-webkit-scrollbar {
            width: 8px;
        }

        .jobs-list-scrollable::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 4px;
        }

        .jobs-list-scrollable::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .jobs-list-scrollable::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Firefox Scrollbar */
        .jobs-list-scrollable {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f3f4f6;
        }

        /* Scroll Shadow Effect */
        .jobs-list-scrollable {
            background:
                linear-gradient(white 30%, rgba(255,255,255,0)),
                linear-gradient(rgba(255,255,255,0), white 70%) 0 100%,
                radial-gradient(farthest-side at 50% 0, rgba(0,0,0,.1), rgba(0,0,0,0)),
                radial-gradient(farthest-side at 50% 100%, rgba(0,0,0,.1), rgba(0,0,0,0)) 0 100%;
            background-repeat: no-repeat;
            background-color: white;
            background-size: 100% 40px, 100% 40px, 100% 14px, 100% 14px;
            background-attachment: local, local, scroll, scroll;
        }

        .job-item {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            transition: all 0.3s;
        }

        .job-item.active {
            border-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
        }

        .job-item.scheduled {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
        }

        .job-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .job-info {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .job-status-indicator {
            flex-shrink: 0;
            padding-top: 4px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .pulse-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .dot.blue {
            background: #3b82f6;
        }

        .dot.blue.pulse-slow {
            animation: pulse-blue 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-blue {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        .dot.orange {
            background: #f59e0b;
            animation: pulse-orange 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-orange {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .dot.red {
            background: #ef4444;
        }

        .dot.gray {
            background: #9ca3af;
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #1a202c;
        }

        .job-detail {
            font-size: 14px;
            color: #4b5563;
            margin: 4px 0;
        }

        .job-detail.scheduled-time {
            font-size: 15px;
            font-weight: 500;
            color: #1e40af;
        }

        .job-time {
            font-size: 12px;
            color: #6b7280;
            margin: 6px 0 0 0;
        }

        .job-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .bold {
            font-weight: 600;
            color: #1a202c;
        }

        .muted {
            color: #6b7280;
            font-weight: 400;
        }

        .status-searching,
        .status-waiting {
            color: #10b981;
        }

        .status-expanding {
            color: #f59e0b;
        }

        .status-completed {
            color: #3b82f6;
        }

        .status-failed {
            color: #ef4444;
        }

        .status-scheduled {
            color: #3b82f6;
        }

        .expanding-text {
            color: #f59e0b;
            font-weight: 600;
        }

        .success-text {
            color: #10b981;
        }

        .info-text {
            color: #3b82f6;
        }

        .next-check {
            color: #8b5cf6;
            font-weight: 500;
        }

        /* Radius History */
        .radius-history {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .radius-item {
            flex-shrink: 0;
            background: #dbeafe;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            min-width: 120px;
        }

        .radius-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e40af;
        }

        .radius-count {
            font-size: 14px;
            color: #4b5563;
            margin-top: 4px;
        }

        .radius-time {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 24px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #f9fafb;
        }

        .data-table th {
            padding: 12px 24px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
        }

        .data-table tbody tr:hover {
            background: #f9fafb;
        }

        .table-text-bold {
            font-size: 14px;
            font-weight: 600;
            color: #1a202c;
        }

        .table-text-muted {
            font-size: 14px;
            color: #6b7280;
        }

        .text-center {
            text-align: center;
            color: #6b7280;
            padding: 32px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-purple {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Loading Indicator */
        .loading-indicator {
            position: fixed;
            bottom: 16px;
            right: 16px;
            background: #3b82f6;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .loading-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spinner {
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        .spinner-circle {
            opacity: 0.25;
        }

        .spinner-path {
            opacity: 0.75;
        }

        /* Animations */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .header-actions {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .job-content {
                flex-direction: column;
                gap: 12px;
            }

            .job-actions {
                width: 100%;
            }

            .job-actions .btn {
                flex: 1;
            }
        }
    </style>
</div>
