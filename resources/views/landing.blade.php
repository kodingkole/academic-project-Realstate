@extends('layouts.app')

@section('title', 'Intern Estate | Real Estate & Construction ERP')

@section('content')

    @php($featuredProject = $projects[0] ?? null)

    {{-- Hero Section --}}
    <section class="hero-section">
        <div class="hero-overlay"></div>

        <div class="container hero-grid">
            <div class="hero-content">
                <span class="section-badge">
                    Real Estate & Construction ERP
                </span>

                <h1>
                    Own better.
                    <span>Build transparently.</span>
                </h1>

                <p>
                    Discover trusted properties, monitor construction
                    progress and manage every project from one secure
                    digital platform.
                </p>

                <div class="hero-actions">
                    <a href="#projects" class="button button-primary">
                        Explore Projects
                    </a>

                    <a href="{{ route('login') }}" class="button button-light">
                        Access Your Portal
                    </a>

                    <a href="{{ route('land.submit') }}" class="button button-light">
                        Submit Your Land
                    </a>
                </div>

                <div class="hero-trust">
                    <div>
                        <strong>{{ $activeProjects }}</strong>
                        <span>Active Projects</span>
                    </div>

                    <div>
                        <strong>{{ $investors }}</strong>
                        <span>Investors</span>
                    </div>

                </div>
            </div>

            @if($featuredProject)
            <div class="hero-card">
                <div class="hero-card-header">
                    <span>Featured Investment</span>
                    <span class="status-pill">Active</span>
                </div>

                <div class="property-visual">
                    <div class="building building-one"></div>
                    <div class="building building-two"></div>


                </div>

                <div class="hero-card-body">
                    <p class="small-label">{{ strtoupper($featuredProject['location']) }}</p>

                    <h2>{{ $featuredProject['name'] }}</h2>

                    <p>
                        Modern residential apartments with transparent
                        construction progress and investment tracking.
                    </p>

                    <div class="progress-information">
                        <span>Construction progress</span>
                        <strong>{{ $featuredProject['progress'] }}%</strong>
                    </div>

                    <div class="progress-track">
                        <div
                            class="progress-bar"
                            style="width: {{ $featuredProject['progress'] }}%"
                        ></div>
                    </div>

                    <div style="margin-top: 18px;">
                        @auth
                            <a href="{{ route('checkout.show', ['type' => 'project', 'id' => $featuredProject['id']]) }}" class="btn-card-invest" style="height: 42px; padding: 0 20px; font-size: 14px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Buy / Invest Now
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-card-invest" style="height: 42px; padding: 0 20px; font-size: 14px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Buy / Invest Now
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Search Section --}}
    <section class="search-wrapper">
        <div class="container">
            <form class="property-search" action="{{ route('landing') }}#projects" method="GET">
                <div class="search-field">
                    <label for="search">Project name</label>
                    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Search project name">
                </div>

                <div class="search-field">
                    <label for="location">Location</label>
                    <input id="location" type="search" name="location" value="{{ $location }}" placeholder="Search location">
                </div>

                <div class="search-field">
                    <label for="budget">Budget</label>

                    <select id="budget" name="budget">
                        <option value="">Choose your budget</option>
                        <option value="under-5cr" @selected($budget === 'under-5cr' || $budget === 'under-60')>Under 5 Crore BDT</option>
                        <option value="5cr-10cr" @selected($budget === '5cr-10cr' || $budget === '60-90')>5 Crore – 10 Crore BDT</option>
                        <option value="over-10cr" @selected($budget === 'over-10cr' || $budget === 'over-90')>Over 10 Crore BDT</option>
                    </select>
                </div>

                <button type="submit" class="search-button">
                    Search Properties
                </button>
            </form>
        </div>
    </section>

    {{-- Featured Projects --}}
    <section class="section" id="projects">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-badge">Featured Projects</span>

                    <h2>Properties built around trust</h2>

                    <p>
                        Explore active residential and commercial
                        developments from our verified project portfolio.
                    </p>
                </div>

                <a href="{{ route('public.projects') }}" class="text-link">
                    View all projects →
                </a>
            </div>

            <div class="project-grid">
                @forelse($projects as $index => $project)
                    <article class="project-card">
                        <div class="project-image project-image-{{ ($index % 3) + 1 }}">
                            <span class="project-type">
                                {{ $project['type'] }}
                            </span>

                            <div class="project-building">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>

                        <div class="project-content">
                            <p class="project-location">
                                {{ $project['location'] }}
                            </p>

                            <h3>{{ $project['name'] }}</h3>

                            <p class="project-price">
                                {{ $project['price'] }}
                            </p>

                            <div class="progress-information">
                                <span>Project progress</span>
                                <strong>
                                    {{ $project['progress'] }}%
                                </strong>
                            </div>

                            <div class="progress-track">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $project['progress'] }}%"
                                ></div>
                            </div>

                            <div class="project-card-footer">
                                @auth
                                    <a href="{{ route('checkout.show', ['type' => 'project', 'id' => $project['id']]) }}" class="btn-card-invest">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Buy / Invest Now
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn-card-invest">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                        Buy / Invest Now
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="empty-state">No active properties match your search.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="section section-soft" id="services">
        <div class="container">
            <div class="center-heading">
                <span class="section-badge">One Integrated Platform</span>

                <h2>Everything needed to build with confidence</h2>

                <p>
                    Different stakeholders receive separate secure
                    portals while the business manages everything
                    through one ERP.
                </p>
            </div>

            <div class="service-grid">
                <article class="service-card">
                    <div class="service-icon">01</div>
                    <h3>Investor Portal & Benefits</h3>
                    <p>
                        Track investments, bookings, payment history,
                        8-12% ROI, 1-3 Yrs EMI and legal title deed updates.
                    </p>
                    <a href="{{ route('public.services') }}">View detailed benefits →</a>
                </article>

                <article class="service-card">
                    <div class="service-icon">02</div>
                    <h3>Landowner JV Portal</h3>
                    <p>
                        Monitor land submissions, 50-50 JV agreements, unit swap,
                        valuations and panel lawyer legal vetting.
                    </p>
                    <a href="{{ route('public.services') }}">View landowner benefits →</a>
                </article>

                <article class="service-card">
                    <div class="service-icon">03</div>
                    <h3>100% Trusted & Compliance</h3>
                    <p>
                        RAJUK & City Corp compliance, 256-bit SSL encrypted
                        payments, automated KYC, and audited ERP logs.
                    </p>
                    <a href="{{ route('public.services') }}">View trust & security standards →</a>
                </article>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="section" id="process">
        <div class="container">
            <div class="center-heading">
                <span class="section-badge">Simple Process</span>
                <h2>From discovery to deed ownership</h2>
            </div>

            <div class="process-grid">
                <article class="process-item">
                    <span>1</span>
                    <h3>Explore</h3>
                    <p>
                        Browse verified projects based on location,
                        property type and budget.
                    </p>
                </article>

                <article class="process-item">
                    <span>2</span>
                    <h3>Connect</h3>
                    <p>
                        Submit your interest and choose full or 1-3 years
                        Credit Card EMI plans.
                    </p>
                </article>

                <article class="process-item">
                    <span>3</span>
                    <h3>Invest</h3>
                    <p>
                        Reserve a property, upload KYC documents, and track
                        panel lawyer legal audits.
                    </p>
                </article>

                <article class="process-item">
                    <span>4</span>
                    <h3>Monitor</h3>
                    <p>
                        Receive official ownership deed and follow milestone progress
                        in real time.
                    </p>
                </article>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('public.how-it-works') }}" class="text-link" style="font-size: 15px;">
                    View detailed 4-step workflow guide →
                </a>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="cta-section">
        <div class="container cta-content">
            <div>
                <span class="section-badge section-badge-light">
                    Start Today
                </span>

                <h2>Build your real estate future transparently.</h2>

                <p>
                    Access project information and stakeholder
                    portals from one secure platform.
                </p>
            </div>

            <a href="{{ route('login') }}" class="button button-light">
                Login to Portal
            </a>
        </div>
    </section>

@endsection
