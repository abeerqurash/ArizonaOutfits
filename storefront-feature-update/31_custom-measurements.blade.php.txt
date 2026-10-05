<fieldset data-custom-measurements hidden disabled><legend>Custom size measurements (inches)</legend>
@foreach(\App\Services\CustomMeasurementsService::FIELDS as $measurementKey=>$measurementLabel)
<label style="display:block;margin:10px 0">{{ $measurementLabel }} — inches <input type="number" name="custom_measurements[{{ $measurementKey }}]" min="0.1" max="200" step="0.01" required inputmode="decimal" value="{{ old('custom_measurements.'.$measurementKey) }}" style="display:block;width:100%;padding:10px"></label>
@endforeach
</fieldset>