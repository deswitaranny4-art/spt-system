@extends('layouts.app')

@section('title', 'Edit Delivery')
@section('page_title', 'Edit Delivery')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/delivhistory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manageuser.css') }}">
@endpush

@section('content')
<form method="POST" action="/delivery/update/{{ $delivery->id }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-title">Edit Delivery Data</div>

        <div class="form-grid">
            <div class="input-group">
                <label>Doc Number</label>
                <input type="text" value="{{ $delivery->docNumber }}" disabled>
            </div>

            <div class="input-group">
                <label>Supplier</label>
                <input type="text" name="supplierSearch" value="{{ $delivery->supplierSearch }}" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>Month</label>
                <select name="delMonth" required>
                    <option value="01" {{ $delivery->del_month == '01' ? 'selected' : '' }}>January</option>
                    <option value="02" {{ $delivery->del_month == '02' ? 'selected' : '' }}>February</option>
                    <option value="03" {{ $delivery->del_month == '03' ? 'selected' : '' }}>March</option>
                    <option value="04" {{ $delivery->del_month == '04' ? 'selected' : '' }}>April</option>
                    <option value="05" {{ $delivery->del_month == '05' ? 'selected' : '' }}>May</option>
                    <option value="06" {{ $delivery->del_month == '06' ? 'selected' : '' }}>June</option>
                    <option value="07" {{ $delivery->del_month == '07' ? 'selected' : '' }}>July</option>
                    <option value="08" {{ $delivery->del_month == '08' ? 'selected' : '' }}>August</option>
                    <option value="09" {{ $delivery->del_month == '09' ? 'selected' : '' }}>September</option>
                    <option value="10" {{ $delivery->del_month == '10' ? 'selected' : '' }}>October</option>
                    <option value="11" {{ $delivery->del_month == '11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{ $delivery->del_month == '12' ? 'selected' : '' }}>December</option>
                </select>
            </div>

            <div class="input-group">
                <label>Year</label>
                <input type="text" name="delYear" value="{{ $delivery->del_year }}" required>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>On Time Delivery</label>
                <select name="otd" id="otd" onchange="calc()">
                    <option value="0" {{ $delivery->otd == '0' ? 'selected' : '' }}>No Delay</option>
                    <option value="2" {{ $delivery->otd == '2' ? 'selected' : '' }}>Delay 1 day</option>
                    <option value="4" {{ $delivery->otd == '4' ? 'selected' : '' }}>Delay 2 days</option>
                    <option value="6" {{ $delivery->otd == '6' ? 'selected' : '' }}>Delay 3 days</option>
                    <option value="10" {{ $delivery->otd == '10' ? 'selected' : '' }}>Delay > 3 days</option>
                </select>
            </div>

            <div class="input-group">
                <label>Fulfillment (%)</label>
                <input type="text" id="fulfillment" name="fulfillment" value="{{ $delivery->fulfillment }}" readonly>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>Qty Order</label>
                <input type="number" id="qtyOrd" name="qtyOrd" value="{{ $delivery->qty_ord }}" oninput="calc()">
            </div>

            <div class="input-group">
                <label>Qty Received</label>
                <input type="number" id="qtyRec" name="qtyRec" value="{{ $delivery->qty_rec }}" oninput="calc()">
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>Delivery Method</label>
                <select name="delMethod" id="delMethod" onchange="calc()">
                    <option value="0" {{ $delivery->del_method == '0' ? 'selected' : '' }}>Normal</option>
                    <option value="4" {{ $delivery->del_method == '4' ? 'selected' : '' }}>Abnormal</option>
                </select>
            </div>

            <div class="input-group">
                <label>Premium Freight (Rp)</label>
                <input type="number" id="premium" name="premium" value="{{ $delivery->premium }}" oninput="calc()">
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>DPS Reply</label>
                <select name="dps" id="dps" onchange="calc()">
                    <option value="0" {{ $delivery->dps == '0' ? 'selected' : '' }}>No Problem</option>
                    <option value="5" {{ $delivery->dps == '5' ? 'selected' : '' }}>On Time</option>
                    <option value="10" {{ $delivery->dps == '10' ? 'selected' : '' }}>Delay</option>
                    <option value="20" {{ $delivery->dps == '20' ? 'selected' : '' }}>No Reply</option>
                </select>
            </div>

            <div class="input-group">
                <label>Total Score</label>
                <input type="text" id="totalScore" name="totalScore" value="{{ $delivery->total_score }}" readonly>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:20px;">
            <button type="submit" class="add-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Save
            </button>

            <a href="/delivery/history" class="add-btn" style="background:#6b7280; text-decoration:none;">
                Cancel
            </a>
        </div>

    </div>

</form>

@push('scripts')
<script>
function calc() {
    const ord = parseFloat(document.getElementById("qtyOrd").value) || 0;
    const rec = parseFloat(document.getElementById("qtyRec").value) || 0;
    const otd = parseInt(document.getElementById("otd").value) || 0;
    const method = parseInt(document.getElementById("delMethod").value) || 0;
    const prem = parseFloat(document.getElementById("premium").value) || 0;
    const dps = parseInt(document.getElementById("dps").value) || 0;

    let fulfillment = 0;
    if (ord > 0) fulfillment = Math.min((rec / ord) * 100, 100);

    document.getElementById("fulfillment").value =
        fulfillment > 0 ? fulfillment.toFixed(0) + "%" : "";

    let sQty = 0;
    if (fulfillment >= 95)      sQty = 0;
    else if (fulfillment >= 85) sQty = 2;
    else if (fulfillment >= 75) sQty = 4;
    else if (fulfillment >= 65) sQty = 6;
    else                        sQty = 8;

    let sPrem = 0;
    if (prem > 3000000)      sPrem = 8;
    else if (prem > 1000000) sPrem = 6;
    else if (prem > 500000)  sPrem = 4;
    else if (prem > 0)       sPrem = 2;

    const total = 100 - (sQty + otd + method + sPrem + dps);
    document.getElementById("totalScore").value = total.toFixed(1);
}

// hitung saat halaman load
calc();
</script>
@endpush

@endsection