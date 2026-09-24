@extends('layouts.app')

@section('title', $method . ' Payment | Intern Estate')

@section('content')
@php
    $themes = [
        'bKash' => ['class' => 'provider-bkash', 'label' => 'bKash', 'action' => 'Continue to bKash'],
        'Nagad' => ['class' => 'provider-nagad', 'label' => 'Nagad', 'action' => 'Continue to Nagad'],
        'Bank' => ['class' => 'provider-bank', 'label' => 'Bank Transfer', 'action' => 'View bank transfer instructions'],
        'SSLCommerz' => ['class' => 'provider-ssl', 'label' => 'SSLCommerz', 'action' => 'Continue to SSLCommerz'],
    ];
    $theme = $themes[$method];
@endphp

<main class="provider-page">
    <section class="provider-card {{ $theme['class'] }}">
        <div class="provider-brand">
            <span>{{ $theme['label'] }}</span>
            <small>Secure payment hand-off</small>
        </div>

        <div class="provider-body">
            <p class="provider-eyebrow">INTERN ESTATE · PAYMENT REQUEST</p>
            <h1>Complete payment securely</h1>
            <div class="provider-order-summary">
                <span>{{ $project->title }}</span>
                <strong>BDT {{ number_format($amount) }}</strong>
            </div>
            <p class="provider-plan">
                {{ $plan === 'full' ? 'Full payment' : strtoupper(str_replace('_', ' ', $plan)) . ' installment' }}
            </p>

            @if($method === 'Bank')
                <div class="provider-notice">
                    Use your bank app or branch to make the transfer. Keep the bank reference number; you will submit it when you return to checkout.
                </div>
            @else
                <div class="provider-notice">
                    You are leaving Intern Estate for secure provider verification. Your phone, OTP and PIN are entered only on the provider's official page and are never collected by Intern Estate.
                </div>
            @endif

            <a class="provider-primary" href="{{ route('checkout.show', ['type' => 'project', 'id' => $project->id]) }}">
                {{ $theme['action'] }}
            </a>
            <a class="provider-cancel" href="{{ route('checkout.show', ['type' => 'project', 'id' => $project->id]) }}">Cancel and return to checkout</a>
            <p class="provider-demo-note">Demo hand-off screen. Connect the official {{ $theme['label'] }} merchant API to make the button redirect to its live hosted checkout.</p>
        </div>
    </section>
</main>
@endsection
