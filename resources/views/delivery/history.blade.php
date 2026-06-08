@extends('layouts.app')

@section('title', 'Delivery History')

@section('page_title', 'Delivery History')

@push('head')
<link rel="stylesheet" href="{{ asset('css/delivhistory.css') }}">
@endpush

@section('content')

<div id="mainContent">

    <!-- FILTER -->
    <div class="filter-box">

        <div class="dropdown">

            <input type="text"
                id="supplierSearch"
                placeholder="Select Supplier...">

            <div id="dropdownList"
                class="dropdown-list"></div>

        </div>

        <select id="monthFilter">

            <option value="">All Month</option>

            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>

        </select>

        <select id="yearFilter"></select>

    </div>

    <!-- TABLE -->
    <table class="history-table">

        <thead>

            <tr>

                <th>Doc No</th>
                <th>Creation Date</th>
                <th>Supplier</th>
                <th>Period</th>
                <th>Updated By</th>
                <th>Total Point</th>
                <th>Action</th>

            </tr>

        </thead>

        <tbody id="supplier-list"></tbody>

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

<!-- DETAIL -->
<div class="panel-card detail-card"
    id="detailCard"
    style="display:none;">

    <div id="detail-table"></div>

</div>

@endsection

@push('scripts')

<script>

let currentPage = 1;
let rowsPerPage = 10;
const data = @json($deliveries);
const approvedDocs = @json($approvedDocs);
const inApprovalDocs = @json($inApprovalDocs);
const userRole = "{{ auth()->user()->role }}";
const canEdit = userRole === 'Admin' || userRole === 'Staff';

const tableBody =
    document.getElementById("supplier-list");

const detailCard =
    document.getElementById("detailCard");

const detailTable =
    document.getElementById("detail-table");

const mainContent =
    document.getElementById("mainContent");

/* =========================
   YEAR FILTER
========================= */

const yearFilter =
    document.getElementById("yearFilter");

const currentYear =
    new Date().getFullYear();

for(let y = currentYear - 3; y <= currentYear + 2; y++){

    const option =
        document.createElement("option");

    option.value = y;

    option.textContent = y;

    if(y == currentYear){
        option.selected = true;
    }

    yearFilter.appendChild(option);
}

/* =========================
   FORMAT DATE
========================= */

function formatDate(date){

    if(!date){
        return "-";
    }

    const d = new Date(date);

    return `
        ${String(d.getMonth()+1).padStart(2,'0')}
        /
        ${String(d.getDate()).padStart(2,'0')}
        /
        ${d.getFullYear()}
    `;
}

function formatPeriod(month, year){

    const months = [

        "January","February","March",
        "April","May","June",

        "July","August","September",
        "October","November","December"

    ];

    return `${months[Number(month)-1]} ${year}`;
}
function openEdit(id) {
    const d = data.find(x => x.id == id);
    if (!d) return;
    window.location.href = `/delivery/edit/${d.id}`;
}
function showEditBlocked(){
    document.getElementById("blockedPopup").style.display = "flex";
}

function closeBlockedPopup(){
    document.getElementById("blockedPopup").style.display = "none";
}
/* =========================
   FILTER
========================= */

function applyFilter(){

    const supplier =
        document.getElementById("supplierSearch")
        .value
        .toLowerCase();

    const month =
        document.getElementById("monthFilter")
        .value;

    const year =
        document.getElementById("yearFilter")
        .value;

    const filtered = data.filter(d => {

        return (

            (!supplier ||

                (d.supplierSearch || "")
                .toLowerCase()
                .includes(supplier))

            &&

            (!month ||
                d.del_month == month)

            &&

            (!year ||
                d.del_year == year)

        );

    });

    renderTable(filtered);
}

/* =========================
   RENDER TABLE
========================= */

function renderTable(filtered){

    tableBody.innerHTML = "";

    const totalPages=Math.ceil(filtered.length/rowsPerPage);

    if(currentPage>totalPages){
        currentPage=1;
    }

    const start=(currentPage-1)*rowsPerPage;
    const end=start+rowsPerPage;

    const paginatedData=filtered.slice(start,end);

    if(filtered.length === 0){

        tableBody.innerHTML = `

            <tr>

                <td colspan="7">
                    No Data Found
                </td>

            </tr>

        `;

        return;
    }

    paginatedData.forEach(d=>{

        const row =
            document.createElement("tr");

        row.innerHTML = `

            <td>
                <b>${d.docNumber ?? '-'}</b>
            </td>

            <td>
                ${formatDate(d.created_at)}
            </td>

            <td>
                ${d.supplierSearch ?? '-'}
            </td>

            <td>
                ${formatPeriod(
                    d.del_month,
                    d.del_year
                )}
            </td>

            <td>
                ${d.updatedBy ?? '-'}
            </td>

            <td>
                <b>
                    ${parseInt(d.total_score ?? 0)}
                </b>
            </td>

         <td class="action-cell">
    <button class="table-view-btn"
            onclick="viewDetail(${d.id})">
        <i class="fa-solid fa-eye"></i>
    </button>

    ${canEdit
        ? `<button 
                class="table-edit-btn ${inApprovalDocs.includes(d.docNumber) ? 'disabled' : ''}"
                onclick="${inApprovalDocs.includes(d.docNumber) 
                    ? `showEditBlocked()` 
                    : `openEdit(${d.id})`}"
                title="${inApprovalDocs.includes(d.docNumber) ? 'Already in Approval' : 'Edit'}">
            <i class="fa-solid fa-pen"></i>
          </button>`
        : ''
    }
</td>
        `;

        tableBody.appendChild(row);

    });
    
    renderPagination(totalPages);
}

function calculateQtyIndex(fulfillment){

    if(!fulfillment){
        return 0;
    }

    const value =
        parseFloat(
            String(fulfillment).replace('%','')
        );

    if(value >= 95){

        return 0;

    }else if(value >= 85){

        return 2;

    }else if(value >= 75){

        return 4;

    }else if(value >= 65){

        return 6;

    }else{

        return 8;
    }
}

function calculatePremiumIndex(premium){

    premium = parseFloat(premium) || 0;

    if(premium > 3000000){

        return 8;

    }else if(premium > 1000000){

        return 6;

    }else if(premium > 500000){

        return 4;

    }else if(premium > 0){

        return 2;
    }

    return 0;
}

/* =========================
   PAGINATION
========================= */
function renderPagination(totalPages){

    let html=`
        <button onclick="changePage(currentPage-1)" ${currentPage===1 ? "disabled" : ""}>
            <
        </button>
    `;

    for(let i=1;i<=totalPages;i++){
        html+=`
            <button
                class="${i===currentPage ? 'active-page' : ''}"
                onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    html+=`
        <button onclick="changePage(currentPage+1)" ${currentPage===totalPages ? "disabled" : ""}>
            >
        </button>
    `;

    document.getElementById("pagination").innerHTML=html;
}

function changeRowsPerPage(value){
    rowsPerPage = parseInt(value);
    currentPage = 1;
    renderTable();
}

/* =========================
   DETAIL
========================= */

function viewDetail(id){

    const d =
        data.find(x => x.id == id);

    if(!d){
        return;
    }

    function getOTDText(value){

    switch(String(value)){

        case "0":
            return "No Delay";

        case "2":
            return "Delay 1 day";

        case "4":
            return "Delay 2 days";

        case "6":
            return "Delay 3 days";

        case "10":
            return "Delay > 3 days";

        default:
            return "-";
    }
}

function getMethodText(value){

    switch(String(value)){

        case "0":
            return "Normal";

        case "4":
            return "Abnormal";

        default:
            return "-";
    }
}

function getDPSText(value){

    switch(String(value)){

        case "0":
            return "No Problem";

        case "5":
            return "On Time";

        case "10":
            return "Delay";

        case "20":
            return "No Reply";

        default:
            return "-";
    }
}

    /* HIDE TABLE */
    mainContent.style.display = "none";

    /* SHOW DETAIL */
    detailCard.style.display = "block";

    detailTable.innerHTML = `

    <div class="detail-header-top">

        <button onclick="closeDetail()"
                class="back-btn">
            ← Back
        </button>

        <div class="doc-title">
            ${d.docNumber}
        </div>

        <div class="header-space"></div>

    </div>

    <div class="detail-subinfo">

        <div class="info-line">
            <span class="info-label">Supplier</span>
            <span class="info-value">
                ${d.supplierSearch ?? '-'}
            </span>
        </div>

        <div class="info-line">
            <span class="info-label">Period</span>
            <span class="info-value">
                ${formatPeriod(
                    d.del_month,
                    d.del_year
                )}
            </span>
        </div>

    </div>

    <div class="section-title-table">
        DELIVERY
    </div>

    <table class="detail-table">

        <tr>
            <td rowspan="2">Fulfillment</td>
            <td>%</td>
            <td>${d.fulfillment}</td>
        </tr>

        <tr>
            <td>Index</td>
            <td>${calculateQtyIndex(d.fulfillment)}</td>
        </tr>

        <tr>
            <td rowspan="2">On Time Delivery</td>
            <td>Day</td>
            <td>${getOTDText(d.otd)}</td>
        </tr>

        <tr>
            <td>Index</td>
            <td>${d.otd ?? 0}</td>
        </tr>

        <tr>
            <td rowspan="2">Delivery Method</td>
            <td>Method</td>
            <td>${getMethodText(d.del_method)}</td>
        </tr>

        <tr>
            <td>Index</td>
            <td>${d.del_method ?? 0}</td>
        </tr>

        <tr>
            <td rowspan="2">Premium Freight</td>
            <td>IDR</td>
            <td>${d.premium ?? 0}</td>
        </tr>

        <tr>
            <td>Index</td>
            <td>${calculatePremiumIndex(d.premium)}</td>
        </tr>

        <tr>
            <td rowspan="2">DPS Reply</td>
            <td>Reply</td>
            <td>${getDPSText(d.dps)}</td>
        </tr>

        <tr>
            <td>Index</td>
            <td>${d.dps ?? 0}</td>
        </tr>

        <tr class="total-row">
            <td>Total Score</td>
            <td></td>
            <td>
                <b>${parseInt(d.total_score ?? 0)}</b>
            </td>
        </tr>

    </table>
    `;

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

/* =========================
   CLOSE DETAIL
========================= */

function closeDetail(){

    detailCard.style.display = "none";

    detailTable.innerHTML = "";

    /* SHOW TABLE AGAIN */
    mainContent.style.display = "block";
}

/* =========================
   EVENTS
========================= */

document
.getElementById("supplierSearch")
.addEventListener("input", applyFilter);

document
.getElementById("monthFilter")
.addEventListener("change", applyFilter);

document
.getElementById("yearFilter")
.addEventListener("change", applyFilter);

/* =========================
   INIT
========================= */

applyFilter();

</script>

@endpush