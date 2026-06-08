@extends('layouts.app')

@section('title', 'QC Inspection')
@section('page_title', 'Goods Inspection')

@push('head')
<link rel="stylesheet" href="{{ asset('css/qcinspection.css') }}">
@endpush

@section('content')

<div class="card">

    <div class="section-title">
        Select Delivery
    </div>

    <label>Delivery *</label>

    <div class="custom-dropdown">

        <input type="text"
            id="deliverySearch"
            placeholder="Select delivery..."
            onkeyup="filterDropdown()"
            onclick="toggleDropdown()">

        <div id="dropdownList"
            class="dropdown-list">
        </div>

    </div>

</div>

<div id="deliveryDetail"></div>

@endsection

{{-- POPUP --}}
<div id="popup" class="popup">

    <div class="popup-box">

        <p id="popup-message"></p>

        <button onclick="closePopup()">
            OK
        </button>

    </div>

</div>

@push('scripts')

<script>

const deliveryData = @json($deliveries);

// Sort terbaru di atas (by docNumber descending)
deliveryData.sort((a, b) => b.docNumber.localeCompare(a.docNumber));

let selectedDoc = null;

/* =========================
   POPUP
========================= */
function showPopup(message){

    document.getElementById("popup-message")
        .textContent = message;

    document.getElementById("popup")
        .style.display = "flex";
}

function closePopup(){

    document.getElementById("popup")
        .style.display = "none";
}

/* =========================
   FORMAT PERIOD
========================= */
function formatPeriod(month, year){

    if(!month || !year){
        return "-";
    }

    return `${month}/${year}`;
}

/* =========================
   RENDER DROPDOWN
========================= */
function renderDropdown(list){

    const dropdown =
        document.getElementById("dropdownList");

    dropdown.innerHTML = "";

    if(list.length === 0){

        dropdown.innerHTML = `
            <div class="dropdown-item">
                No Data
            </div>
        `;

        return;
    }

    list.forEach(d => {

        const div =
            document.createElement("div");

        div.className = "dropdown-item";

        div.textContent =
            `${d.docNumber} | ${d.supplierSearch} | ${formatPeriod(d.del_month, d.del_year)}`;

        div.onclick = () => {

            selectedDoc = d;

            document.getElementById("deliverySearch").value =
                `${d.supplierSearch} (${formatPeriod(d.del_month, d.del_year)})`;

            dropdown.style.display = "none";

            showDetail(d);
        };

        dropdown.appendChild(div);
    });
}

renderDropdown(deliveryData);

/* =========================
   TOGGLE DROPDOWN
========================= */
function toggleDropdown(){

    document.getElementById("dropdownList")
        .style.display = "block";
}

/* =========================
   FILTER DROPDOWN
========================= */
function filterDropdown(){

    const keyword =
        document.getElementById("deliverySearch")
        .value
        .toLowerCase();

    const filtered =
        deliveryData.filter(d =>

            `${d.supplierSearch} ${d.docNumber} ${d.del_month} ${d.del_year}`
            .toLowerCase()
            .includes(keyword)
        );

    renderDropdown(filtered);
}

/* =========================
   SHOW DETAIL
========================= */
function showDetail(d){

    document.getElementById("deliveryDetail").innerHTML = `

<div class="detail-card">

    <p><b>Doc No:</b> ${d.docNumber}</p>

    <p><b>Supplier:</b> ${d.supplierSearch}</p>

    <p><b>Period:</b> ${formatPeriod(d.del_month, d.del_year)}</p>

    <hr>

    <h3>QC Inspection</h3>

    <div class="form-grid">

        <div class="form-group">

            <label>Line Stop</label>

            <select id="lineStop" onchange="calcQC()">

                <option value="">Select</option>

                <option value="40">
                    Yes
                </option>

                <option value="0">
                    No
                </option>

            </select>

        </div>

        <div class="form-group">

            <label>NG</label>

            <input type="number"
                id="ng"
                oninput="calcQC()">

        </div>

        <div class="form-group">

            <label>Supply</label>

            <input type="number"
                id="supply"
                readonly>

        </div>

        <div class="form-group">

            <label>PPM</label>

            <input type="text"
                id="ppm"
                readonly>

        </div>

        <div class="form-group">

            <label>PPM Score</label>

            <input type="text"
                id="ppmScore"
                readonly>

        </div>

        <div class="form-group">

            <label>Rank</label>

            <select id="rank"
                onchange="calcQC()">

                <option value="">Select</option>

                <option value="0">
                    No Problem
                </option>

                <option value="25">
                    A
                </option>

                <option value="10">
                    B
                </option>

                <option value="5">
                    C
                </option>

            </select>

        </div>

        <div class="form-group">

            <label>FPPK</label>

            <select id="fppk"
                onchange="calcQC()">

                <option value="">Select</option>

                <option value="0">
                    No Problem
                </option>

                <option value="10">
                    Delay
                </option>

                <option value="20">
                    No Reply
                </option>

            </select>

        </div>

    </div>

    <hr>

    <h3>Total Score</h3>

    <div class="total-box" id="qcTotal">
        —
    </div>

    <button class="btn-save"
        onclick="saveQC()">

        Save QC

    </button>

</div>
`;

    document.getElementById("supply").value =
        Number(d.qty_rec || 0);
}

/* =========================
   CALCULATE QC
========================= */
function calcQC(){

    const lineStop =
        Number(
            document.getElementById("lineStop").value
        ) || 0;

    const ng =
        Number(
            document.getElementById("ng").value
        ) || 0;

    const supply =
        Number(
            document.getElementById("supply").value
        ) || 0;

    const rankScore =
        Number(
            document.getElementById("rank").value
        ) || 0;

    const fppk =
        Number(
            document.getElementById("fppk").value
        ) || 0;

    let ppmScore = 0;

    if(supply > 0){

        let ppm =
            (ng / supply) * 1000000;

        document.getElementById("ppm").value =
            Math.round(ppm);

        if(ppm === 0){

            ppmScore = 0;

        }else if(ppm <= 20){

            ppmScore = 5;

        }else if(ppm <= 200){

            ppmScore = 10;

        }else{

            ppmScore = 15;
        }

        document.getElementById("ppmScore").value =
            ppmScore;
    }

    const total =
        100 - (
            lineStop +
            ppmScore +
            rankScore +
            fppk
        );

    document.getElementById("qcTotal").innerText =
        Math.max(0, Math.min(100, total))
        .toFixed(1);
}

/* =========================
   SAVE QC
========================= */
async function saveQC(){

    if(!selectedDoc){

        showPopup("Select delivery first");
        return;
    }

    const payload = {

        docNumber:
            selectedDoc.docNumber,

        supplier:
            selectedDoc.supplierSearch,

        del_month:
            selectedDoc.del_month,

        del_year:
            selectedDoc.del_year,

        lineStop:
            Number(
                document.getElementById("lineStop").value
            ) || 0,

        ng:
            Number(
                document.getElementById("ng").value
            ) || 0,

        supply:
            Number(
                document.getElementById("supply").value
            ) || 0,

        ppm:
            Number(
                document.getElementById("ppm").value
            ) || 0,

        ppmScore:
            Number(
                document.getElementById("ppmScore").value
            ) || 0,

        rank_score:
            Number(
                document.getElementById("rank").value
            ) || 0,

        fppk:
            Number(
                document.getElementById("fppk").value
            ) || 0,

        total_score:
            Number(
                document.getElementById("qcTotal").innerText
            ) || 0
    };

    try{

        const response = await fetch('/qc/store', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'
            },

            body: JSON.stringify(payload)
        });

        const result =
            await response.json();

        if(result.success){

            // Hapus dari dropdown setelah save
            const idx = deliveryData.findIndex(
                d => d.docNumber === selectedDoc.docNumber
            );
            if(idx !== -1) deliveryData.splice(idx, 1);

            // Reset input & detail
            document.getElementById("deliverySearch").value = "";
            document.getElementById("deliveryDetail").innerHTML = "";
            selectedDoc = null;

            // Refresh dropdown
            renderDropdown(deliveryData);

            showPopup(
                `${payload.docNumber} QC saved successfully`
            );

            setTimeout(() => {

                window.location.href =
                    "/qc/history";

            }, 1000);

        }else{

            showPopup(result.message);
        }

    }catch(error){

        console.error(error);

        showPopup(error);
    }
}

</script>

@endpush