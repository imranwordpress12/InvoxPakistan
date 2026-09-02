@extends('layouts.company')

@section('title', 'Compliance Instructions')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h5 text-primary fw-bold mb-4">
                <i class="bi bi-shield-fill-check me-2"></i> Important Disclaimer &amp; Compliance Information
            </h1>
            <hr>

            <h2 class="h6 text-danger fw-bold mt-4">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Disclaimer of Liability
            </h2>
            <p>
                <strong>Invox Pakistan</strong> is a digital invoicing facilitation platform that enables
                customers to prepare and submit invoices to Pakistan's <strong>FBR IRIS Portal</strong> based
                on data provided by the user.
            </p>
            <p>
                While we strive to ensure accuracy, system stability, and compliance based on our best
                understanding of prevailing FBR regulations, <strong>Invox Pakistan</strong> does not
                guarantee error-free submission, acceptance, or validation by the IRIS system.
            </p>
            <p><strong>Invox Pakistan</strong> shall not be held liable for any direct or indirect loss,
                penalty, tax implication, business disruption, or legal consequence arising due to:</p>
            <ul>
                <li>Incorrect, incomplete, or misleading data entered by the user.</li>
                <li>Changes in FBR laws, rules, validations, or IRIS behavior.</li>
                <li>Delays, failures, or outages of IRIS or government systems.</li>
            </ul>

            <h2 class="h6 text-warning-emphasis fw-bold mt-4">Customer Responsibilities &amp; Obligations</h2>
            <p>
                <strong>Accuracy of Data:</strong> All invoice data including values, tax amounts,
                buyer/seller details, HS codes, classifications, and descriptions are entirely the
                responsibility of the customer.
            </p>
            <p>
                <strong>Final Verification:</strong> Once an invoice is submitted to IRIS, it cannot be
                edited, reversed, or cancelled. Customers must review all details carefully before
                submission.
            </p>
            <p><strong>Duplicate Invoice Risk:</strong></p>
            <ul>
                <li>If IRIS returns a valid failure reason, the invoice is considered not submitted.</li>
                <li>
                    If no meaningful response is received, customers must verify directly on IRIS and
                    wait at least <span class="badge text-bg-dark">48 hours</span> before reattempting
                    submission.
                </li>
                <li>Invox Pakistan shall not be responsible for duplicate invoices caused by premature re-submission.</li>
            </ul>

            <h2 class="h6 text-info-emphasis fw-bold mt-4">Excel Upload Guidelines</h2>
            <p class="mb-1">Formulas are not allowed — only static values.</p>
            <p>Amounts must be entered without commas.</p>
            <div class="bg-light border rounded p-3 mb-3">
                <div class="text-danger">&#10060; 100,000 (Invalid)</div>
                <div class="text-success">&#9989; 100000 (Valid)</div>
            </div>

            <h2 class="h6 fw-bold mt-4">
                <i class="bi bi-plug-fill me-1"></i> IRIS / FBR Integration Limitations
            </h2>
            <p>
                <strong>Real-Time Dependency:</strong> Certain selectable values (e.g., registration
                status, tax profiles) are fetched in real time from IRIS and may be delayed due to
                government system latency.
            </p>
            <p>
                <strong>System Availability:</strong> Any outage, throttling, timeout, or validation error
                originating from IRIS is beyond the control and liability of Invox Pakistan.
            </p>
            <p>
                <strong>Regulatory Changes:</strong> FBR may update invoicing rules, formats, or
                validations at any time. Customers remain solely responsible for compliance with current
                laws and notifications.
            </p>

            <div class="alert alert-danger mt-4 mb-0">
                <strong>Legal Notice:</strong> Invox Pakistan acts solely as a technology service provider
                and does not provide tax, legal, or financial advice. Customers are strongly advised to
                consult their tax or legal advisors. Use of this platform constitutes acceptance of this
                disclaimer.
            </div>
        </div>
    </div>
@endsection
