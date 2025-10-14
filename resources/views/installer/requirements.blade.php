@extends('installer::installer.layout')

@section('content')


    <h3 class="d-flex align-items-center mb-4 h5 text-dark">
        <span class="material-icons me-2 text-primary" style="font-size: 1.2em;">settings</span> Application Setup
    </h3>

    <form action="{{ route('install.environment.set') }}" method="post" class="needs-validation" novalidate>
        @csrf

        <h5 class="text-primary mb-3">Application Identity</h5>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="application_url" class="form-label small fw-medium text-secondary">Application URL</label>
                <input type="text" id="application_url" name="application_url"
                    class="form-control"
                    value="{{ rtrim(str_replace('/public', '', request()->getSchemeAndHttpHost()), '/') }}" readonly
                    required>
                <div class="form-text">This field is read-only.</div>
            </div>

            <div class="col-md-6">
                <label for="app_name" class="form-label small fw-medium text-secondary">Application Name</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">label</span>
                    </span>
                    <input type="text" id="app_name" name="app_name" class="form-control"
                        placeholder="e.g., Relief Hub Pro" required>
                    <div class="invalid-feedback">Application Name is required.</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="text-primary mb-3">License Details</h5>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="domain_name" class="form-label small fw-medium text-secondary">Domain Name (Current Host)</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">language</span>
                    </span>
                    <input type="text" id="domain_name" name="domain_name" class="form-control"
                        value="{{ request()->getHost() }}" required>
                    <div class="invalid-feedback">Domain Name is required.</div>
                </div>
            </div>

            <div class="col-md-6">
                <label for="codecanyon_username" class="form-label small fw-medium text-secondary">CodeCanyon Username</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">person</span>
                    </span>
                    <input type="text" id="codecanyon_username" name="codecanyon_username" class="form-control"
                        placeholder="Your Envato Username" required>
                    <div class="invalid-feedback">CodeCanyon Username is required.</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label for="codecanyon_license_key" class="form-label small fw-medium text-secondary">CodeCanyon License Key</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">vpn_key</span>
                    </span>
                    <input type="text" id="codecanyon_license_key" name="codecanyon_license_key" class="form-control"
                        placeholder="Purchase Code (e.g., 1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p)" required>
                    <div class="invalid-feedback">CodeCanyon License Key is required.</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="text-primary mb-3">Database Connection</h5>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="db_host" class="form-label small fw-medium text-secondary">Database Host</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">storage</span>
                    </span>
                    <input type="text" id="db_host" name="db_host" class="form-control"
                        value="127.0.0.1" required>
                    <div class="invalid-feedback">Database Host is required.</div>
                </div>
            </div>

            <div class="col-md-4">
                <label for="db_port" class="form-label small fw-medium text-secondary">Database Port</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">settings_input_component</span>
                    </span>
                    <input type="text" id="db_port" name="db_port" class="form-control"
                        value="3306" required>
                    <div class="invalid-feedback">Database Port is required.</div>
                </div>
            </div>

            <div class="col-md-4">
                <label for="db_user" class="form-label small fw-medium text-secondary">Database User</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">account_circle</span>
                    </span>
                    <input type="text" id="db_user" name="db_user" class="form-control"
                        value="root" required>
                    <div class="invalid-feedback">Database User is required.</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="db_name" class="form-label small fw-medium text-secondary">Database Name</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">dvr</span>
                    </span>
                    <input type="text" id="db_name" name="db_name" class="form-control"
                        value="relief_hub_pro" required>
                    <div class="invalid-feedback">Database Name is required.</div>
                </div>
            </div>

            <div class="col-md-6">
                <label for="db_pass" class="form-label small fw-medium text-secondary">Database Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="material-icons">lock</span>
                    </span>
                    <input type="password" id="db_pass" name="db_pass" class="form-control"
                        value="11111111">
                </div>
                <div class="form-text">Leave blank if no password is set.</div>
            </div>
        </div>

        <div class="d-flex justify-content-end pt-3">
            <button type="submit"
                class="btn btn-primary d-flex align-items-center px-4 py-2">
                Connect Application to Database
                <span class="material-icons ms-2" style="font-size: 1.2em;">arrow_right_alt</span>
            </button>
        </div>
    </form>

    <script>
        // Custom validation for Bootstrap forms
        (function () {
            'use strict'
            const form = document.querySelector('.needs-validation');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            }
        })()
    </script>
@endsection
