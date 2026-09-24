@extends('layouts.app')
@section('title','Submit Land for Joint Venture | InternEstate')
@section('content')
<section class="land-wizard-page"><div class="land-wizard-shell">
<header class="land-wizard-intro"><span>JOINT VENTURE DEVELOPMENT</span><h1>Turn your land into a lasting asset.</h1><p>Submit your property securely. Our legal and development teams will evaluate it and keep you informed at every stage.</p></header>
<aside class="land-share-notice" aria-label="Joint venture share notice"><span class="land-share-notice-icon">%</span><div><strong>Joint-venture share: 40% for the landowner, 60% for the developer</strong><p>By submitting your land, you acknowledge the proposed 40/60 share. The final agreement remains subject to legal verification, approved plans and written JV approval.</p></div></aside>
@if(session('submission_success'))<div class="land-success"><span>&#10003;</span><div><h2>Submission received</h2><p>Your unique tracking code is</p><strong>{{ session('submission_success') }}</strong><small>Save this code. Our legal team will contact you after initial review.</small></div></div>@else
<div class="wizard-steps"><button class="active" data-wizard-step="1"><b>1</b><span>Land Details</span></button><i></i><button data-wizard-step="2"><b>2</b><span>Ownership</span></button><i></i><button data-wizard-step="3"><b>3</b><span>Confirm</span></button></div>
<form method="POST" action="{{ route('land.submit.store') }}" enctype="multipart/form-data" id="landWizard" class="land-wizard-card" data-error-step="{{ $errors->hasAny(['phone','division','district','location','katha','road_width']) ? 1 : 2 }}">@csrf
<section class="wizard-panel active" data-wizard-panel="1"><div class="wizard-heading"><span>01</span><div><h2>Tell us about the land</h2><p>Location, access and plot measurements. Fields marked <em>*</em> are mandatory.</p></div></div><div class="land-form-grid">
<label>Phone number <em>*</em><input name="phone" type="tel" value="{{ old('phone', auth()->user()?->phone ?? '') }}" pattern="01[3-9][0-9]{8}" maxlength="11" inputmode="numeric" placeholder="01XXXXXXXXX" title="Use an 11-digit Bangladeshi mobile number beginning with 013 to 019. You can edit this number." required><small>From your account; editable. Use 11 digits starting 013 to 019</small>@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>Division <em>*</em><select name="division" id="landDivision" required><option value="">Select division</option>@foreach(array_keys($districtsByDivision) as $division)<option value="{{ $division }}" @selected(old('division')===$division)>{{ $division }}</option>@endforeach</select><small>Required: choose your land's division</small>@error('division')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>District <em>*</em><select name="district" id="landDistrict" required disabled><option value="">Choose division first</option></select><small>Required: choose the district in that division</small>@error('district')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>Land size (Katha) <em>*</em><input type="number" step=".01" min="2" max="999999" name="katha" value="{{ old('katha') }}" required><small>Minimum: 2 Katha</small>@error('katha')<small class="field-error">{{ $message }}</small>@enderror</label>
<label class="wide">Location address <em>*</em><textarea name="location" maxlength="500" required>{{ old('location') }}</textarea><small>Required: include area, road and plot details</small>@error('location')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>Road width (feet) <em>*</em><input type="number" min="20" max="500" name="road_width" value="{{ old('road_width') }}" required><small>Minimum: 20 feet; whole number up to 500</small>@error('road_width')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>Nearby landmark<input name="landmark" value="{{ old('landmark') }}" maxlength="255"><small>Optional: e.g. school, mosque or market</small></label>
<label class="wide">Short description<textarea name="description" maxlength="3000">{{ old('description') }}</textarea><small>Optional: add useful land details</small></label>
</div></section>
<section class="wizard-panel" data-wizard-panel="2"><div class="wizard-heading"><span>02</span><div><h2>Legal ownership</h2><p>Details must match the submitted NID and title deed. Fields marked <em>*</em> are mandatory.</p></div></div><div class="land-form-grid">
<label>Owner full name <em>*</em><input name="owner_name" value="{{ old('owner_name') }}" maxlength="255" required><small>Required: must match the NID and deed</small>@error('owner_name')<small class="field-error">{{ $message }}</small>@enderror</label>
<label>NID number <em>*</em><input name="nid_number" value="{{ old('nid_number', auth()->user()?->nid_number ?? '') }}" pattern="([0-9]{10}|[0-9]{13}|[0-9]{17})" minlength="10" maxlength="17" inputmode="numeric" title="Enter a 10, 13, or 17 digit NID. You can edit this number." required><small>From your account; editable. Enter 10, 13, or 17 digits</small>@error('nid_number')<small class="field-error">{{ $message }}</small>@enderror</label>
<label class="file-field">Title deed / document copy <em>*</em><input type="file" name="deed_path" accept=".pdf,.jpg,.jpeg,.png" required><small>Required: PDF, JPG or PNG; maximum 5 MB</small>@error('deed_path')<small class="field-error">{{ $message }}</small>@enderror</label>
<label class="file-field">Owner NID copy <em>*</em><input type="file" name="nid_path" accept=".pdf,.jpg,.jpeg,.png" required><small>Required: PDF, JPG or PNG; maximum 5 MB</small>@error('nid_path')<small class="field-error">{{ $message }}</small>@enderror</label>
</div></section>
<section class="wizard-panel" data-wizard-panel="3"><div class="wizard-heading"><span>03</span><div><h2>Review & confirmation</h2><p>Confirm the information before secure submission.</p></div></div><div id="landReview" class="land-review"></div><label class="land-consent"><input type="checkbox" required> I confirm that the information and uploaded ownership documents are accurate.</label></section>
@if($errors->any())<div class="land-errors">Please correct the highlighted information and submit again.</div>@endif
<footer class="wizard-actions"><button type="button" id="wizardBack" class="wizard-back" hidden>&larr; Back</button><button type="button" id="wizardNext" class="wizard-next">Continue &rarr;</button><button type="submit" id="wizardSubmit" class="wizard-next" hidden>Submit securely</button></footer>
</form>@endif
<div class="land-assurance"><div><b>Private & encrypted</b><span>Documents are stored securely</span></div><div><b>Legal review</b><span>Property counsel verifies ownership</span></div><div><b>Transparent tracking</b><span>Follow every JV stage</span></div></div>
</div></section>
<script>
(() => {
    const divisionSelect = document.getElementById('landDivision');
    const districtSelect = document.getElementById('landDistrict');
    if (!divisionSelect || !districtSelect) return;
    const districtsByDivision = @json($districtsByDivision);
    const previousDistrict = @json(old('district'));

    function updateDistricts(selectedDistrict = '') {
        const districts = districtsByDivision[divisionSelect.value] || [];
        districtSelect.replaceChildren(new Option(districts.length ? 'Select district' : 'Choose division first', ''));
        districts.forEach((district) => {
            const option = new Option(district, district);
            option.selected = district === selectedDistrict;
            districtSelect.add(option);
        });
        districtSelect.disabled = districts.length === 0;
    }

    divisionSelect.addEventListener('change', () => updateDistricts());
    updateDistricts(previousDistrict);
})();
</script>
@endsection
