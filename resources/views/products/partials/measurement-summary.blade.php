@if(!empty($item['custom_measurements']))
<dl class="custom-size-summary">@foreach($item['custom_measurements'] as $measurementKey=>$measurementValue)<div><dt>{{ \App\Services\CustomMeasurementsService::FIELDS[$measurementKey] ?? $measurementKey }} (inches)</dt><dd>{{ $measurementValue }}</dd></div>@endforeach</dl>
@endif