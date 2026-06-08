@extends('layouts.app')

@section('title', 'SPT Dashboard')

@section('page_title', 'Performance Dashboard')

@push('head')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')

<!-- TOPBAR -->
<div class="topbar">

    <div class="topbar-left">

        <!-- SUPPLIER -->
        <div class="filter-box"> 
            <select id="supplierSelect">
                <option value="ALL">All Suppliers</option>
            </select>
        </div>

        <!-- MONTH -->
        <div class="filter-box">
            <select id="monthFilter">
                <option value="ALL">All Month</option>
                <option value="0">January</option>
                <option value="1">February</option>
                <option value="2">March</option>
                <option value="3">April</option>
                <option value="4">May</option>
                <option value="5">June</option>
                <option value="6">July</option>
                <option value="7">August</option>
                <option value="8">September</option>
                <option value="9">October</option>
                <option value="10">November</option>
                <option value="11">December</option>
            </select>
        </div>

        <!-- YEAR -->
        <div class="filter-box">
            <select id="yearFilter">
                <option value="ALL">All Year</option>
            </select>
        </div>

    </div>

    <div class="topbar-right">
        <p id="date"></p>
    </div>

</div>

<!-- PERFORMANCE -->
<div class="chart-section">
    <div class="chart-box">
        <h3>Supplier Performance</h3>
        <div class="chart-container tall">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<!-- TREND -->
<div class="chart-row">
    <div class="chart-box">
        <h3>QC Score Per Supplier</h3>
        <div class="chart-container">
            <canvas id="qcChart"></canvas>
        </div>
    </div>

    <div class="chart-box">
        <h3>Delivery Score Per Supplier</h3>
        <div class="chart-container">
            <canvas id="delivChart"></canvas>
        </div>
    </div>
</div>

<!-- TOP -->
<div class="section">
    <div class="list-box">
        <h3>Top 5 Best Suppliers</h3>
        <div id="bestList"></div>
    </div>

    <div class="list-box">
        <h3>Top 5 Worst Suppliers</h3>
        <div id="worstList"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>

/* ======================
THRESHOLD LINE
====================== */
const thresholdLine = {
    id:'thresholdLine',
    afterDraw(chart,args,pluginOptions){
        const { ctx, chartArea:{left,right}, scales:{y} } = chart;
        if(!y) return;
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(left, y.getPixelForValue(pluginOptions.value));
        ctx.lineTo(right, y.getPixelForValue(pluginOptions.value));
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'red';
        ctx.shadowColor = 'rgba(255,0,0,0.45)';
        ctx.shadowBlur = 6;
        ctx.stroke();
        ctx.restore();
    }
};

/* ======================
DATE
====================== */
document.getElementById("date").innerText =
new Date().toLocaleDateString('en-US',{
    weekday:'long', year:'numeric', month:'long', day:'numeric'
});

/* ======================
DATA FROM DATABASE
====================== */
const qc = @json($qcData);
const delivery = @json($deliveryData);

/* ======================
SUPPLIER SET
====================== */
let supplierSet = new Set([
    ...qc.map(d=>d.supplier).filter(Boolean),
    ...delivery.map(d=>d.supplierSearch).filter(Boolean)
]);
let suppliers = [...supplierSet];

/* ======================
SUPPLIER DROPDOWN
====================== */
const supplierSelect = document.getElementById("supplierSelect");
suppliers.forEach(s=>{
    let opt = document.createElement("option");
    opt.value = s;
    opt.textContent = s;
    supplierSelect.appendChild(opt);
});

/* ======================
YEAR DROPDOWN
====================== */
const yearFilter = document.getElementById("yearFilter");
let yearSet = new Set();
[...qc, ...delivery].forEach(item => {
    if(item.del_year) yearSet.add(Number(item.del_year));
});
[...yearSet].sort((a,b)=>b-a).forEach(year=>{
    let opt = document.createElement("option");
    opt.value = year;
    opt.textContent = year;
    yearFilter.appendChild(opt);
});

/* ======================
FILTER DATA
====================== */
function filterData(data){
    let supplier = supplierSelect.value;
    let month = document.getElementById("monthFilter").value;
    let year = document.getElementById("yearFilter").value;

    return data.filter(item => {
        let itemSupplier = item.supplier || item.supplierSearch || "";
        let supplierMatch = supplier === "ALL" || itemSupplier === supplier;

        let itemMonth = item.del_month ? Number(item.del_month) - 1 : null;
        let monthMatch = month === "ALL" || itemMonth === Number(month);

        let itemYear = item.del_year ? Number(item.del_year) : null;
        let yearMatch = year === "ALL" || itemYear === Number(year);

        return supplierMatch && monthMatch && yearMatch;
    });
}

/* ======================
SUPPLIER PERFORMANCE
====================== */
function getSupplierPerformance(name){
    let qcFiltered = filterData(qc).filter(d => d.supplier === name);
    let delFiltered = filterData(delivery).filter(d => d.supplierSearch === name);

    let qcAvg = qcFiltered.length
        ? qcFiltered.reduce((s,x) => s + (Number(x.total_score)||0), 0) / qcFiltered.length
        : 0;

    let delAvg = delFiltered.length
        ? delFiltered.reduce((s,x) => s + (Number(x.total_score)||0), 0) / delFiltered.length
        : 0;

    return ((qcAvg + delAvg) / 2).toFixed(1);
}

/* ======================
BEST WORST
====================== */
function renderRankingList(){
    let ranking = suppliers.map(name=>{
        let score = getSupplierPerformance(name);
        let grade = score >= 80 ? "A" : score >= 60 ? "B" : score >= 40 ? "C" : "D";
        return { name, score, grade };
    });

    let sorted = [...ranking].sort((a,b)=> b.score - a.score);
    renderList(sorted.slice(0,5), "bestList");
    renderList(sorted.slice(-5).reverse(), "worstList");
}

function renderList(data,id){
    let el = document.getElementById(id);
    el.innerHTML = "";
    data.forEach((item,i)=>{
        el.innerHTML += `
        <div class="item">
            <span>${i+1}. ${item.name}</span>
            <span><b>${item.score}</b> <small>(${item.grade})</small></span>
        </div>`;
    });
}

/* ======================
BAR CHART
====================== */
let barChart;
function renderBarChart(){
    let filteredSuppliers = suppliers.filter(name=>{
        let q = filterData(qc).filter(d=>d.supplier===name);
        let d = filterData(delivery).filter(d=>d.supplierSearch===name);
        return q.length || d.length;
    });

    if(barChart) barChart.destroy();
    barChart = new Chart(document.getElementById("barChart"), {
        type:'bar',
        data:{
            labels: filteredSuppliers,
            datasets:[{
                data: filteredSuppliers.map(s=> getSupplierPerformance(s)),
                backgroundColor:'#2d5fde',
                borderRadius:12,
                maxBarThickness:240,
                categoryPercentage:0.95,
                barPercentage:0.95
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{
                x:{ grid:{ display:false } },
                y:{ beginAtZero:true, max:100, ticks:{ stepSize:10 } }
            }
        }
    });
}

/* ======================
TREND CHART
====================== */
/* ======================
TREND CHART (PER BULAN)
====================== */
let qcChart;
let delivChart;

const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function renderCharts(){
    let qcFiltered = filterData(qc);
    let deliveryFiltered = filterData(delivery);

    // Group QC by bulan
    let qcMonthMap = {};
    qcFiltered.forEach(item => {
        if(item.del_month == null) return;
        let key = Number(item.del_month) - 1;
        if(!qcMonthMap[key]) qcMonthMap[key] = [];
        qcMonthMap[key].push(Number(item.total_score) || 0);
    });

    // Group Delivery by bulan
    let delivMonthMap = {};
    deliveryFiltered.forEach(item => {
        if(item.del_month == null) return;
        let key = Number(item.del_month) - 1;
        if(!delivMonthMap[key]) delivMonthMap[key] = [];
        delivMonthMap[key].push(Number(item.total_score) || 0);
    });

    // Paksa semua 12 bulan, 0 kalau tidak ada data
    let allMonths = [0,1,2,3,4,5,6,7,8,9,10,11];

    let qcData = allMonths.map(k =>
        qcMonthMap[k]
            ? (qcMonthMap[k].reduce((a,b)=>a+b,0) / qcMonthMap[k].length).toFixed(1)
            : 0
    );

    let delivData = allMonths.map(k =>
        delivMonthMap[k]
            ? (delivMonthMap[k].reduce((a,b)=>a+b,0) / delivMonthMap[k].length).toFixed(1)
            : 0
    );

    if(qcChart) qcChart.destroy();
    if(delivChart) delivChart.destroy();

   qcChart = new Chart(document.getElementById("qcChart"), {
    type:'line',
    data:{ 
        labels: monthNames, 
        datasets:[{ 
            data: qcData, 
            borderColor:'#f59e0b',
            backgroundColor:'rgba(245,158,11,0.12)',
            borderWidth: 2.5,
            pointBackgroundColor:'#f59e0b',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
            tension: 0.4
        }] 
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ display:false }, thresholdLine:{ value:20 } },
        scales:{ y:{ beginAtZero:true, max:100, ticks:{ stepSize:10 } } }
    },
    plugins:[thresholdLine]
});

delivChart = new Chart(document.getElementById("delivChart"), {
    type:'line',
    data:{ 
        labels: monthNames, 
        datasets:[{ 
            data: delivData, 
            borderColor:'#f59e0b',
            backgroundColor:'rgba(245,158,11,0.12)',
            borderWidth: 2.5,
            pointBackgroundColor:'#f59e0b',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
            tension: 0.4
        }] 
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ display:false }, thresholdLine:{ value:95 } },
        scales:{ y:{ beginAtZero:true, max:100, ticks:{ stepSize:10 } } }
    },
    plugins:[thresholdLine]
});
}
/* ======================
RENDER ALL
====================== */
function renderAll(){
    renderBarChart();
    renderCharts();
    renderRankingList();
}

/* ======================
EVENT
====================== */
supplierSelect.addEventListener("change", renderAll);
document.getElementById("monthFilter").addEventListener("change", renderAll);
document.getElementById("yearFilter").addEventListener("change", renderAll);

/* ======================
INIT
====================== */
renderAll();

</script>
@endpush