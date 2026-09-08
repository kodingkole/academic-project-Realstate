@extends('layouts.app')

@section('title', 'Intern Estate | Real Estate & Construction ERP')

@section('content')

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
                        <strong>12+</strong>
                        <span>Active Projects</span>
                    </div>

                    <div>
                        <strong>146+</strong>
                        <span>Investors</span>
                    </div>

                    <div>
                        <strong>98%</strong>
                        <span>Client Satisfaction</span>
                    </div>
                </div>
            </div>

            <div class="hero-card">
                <div class="hero-card-header">
                    <span>Featured Investment</span>
                    <span class="status-pill">Active</span>
                </div>

                <div class="property-visual">
                    <div class="building building-one"></div>
                    <div class="building building-two"></div>
                    <div class="building building-three"></div>
                </div>

                <div class="hero-card-body">
                    <p class="small-label">UTTARA, DHAKA</p>

                    <h2>Skyline Residence</h2>

                    <p>
                        Modern residential apartments with transparent
                        construction progress and investment tracking.
                    </p>

                    <div class="progress-information">
                        <span>Construction progress</span>
                        <strong>72%</strong>
                    </div>

                    <div class="progress-track">
                        <div
                            class="progress-bar"
                            style="width: 72%"
                        ></div>
                    </div>

                    <div style="margin-top: 18px;">
                        @auth
                            <a href="{{ route('checkout.show', ['type' => 'project', 'id' => 1]) }}" class="btn-card-invest" style="height: 42px; padding: 0 20px; font-size: 14px;">
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
        </div>
    </section>

    {{-- Search Section --}}
    <section class="search-wrapper">
        <div class="container">
            <form class="property-search" action="#projects">
                <div class="search-field">
                    <label for="location">Location</label>

                    <select id="location" name="location">
                        <option value="">Choose a location</option>
                        <option>Uttara</option>
                        <option>Bashundhara</option>
                        <option>Gazipur</option>
                    </select>
                </div>

                <div class="search-field">
                    <label for="property_type">Property Type</label>

                    <select id="property_type" name="property_type">
                        <option value="">Choose property type</option>
                        <option>Residential</option>
                        <option>Commercial</option>
                        <option>Luxury Apartment</option>
                    </select>
                </div>

                <div class="search-field">
                    <label for="budget">Budget</label>

                    <select id="budget" name="budget">
                        <option value="">Choose your budget</option>
                        <option>৳40–60 Lakh</option>
                        <option>৳60–90 Lakh</option>
                        <option>৳90 Lakh–1.5 Crore</option>
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
                @foreach($projects as $index => $project)
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
                                <a href="{{ route('login') }}" class="btn-card-details">
                                    View details →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
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
