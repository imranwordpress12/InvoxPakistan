@extends('layouts.app')

@section('title', 'Invox Pakistan')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="text-center mb-4">
                    <i class="bi bi-building-check display-4 text-primary"></i>
                    <h1 class="mt-3">Invox Pakistan</h1>
                    <p class="lead text-muted">Company Subscription &amp; Payment Management System</p>
                </div>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <a href="{{ route('admin.login') }}" class="btn btn-dark">
                        <i class="bi bi-shield-lock me-1"></i> Admin Login
                    </a>
                    <a href="{{ route('company.login') }}" class="btn btn-outline-dark">
                        <i class="bi bi-building me-1"></i> Company Login
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 card-title">Environment</h2>
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" style="width: 220px;">Laravel</th>
                                    <td>{{ app()->version() }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">PHP</th>
                                    <td>{{ PHP_VERSION }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Environment</th>
                                    <td><span class="badge text-bg-secondary">{{ config('app.env') }}</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Database connection</th>
                                    <td><code>{{ config('database.default') }}</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
