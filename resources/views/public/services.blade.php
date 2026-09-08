@extends('layouts.app')

@section('title', 'Services, Investor & Landowner Benefits | Intern Estate')

@section('content')

    {{-- Page Header --}}
    <section class="section section-soft" style="padding: 60px 0 40px 0; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
        <div class="container">
            <div class="center-heading">
                <span class="section-badge">Platform Benefits & Trust</span>
                <h2>Detailed Services, Benefits & Legal Security</h2>
                <p>Discover how Intern Estate protects investors and landowners with 100% legal compliance and bank-grade security.</p>
            </div>
        </div>
    </section>

    {{-- Detailed Services & Benefits Section --}}
    <section class="section">
        <div class="container">
            <div class="service-grid benefits-grid">
                
                {{-- Card 1: Investor Benefits --}}
                <article class="service-card benefit-card-enhanced">
                    <div class="service-icon">01</div>
                    <h3>Investor Opportunities & Benefits</h3>
                    <p class="service-intro">Maximize your wealth with direct equity shares in prime real estate developments.</p>
                    
                    <ul class="benefit-list">
                        <li><strong>High ROI & Rental Yield:</strong> Estimated 8-12% annual rental return upon completion.</li>
                        <li><strong>1-3 Years Credit Card EMI:</strong> Backed by City Bank Amex, BRAC, EBL, SCB & DBBL.</li>
                        <li><strong>Direct Owner Savings:</strong> Save 25-30% markup compared to commercial developers.</li>
                        <li><strong>Transparent Digital Ledger:</strong> Track every payment & deed status from your portal.</li>
                        <li><strong>Legal Equity Share Deed:</strong> Registered land ownership share assigned upon full booking.</li>
                    </ul>

                    @auth
                        <a href="{{ route('investor.dashboard') }}" class="service-link">Access Investor Portal →</a>
                    @else
                        <a href="{{ route('investor.register') }}" class="service-link">Register as Investor →</a>
                    @endauth
                </article>

                {{-- Card 2: Landowner Benefits --}}
                <article class="service-card benefit-card-enhanced">
                    <div class="service-icon">02</div>
                    <h3>Landowner Joint-Venture Benefits</h3>
                    <p class="service-intro">Transform your land into high-value modern apartments without spending a Taka.</p>
                    
                    <ul class="benefit-list">
                        <li><strong>50-50 JV Share Ratio:</strong> High-return equity sharing or customized unit swap.</li>
                        <li><strong>Zero Construction Cost:</strong> We finance 100% of architectural & engineering costs.</li>
                        <li><strong>Legal Protection:</strong> Panel of vetted lawyers conducts title deed & clearance checks.</li>
                        <li><strong>Milestone Transparency:</strong> Monitor live material stock & workforce attendance.</li>
                        <li><strong>Guaranteed Completion Timeline:</strong> Penalty-backed construction scheduling.</li>
                    </ul>

                    <a href="{{ route('land.submit') }}" class="service-link">Submit Your Land for JV →</a>
                </article>

                {{-- Card 3: 100% Trusted & Security Features --}}
                <article class="service-card benefit-card-enhanced trust-card-highlight">
                    <div class="service-icon trust-icon-badge">03</div>
                    <h3>Why Intern Estate is 100% Trusted</h3>
                    <p class="service-intro">Strict legal compliance and bank-grade security for total peace of mind.</p>
                    
                    <ul class="benefit-list">
                        <li><strong>RAJUK & City Corp Approved:</strong> Built according to official structural engineering standards.</li>
                        <li><strong>256-Bit SSL Encrypted Payments:</strong> Bank-grade protection for bKash, Nagad & Cards.</li>
                        <li><strong>Automated KYC Verification:</strong> Verification of NID, E-TIN clearance & utility bills.</li>
                        <li><strong>Audited ERP Tracking:</strong> Construction progress logged live with real-time site photos.</li>
                        <li><strong>Panel Lawyer Title Vetting:</strong> Official legal clearance documentation for every plot.</li>
                    </ul>

                    <a href="{{ route('login') }}" class="service-link">Access Stakeholder Login →</a>
                </article>

            </div>
        </div>
    </section>

@endsection
