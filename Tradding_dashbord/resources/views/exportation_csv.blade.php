

@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    @if (session('csv_success'))
        <div id="teamSuccessMessage" class="alert alert-success">
            {{ session('csv_success') }}
        </div>
    @endif

    @if (session('csv_error'))
        <div id="fileErrorMessage" class="alert alert-danger">
            {{ session('csv_error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Importer un fichier CSV</h4>
                </div>
                <div class="card-body">
                        <form id="importCsvForm" action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data">

                        @csrf
                        <div class="form-group">
                            <label for="league_id">Choisissez une ligue</label>
                            <select class="form-control" name="league_id" id="league_id">
                                @foreach ($leagues as $league)
                                    <option value="{{ $league->id }}">{{ $league->league_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file">Choisissez un fichier CSV</label>
                            <input type="file" class="form-control" name="file" id="file" required onchange="handleFileSelection()">
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Importer</button>
                    </form>
                    <div id="loader-container" class="loader-container" style="display: none;">
                        <div class="loader"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.loader-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
}

.loader {
  width: 80px;
  padding: 8px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: #25b09b;
  --_m:
    conic-gradient(#0000 10%,#000),
    linear-gradient(#000 0 0) content-box;
  -webkit-mask: var(--_m);
          mask: var(--_m);
  -webkit-mask-composite: source-out;
          mask-composite: subtract;
  animation: l3 1s infinite linear;
}
@keyframes l3 {to{transform: rotate(1turn)}}
</style>

@push('scripts')
<script>
    document.getElementById('importCsvForm').addEventListener('submit', function () {
        document.getElementById('loader-container').style.display = 'flex';
    });

    function handleFileSelection() {
        document.getElementById('loader-container').style.display = 'none';
        document.getElementById('teamSuccessMessage').style.display = 'none';
        document.getElementById('fileErrorMessage').style.display = 'none';
    }

    window.onload = function() {
        setTimeout(function() {
            var successMessage = document.getElementById('teamSuccessMessage');
            var errorMessage = document.getElementById('fileErrorMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 5000); // Masquer les messages après 5 secondes (5000 ms)
    };
</script>
@endpush
