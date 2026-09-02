@extends('layouts.portal')

@section('title', 'Admin Dashboard | Intern Estate')

@section('page-heading', 'Admin Command Center')

@section('content')
    <section class="dashboard-welcome">
        <div>
            <span class="section-badge">Overview</span>

            <h2>
                Welcome back, {{ auth()->user()->name }}.
            </h2>

            <p>
                Here is a summary of the current real estate and
                construction operations.
            </p>
        </div>

        <a href="{{ route('admin.modules.create', 'projects') }}" class="dashboard-action-button">
            + Create Project
        </a>
    </section>

    <section class="stat-card-grid">
        <article class="stat-card">
            <div class="stat-card-icon">P</div>

            <div>
                <p>Total Projects</p>
                <h3>{{ $statistics['total_projects'] }}</h3>
                <span>All registered projects</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card-icon">A</div>

            <div>
                <p>Active Projects</p>
                <h3>{{ $statistics['active_projects'] }}</h3>
                <span>Currently under construction</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card-icon">I</div>

            <div>
                <p>Total Investors</p>
                <h3>{{ $statistics['total_investors'] }}</h3>
                <span>Verified investor accounts</span>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card-icon">T</div>

            <div>
                <p>Pending Tasks</p>
                <h3>{{ $statistics['pending_tasks'] }}</h3>
                <span>Require team attention</span>
            </div>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-panel project-table-panel">
            <div class="panel-header">
                <div>
                    <h3>Recent Projects</h3>
                    <p>Latest project progress overview</p>
                </div>

                <a href="{{ route('admin.modules.index', 'projects') }}">View all</a>
            </div>

            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($recentProjects as $project)
                            <tr>
                                <td>
                                    <strong>{{ $project['name'] }}</strong>
                                </td>

                                <td>{{ $project['manager'] }}</td>

                                <td>
                                    <span class="table-status">
                                        {{ $project['status'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="table-progress">
                                        <div class="progress-track">
                                            <div
                                                class="progress-bar"
                                                style="width: {{ $project['progress'] }}%"
                                            ></div>
                                        </div>

                                        <strong>
                                            {{ $project['progress'] }}%
                                        </strong>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3>Quick Actions</h3>
                    <p>Frequently used operations</p>
                </div>
            </div>

            <div class="quick-actions">
                <a href="{{ route('admin.investors') }}">
                    <span>🤝</span>
                    Verify Investor Payments & Bookings
                </a>

                <a href="{{ route('admin.investor-documents') }}">
                    <span>📁</span>
                    Upload Investor Documents
                </a>

                <a href="{{ route('admin.modules.create', 'projects') }}">
                    <span>+</span>
                    Add new project
                </a>

                <a href="{{ route('admin.modules.create', 'tasks') }}">
                    <span>✓</span>
                    Assign project task
                </a>

                <a href="{{ route('admin.modules.create', 'inventory') }}">
                    <span>◫</span>
                    Record material stock
                </a>

                <a href="{{ route('admin.modules.create', 'finance') }}">
                    <span>৳</span>
                    Add project expense
                </a>
            </div>
        </aside>
    </section>

    <section class="module-preview">
        <div class="panel-header">
            <div>
                <h3>ERP Module Overview</h3>

                <p>
                    Planned modules for the complete construction ERP.
                </p>
            </div>
        </div>

        <div class="module-grid">
            <article>
                <span>01</span>
                <h4>Project Management</h4>
                <p>Planning, milestones and progress.</p>
            </article>

            <article>
                <span>02</span>
                <h4>Inventory</h4>
                <p>Materials and stock transactions.</p>
            </article>

            <article>
                <span>03</span>
                <h4>Procurement</h4>
                <p>Suppliers, quotations and orders.</p>
            </article>

            <article>
                <span>04</span>
                <h4>Workforce</h4>
                <p>Attendance, workers and payroll.</p>
            </article>

            <article>
                <span>05</span>
                <h4>Finance</h4>
                <p>Budget, expenses and reporting.</p>
            </article>

            <article>
                <span>06</span>
                <h4>Inspection</h4>
                <p>Quality and safety monitoring.</p>
            </article>
        </div>
    </section>
@endsection
