{{-- Shared by create.blade.php and edit.blade.php. $company is null on create. --}}
@php($c = $company ?? null)

<div class="col-md-6">
    <label class="form-label">Company Name</label>
    <input type="text" name="name" value="{{ old('name', $c?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
    <label class="form-label">Business Name</label>
    <input type="text" name="business_name" value="{{ old('business_name', $c?->business_name) }}" class="form-control @error('business_name') is-invalid @enderror">
    @error('business_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email', $c?->email) }}" class="form-control @error('email') is-invalid @enderror" required>
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" value="{{ old('phone', $c?->phone) }}" class="form-control @error('phone') is-invalid @enderror">
    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-12">
    <label class="form-label">Address</label>
    <input type="text" name="address" value="{{ old('address', $c?->address) }}" class="form-control @error('address') is-invalid @enderror">
    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-4">
    <label class="form-label">City</label>
    <input type="text" name="city" value="{{ old('city', $c?->city) }}" class="form-control @error('city') is-invalid @enderror">
    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-4">
    <label class="form-label">Province</label>
    <input type="text" name="province" value="{{ old('province', $c?->province) }}" class="form-control @error('province') is-invalid @enderror">
    @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-4">
    <label class="form-label">Country</label>
    <input type="text" name="country" value="{{ old('country', $c?->country ?? 'Pakistan') }}" class="form-control @error('country') is-invalid @enderror">
    @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
    <label class="form-label">NTN / CNIC</label>
    <input type="text" name="ntn_cnic" value="{{ old('ntn_cnic', $c?->ntn_cnic) }}" class="form-control @error('ntn_cnic') is-invalid @enderror">
    @error('ntn_cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
    <label class="form-label">Business Registration No.</label>
    <input type="text" name="business_registration_number" value="{{ old('business_registration_number', $c?->business_registration_number) }}" class="form-control @error('business_registration_number') is-invalid @enderror">
    @error('business_registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@if ($c)
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="active" @selected(old('status', $c->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $c->status) === 'inactive')>Inactive</option>
        </select>
        <div class="form-text">Administrative status only — does not affect the subscription/payment state.</div>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
@endif
