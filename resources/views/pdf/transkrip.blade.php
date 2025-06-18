<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transkrip Nilai</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      line-height: 1.5;
      position: relative;
      margin: 0;
      padding: 30px 40px;
    }

    .kop-sekolah {
      text-align: center;
      margin-bottom: 20px;
    }

    .kop-sekolah img {
      max-width: 100%;
      height: auto;
    }

    .judul {
      text-align: center;
      font-size: 16px;
      font-weight: bold;
      margin-top: -10px;
      margin-bottom: 5px;
    }

    .no-transkrip {
      text-align: center;
      font-size: 12px;
      margin-bottom: 20px;
    }

    .biodata {
      margin-bottom: 20px;
    }

    .biodata div {
      margin-bottom: 5px;
    }

    .biodata span.label {
      display: inline-block;
      width: 200px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 20px;
    }

    th, td {
      border: 1px solid #555;
      padding: 6px;
      font-size: 12px;
    }

    th {
      background-color: #eee;
      text-align: left;
    }

    .rata-rata td {
      font-weight: bold;
    }

    .ttd {
      text-align: right;
      margin-top: 40px;
    }

    .ttd p {
      margin: 2px 0;
    }

    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      opacity: 0.05;
      z-index: 0;
    }

    .watermark img {
      max-width: 400px;
      height: auto;
    }

    .content {
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>
  {{-- Watermark Transparan --}}
  @if($schoolConfig && $schoolConfig->watermark)
    <div class="watermark">
      <img src="{{ public_path('storage/' . $schoolConfig->watermark) }}" alt="Watermark">
    </div>
  @endif

  <div class="content">
    {{-- Kop Sekolah --}}
    <div class="kop-sekolah">
      @if($schoolConfig && $schoolConfig->kop_sekolah)
        <img src="{{ public_path('storage/' . $schoolConfig->kop_sekolah) }}" alt="Kop Sekolah">
      @endif
    </div>

    {{-- Judul --}}
    <div class="judul">TRANSKRIP NILAI</div>

    {{-- Nomor Transkrip --}}
    @if($siswa->no_transkrip)
      <div class="no-transkrip">Nomor: {{ $siswa->no_transkrip }}</div>
    @endif

    {{-- Biodata Siswa --}}
    <div class="biodata">
      <div><span class="label">Satuan Pendidikan</span>: {{ $schoolConfig->nama_sekolah ?? '-' }}</div>
      <div><span class="label">NPSN</span>: {{ $schoolConfig->npsn ?? '-' }}</div>
      <div><span class="label">Nama Lengkap</span>: {{ $siswa->nama_lengkap }}</div>
      <div><span class="label">Tempat, Tanggal Lahir</span>: {{ $siswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') }}</div>
      <div><span class="label">NISN</span>: {{ $siswa->nisn }}</div>
      <div><span class="label">Nomor Ijazah</span>: {{ $siswa->nomor_ijazah }}</div>
      <div><span class="label">Tanggal Kelulusan</span>: {{ \Carbon\Carbon::parse($transcriptConfig->tanggal_kelulusan)->translatedFormat('d F Y') }}</div>
      <div><span class="label">Program Keahlian</span>: {{ $siswa->programKeahlian->nama_program ?? '-' }}</div>
      <div><span class="label">Konsentrasi Keahlian</span>: {{ $siswa->programKeahlian->nama_konsentrasi ?? '-' }}</div>
    </div>

    {{-- Tabel Nilai --}}
    <table>
      <thead>
        <tr>
          <th style="width: 5%;">No</th>
          <th style="width: 50%;">Mata Pelajaran</th>
          <th style="width: 10%; text-align:center;">Nilai</th>
          <th style="width: 25%;">Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($siswa->transkripNilai as $index => $nilai)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $nilai->mapel->nama_mata_pelajaran }}</td>
            <td style="text-align:center;">{{ $nilai->nilai }}</td>
            <td>Lulus</td>
          </tr>
        @endforeach
        <tr class="rata-rata">
          <td colspan="2">Rata-rata</td>
          <td style="text-align:center;">
            {{
              round($siswa->transkripNilai->avg('nilai'), 2)
            }}
          </td>
          <td>-</td>
        </tr>
      </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <div class="ttd">
      <p>{{ $schoolConfig->kota ?? '-' }}, {{ \Carbon\Carbon::parse($transcriptConfig->tanggal_transkrip)->translatedFormat('d F Y') }}</p>
      <p style="margin-bottom: 60px;">Kepala Sekolah</p>
      <p style="font-weight: bold;">{{ $schoolConfig->nama_kepala_sekolah ?? '-' }}</p>
      <p>NIP. {{ $schoolConfig->nip_kepala_sekolah ?? '-' }}</p>
    </div>
  </div>
</body>
</html>
