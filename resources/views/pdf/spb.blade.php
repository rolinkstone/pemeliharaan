<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Permintaan Barang (SPB)</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      background-color: #f9c9f1;
      padding: 30px;
    }

    .container {
      background-color: #fff;
      border: 2px solid #000;
      padding: 20px;
      max-width: 800px;
      margin: 0 auto;
      background-color: #f9c9f1;
    }

    h1 {
      text-align: center;
      font-size: 18px;
      margin-bottom: 5px;
    }

    .subtitle {
      text-align: center;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .table-info,
    .table-barang {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .table-info td {
      padding: 4px;
    }

    .table-barang th,
    .table-barang td {
      border: 1px solid #000;
      padding: 5px;
      text-align: center;
    }

    .ttd {
  display: flex;
  justify-content: space-between;
  text-align: center;
  margin-top: 50px;
  background-color: #f9c9f3; /* Sesuaikan dengan warna pink seperti gambar */
  padding: 20px;
  border-radius: 10px;
}

.kolom-ttd {
  flex: 1;
}

.ttd div {
  text-align: center;
  flex: 1;
  padding: 10px;
}

.ttd div p {
  margin-top: 60px;
  border-top: 1px dotted #000;
  padding-top: 5px;
}


    .small {
      font-size: 10px;
      float: right;
    }
  </style>
</head>
<body>
    <br>
    <br>
<div class="small">POM-14.02/CFM.02/SOP.01/IK.16A.01/F.03</div>
  <div class="container">
    
    <div style="text-align:center;">
    <img src="{{ $logo_base64 }}" width="75" style="display: block; margin: 0 auto;">

    </div>
    <br>
    <div class="subtitle">BADAN PENGAWAS OBAT DAN MAKANAN</div>
    <h1>SURAT PERMINTAAN BARANG (SPB)</h1>

    <table class="table-info">
      <tr>
        <td><strong>Unit Seksi/Sub Bagian</strong></td>
        <td>: <em>{{ $fungsi }}</em></td>
      </tr>
      <tr>
        <td><strong>Nomor</strong></td>
        <td>: <em>{{ $no_ticket }}</em></td>
      </tr>
      <tr>
        <td><strong>Tanggal</strong></td>
        <td>: <em>{{ $tanggal }}</em></td>
      </tr>
    </table>

    <table class="table-barang">
      <thead>
        <tr>
          <th>NO</th>
          <th>NAMA BARANG</th>
          <th>SATUAN</th>
          <th>JUMLAH PERMINTAAN</th>
          <th>JUMLAH DISETUJUI</th>
          <th>KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
    @foreach ($items as $index => $item)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td style="text-align: left;">{{ $item->nama_barang }}</td>
        <td>{{ $item->satuan }}</td>
        <td>{{ $item->jumlah }}</td>
        <td>{{ $item->jumlah }}</td>
        <td>{{ $item->keterangan }}</td>
    </tr>
    @endforeach
    @for ($i = count($items); $i < 10; $i++)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
  </tr>
  @endfor
    </tbody>
    </table>

    <br>
    <br>
    <br>
    <div style="text-align: center; margin-top: 50px;">
  <div style="text-align: left; width: 150px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Diserahkan</strong><br>
      Pengelola Gudang<br><br><br>
      <strong>I Putu Hendrawan</strong>
    </div>
  </div>

  <div style="width: 100px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Diterima</strong><br><br><br><br>
      <strong> {{ $nama_pelapor }}</strong>
    </div>
  </div>

  <div style="width: 100px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Mengetahui</strong><br>
      Ketua Tim Kerja<br><br><br>
      <strong>{{ $nama_katim }}</strong>
    </div>
  </div>
</div>


    
  
  
</div>


  </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Permintaan Barang (SPB)</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      background-color: #f9c9f1;
      padding: 30px;
    }

    .container {
      background-color: #fff;
      border: 2px solid #000;
      padding: 20px;
      max-width: 800px;
      margin: 0 auto;
      background-color: #f9c9f1;
    }

    h1 {
      text-align: center;
      font-size: 18px;
      margin-bottom: 5px;
    }

    .subtitle {
      text-align: center;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .table-info,
    .table-barang {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .table-info td {
      padding: 4px;
    }

    .table-barang th,
    .table-barang td {
      border: 1px solid #000;
      padding: 5px;
      text-align: center;
    }

    .ttd {
  display: flex;
  justify-content: space-between;
  text-align: center;
  margin-top: 50px;
  background-color: #f9c9f3; /* Sesuaikan dengan warna pink seperti gambar */
  padding: 20px;
  border-radius: 10px;
}

.kolom-ttd {
  flex: 1;
}

.ttd div {
  text-align: center;
  flex: 1;
  padding: 10px;
}

.ttd div p {
  margin-top: 60px;
  border-top: 1px dotted #000;
  padding-top: 5px;
}


    .small {
      font-size: 10px;
      float: right;
    }
  </style>
</head>
<body>
    <br>
    <br>
<div class="small">POM-14.02/CFM.02/SOP.01/IK.16A.01/F.04</div>
  <div class="container">
    
    <div style="text-align:center;">
    <img src="{{ $logo_base64 }}" width="75" style="display: block; margin: 0 auto;">

    </div>
    <br>
    <div class="subtitle">BADAN PENGAWAS OBAT DAN MAKANAN</div>
    <h1>SURAT BUKTI BARANG KELUAR (SBBK)</h1>

    <table class="table-info">
      <tr>
        <td><strong>Unit Seksi/Sub Bagian</strong></td>
        <td>: <em>{{ $fungsi }}</em></td>
      </tr>
      <tr>
        <td><strong>Nomor</strong></td>
        <td>: <em>{{ $no_ticket }}</em></td>
      </tr>
      <tr>
        <td><strong>Tanggal</strong></td>
        <td>: <em>{{ $tanggal }}</em></td>
      </tr>
    </table>

    <table class="table-barang">
      <thead>
        <tr>
          <th>NO</th>
          <th>NAMA BARANG</th>
          <th>SATUAN</th>
          <th>JUMLAH</th>
          <th>KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
    @foreach ($items as $index => $item)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td style="text-align: left;">{{ $item->nama_barang }}</td>
        <td>{{ $item->satuan }}</td>
        <td>{{ $item->jumlah }}</td>
        <td>{{ $item->keterangan }}</td>
    </tr>
    @endforeach
    @for ($i = count($items); $i < 10; $i++)
  <tr>
      <td>{{ $i + 1 }}</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
     
  </tr>
  @endfor
    </tbody>
    </table>

    <br>
    <br>
    <br>
    <div style="text-align: center; margin-top: 50px;">
  <div style="text-align: left; width: 150px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Diserahkan</strong><br>
      Pengelola Gudang<br><br><br>
      <strong>I Putu Hendrawan</strong>
    </div>
  </div>

 

  <div style="width: 100px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Mengetahui</strong><br>
      <br><br><br>
      <strong>{{ $nama_kabag_tu }}</strong>
    </div>
  </div>

  <div style="width: 100px; height: 150px; margin: 0 40px; display: inline-block; vertical-align: top;">
    <div class="kolom-ttd">
      <strong>Diterima</strong><br><br><br><br>
      <strong> {{ $nama_pelapor }}</strong>
    </div>
  </div>
</div>


    
  
  
</div>


  </div>
</body>
</html>

