@extends('admin.layouts.app')
@section('title', 'Global Analytics')
@section('page-heading', 'Global Analytics')

@section('content')
<div class="analytics-page">
    <header class="analytics-hero">
        <div><span>Business intelligence</span><h2>Global Store Analytics</h2><p>Revenue, orders, customers, products, payments and geographic performance in one workspace.</p></div>
        <a href="{{ route('admin.analytics.export', request()->query()) }}"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
    </header>

    <section class="analytics-panel filter-panel">
        <form method="GET" action="{{ route('admin.analytics.index') }}">
            <div><label>Start date</label><input type="date" name="start_date" value="{{ $start->toDateString() }}" required></div>
            <div><label>End date</label><input type="date" name="end_date" value="{{ $end->toDateString() }}" required></div>
            <button type="submit"><i class="fa-solid fa-filter"></i> Apply dates</button>
            <div class="quick-ranges"><a href="{{ route('admin.analytics.index', ['start_date'=>now()->subDays(6)->toDateString(),'end_date'=>now()->toDateString()]) }}">7 days</a><a href="{{ route('admin.analytics.index', ['start_date'=>now()->subDays(29)->toDateString(),'end_date'=>now()->toDateString()]) }}">30 days</a><a href="{{ route('admin.analytics.index', ['start_date'=>now()->startOfYear()->toDateString(),'end_date'=>now()->toDateString()]) }}">This year</a></div>
        </form>
    </section>

    <section class="metric-grid">
        @php
            $cards = [
                ['revenue','Revenue','fa-dollar-sign',true], ['orders','Paid orders','fa-bag-shopping',false],
                ['average_order','Average order','fa-receipt',true], ['customers','New customers','fa-user-plus',false],
                ['items','Items sold','fa-box-open',false], ['discounts','Discounts given','fa-tags',true],
            ];
        @endphp
        @foreach($cards as [$key,$label,$icon,$money])
            @php($metric=$metrics[$key])
            <article><div class="metric-icon"><i class="fa-solid {{ $icon }}"></i></div><div><small>{{ $label }}</small><strong>{{ $money ? '$'.number_format($metric['value'],2) : number_format($metric['value']) }}</strong><span class="{{ $metric['direction'] }}"><i class="fa-solid {{ $metric['direction']==='up'?'fa-arrow-trend-up':($metric['direction']==='down'?'fa-arrow-trend-down':'fa-minus') }}"></i> {{ number_format(abs($metric['change']),1) }}% vs previous period</span></div></article>
        @endforeach
    </section>

    <section class="analytics-panel wide-chart"><div class="panel-heading"><div><h3>Revenue and order trend</h3><p>{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p></div></div><div class="chart-box"><canvas id="revenueOrdersChart"></canvas></div></section>

    <div class="chart-grid">
        <section class="analytics-panel"><div class="panel-heading"><div><h3>Order status</h3><p>All orders created in this period</p></div></div><div class="chart-box compact"><canvas id="statusChart"></canvas></div></section>
        <section class="analytics-panel"><div class="panel-heading"><div><h3>Payment methods</h3><p>Customer payment preferences</p></div></div><div class="chart-box compact"><canvas id="paymentChart"></canvas></div></section>
        <section class="analytics-panel"><div class="panel-heading"><div><h3>Customer acquisition</h3><p>New registered customer accounts</p></div></div><div class="chart-box compact"><canvas id="customerChart"></canvas></div></section>
    </div>

    <div class="table-grid">
        <section class="analytics-panel"><div class="panel-heading"><div><h3>Top-selling products</h3><p>Ranked by paid sales value</p></div></div><div class="table-wrap"><table><thead><tr><th>Product</th><th>Units</th><th>Sales</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td><strong>{{ $product->product_title }}</strong><small>{{ $product->sku }}</small></td><td>{{ number_format($product->units) }}</td><td><strong>${{ number_format($product->sales,2) }}</strong></td></tr>@empty<tr><td colspan="3" class="empty">No paid product sales in this period.</td></tr>@endforelse</tbody></table></div></section>
        <section class="analytics-panel"><div class="panel-heading"><div><h3>Sales by country</h3><p>Based on shipping or billing country</p></div></div><div class="table-wrap"><table><thead><tr><th>Country</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>@forelse($topCountries as $country)<tr><td><strong>{{ $country->country }}</strong></td><td>{{ number_format($country->orders) }}</td><td><strong>${{ number_format($country->revenue,2) }}</strong></td></tr>@empty<tr><td colspan="3" class="empty">No geographic sales data in this period.</td></tr>@endforelse</tbody></table></div></section>
    </div>
</div>
<script type="application/json" id="globalAnalyticsData">@json($chartData)</script>
@endsection

@push('page-styles')
<style>
.analytics-page{display:grid;gap:19px;color:#172033}.analytics-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px;border-radius:20px;background:linear-gradient(135deg,#0f172a,#0369a1);color:#fff}.analytics-hero span{color:#bae6fd;font-size:10px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.analytics-hero h2{margin:6px 0 0;font-size:30px}.analytics-hero p{margin:7px 0 0;color:#e0f2fe}.analytics-hero a{padding:11px 14px;border-radius:10px;background:#fff;color:#0369a1;font-weight:850;text-decoration:none}.analytics-panel{overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.filter-panel form{display:flex;align-items:end;gap:12px;padding:15px 18px}.filter-panel label{display:block;margin-bottom:6px;font-size:10px;font-weight:850;text-transform:uppercase}.filter-panel input{padding:9px;border:1px solid #cbd5e1;border-radius:8px}.filter-panel button{padding:10px 13px;border:0;border-radius:8px;background:#0369a1;color:#fff;font-weight:800;cursor:pointer}.quick-ranges{display:flex;gap:6px;margin-left:auto}.quick-ranges a{padding:8px 10px;border-radius:8px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:750;text-decoration:none}.metric-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:13px}.metric-grid article{display:flex;gap:12px;padding:17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.metric-icon{display:grid;width:41px;height:41px;flex:0 0 41px;place-items:center;border-radius:11px;background:#e0f2fe;color:#0369a1}.metric-grid small,.metric-grid strong,.metric-grid span{display:block}.metric-grid small{color:#64748b}.metric-grid strong{margin-top:4px;font-size:22px}.metric-grid span{margin-top:5px;color:#64748b;font-size:9px}.metric-grid span.up{color:#047857}.metric-grid span.down{color:#be123c}.panel-heading{padding:16px 18px;border-bottom:1px solid #e2e8f0}.panel-heading h3{margin:0;font-size:16px}.panel-heading p{margin:4px 0 0;color:#64748b;font-size:11px}.chart-box{position:relative;height:360px;padding:17px}.chart-box.compact{height:270px}.chart-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:17px}.table-grid{display:grid;grid-template-columns:1fr 1fr;gap:17px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse}th{padding:10px 13px;background:#f8fafc;color:#64748b;font-size:9px;text-align:left;text-transform:uppercase}td{padding:12px 13px;border-top:1px solid #edf0f5;font-size:11px}td strong,td small{display:block}td small{margin-top:3px;color:#94a3b8}.empty{padding:35px;text-align:center;color:#64748b}
@media(max-width:1100px){.chart-grid{grid-template-columns:1fr 1fr}.metric-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:760px){.analytics-hero,.filter-panel form{align-items:stretch;flex-direction:column}.quick-ranges{margin-left:0}.metric-grid,.chart-grid,.table-grid{grid-template-columns:1fr}.filter-panel input{width:100%;box-sizing:border-box}}
</style>
@endpush

@push('page-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const raw=document.getElementById('globalAnalyticsData'); if(!raw||typeof Chart==='undefined')return; const data=JSON.parse(raw.textContent);
 Chart.defaults.font.family='Montserrat,Arial,sans-serif'; Chart.defaults.color='#64748b';
 new Chart(document.getElementById('revenueOrdersChart'),{type:'line',data:{labels:data.daily.labels,datasets:[{label:'Revenue ($)',data:data.daily.revenue,borderColor:'#0284c7',backgroundColor:'rgba(14,165,233,.12)',fill:true,tension:.35,yAxisID:'y'},{label:'Orders',data:data.daily.orders,borderColor:'#7c3aed',backgroundColor:'#7c3aed',tension:.35,yAxisID:'y1'}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},scales:{y:{beginAtZero:true},y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false}}}}});
 const colors=['#0284c7','#7c3aed','#059669','#d97706','#e11d48','#64748b','#0891b2','#4f46e5'];
 new Chart(document.getElementById('statusChart'),{type:'doughnut',data:{labels:data.statuses.labels,datasets:[{data:data.statuses.values,backgroundColor:colors}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}});
 new Chart(document.getElementById('paymentChart'),{type:'bar',data:{labels:data.payments.labels,datasets:[{label:'Orders',data:data.payments.values,backgroundColor:'#0ea5e9',borderRadius:7}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
 new Chart(document.getElementById('customerChart'),{type:'line',data:{labels:data.customers.labels,datasets:[{label:'New customers',data:data.customers.values,borderColor:'#059669',backgroundColor:'rgba(5,150,105,.12)',fill:true,tension:.35}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
});
</script>
@endpush
