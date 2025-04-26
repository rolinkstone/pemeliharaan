<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulir Identitas Sarana Prasarana</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid black;
      padding: 6px;
      vertical-align: top;
    }
    td.label {
      white-space: nowrap;
      width: 35%; /* LEBAR kolom pertama diperlebar di sini */
    }
    .no-border {
      border: none;
    }
    .section-title {
      text-align: center;
      font-weight: bold;
      margin: 20px 0 10px;
    }
    .signature {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
    }
    .signature div {
      text-align: center;
    }
    .small-text {
      font-size: 10px;
    }
    .no-border {
  border-collapse: collapse; /* satukan garis */
  width: 100%;
}

.no-border td {
  border: none; /* hilangkan garis */
  text-align: center; /* tengahin isi */
  width: 50%; /* bagi dua kolom rata */
  vertical-align: top; /* teks mulai dari atas */
}

  </style>
</head>
<body>
<div style="text-align:center;">
    <img src="{{ $logo_base64 }}" width="75" style="display: block; margin: 0 auto;">

    </div>
<div class="small-text">
  Bagian/KS:  {{ $fungsi }}<br>
  Nama Formulir: Laporan Sarana Prasarana<br>
  <br>
  <div style="text-align: right;">POM-04.SOP.04/L/5.5/F.08</div>
</div>

<h3 class="section-title">IDENTITAS SARANA PRASARANA</h3>

<table>
  <tr>
    <td class="label">Jenis Laporan</td>
    <td>{{ $jenis_laporan }} </td>
  </tr>
  <tr>
    <td class="label">Uraian Laporan</td>
    <td>{{ $uraian_laporan }}</td>
  </tr>
  <tr>
    <td class="label">Nama Alat</td>
    <td>{{ $nama }}</td>
  </tr>
  <tr>
    <td class="label">Kode BMN</td>
    <td> {{ $kode_barang }}</td>
  </tr>
  <tr>
    <td class="label">Type/Seri</td>
    <td> {{ $jenis_barang }}</td>
  </tr>
  <tr>
    <td class="label">Lokasi</td>
    <td>{{ $ruangan }}</td>
  </tr>
  <tr>
    <td class="label">Tipe Alat</td>
    <td> {{ $tipe_alat }}</td>
  </tr>
</table>

<div class="signature">
  <table class="no-border">
    <tr>
      <td>
        <div class="left">
          Tanggal : {{ $tanggal }}<br><br>
          Nama Pelapor
        <br><br> 
        {{ $nama_pelapor }}
        </div>
      </td>
      <td>
        <div class="right">
        <br>
          Mengetahui<br>
          Ketua TIM Kerja
          <br><br> 

          {{ $nama_katim }}
        </div>
      </td>
    </tr>
  </table>
</div>

<br>

<h3 class="section-title">DISPOSISI KEPALA BAGIAN TATA USAHA</h3>
@if($items && count($items))
          
          @foreach($items as $item)
<table>
  <tr>
    <td class="label">Ditujukan Ke</td>
    <td>{{ $item->ditujukan_ke }}</td>
  </tr>
  <tr>
    <td class="label">Isi</td>
    <td>{{ $item->isi }}</td>
  </tr>
</table>

<div class="signature">
  <table class="no-border">
    <tr>
      <td>
        
      </td>
      <td>
        <div class="right">
        <br>
         Plt.Kepala  Bagian Tata Usaha
          <br><br>

          {{ $nama_kabag_tu }}
        </div>
      </td>
    </tr>
  </table>
</div>
@endforeach
@else
        <p>Data Belum Diinput</p>
        @endif
<br>

<h3 class="section-title">TINDAK LANJUT</h3>
@if($items && count($items))
@foreach($items as $item)
<table>
  <tr>
    <td class="label">Diserahkan kepada</td>
    <td>{{ $item->diserahkan }}</td>
  </tr>
  <tr>
    <td class="label">Tanggal</td>
    <td>{{ $item->tanggal }}</td>
  </tr>
</table>

<div class="signature">
  <table class="no-border">
    <tr>
      <td>
        
      </td>
      <td>
        <div class="right">
        <br>
         Plt.Kepala  Bagian Tata Usaha
          <br><br> 

          {{ $nama_kabag_tu }}
        </div>
      </td>
    </tr>
  </table>
</div>
@endforeach
@else
<p>Data Belum Diinput</p>
@endif

<h3 class="section-title">TINDAKAN PERBAIKAN</h3>
@if($perbaikanItems && count($perbaikanItems))
@foreach($perbaikanItems as $perbaikanItem)
<table>
  <tr>

    <td class="label">Tanggal</td>
    <td>{{ $perbaikanItem->tanggal }}</td>
  </tr>
  <tr>
    <td class="label">Kerusakan</td>
    <td>{{ $perbaikanItem->kerusakan }}</td>
  </tr>
  <tr>
    <td class="label">Hasil Perbaikan</td>
    <td>{{ $perbaikanItem->hasil }}</td>
  </tr>
  <tr>
    <td class="label">Kesimpulan</td>
    <td>{{ $perbaikanItem->kesimpulan }}</td>
  </tr>
  <tr>
    <td class="label">Catatan</td>
    <td>{{ $perbaikanItem->catatan }}</td>
  </tr>
</table>

<div class="signature">
  <table class="no-border">
    <tr>
      <td>
        <div class="left">
        Nama Petugas
          <br><br> 

          {{ $perbaikanItem->nama }}
        </div>
      </td>
    
      <td>
        <div class="right">
        Plt.Kepala  Bagian Tata Usaha
          <br><br> 

          {{ $nama_kabag_tu }}
        </div>
      </td>
    </tr>
  </table>
</div>
@endforeach
           
           @else
           <p>Data Belum Diinput</p>
           @endif
<br>
<h3 class="section-title">PENYELESAIAN TINDAK LANJUT / KESIMPULAN</h3>
@if($perbaikanItems && count($perbaikanItems))
@foreach($perbaikanItems as $perbaikanItem)
<table>
  <tr>
    <td class="label">Kesimpulan</td>
    <td>{{ $perbaikanItem->kesimpulan }}</td>
  </tr>
  <tr>
    <td class="label">Tanggal</td>
    <td>{{ $perbaikanItem->tanggal }}</td>
  </tr>
</table>

<div class="signature">
  <table class="no-border">
    <tr>
      <td>
        
      </td>
      <td>
        <div class="right">
        <br>
         Plt.Kepala  Bagian Tata Usaha
          <br><br> 

          {{ $nama_kabag_tu }}
        </div>
      </td>
    </tr>
  </table>
</div>
@endforeach
           
           @else
           <p>Data Belum Diinput</p>
           @endif
</body>
</html>
