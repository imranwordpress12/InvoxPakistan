@php
    $customer = $customer ?? null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    method="POST"
    action="{{ $customer ? route('company.customers.update', $customer) : route('company.customers.store') }}"
>
    @csrf
    @if ($customer)
        @method('PUT')
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="business_name"
                    value="{{ old('business_name', $customer?->business_name) }}"
                    class="form-control @error('business_name') is-invalid @enderror"
                    required
                >
                @error('business_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    NTN/CNIC
                    <i class="bi bi-info-circle" title="Enter 7-digit NTN and 13-digit CNIC without dashes"></i>
                    <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="ntn_cnic"
                    value="{{ old('ntn_cnic', $customer?->ntn_cnic) }}"
                    class="form-control @error('ntn_cnic') is-invalid @enderror"
                    required
                >
                @error('ntn_cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <textarea
                    name="address"
                    rows="2"
                    class="form-control @error('address') is-invalid @enderror"
                    required
                >{{ old('address', $customer?->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Registration Type <span class="text-danger">*</span></label>
                <select
                    name="buyer_registration_type"
                    class="form-select @error('buyer_registration_type') is-invalid @enderror"
                    required
                >
                    <option value="" disabled @selected(! old('buyer_registration_type', $customer?->buyer_registration_type))>Select...</option>
                    <option value="registered" @selected(old('buyer_registration_type', $customer?->buyer_registration_type) === 'registered')>Registered</option>
                    <option value="unregistered" @selected(old('buyer_registration_type', $customer?->buyer_registration_type) === 'unregistered')>Unregistered</option>
                </select>
                @error('buyer_registration_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Province <span class="text-danger">*</span></label>
                <select
                    name="province"
                    class="form-select @error('province') is-invalid @enderror"
                    required
                >
                    <option value="" disabled @selected(! old('province', $customer?->province))>Select...</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(old('province', $customer?->province) === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">STRN</label>
                <input
                    type="text"
                    name="strn"
                    value="{{ old('strn', $customer?->strn) }}"
                    class="form-control @error('strn') is-invalid @enderror"
                >
                @error('strn') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" @selected(old('status', $customer?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $customer?->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Contact Person</label>
                <input
                    type="text"
                    name="contact_person"
                    value="{{ old('contact_person', $customer?->contact_person) }}"
                    class="form-control @error('contact_person') is-invalid @enderror"
                >
                @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $customer?->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                >
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Contact Number</label>
                <input
                    type="text"
                    name="contact_number"
                    value="{{ old('contact_number', $customer?->contact_number) }}"
                    class="form-control @error('contact_number') is-invalid @enderror"
                >
                @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $customer ? 'Update Customer' : 'Save Customer' }}</button>
        <a href="{{ route('company.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
