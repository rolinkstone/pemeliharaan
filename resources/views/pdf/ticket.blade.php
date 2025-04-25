<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Laporan  {{ $jenis_laporan }} {{ $tipe_alat }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            line-height: 1.4;
            font-size: 10px; /* Ukuran font diperkecil */
        }
        h1, h2, h3 {
            color: #333;
            margin: 5px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 10px;
        }
        .section h2 {
            background-color: #f4f4f4;
            padding: 5px;
            border-left: 3px solid #333;
            font-size: 12px; /* Judul section sedikit lebih besar */
        }
        .section p {
            margin: 3px 0;
        }
        .section ul {
            margin: 3px 0;
            padding-left: 15px;
        }
        .signature {
            margin-top: 15px;
        }
        .signature p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 8px; /* Ukuran font footer diperkecil */
            color: #777;
            text-align: center;
        }
        .page-break {
            page-break-inside: avoid; /* Memastikan tidak ada pemisahan halaman di tengah section */
        }
    </style>
</head>

<body>
<div class="small" style="text-align: right;">POM-14.02/CFM.02/SOP.01/IK.16A.01/F.03</div>
<div style="text-align:center;">
    <img src="{{ $logo_base64 }}" width="75" style="display: block; margin: 0 auto;">

    </div>
<br>
    <div class="header page-break">
        <h1>Laporan  {{ $jenis_laporan }} {{ $tipe_alat }}</h1>
        <p><strong>No. Laporan:</strong>  {{ $no_ticket }}</p>
        <p><strong>Status:</strong>
    @if($perbaikanItems && count($perbaikanItems))
        Selesai
    @else
        On Proses
    @endif
</p>
        <p><strong>Jenis Laporan:</strong> {{ $jenis_laporan }}</p>
    </div>

    <div class="section page-break">
        <h2>INFORMASI LAPORAN</h2>
        <p><strong>Uraian Laporan:</strong> {{ $uraian_laporan }}</p>
        
        <p><strong>Jenis Barang:</strong> {{ $jenis_barang }}</p>
        <p><strong>Nama Barang:</strong> {{ $nama }}</p>
        <p><strong>Kode Barang:</strong> {{ $kode_barang }}</p>
        <p><strong>Ruangan:</strong> {{ $ruangan }}</p>
        <p><strong>Tipe Alat:</strong>{{ $tipe_alat }}</p>
        <p><strong>Tanggal Laporan:</strong>{{ $tanggal }}</p>
        <br>
        <h2>DISPOSISI</h2>

      @if($items && count($items))
          
                @foreach($items as $item)
                <p><strong>Ditujukan Ke:</strong> {{ $item->ditujukan_ke }}<p>
                <p><strong>Disposisi:</strong> {{ $item->isi }}</p>
                <br>
                <h2>TINDAK LANJUT</h2>
                <p><strong>Diserahkan Ke:</strong> {{ $item->diserahkan }}</p>
                <p><strong>Pada Tanggal:</strong> {{ $item->tanggal }}</p>
                @endforeach
           
        @else
        <p>Data Belum Diinput</p>
        @endif

      
    </div>

  

    <div class="section page-break">
        <h2>PENYELESAIAN TINDAK LANJUT</h2>
       
        @if($perbaikanItems && count($perbaikanItems))
          
          @foreach($perbaikanItems as $perbaikanItem)
          <p><strong>Deskripsi Kerusakan:</strong> {{ $perbaikanItem->kerusakan }}</p>
            <p><strong>Hasil Pemeriksaan:</strong> {{ $perbaikanItem->hasil }}</p>
            <p><strong>Kesimpulan:</strong> {{ $perbaikanItem->kesimpulan }}</p>
            <p><strong>Catatan:</strong> {{ $perbaikanItem->catatan }}</p>
            <p><strong>Tanggal Penyelesaian:</strong> {{ $perbaikanItem->tanggal }}</p>
           
          @endforeach
     
 
    </div>

    <div style="text-align: center; margin-top: 50px;">
  <div style="text-align: left; width: 180px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Nama Petugas</strong><br>
      <br><br><br>
      <strong>  <p><strong>{{ $perbaikanItem->nama }}</p></strong>
    </div>
  </div>
  @else
      <p>Data Belum Diinput</p>
  @endif
  <div style="text-align: left; width: 180px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Pelapor</strong><br>
      <br><br><br>
      <strong>  <p><strong>{{ $nama_pelapor }}</p></strong>
    </div>
  </div>

  
</div>

    <div class="footer page-break">
        <p><strong>Catatan:</strong></p>
        <ul>
            <li>Pastikan laporan disimpan sebagai arsip.</li>
            <li>Jika terdapat masalah lebih lanjut, segera hubungi Tim BMN.</li>
        </ul>
    </div>

</body>
</html>