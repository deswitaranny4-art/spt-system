@extends('layouts.app')

@section('title', 'Summary Report')
@section('page_title', 'Print Report')

@push('head')
<link rel="stylesheet" href="{{ asset('css/report.css') }}">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush

@section('content')

<div class="filter-box">
    <div class="dropdown">
        <input type="text"
               id="supplierSearch"
               placeholder="Search Supplier..."
               onclick="toggleSupplierDropdown()"
               onkeyup="filterSuppliers()">
        <div id="supplierDropdown" class="dropdown-list"></div>
    </div>

    <select id="month">
        <option value="">All Month</option>
        <option value="01">Jan</option>
        <option value="02">Feb</option>
        <option value="03">Mar</option>
        <option value="04">Apr</option>
        <option value="05">May</option>
        <option value="06">Jun</option>
        <option value="07">Jul</option>
        <option value="08">Aug</option>
        <option value="09">Sep</option>
        <option value="10">Oct</option>
        <option value="11">Nov</option>
        <option value="12">Dec</option>
    </select>

    <select id="year"></select>
</div>

<div id="reportArea" class="report-box"></div>
<div id="pdfReport" style="display:none;"></div>

<button onclick="downloadPDF()" class="print-btn">
    <i class="fas fa-print"></i> Print Report
</button>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const qc         = @json($qcData);
const delivery   = @json($deliveryData);
const approvals  = @json($approvalData);
const histories  = @json($historyData);
const approvedDocs = approvals.map(a => a.doc_number);

let supplierData    = [];
let selectedSupplier = "";

/* =========================
   LOAD SUPPLIER
========================= */
async function initSupplier(){
    try{
        const res  = await fetch('/api/suppliers');
        const data = await res.json();
        supplierData = data;
        buildSupplierDropdown(supplierData);
        render();
    }catch(err){
        console.error(err);
    }
}

function getRankLabel(v){ return v ?? "-"; }
function getFppkLabel(v){ return v ?? "-"; }

/* =========================
   BUILD DROPDOWN
========================= */
function buildSupplierDropdown(data){
    const dropdown = document.getElementById("supplierDropdown");
    dropdown.innerHTML = "";

    const all = document.createElement("div");
    all.className   = "option";
    all.textContent = "All Supplier";
    all.onclick = () => {
        document.getElementById("supplierSearch").value = "All Supplier";
        selectedSupplier = "";
        dropdown.style.display = "none";
        render();
    };
    dropdown.appendChild(all);

    data.forEach(item => {
        const div = document.createElement("div");
        div.className   = "option";
        div.textContent = `${item.bp_code} - ${item.bp_name}`;
        div.onclick = () => {
            document.getElementById("supplierSearch").value = `${item.bp_code} - ${item.bp_name}`;
            selectedSupplier = item.bp_name.trim().toLowerCase();
            dropdown.style.display = "none";
            render();
        };
        dropdown.appendChild(div);
    });
}

/* =========================
   TOGGLE
========================= */
function toggleSupplierDropdown(){
    const dropdown = document.getElementById("supplierDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

/* =========================
   PERIOD
========================= */
function formatPeriod(month, year){

    const monthNames = {
        "01":"January",
        "02":"February",
        "03":"March",
        "04":"April",
        "05":"May",
        "06":"June",
        "07":"July",
        "08":"August",
        "09":"September",
        "10":"October",
        "11":"November",
        "12":"December"
    };

    return `${monthNames[String(month).padStart(2,'0')] || '-'} ${year || ''}`;
}

/* =========================
   FILTER
========================= */
function filterSuppliers(){
    const keyword  = document.getElementById("supplierSearch").value.toLowerCase();
    const filtered = supplierData.filter(item =>
        `${item.bp_code} ${item.bp_name}`.toLowerCase().includes(keyword)
    );
    buildSupplierDropdown(filtered);
    document.getElementById("supplierDropdown").style.display = "block";
}

/* =========================
   CLOSE DROPDOWN
========================= */
document.addEventListener("click", function(e){
    const dropdown = document.querySelector(".dropdown");
    if(dropdown && !dropdown.contains(e.target)){
        document.getElementById("supplierDropdown").style.display = "none";
    }
});

window.onload = () => {
    initYear();
    initSupplier();
};

function getGrade(score){
    if(score >= 90) return "A";
    if(score >= 80) return "B";
    if(score >= 70) return "C";
    return "D";
}

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

function initYear(){
    const y   = document.getElementById("year");
    const now = new Date().getFullYear();
    for(let i = now - 3; i <= now + 2; i++){
        const opt = document.createElement("option");
        opt.value       = i;
        opt.textContent = i;
        if(i === now) opt.selected = true;
        y.appendChild(opt);
    }
}

function getFilteredData(){
    const s = selectedSupplier;
    const m = document.getElementById("month").value;
    const y = document.getElementById("year").value;

    const gmApprovedDocs = histories
        .filter(h =>
            String(h.action).toUpperCase() === "APPROVED" &&
            String(h.role).toLowerCase() === "general manager" &&
            String(h.department).toLowerCase() === "production"
        )
        .map(h => String(h.doc_number).trim());

    const qcData = qc.filter(d =>
        gmApprovedDocs.includes(String(d.docNumber).trim()) &&
        (!s || String(d.supplier).trim().toLowerCase().includes(s)) &&
        (!m || String(d.del_month || "").padStart(2,'0') === m) &&
        (!y || String(d.del_year) === y)
    );

    const delData = delivery.filter(d =>
        gmApprovedDocs.includes(String(d.docNumber).trim()) &&
        (!s || String(d.supplier).trim().toLowerCase().includes(s)) &&
        (!m || String(d.del_month || "").padStart(2,'0') === m) &&
        (!y || String(d.del_year) === y)
    );

    const merged = [];
    qcData.forEach(q => {
        const del = delData.find(d => String(d.docNumber).trim() === String(q.docNumber).trim());
        merged.push({
            docNumber:  q.docNumber,
            supplier:   q.supplier,
            period: formatPeriod(q.del_month, q.del_year),
            qcTotal:    Number(q.total_score || 0),
            delTotal:   Number(del?.total_score || 0),
            finalTotal: (Number(q.total_score || 0) + Number(del?.total_score || 0)) / 2
        });
    });

    return merged;
}

/* =========================
   RENDER
========================= */
function render(){
    const merged = getFilteredData();

    let html = `
    <div class="report-header">
        <h2>SUPPLIER PERFORMANCE</h2>
        <h3>PT SANOH INDONESIA</h3>
    </div>
    <table>
        <tr>
            <th>Doc No</th>
            <th>Supplier</th>
            <th>Period</th>
            <th>QC Point</th>
            <th>Delivery Point</th>
            <th>Final Score</th>
            <th>Action</th>
        </tr>
    `;

    if(merged.length === 0){
        html += `<tr><td colspan="7" style="text-align:center">No Approved Data</td></tr>`;
    }

    merged.forEach(row => {
        html += `
        <tr>
            <td>${row.docNumber}</td>
            <td>${row.supplier}</td>
            <td>${row.period}</td>
            <td>${row.qcTotal}</td>
            <td>${row.delTotal}</td>
            <td>${row.finalTotal.toFixed(2)}</td>
            <td>
                <button class="detail-btn"
                    onclick="downloadDetailPDF('${row.docNumber}')">
                    <i class="fa-solid fa-print"></i>
                </button>
            </td>
        </tr>
        `;
    });

    html += `</table>`;
    document.getElementById("reportArea").innerHTML = html;
}

async function waitImagesLoaded(element) {
    const images = [...element.querySelectorAll("img")];

    await Promise.all(images.map(img => {
        return new Promise(resolve => {

            if (!img.src) return resolve();

            img.onload = () => resolve();
            img.onerror = () => resolve();

        });
    }));
}

/* =========================
   BUILD PDF REPORT
========================= */
function buildPDFReport(){
    const merged = getFilteredData();
    const y      = document.getElementById("year").value;
    const m      = document.getElementById("month").value;

    const monthNames = {
        "01":"January","02":"February","03":"March",
        "04":"April","05":"May","06":"June",
        "07":"July","08":"August","09":"September",
        "10":"October","11":"November","12":"December"
    };

    const monthLabel    = m ? monthNames[m] : "All Month";
    const supplierLabel = document.getElementById("supplierSearch").value || "All Supplier";
    const currentDate   = new Date().toLocaleDateString('en-GB');

    let html = `
    <div class="pdf-page">
        <div class="pdf-header">
            <div class="logo-section">
                <img src="/images/sanohlogo.png" class="company-logo">
            </div>
            <div class="company-section">
                <h1>PT SANOH INDONESIA</h1>
                <p>Supplier Performance Evaluation Report</p>
            </div>
        </div>
        <div class="pdf-info">
            <p><strong>Date :</strong> ${currentDate}</p>
            <p><strong>Supplier :</strong> ${supplierLabel}</p>
            <p><strong>Month :</strong> ${monthLabel}</p>
            <p><strong>Year :</strong> ${y}</p>
        </div>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th>No</th><th>Doc Number</th><th>Supplier</th>
                    <th>Period</th><th>QC</th><th>Delivery</th>
                    <th>Final</th><th>Grade</th>
                </tr>
            </thead>
            <tbody>
    `;

    merged.forEach((row, index) => {
        let grade = row.finalTotal >= 90 ? "A" : row.finalTotal >= 80 ? "B" : row.finalTotal >= 70 ? "C" : "D";
        html += `
        <tr>
            <td>${index+1}</td>
            <td>${row.docNumber}</td>
            <td>${row.supplier}</td>
            <td>${row.period}</td>
            <td>${row.qcTotal.toFixed(2)}</td>
            <td>${row.delTotal.toFixed(2)}</td>
            <td>${row.finalTotal.toFixed(2)}</td>
            <td>${grade}</td>
        </tr>
        `;
    });

    html += `</tbody></table></div>`;
    const approvalFlowPDF = [
    { role: "Supervisor",      dept: "Quality Control" },
    { role: "Manager",         dept: "Quality Control" },
    { role: "Supervisor",      dept: "PPIC" },
    { role: "Manager",         dept: "PPIC" },
    { role: "Leader",          dept: "Purchasing" },
    { role: "Manager",         dept: "Purchasing" },
    { role: "General Manager", dept: "Production" },
];

const approvedGlobal = histories.filter(h =>
    String(h.action).toUpperCase() === "APPROVED"
);

html += `
<div class="ttd-section">
    <h3 class="section-title">Approval Signatures</h3>

    <table class="ttd-table">
        <thead>
            <tr>
                ${approvalFlowPDF.map(s => `
                    <th>${s.role}<br><small>${s.dept}</small></th>
                `).join('')}
            </tr>
        </thead>

        <tbody>
            <tr>
                ${approvalFlowPDF.map(step => {

                    const match = approvedGlobal.find(h =>
                        String(h.role).toLowerCase().trim() === step.role.toLowerCase().trim() &&
                        String(h.department).toLowerCase().trim() === step.dept.toLowerCase().trim()
                    );

                    let signatureUrl = null;

                    if (match?.signature_path) {
                        signatureUrl = match.signature_path.startsWith('http')
                            ? match.signature_path
                            : `${window.location.origin}/storage/${match.signature_path}`;
                    }

                    return `
                        <td class="ttd-cell">
                            ${signatureUrl
                                ? `<img src="${signatureUrl}" class="ttd-img">`
                                : `<div class="ttd-empty">-</div>`
                            }
                            <div class="ttd-name">${match?.user_name || ''}</div>
                        </td>
                    `;
                }).join('')}
            </tr>
        </tbody>
    </table>
</div>
`;
    document.getElementById("pdfReport").innerHTML = html;
}

function downloadPDF(){

    buildPDFReport();

    const element=document.getElementById("pdfReport");
    element.style.display="block";

    html2pdf().set({
        margin:0.2,
        filename:`Supplier_Performance_Report.pdf`,
        image:{type:'jpeg',quality:1},
        html2canvas:{
            scale:2.5,
            useCORS:true,
            scrollY:0
        },
        jsPDF:{
            unit:'in',
            format:'a4',
            orientation:'portrait'
        },
        pagebreak:{
            mode:['css','legacy']
        }
    }).from(element).save().then(()=>{
        element.style.display="none";
    });
}

/* =========================
   DETAIL PDF
========================= */
async function downloadDetailPDF(docNo){

    const qcDetail  = qc.find(q => String(q.docNumber).trim() === String(docNo).trim());
    const delDetail = delivery.find(d => String(d.docNumber).trim() === String(docNo).trim());

    if(!qcDetail && !delDetail){
        alert("Detail not found");
        return;
    }

    const supplier    = qcDetail?.supplier || delDetail?.supplier || "-";
    const period = formatPeriod(qcDetail?.del_month, qcDetail?.del_year);
    const currentDate = new Date().toLocaleDateString('en-GB');

    let html = `
    <div class="pdf-page single-page">
        <div class="pdf-header">
            <div class="logo-section">
                <img src="/images/sanohlogo.png" class="company-logo">
            </div>
            <div class="company-section">
                <h1>PT SANOH INDONESIA</h1>
                <p>Supplier Performance Detail Report</p>
            </div>
        </div>
       <div class="pdf-info" style="display:flex; gap:120px;">
        <div>
            <p><strong>Date :</strong> ${currentDate}</p>
            <p><strong>Doc Number :</strong> ${docNo}</p>
        </div>
        <div>
            <p><strong>Supplier :</strong> ${supplier}</p>
            <p><strong>Period :</strong> ${period}</p>
        </div>
    </div>
        `;

    let qcRows = [
        { category: "Line Stop",    type: "STAT",   value: qcDetail?.lineStop == 40 ? "YES" : "NO" },
        { category: "",             type: "POINT",  value: `<b>${qcDetail?.lineStop || 0}</b>` },
        { category: "PPM",          type: "NG",     value: qcDetail?.ng || 0 },
        { category: "",             type: "SUPPLY", value: qcDetail?.supply || 0 },
        { category: "",             type: "PPM",    value: qcDetail?.ppm || 0 },
        { category: "",             type: "POINT",  value: `<b>${qcDetail?.ppmScore || 0}</b>` },
        { category: "Problem Rank", type: "RANK",   value: getRankLabel(qcDetail?.rank_score) },
        { category: "",             type: "POINT",  value: `<b>${qcDetail?.rank_score || 0}</b>` },
        { category: "FPPK",         type: "STAT",   value: getFppkLabel(qcDetail?.fppk) },
        { category: "",             type: "POINT",  value: `<b>${qcDetail?.fppk || 0}</b>` },
        { category: "<b>Total Score</b>", type: "", value: `<b>${qcDetail?.total_score}</b>` }
    ];

    let delRows = [
        { category: "Fulfillment",      type: "%",      value: delDetail?.fulfillment || "-" },
        { category: "",                 type: "INDEX",  value: calculateQtyIndex(delDetail?.fulfillment) },
        { category: "On Time Delivery", type: "DAY",    value: getOTDText(delDetail?.otd) },
        { category: "",                 type: "INDEX",  value: delDetail?.otd ?? 0 },
        { category: "Delivery Method",  type: "METHOD", value: getMethodText(delDetail?.del_method) },
        { category: "",                 type: "INDEX",  value: delDetail?.del_method ?? 0 },
        { category: "Premium Freight",  type: "IDR",    value: delDetail?.premium || 0 },
        { category: "",                 type: "INDEX",  value: calculatePremiumIndex(delDetail?.premium) },
        { category: "DPS Reply",        type: "REPLY",  value: getDPSText(delDetail?.dps) },
        { category: "",                 type: "INDEX",  value: delDetail?.dps ?? 0 },
        { category: "<b>Total Score</b>", type: "",     value: `<b>${parseInt(delDetail?.total_score ?? 0)}</b>` }
    ];

    html += `<h3 class="section-title">QC Detail</h3>
    <table class="pdf-table small-table">
        <thead><tr><th>No</th><th>Category</th><th>Type</th><th>Value</th></tr></thead>
        <tbody>`;
    qcRows.forEach((item, i) => {
        html += `<tr><td>${i+1}</td><td>${item.category}</td><td>${item.type}</td><td>${item.value}</td></tr>`;
    });
    html += `</tbody></table>`;

    html += `<h3 class="section-title">Delivery Detail</h3>
    <table class="pdf-table small-table">
        <thead><tr><th>No</th><th>Category</th><th>Type</th><th>Value</th></tr></thead>
        <tbody>`;
    delRows.forEach((item, i) => {
        html += `<tr><td>${i+1}</td><td>${item.category}</td><td>${item.type}</td><td>${item.value}</td></tr>`;
    });
    html += `</tbody></table>`;

    const finalScore = (Number(qcDetail?.total_score || 0) + Number(delDetail?.total_score || 0)) / 2;
    const grade      = finalScore >= 90 ? "A" : finalScore >= 80 ? "B" : finalScore >= 70 ? "C" : "D";

    html += `
    <div class="final-box">
        <h3>Final Result</h3>
        <table class="pdf-table">
            <tr>
                <th style="width:25%">QC Total</th>
                <th style="width:25%">Delivery Total</th>
                <th style="width:25%">Final Score</th>
                <th style="width:25%">Grade</th>
            </tr>
            <tr>
                <td>${Number(qcDetail?.total_score || 0).toFixed(2)}</td>
                <td>${Number(delDetail?.total_score || 0).toFixed(2)}</td>
                <td>${finalScore.toFixed(2)}</td>
                <td>${grade}</td>
            </tr>
        </table>
    </div>
    `;
    /* ===== TTD SECTION ===== */
    const approvalFlowPDF = [
        { role: "Supervisor",      dept: "Quality Control" },
        { role: "Manager",         dept: "Quality Control" },
        { role: "Supervisor",      dept: "PPIC"            },
        { role: "Manager",         dept: "PPIC"            },
        { role: "Leader",          dept: "Purchasing"      },
        { role: "Manager",         dept: "Purchasing"      },
        { role: "General Manager", dept: "Production"      },
    ];

    const docHistories = histories.filter(h =>
        String(h.doc_number).trim() === String(docNo).trim() &&
        String(h.action).toUpperCase() === "APPROVED"
    );
    html += `
    <div class="ttd-section">
        <h3 class="section-title">Approval Signatures</h3>

        <table class="ttd-table">
            <thead>
                <tr>
                    ${approvalFlowPDF.map(s => `
                        <th>${s.role}<br><small>${s.dept}</small></th>
                    `).join('')}
                </tr>
            </thead>

            <tbody>
                <tr>
                    ${approvalFlowPDF.map(step => {

                        const match = docHistories.find(h =>
                            String(h.role).toLowerCase().trim() === step.role.toLowerCase().trim() &&
                            String(h.department).toLowerCase().trim() === step.dept.toLowerCase().trim()
                        );

                        let signatureUrl = null;

                        if (match?.signature_path) {
                            if (match.signature_path.startsWith('http')) {
                                signatureUrl = match.signature_path;
                            } else {
                                signatureUrl = `${window.location.origin}/storage/${match.signature_path}`;
                            }
                        }

                        return `
                            <td class="ttd-cell">
                                ${signatureUrl
                                    ? `<img src="${signatureUrl}" class="ttd-img">`
                                    : `<div class="ttd-empty">-</div>`
                                }
                                <div class="ttd-name">${match?.user_name || ''}</div>
                            </td>
                        `;
                    }).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    `;

    async function preloadImages(container) {
    const images = [...container.querySelectorAll("img")];

    await Promise.all(images.map(img => {
        return new Promise(resolve => {

            if (!img.src) return resolve();

            // paksa reload image (anti cache + anti blank canvas)
            const temp = new Image();
            temp.crossOrigin = "anonymous";

            temp.onload = () => {
                resolve();
            };

            temp.onerror = () => {
                console.log("FAILED LOAD IMAGE:", temp.src);
                resolve();
            };

            temp.src = img.src + "?v=" + Date.now();
        });
    }));
}

    const pdfContainer = document.getElementById("pdfReport");
    pdfContainer.innerHTML = html;
    pdfContainer.style.display = "block";

    await new Promise(r => setTimeout(r, 800));

    await preloadImages(pdfContainer);
    await new Promise(r => setTimeout(r, 500)); // extra buffer

    document.querySelectorAll("img").forEach(img => {
    console.log("IMG CHECK:", img.src, img.complete, img.naturalWidth);
});

await document.fonts?.ready;
await new Promise(r => setTimeout(r, 1000));

    html2pdf().set({
        margin:0.2,
        filename:`Detail_Report_${docNo}.pdf`,
        image:{type:'jpeg',quality:1},
        html2canvas:{
            scale:2.5,
            useCORS:true,
            scrollY:0,
            allowTaint: false
        },
        jsPDF:{
            unit:'in',
            format:'a4',
            orientation:'portrait'
        },
        pagebreak:{
            mode:['css','legacy']
        }
    }).from(pdfContainer).save().then(()=>{
        pdfContainer.style.display="none";
    });
}

/* =========================
   FILTER EVENT
========================= */
["month","year"].forEach(id => {
    document.getElementById(id).addEventListener("change", () => { render(); });
});

</script>
@endpush