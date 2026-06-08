@extends('layouts.app')

@section('title', 'Edit QC')
@section('page_title', 'Edit QC')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/qchistory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manageuser.css') }}">
@endpush

@section('content')
<form method="POST" action="/qc/update/{{ $qc->id }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-title">Edit QC Data</div>

        <div class="form-grid">
            <div class="input-group">
                <label>Doc Number</label>
                <input type="text" value="{{ $qc->docNumber }}" disabled>
            </div>

            <div class="input-group">
                <label>Supplier</label>
                <input type="text" value="{{ $qc->supplier }}" disabled>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>Line Stop</label>
                <select name="lineStop" id="lineStop" onchange="calcQC()">
                    <option value="0" {{ $qc->lineStop == '0' ? 'selected' : '' }}>No</option>
                    <option value="40" {{ $qc->lineStop == '40' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div class="input-group">
                <label>NG</label>
                <input type="number" name="ng" id="ng" value="{{ $qc->ng }}" oninput="calcQC()">
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>Supply</label>
                <input type="number" name="supply" id="supply" value="{{ $qc->supply }}" oninput="calcQC()">
            </div>

            <div class="input-group">
                <label>PPM</label>
                <input type="text" id="ppm" name="ppm" value="{{ $qc->ppm }}" readonly>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>PPM Score</label>
                <input type="text" id="ppmScore" name="ppmScore" value="{{ $qc->ppmScore }}" readonly>
            </div>

            <div class="input-group">
                <label>Problem Rank</label>
                <select name="rank_score" id="rank_score" onchange="calcQC()">
                    <option value="0" {{ $qc->rank_score == '0' ? 'selected' : '' }}>No Problem</option>
                    <option value="25" {{ $qc->rank_score == '25' ? 'selected' : '' }}>A</option>
                    <option value="10" {{ $qc->rank_score == '10' ? 'selected' : '' }}>B</option>
                    <option value="5" {{ $qc->rank_score == '5' ? 'selected' : '' }}>C</option>
                </select>
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label>FPPK</label>
                <select name="fppk" id="fppk" onchange="calcQC()">
                    <option value="0" {{ $qc->fppk == '0' ? 'selected' : '' }}>No Problem</option>
                    <option value="10" {{ $qc->fppk == '10' ? 'selected' : '' }}>Delay</option>
                    <option value="20" {{ $qc->fppk == '20' ? 'selected' : '' }}>No Reply</option>
                </select>
            </div>

            <div class="input-group">
                <label>Total Score</label>
                <input type="text" id="total_score" name="total_score" value="{{ $qc->total_score }}" readonly>
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-top:20px;">
            <button type="submit" class="add-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Save
            </button>

            <a href="/qc/history" class="add-btn" style="background:#6b7280; text-decoration:none;">
                Cancel
            </a>
        </div>

    </div>

</form>

@push('scripts')
<script>
function calcQC() {
    const lineStop = Number(document.getElementById("lineStop").value) || 0;
    const ng = Number(document.getElementById("ng").value) || 0;
    const supply = Number(document.getElementById("supply").value) || 0;
    const rankScore = Number(document.getElementById("rank_score").value) || 0;
    const fppk = Number(document.getElementById("fppk").value) || 0;

    let ppmScore = 0;

    if (supply > 0) {
        let ppm = (ng / supply) * 1000000;
        document.getElementById("ppm").value = Math.round(ppm);

        if (ppm === 0)       ppmScore = 0;
        else if (ppm <= 20)  ppmScore = 5;
        else if (ppm <= 200) ppmScore = 10;
        else                 ppmScore = 15;

        document.getElementById("ppmScore").value = ppmScore;
    }

    const total = 100 - (lineStop + ppmScore + rankScore + fppk);
    document.getElementById("total_score").value = Math.max(0, Math.min(100, total)).toFixed(1);
}

calcQC();
</script>
@endpush

@endsection