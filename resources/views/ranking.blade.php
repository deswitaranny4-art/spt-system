@extends('layouts.app')

@section('title', 'Supplier Ranking')
@section('page_title', 'Supplier Ranking') 

@push('head')
<link rel="stylesheet" href="{{ asset('css/ranking.css') }}">
@endpush

@section('content')

<!-- FILTER -->
<div class="filter-rank">

    <select id="sortOrder">
        <option value="desc">Highest Score</option>
        <option value="asc">Lowest Score</option>
    </select>

    <select id="supplierFilter">
        <option value="all">All Supplier</option>
    </select>

</div>

<!-- TABLE -->
<table id="rankingTable">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Supplier Name</th>
            <th>Delivery Score</th>
            <th>QC Score</th>
            <th>Total Score</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

@endsection

@push('scripts')
<script>

/* =========================
   DATA FROM LARAVEL
========================= */
const qcRaw       = @json($qcData);
const deliveryRaw = @json($deliveryData);

/* =========================
   GLOBAL STATE
========================= */
let globalQc       = [];
let globalDelivery = [];

/* =========================
   GRADE
========================= */
function getGrade(score){
    if(score === 100) return "A";
    if(score >= 80)   return "B";
    if(score >= 60)   return "C";
    return "D";
}

/* =========================
   LOAD FILTER SUPPLIER
========================= */
function loadSupplierFilter(qc, delivery){

    const suppliers = [...new Set([
        ...qc.map(d => d.supplier),
        ...delivery.map(d => d.supplier)
    ])].filter(Boolean).sort();

    const filter = document.getElementById("supplierFilter");
    filter.innerHTML = `<option value="all">All Supplier</option>`;

    suppliers.forEach(s => {
        filter.innerHTML += `<option value="${s}">${s}</option>`;
    });
}

/* =========================
   RENDER RANKING
========================= */
function renderRanking(qc, delivery){

    const tbody = document.querySelector("#rankingTable tbody");
    tbody.innerHTML = "";

    const suppliers = [...new Set([
        ...qc.map(d => d.supplier),
        ...delivery.map(d => d.supplier)
    ])].filter(Boolean);

    if(suppliers.length === 0){
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align:center;">
                    No Data
                </td>
            </tr>`;
        return;
    }

    let result = suppliers.map(s => {

        const dData = delivery.filter(d => d.supplier === s);
        const qData = qc.filter(d => d.supplier === s);

        const avgDel = dData.length
            ? dData.reduce((a,b) => a + Number(b.total || 0), 0) / dData.length
            : 0;

        const avgQc = qData.length
            ? qData.reduce((a,b) => a + Number(b.total_score || 0), 0) / qData.length
            : 0;

        const finalScore = (avgDel + avgQc) / 2;

        return {
            supplier : s,
            avgDel   : avgDel,
            avgQc    : avgQc,
            score    : finalScore,
            grade    : getGrade(finalScore)
        };
    });

    /* FILTER */
    const selectedSupplier =
        document.getElementById("supplierFilter")?.value || "all";

    if(selectedSupplier !== "all"){
        result = result.filter(r => r.supplier === selectedSupplier);
    }

    /* SORT */
    const order =
        document.getElementById("sortOrder")?.value || "desc";

    result.sort((a,b) =>
        order === "asc" ? a.score - b.score : b.score - a.score
    );

    /* RENDER ROWS */
    result.forEach((r, i) => {

        let rankClass = "";
        if(i === 0)      rankClass = "rank-1";
        else if(i === 1) rankClass = "rank-2";
        else if(i === 2) rankClass = "rank-3";

        tbody.innerHTML += `
        <tr class="${rankClass}">
            <td class="rank-number ${rankClass}">${i + 1}</td>
            <td class="supplier-name">${r.supplier}</td>
            <td>${r.avgDel.toFixed(1)}</td>
            <td>${r.avgQc.toFixed(1)}</td>
            <td><div class="score-box">${r.score.toFixed(1)}</div></td>
            <td><span class="grade grade-${r.grade}">${r.grade}</span></td>
        </tr>`;
    });
}

/* =========================
   INIT
========================= */
document.addEventListener("DOMContentLoaded", () => {

    const qc = qcRaw.map(d => ({
        supplier    : d.supplier,
        total_score : d.total_score
    }));

    const delivery = deliveryRaw.map(d => ({
        supplier : d.supplierSearch,
        total    : d.total_score
    }));

    globalQc       = qc;
    globalDelivery = delivery;

    loadSupplierFilter(qc, delivery);
    renderRanking(qc, delivery);

    document.getElementById("sortOrder")
        .addEventListener("change", () =>
            renderRanking(globalQc, globalDelivery));

    document.getElementById("supplierFilter")
        .addEventListener("change", () =>
            renderRanking(globalQc, globalDelivery));
});

</script>
@endpush