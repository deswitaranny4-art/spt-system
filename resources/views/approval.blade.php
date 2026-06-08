@extends('layouts.app')

@section('title', 'SPT - Approval Workflow')

@section('page_title', 'Approval Workflow')

@push('head')
<link rel="stylesheet" href="{{ asset('css/approval.css') }}">
@endpush

@section('content')

<div id="mainContent">

<div class="filter-box">

    <input
        type="text"
        id="searchDoc"
        class="search-input"
        placeholder="Search Doc No..."
        onkeyup="searchDocument(this.value)"
    >

    <select onchange="setFilterStatus(this.value)">
        <option value="ALL">All Status</option>
        <option value="PROGRESS">Progress</option>
        <option value="WAITING">Waiting</option>
        <option value="APPROVED">Approved</option>
        <option value="REJECTED">Rejected</option>
    </select>

    <select
        id="filterSupplier"
        onchange="setFilterSupplier(this.value)"
    >
        <option value="ALL">All Supplier</option>
    </select>

</div>

<table class="history-table">

    <thead>

        <tr>
            <th>Doc No</th>
            <th>Supplier</th>
            <th>Period</th>
            <th>Approved By</th>

            <th>
                Submitted On

                <i class="fa-solid fa-sort sort-icon"
                   onclick="toggleSortDate()"></i>
            </th>

            <th>Department</th>
            <th>Status</th>
            <th>Detail</th>
        </tr>

    </thead>

    <tbody id="purchase-list"></tbody>

</table>

<div class="table-footer">
    <div class="show-entries">
        Show
        <select onchange="changeRowsPerPage(this.value)">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        entries
    </div>

    <div id="pagination" class="pagination"></div>
</div>

</div> 
<div id="detailCard"
     class="panel-card detail-card"
     style="display:none;">

    <div id="detail-section"></div>

</div>

<div id="confirmModal" class="popup">
    <div class="popup-box">
        <p id="confirmText"></p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button onclick="closeConfirm()">Cancel</button>
            <button id="yesBtn">Yes</button>
        </div>
    </div>
</div>

<div id="successPopup" class="popup">

    <div class="popup-box">

        <p id="successText"></p>

        <button onclick="closeSuccess()">
            OK
        </button>

    </div>

</div>
<div id="failedPopup" class="popup" style="display:none;">
    <div class="popup-box">
        <p id="failedText"></p>
        <button onclick="closeFailedPopup()">OK</button>
    </div>
</div>

@endsection

@push('scripts')

<script>

/* =========================
   USER
========================= */

const currentUser = {

    name: "{{ Auth::user()->name }}",

    role: "{{ Auth::user()->role }}",

    department: "{{ Auth::user()->department }}"
};

/* =========================
   APPROVAL FLOW
========================= */

const approvalFlow = [

    { role: "Supervisor", dept: "Quality Control" },
    { role: "Manager", dept: "Quality Control" },

    { role: "Supervisor", dept: "PPIC" },
    { role: "Manager", dept: "PPIC" },

    { role: "Leader", dept: "Purchasing" },
    { role: "Manager", dept: "Purchasing" },

    { role: "General Manager", dept: "Production" }
];

/* =========================
   STATE
========================= */

let currentSearchDoc = "";
let currentFilterStatus = "ALL";
let currentFilterSupplier = "ALL";
let currentSortDate = "DESC";
let selectedDoc = null;
let currentPage = 1;
let rowsPerPage = 10;

/* =========================
   DATA
========================= */

let qcRaw = @json($qcData);

let deliveryRaw = @json($deliveryData);

let approvalRaw = @json($approvalData);

let historyRaw = @json($historyData);

/* =========================
   NORMALIZE
========================= */

function normalizeQC(d){

    return {

        ...d,

        docNumber: d.docNumber || "-",

        supplier: d.supplier || "-"
    };
}

function normalizeDEL(d){

    return {

        ...d,

        docNumber: d.docNumber || "-"
    };
}

qcRaw = qcRaw.map(normalizeQC);

deliveryRaw = deliveryRaw.map(normalizeDEL);

/* =========================
   JOIN
========================= */

const qcData = qcRaw.map(q => {

    const delivery = deliveryRaw.find(d =>

        String(d.docNumber) ===
        String(q.docNumber)
    );

    return {

        ...q,

        delivery: delivery || {},

        period: formatPeriod(q.del_month, q.del_year)
    };
});

/* =========================
   SUPPLIER FILTER
========================= */

const supplierSelect =
    document.getElementById("filterSupplier");

const uniqueSuppliers = [

    ...new Set(
        qcData.map(d => d.supplier)
    )

];

uniqueSuppliers.forEach(s => {

    const opt =
        document.createElement("option");

    opt.value = s;

    opt.textContent = s;

    supplierSelect.appendChild(opt);
});

/* =========================
   WORKFLOW
========================= */

function getWF(doc){

    const wf = approvalRaw.find(a =>

        String(a.doc_number) ===
        String(doc)
    );

    const history = historyRaw.filter(h =>

        String(h.doc_number) ===
        String(doc)
    );

    return {

        step: wf?.current_step || 0,

        status: wf?.status || "WAITING",

        current_department:
            wf?.current_department || "-",

        history: history
    };
}

function getCurrentUserStep(){

    return approvalFlow.findIndex(s =>

        s.role === currentUser.role &&

        s.dept === currentUser.department
    );
}

function changeRowsPerPage(value){
    rowsPerPage=parseInt(value);
    currentPage=1;
    renderTable();
}

function getUserStatus(doc){

    const wf = getWF(doc);

    const userStep =
        getCurrentUserStep();

    if(wf.status === "REJECTED"){
        return "REJECTED";
    }

    if(wf.status === "APPROVED"){
        return "APPROVED";
    }

    const alreadyApproved =
    wf.history.some(h =>

        h.user_name === currentUser.name
    );

    if(alreadyApproved){
        return "APPROVED";
    }

    if(wf.step === userStep){
        return "WAITING";
    }

    return "PROGRESS";
}

function getStatus(doc){

    const wf = getWF(doc);

    return wf.status;
}

function getStatusLabel(doc){

    const wf = getWF(doc);

    if(wf.status === "APPROVED"){
        return "Completed";
    }

    if(wf.status === "REJECTED"){
        return "Rejected";
    }

    const step =
        approvalFlow[wf.step];

    return `${step.role} - ${step.dept}`;
}

/* =========================
   APPROVE / REJECT
========================= */

async function setStatus(doc, action){

    try{

        const response = await fetch(

            "/approval/update",

            {

                method: "POST",

                headers: {

                    "Content-Type":
                        "application/json",

                    "X-CSRF-TOKEN":

                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                },

                 body: JSON.stringify({

                    doc_number: doc,

                    action: action
                })
            }
        );

const text = await response.text();

        console.log(text);

        const result = JSON.parse(text);

        if(result.success){

            approvalRaw =
                result.approvals;

            historyRaw =
                result.histories;

            closeConfirm();

            renderTable();

            showDetail(doc);

            showSuccess(

                `Document ${doc} ${action}`
            );
        }

} catch(err){

    console.error(err);

    showFailed("Failed to update approval. Please try again.");
}
}

/* =========================
   FILTER
========================= */

function searchDocument(v){
    currentSearchDoc=v.toLowerCase();
    currentPage=1;
    renderTable();
}

function setFilterStatus(v){
    currentFilterStatus=v;
    currentPage=1;
    renderTable();
}

function setFilterSupplier(v){
    currentFilterSupplier=v;
    currentPage=1;
    renderTable();
}

function changePage(page){
    currentPage = page;
    renderTable();
}

function toggleSortDate(){

    currentSortDate =
        currentSortDate === "DESC"
            ? "ASC"
            : "DESC";

    renderTable();
}

function formatPeriod(month, year){

    if(!month || !year){
        return "-";
    }

    const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];

    return `${monthNames[month - 1]} ${year}`;
}

/* =========================
   UTIL
========================= */

function formatDateTime(val){

    if(!val){
        return "-";
    }

    const d =
        new Date(val);

    return d.toLocaleString(
        "en-US",
        {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        }
    );
}

function getUserBadge(status){

    if(status === "APPROVED"){

        return `
            <span class="status-badge completed"></span>
        `;
    }

    if(status === "PROGRESS"){

        return `
            <span class="status-badge in-progress"></span>
        `;
    }

    if(status === "REJECTED"){

        return `
            <span class="status-badge rejected"></span>
        `;
    }

    return `
        <span class="status-badge waiting"></span>
    `;
}

/* =========================
   TABLE
========================= */
function renderTable(){

    const tbody=document.getElementById("purchase-list");
    tbody.innerHTML="";

    let filtered=qcData.filter(d=>{

        const userStatus=getUserStatus(d.docNumber);

        return (
            (currentFilterStatus==="ALL" || userStatus===currentFilterStatus)
            &&
            (currentFilterSupplier==="ALL" || d.supplier===currentFilterSupplier)
            &&
            (!currentSearchDoc || String(d.docNumber).toLowerCase().includes(currentSearchDoc))
        );
    });

    filtered.sort((a,b)=>{

        const aDate=new Date(a.created_at).getTime();
        const bDate=new Date(b.created_at).getTime();

        return currentSortDate==="DESC"
            ? bDate-aDate
            : aDate-bDate;
    });

    if(filtered.length===0){

        tbody.innerHTML=`
            <tr>
                <td colspan="8">No Data</td>
            </tr>
        `;

        document.getElementById("pagination").innerHTML="";
        return;
    }

    const totalPages=Math.ceil(filtered.length/rowsPerPage);

    if(currentPage>totalPages){
        currentPage=1;
    }

    const start=(currentPage-1)*rowsPerPage;
    const end=start+rowsPerPage;

    const paginatedData=filtered.slice(start,end);

    paginatedData.forEach(d=>{

        const wf=getWF(d.docNumber);
        const userStatus=getUserStatus(d.docNumber);

        const row=document.createElement("tr");

        row.innerHTML=`
            <td>${d.docNumber}</td>
            <td>${d.supplier}</td>
            <td>${d.period}</td>
            <td>
                ${
                    wf.history.length
                    ? wf.history.map(h=>h.user_name).join(", ")
                    : "-"
                }
            </td>
            <td>${formatDateTime(d.created_at)}</td>
            <td>${getStatusLabel(d.docNumber)}</td>
            <td>${getUserBadge(userStatus)}</td>
            <td>
                <button
                    class="detail-btn eye-btn"
                    onclick="selectRow('${d.docNumber}')">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });

    renderPagination(totalPages);
}

/* =========================
   PAGINATION
========================= */
function renderPagination(totalPages){

    let html = `
        <button onclick="changePage(currentPage-1)" ${currentPage===1 ? "disabled" : ""}>
            <
        </button>
    `;

    for(let i=1;i<=totalPages;i++){
        html += `
            <button
                class="${i===currentPage ? 'active-page' : ''}"
                onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    html += `
        <button onclick="changePage(currentPage+1)" ${currentPage===totalPages ? "disabled" : ""}>
            >
        </button>
    `;

    document.getElementById("pagination").innerHTML = html;
}

function changePage(page){
    currentPage = page;
    renderTable();
}

/* =========================
   DETAIL
========================= */

function selectRow(doc){
    selectedDoc = doc;

    const mainContent = document.getElementById("mainContent");
    const detailCard  = document.getElementById("detailCard");

    mainContent.style.display = "none";
    detailCard.style.display  = "block";

    showDetail(doc);

    window.scrollTo({ top: 0, behavior: "smooth" });
}

function getRankLabel(score){

    score = parseInt(score);

    if(score >= 40) return "Critical";
    if(score >= 30) return "Major";
    if(score >= 20) return "Minor";

    return "Good";
}

function getFppkLabel(score){

    score = parseInt(score);

    return score > 0 ? "NG" : "OK";
}

function showDetail(doc){

    const container = document.getElementById("detail-section");

    const data = qcData.find(x => String(x.docNumber) === String(doc));

    if(!data){ return; }

    const wf          = getWF(doc);
    const currentStep = approvalFlow[wf.step];
    const userStep    = getCurrentUserStep();

    console.log("wf.step:", wf.step);
    console.log("userStep:", userStep);
    console.log("wf.status:", wf.status);
    console.log("currentUser:", currentUser);

    const canApprove =
        wf.step === userStep &&
        wf.status !== "APPROVED" &&
        wf.status !== "REJECTED";

    console.log("canApprove:", canApprove);
        /* ---- DELIVERY HELPERS ---- */
        function getOTDText(value){
            switch(String(value)){
                case "0":  return "No Delay";
                case "2":  return "Delay 1 day";
                case "4":  return "Delay 2 days";
                case "6":  return "Delay 3 days";
                case "10": return "Delay > 3 days";
                default:   return "-";
            }
        }

    function getMethodText(value){
        switch(String(value)){
            case "0": return "Normal";
            case "4": return "Abnormal";
            default:  return "-";
        }
    }

    function getDPSText(value){
        switch(String(value)){
            case "0":  return "No Problem";
            case "5":  return "On Time";
            case "10": return "Delay";
            case "20": return "No Reply";
            default:   return "-";
        }
    }

    function calculateQtyIndex(fulfillment){
        if(!fulfillment){ return 0; }
        const value = parseFloat(String(fulfillment).replace('%',''));
        if(value >= 95) return 0;
        if(value >= 85) return 2;
        if(value >= 75) return 4;
        if(value >= 65) return 6;
        return 8;
    }

    function calculatePremiumIndex(premium){
        premium = parseFloat(premium) || 0;
        if(premium > 3000000) return 8;
        if(premium > 1000000) return 6;
        if(premium > 500000)  return 4;
        if(premium > 0)       return 2;
        return 0;
    }

    /* ---- DELIVERY DATA ---- */
    const del = data.delivery || {};

    let html = `

    <div class="detail-header-top">

        <button onclick="backToList()"
                class="back-btn">

            ← Back

        </button>

        <div class="doc-title">
            ${data.docNumber}
        </div>

        <div class="header-space"></div>

    </div>

    <div class="detail-subinfo">

        <div class="info-line">
            <span class="info-label">Supplier</span>
            <span class="info-value">
                ${data.supplier || '-'}
            </span>
        </div>

        <div class="info-line">
            <span class="info-label">Period</span>
            <span class="info-value">
                ${data.period}
            </span>
        </div>

    </div>

    <table class="detail-table">

        <!-- ===== QUALITY ===== -->
        <tr><th colspan="3">QUALITY</th></tr>

        <tr>
            <td>Line Stop</td>
            <td>Status</td>
            <td>
                ${data.lineStop == 40 ? "YES" : "NO"}
            </td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${data.lineStop ?? 0}</td>
        </tr>

        <tr>
            <td>PPM</td>
            <td>NG</td>
            <td>${data.ng ?? 0}</td>
        </tr>

        <tr>
            <td></td>
            <td>Supply</td>
            <td>${data.supply ?? 0}</td>
        </tr>

        <tr>
            <td></td>
            <td>PPM</td>
            <td>${data.ppm ?? 0}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${data.ppmScore ?? 0}</td>
        </tr>

        <tr>
            <td>Problem Rank</td>
            <td>Rank</td>
            <td>
                ${getRankLabel(data.rank_score)}
            </td>
        </tr>

         <tr>
            <td></td>
            <td>Index</td>
            <td>${data.rank_score ?? 0}</td>
        </tr>

        <tr>
            <td>FPPK</td>
            <td>Status</td>
            <td>
                ${getFppkLabel(data.fppk)}
            </td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${data.fppk ?? 0}</td>
        </tr>

        <tr class="total-row">
            <td>Total Score</td>
            <td></td>
            <td>    
            <b>${parseInt(data.total_score ?? 0)}</b>
        </td>
    </tr>

        <!-- ===== DELIVERY ===== -->
        <tr><th colspan="3">DELIVERY</th></tr>

        <tr>
            <td>Fulfillment</td>
            <td>%</td>
            <td>${del.fulfillment ?? '-'}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${calculateQtyIndex(del.fulfillment)}</td>
        </tr>

        <tr>
            <td>On Time Delivery</td>
            <td>Day</td>
            <td>${getOTDText(del.otd)}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${del.otd ?? 0}</td>
        </tr>

        <tr>
            <td>Delivery Method</td>
            <td>Method</td>
            <td>${getMethodText(del.del_method)}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${del.del_method ?? 0}</td>
        </tr>

        <tr>
            <td>Premium Freight</td>
            <td>IDR</td>
            <td>${del.premium ?? 0}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${calculatePremiumIndex(del.premium)}</td>
        </tr>

        <tr>
            <td>DPS Reply</td>
            <td>Reply</td>
            <td>${getDPSText(del.dps)}</td>
        </tr>

        <tr>
            <td></td>
            <td>Index</td>
            <td>${del.dps ?? 0}</td>
        </tr>

        <tr class="total-row">
            <td colspan="2"><b>Total Delivery</b></td>
            <td><b>${parseInt(del.total_score || 0)}</b></td>
        </tr>

    </table>

    ${canApprove ? `
    <div class="action-box">
        <button onclick="openConfirm('${doc}','APPROVED')">
            Approve
        </button>

        <button onclick="openConfirm('${doc}','REJECTED')">
            Reject
        </button>
    </div>
    ` : ""}

    <div class="status-info">
        Current Status: <b>${getStatus(doc)}</b><br>

        Waiting approval from:
        <b>${currentStep?.role || "-"}</b>
        - ${currentStep?.dept || "-"}
    </div>
    `;

    container.innerHTML = html;
}
/* =========================
   BACK
========================= */

function backToList(){
    selectedDoc = null;

    document.getElementById("detailCard").style.display  = "none";
    document.getElementById("mainContent").style.display = "block";
    document.getElementById("detail-section").innerHTML  = "";
}

/* =========================
   CONFIRM
========================= */

let confirmDoc = null;

let confirmStatus = null;

function openConfirm(doc, status){

    confirmDoc = doc;

    confirmStatus = status;

    document.getElementById("confirmText")
        .innerText = `Confirm ${status}?`;

    document.getElementById("confirmModal")
        .style.display = "flex";
}

function closeConfirm(){

    document.getElementById("confirmModal")
        .style.display = "none";
}

document.getElementById("yesBtn").onclick = () => {

    setStatus(confirmDoc, confirmStatus);
};

/* =========================
   SUCCESS
========================= */

function showSuccess(msg){

    document.getElementById("successText")
        .innerText = msg;

    document.getElementById("successPopup")
        .style.display = "flex";
}

function closeSuccess(){

    document.getElementById("successPopup")
        .style.display = "none";
}
function showFailed(msg){
    document.getElementById("failedText").innerText = msg;
    document.getElementById("failedPopup").style.display = "flex";
}

function closeFailedPopup(){
    document.getElementById("failedPopup").style.display = "none";
}
/* =========================
   INIT
========================= */

renderTable();

</script>

@endpush