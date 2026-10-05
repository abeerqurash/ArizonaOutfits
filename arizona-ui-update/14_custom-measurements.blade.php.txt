<fieldset class="az-custom-measurements" data-custom-measurements hidden disabled>
<legend>Custom size · measurements in inches</legend>
<p>Enter your measurements for this item. These details will be saved with your order.</p>
<div class="az-measurement-grid">
@foreach(\App\Services\CustomMeasurementsService::FIELDS as $measurementKey=>$measurementLabel)
<label>{{ $measurementLabel }} <span>inches</span><input type="number" name="custom_measurements[{{ $measurementKey }}]" min="0.1" max="200" step="0.01" required inputmode="decimal" value="{{ old('custom_measurements.'.$measurementKey) }}"></label>
@endforeach
</div></fieldset>
