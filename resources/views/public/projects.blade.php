@extends('layouts.app')

@section('title', 'All Real Estate & Construction Projects | Intern Estate')

@section('content')

    {{-- Page Header --}}
    <section class="section section-soft" style="padding: 60px 0 40px 0; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
        <div class="container">
            <div class="center-heading">
                <span class="section-badge">Verified Portfolio</span>
                <h2>All Construction & Real Estate Projects</h2>
                <p>Explore all active residential, commercial, and joint-venture developments from our verified database portfolio.</p>
            </div>
        </div>
    </section>

    {{-- Projects Section --}}
    <section class="section" style="padding-top: 20px;">
        <div class="container">
            <form action="{{ route('public.projects') }}" method="GET" class="project-search-form">
                <label for="project-search" class="sr-only">Search projects by name</label>
                <input id="project-search" type="search" name="search" value="{{ $search }}" placeholder="Search by project name...">
                <button type="submit">Search</button>
                @if($search !== '')
                    <a href="{{ route('public.projects') }}">Clear</a>
                @endif
            </form>

            <div class="project-grid">
                @forelse($dbProjects as $index => $project)
                    @php
                        $unitCost = $project->total_budget > 0 ? (int) ($project->total_budget / 20) : ($project->estimated_cost ?? 6000000);
                    @endphp
                    <article class="project-card">
                        <div class="project-image project-image-{{ ($index % 3) + 1 }}">
                            <span class="project-type">
                                {{ ucfirst($project->status) }} • Verified
                            </span>

                            <div class="project-building">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>

                        <div class="project-content">
                            <p class="project-location">
                                {{ $project->location }}
                            </p>

                            <h3>{{ $project->title }}</h3>

                            <p class="project-price">
                                Starting from ৳{{ number_format($unitCost) }} BDT
                            </p>

                            <div class="progress-information">
                                <span>Construction progress</span>
                                <strong>
                                    {{ $project->progress_percentage }}%
                                </strong>
                            </div>

                            <div class="progress-track">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $project->progress_percentage }}%"
                                ></div>
                            </div>

                            <div class="project-card-footer">
                                @auth
                                    <a href="{{ route('checkout.show', ['type' => 'project', 'id' => $project->id]) }}" class="btn-card-invest">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Buy / Invest Share
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn-card-invest">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Buy / Invest Share
                                    </a>
                                @endauth
                                <a href="{{ route('checkout.show', ['type' => 'project', 'id' => $project->id]) }}" class="btn-card-details">
                                    Checkout →
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <h3>No active projects are available right now.</h3>
                        <p>Please check back soon for verified project opportunities.</p>
                    </div>
                @endforelse
            </div>

            @if($dbProjects->hasPages())
                <nav class="projects-pagination" aria-label="Projects pagination">
                    @if($dbProjects->onFirstPage())
                        <span class="is-disabled">← Previous</span>
                    @else
                        <a href="{{ $dbProjects->previousPageUrl() }}">← Previous</a>
                    @endif

                    <div class="page-numbers">
                        @foreach($dbProjects->getUrlRange(1, $dbProjects->lastPage()) as $page => $url)
                            @if($page === $dbProjects->currentPage())
                                <span class="is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    </div>

                    @if($dbProjects->hasMorePages())
                        <a href="{{ $dbProjects->nextPageUrl() }}">Next →</a>
                    @else
                        <span class="is-disabled">Next →</span>
                    @endif
                </nav>
            @endif

        </div>
    </section>

@endsection
