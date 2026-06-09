@extends('layouts.app', ['is_seller' => true])
@section('content')
<div class="fade-in">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <i class="fas fa-file-upload mr-2"></i> Recibir Documento
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
                Seleccione el archivo Attached Document XML para recibir el documento electrónico.
            </p>
            
            <form method="POST" action="{{ url('/sellers-document-reception/'.$company_idnumber) }}" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label style="font-size: 13px; font-weight: 500; color: #3d4f5f;">Archivo XML</label>
                    <div class="custom-file-upload" style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: border-color 0.2s;" onclick="document.getElementById('formFileInput').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #94a3b8; margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: #64748b; font-size: 13px;" id="fileName">Haga clic para seleccionar archivo</p>
                        <input type="file" id="formFileInput" name="formFileInput" accept=".xml" style="display: none;" onchange="updateFileName(this)">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" style="margin-top: 20px;">
                    <i class="fas fa-upload mr-2"></i> Recibir Documento
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    var fileName = input.files[0] ? input.files[0].name : 'Haga clic para seleccionar archivo';
    document.getElementById('fileName').textContent = fileName;
    if(input.files[0]) {
        input.parentElement.style.borderColor = '#2563eb';
    }
}
</script>
@endsection
