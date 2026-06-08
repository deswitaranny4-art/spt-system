@extends('layouts.app')

@section('title', 'SPT - Delivery Input')
@section('page_title', 'Delivery Input')

@push('head')
<link rel="stylesheet" href="{{ asset('css/delivery.css') }}">
@endpush

@section('content')

<div class="form-card">

    <div class="section-title">
        Delivery Data
    </div>

    <div class="grid2">

        {{-- SUPPLIER --}}
        <div class="field">

    <label>Supplier</label>

    <div class="dropdown">

        <input type="text"
               id="supplierSearch"
               placeholder="Search Supplier..."
               onclick="toggleSupplierDropdown()"
               onkeyup="filterSuppliers()">

        <div id="supplierDropdown"
             class="dropdown-list">
        </div>

    </div>

</div>

        {{-- CREATED --}}
        <div class="field">

            <label>Created On</label>

            <input type="text"
                   id="created-on"
                   readonly>

        </div>

        {{-- MONTH --}}
        <div class="field">

            <label>Month</label>

            <select id="del-month">

                <option value="">
                    Select Month
                </option>

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

        </div>

        {{-- YEAR --}}
        <div class="field">

            <label>Year</label>

            <select id="del-year"></select>

        </div>

        {{-- OTD --}}
        <div class="field">

            <label>On-Time Delivery</label>

            <select id="otd"
                    onchange="calc()">

                <option value="">
                    Select status
                </option>

                <option value="0">
                    No Delay
                </option>

                <option value="2">
                    Delay 1 day
                </option>

                <option value="4">
                    Delay 2 days
                </option>

                <option value="6">
                    Delay 3 days
                </option>

                <option value="10">
                    Delay > 3 days
                </option>

            </select>

        </div>

        {{-- QTY ORDER --}}
        <div class="field">

            <label>Quantity Ordered</label>

            <input type="number"
                   id="qty-ord"
                   oninput="calc()">

        </div>

        {{-- QTY RECEIVED --}}
        <div class="field">

            <label>Quantity Received</label>

            <input type="number"
                   id="qty-rec"
                   oninput="calc()">

        </div>

        {{-- FULFILLMENT --}}
        <div class="field">

            <label>Order Fulfillment (%)</label>

            <input type="text"
                   id="fulfillment"
                   readonly>

        </div>

        {{-- DELIVERY METHOD --}}
        <div class="field">

            <label>Delivery Method</label>

            <select id="del-method"
                    onchange="calc()">

                <option value="">
                    Select method
                </option>

                <option value="0">
                    Normal
                </option>

                <option value="4">
                    Abnormal
                </option>

            </select>

        </div>

        {{-- PREMIUM --}}
        <div class="field">

            <label>Premium Freight (Rp)</label>

            <input type="number"
                   id="premium"
                   oninput="calc()">

        </div>

        {{-- DPS --}}
        <div class="field">

            <label>DPS Reply</label>

            <select id="dps"
                    onchange="calc()">

                <option value="">
                    Select reply
                </option>

                <option value="0">
                    No Problem
                </option>

                <option value="5">
                    On Time
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

    <div class="section-divider"></div>

    {{-- TOTAL --}}
    <div class="section-title">
        Total Score
    </div>

    <div class="total-box">

        <div class="total-num"
             id="total-score">

            —

        </div>

    </div>

    {{-- SAVE --}}
    <button class="btn-save"
            type="button"
            onclick="saveDelivery()">

        Save Data

    </button>

</div>

{{-- POPUP --}}
<div id="popup"
     class="popup">

    <div class="popup-box">

        <p id="popup-message">
            ✔ `${docNumber} saved successfully'
        </p>

        <button onclick="closePopup()">
            OK
        </button>

    </div>

</div>

@endsection

@push('scripts')

<script>

let supplierData = [];
let selectedSupplier = "";

console.log("DELIVERY PAGE LOADED");

/* =========================
   USER
========================= */

const currentUser = {
    name: "{{ Auth::user()->name }}",
    role: "{{ Auth::user()->role }}",
    department: "{{ Auth::user()->department }}"
};

/* =========================
   DATE INIT
========================= */

const today = new Date();

const currentMonth =
    String(today.getMonth() + 1).padStart(2, '0');

const currentDay =
    String(today.getDate()).padStart(2, '0');

const currentYear =
    today.getFullYear();

const currentHour =
    String(today.getHours()).padStart(2, '0');

const currentMinute =
    String(today.getMinutes()).padStart(2, '0');

document.getElementById("created-on").value =
`${currentMonth}/${currentDay}/${currentYear} ${currentHour}:${currentMinute}`;

/* =========================
   YEAR INIT
========================= */

const yearSelect =
    document.getElementById("del-year");

for(let y = currentYear - 3; y <= currentYear + 2; y++){

    const option =
        document.createElement("option");

    option.value = y;
    option.textContent = y;

    if(y === currentYear){
        option.selected = true;
    }

    yearSelect.appendChild(option);
}

/* =========================
   CALCULATION
========================= */

function calc(){

    const ord =
        parseFloat(document.getElementById("qty-ord").value) || 0;

    const rec =
        parseFloat(document.getElementById("qty-rec").value) || 0;

    const otd =
        parseInt(document.getElementById("otd").value) || 0;

    const method =
        parseInt(document.getElementById("del-method").value) || 0;

    const prem =
        parseFloat(document.getElementById("premium").value) || 0;

    const dps =
        parseInt(document.getElementById("dps").value) || 0;

    let fulfillment = 0;

    if(ord > 0){

        fulfillment =
            Math.min((rec / ord) * 100, 100);
    }

    document.getElementById("fulfillment").value =

        fulfillment > 0
            ? fulfillment.toFixed(0) + "%"
            : "";

    let sQty = 0;

    if(fulfillment >= 95){

        sQty = 0;

    }else if(fulfillment >= 85){

        sQty = 2;

    }else if(fulfillment >= 75){

        sQty = 4;

    }else if(fulfillment >= 65){

        sQty = 6;

    }else{

        sQty = 8;
    }

    let sPrem = 0;

    if(prem > 3000000){

        sPrem = 8;

    }else if(prem > 1000000){

        sPrem = 6;

    }else if(prem > 500000){

        sPrem = 4;

    }else if(prem > 0){

        sPrem = 2;
    }

    const total =

        100 - (
            sQty +
            otd +
            method +
            sPrem +
            dps
        );

    document.getElementById("total-score")
    .textContent = total;
}

/* =========================
   LOAD SUPPLIER
========================= */

async function loadSuppliers(){

    try{

        const response =
            await fetch('/api/suppliers');

        const suppliers =
            await response.json();

        supplierData = suppliers.filter(
            s => s.bp_status_desc === "Active"
        );

        buildSupplierDropdown(supplierData);

    }catch(error){

        console.error(
            "Supplier API Error:",
            error
        );

        document.getElementById(
            "supplierDropdown"
        ).innerHTML = `

            <div class="option">
                Failed load supplier
            </div>
        `;
    }
}

/* =========================
   DROPDOWN
========================= */

function buildSupplierDropdown(data){

    const dropdown =
        document.getElementById("supplierDropdown");

    dropdown.innerHTML = "";

    data.forEach(s => {

        const div =
            document.createElement("div");

        div.className = "option";

        div.textContent =
            `${s.bp_code} - ${s.bp_name}`;

        div.onclick = () => {

            document.getElementById(
                "supplierSearch"
            ).value =
                `${s.bp_code} - ${s.bp_name}`;

            selectedSupplier =
                s.bp_code;

            dropdown.style.display = "none";
        };

        dropdown.appendChild(div);
    });
}

/* =========================
   TOGGLE DROPDOWN
========================= */

function toggleSupplierDropdown(){

    const dropdown =
        document.getElementById("supplierDropdown");

    dropdown.style.display =

        dropdown.style.display === "block"
            ? "none"
            : "block";
}

/* =========================
   FILTER SUPPLIER
========================= */

function filterSuppliers(){

    const keyword =
        document.getElementById(
            "supplierSearch"
        ).value.toLowerCase();

    const filtered =
        supplierData.filter(s =>

            `${s.bp_code} ${s.bp_name}`
                .toLowerCase()
                .includes(keyword)

        );

    buildSupplierDropdown(filtered);

    document.getElementById(
        "supplierDropdown"
    ).style.display = "block";
}

/* =========================
   CLOSE DROPDOWN
========================= */

document.addEventListener("click", function(e){

    const dropdown =
        document.querySelector(".dropdown");

    if(
        dropdown &&
        !dropdown.contains(e.target)
    ){

        document.getElementById(
            "supplierDropdown"
        ).style.display = "none";
    }
});

/* =========================
   SAVE DATABASE
========================= */

async function saveDelivery(){

    console.log("BUTTON CLICKED");

    // VALIDASI
    const fields = [
        { id: "supplierSearch",  label: "Supplier" },
        { id: "del-month",       label: "Month" },
        { id: "del-year",        label: "Year" },
        { id: "otd",             label: "On-Time Delivery" },
        { id: "qty-ord",         label: "Quantity Ordered" },
        { id: "qty-rec",         label: "Quantity Received" },
        { id: "del-method",      label: "Delivery Method" },
        { id: "dps",             label: "DPS Reply" },
    ];

    for(let f of fields){
        const val = document.getElementById(f.id).value.trim();
        if(!val || val === ""){
            showPopup(`⚠️ "${f.label}" is required`);
            return;
        }
    }

    // Cek supplier sudah dipilih dari dropdown
    if(!selectedSupplier){
        showPopup(`⚠️ Please select Supplier from dropdown`);
        return;
    }
    const docNumber =
        "DELQC-" + Date.now();

    const payload = {

        docNumber:
            docNumber,

        supplierSearch:
            document.getElementById("supplierSearch").value,

        createdOn:
            document.getElementById("created-on").value,

        delMonth:
            document.getElementById("del-month").value,

        delYear:
            document.getElementById("del-year").value,

        otd:
            document.getElementById("otd").value,

        qtyOrd:
            document.getElementById("qty-ord").value,

        qtyRec:
            document.getElementById("qty-rec").value,

        fulfillment:
            document.getElementById("fulfillment").value,

        delMethod:
            document.getElementById("del-method").value,

        premium:
            document.getElementById("premium").value,

        dps:
            document.getElementById("dps").value,

        totalScore:
            document.getElementById("total-score").textContent
    };

    console.log("PAYLOAD:", payload);

    try{

        const response = await fetch('/delivery/store', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'Accept': 'application/json',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'
            },

            body: JSON.stringify(payload)
        });

        console.log("STATUS:", response.status);

        const text = await response.text();

        console.log("RAW RESPONSE:", text);

        let result = {};

        try{
            result = JSON.parse(text);
        }catch(e){
            console.log("NOT JSON RESPONSE");
        }

        if(response.ok){

            document.getElementById(
                "popup-message"
            ).textContent =

                `✔ ${docNumber} saved successfully`;

            document.getElementById("popup")
                .style.display = "flex";

        }else{

            alert("Laravel Error Check Console");
        }

    }catch(error){

        console.error("FETCH ERROR:", error);

        alert("Failed save delivery data");
    }
}
function showPopup(message){
    document.getElementById("popup-message").textContent = message;
    document.getElementById("popup").style.display = "flex";
}
/* =========================
   CLOSE POPUP
========================= */

function closePopup(){
    document.getElementById("popup").style.display = "none";

    // Hanya redirect kalau sukses
    const msg = document.getElementById("popup-message").textContent;
    if(msg.includes("saved successfully")){
        window.location.href = "/delivery/history";
    }
}

/* =========================
   INIT
========================= */

loadSuppliers();

</script>

@endpush