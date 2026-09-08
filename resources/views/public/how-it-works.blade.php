@extends('layouts.app')

@section('title', 'How It Works - Step by Step Guide | Intern Estate')

@section('content')

    {{-- Page Header --}}
    <section class="section section-soft how-it-works-header" style="padding: 45px 0 24px 0; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
        <div class="container">
            <div class="center-heading">
                <span class="section-badge">How It Works</span>
                <h2>Detailed Process from Discovery to Deed Ownership</h2>
                <p>A transparent 4-step workflow ensuring legal security, flexible payments, and seamless investment management.</p>
            </div>
        </div>
    </section>

    {{-- Detailed How It Works Timeline --}}
    <section class="section how-it-works-section">
        <div class="container">
            
            <div class="workflow-timeline">
                <article class="workflow-step">
                    <div class="process-step-header">
                        <span class="workflow-step-number">Step 1</span>
                        <span class="workflow-step-badge">Discovery</span>
                    </div>
                    <h3>1. Explore & Match Property</h3>
                    <p>
                        Browse verified residential & commercial developments based on location (Uttara, Bashundhara, Dhanmondi, Gazipur), unit size (Sq Ft), and budget. Use our AI Assistant to find your ideal share.
                    </p>
                </article>

                <article class="workflow-step">
                    <div class="process-step-header">
                        <span class="workflow-step-number">Step 2</span>
                        <span class="workflow-step-badge">Payment & KYC</span>
                    </div>
                    <h3>2. Choose Payment</h3>
                    <p>
                        Select full one-time payment or 1-3 years Credit Card EMI plans via SSLCommerz, bKash, Nagad, or Bank Transfer. Upload required NID, Tax TIN certificate, and utility bill proof.
                    </p>
                </article>

                <article class="workflow-step">
                    <div class="process-step-header">
                        <span class="workflow-step-number">Step 3</span>
                        <span class="workflow-step-badge">Legal Audit</span>
                    </div>
                    <h3>3. Legal Vetting</h3>
                    <p>
                        Our panel of assigned legal advisors conducts title deed verification and document checks. Transactions are reviewed and audited with 256-bit encryption before approval.
                    </p>
                </article>

                <article class="workflow-step">
                    <div class="process-step-header">
                        <span class="workflow-step-number">Step 4</span>
                        <span class="workflow-step-badge">Ownership</span>
                    </div>
                    <h3>4. Deed Issue</h3>
                    <p>
                        Receive your official Joint Equity Ownership Deed and log into your Investor / Landowner portal to track live building construction progress, materials, and milestone updates.
                    </p>
                </article>
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('public.projects') }}" class="button button-primary" style="padding: 14px 28px; font-size: 15px;">
                    Explore Verified Projects Now →
                </a>
            </div>

        </div>
    </section>

@endsection
