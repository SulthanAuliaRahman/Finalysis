<form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="text"   name="company" placeholder="Nama Perusahaan" required>
    <input type="text"   name="period"  placeholder="Periode (misal: 2024)" required>
    <input type="file"   name="file"    accept=".pdf" required>
    <button type="submit">Upload & Proses</button>
</form>
