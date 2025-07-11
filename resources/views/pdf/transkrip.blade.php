<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transkrip Nilai</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 100px 40px 40px 40px;
            /* padding top digeser agar content turun, tapi kop tetap di atas */
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            position: relative;
        }

        .kop-sekolah-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 2;
        }

        .kop-sekolah-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }

        .judul {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: -10px;
            margin-bottom: -3px;
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
            margin-bottom: 1px;
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

        th,
        td {
            border: 1px solid #555;
            padding: 1px;
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
            text-align: left;
            margin-top: 20px;
            margin-left: 530px;
        }

        .ttd p {
            margin: 2px 0;
        }

        .watermark {
            position: fixed;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            z-index: -1;
            width: 60%;
        }

        .watermark img {
            width: 100%;
        }

        .content {
            margin-top: 80px;
            /* agar konten turun setelah kop */
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    @if (isset($watermarkBase64))
        <div
            style="
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.05;
        z-index: 0;
        width: 90%;
        text-align: center;
    ">
            <img src="{{ $watermarkBase64 }}" alt="Watermark" style="width: 100%; height: auto;" />
        </div>
    @endif

    {{-- KOP SEKOLAH --}}
    @if (isset($kopBase64))
        <div class="kop-sekolah-wrapper">
            <img src="{{ $kopBase64 }}" alt="Kop Sekolah" />
        </div>
    @endif

    <div class="content">

        {{-- Judul --}}
        <div class="judul">TRANSKRIP NILAI</div>

        {{-- Nomor Transkrip --}}
        @if ($siswa->no_transkrip)
            <div class="no-transkrip">Nomor: {{ $siswa->no_transkrip }}</div>
        @endif

        {{-- Biodata Siswa --}}
        <div class="biodata">
            <div><span class="label">Satuan Pendidikan</span>: {{ $schoolConfig->nama_sekolah ?? '-' }}</div>
            <div><span class="label">Nomor Pokok Sekolah Nasional</span>: {{ $schoolConfig->npsn ?? '-' }}</div>
            <div><span class="label">Nama Lengkap</span>: {{ ucwords(strtolower($siswa->nama_lengkap)) }}</div>
            <div><span class="label">Tempat dan Tanggal Lahir</span>: {{ ucwords(strtolower($siswa->tempat_lahir)) }},
                {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') }}</div>
            <div><span class="label">Nomor Induk Siswa Nasional</span>: {{ $siswa->nisn }}</div>
            <div><span class="label">Nomor Ijazah</span>: {{ $siswa->nomor_ijazah }}</div>
            <div><span class="label">Tanggal Kelulusan</span>:
                {{ \Carbon\Carbon::parse($transcriptConfig->tanggal_kelulusan)->translatedFormat('d F Y') }}</div>
            <div><span class="label">Program Keahlian</span>: {{ $siswa->programKeahlian->nama_program ?? '-' }}</div>
            <div><span class="label">Konsentrasi Keahlian</span>:
                {{ $siswa->programKeahlian->nama_konsentrasi ?? '-' }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align:center; padding: 10px 0;">No</th>
                    <th style="width: 50%; text-align:center; padding: 10px 0;">Mata Pelajaran</th>
                    <th style="width: 20%; text-align:center; padding: 10px 0;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupChar = 'A';
                @endphp

                @foreach ($groupedNilai as $kelompok => $mapels)
                    @php
                        $no = 1;
                        $showMulokHeader = false;
                        $abjad = 'a';
                    @endphp
                    <tr>
                        <td colspan="3" style="font-weight:bold; background-color:#f2f2f2; padding-left: 15px;">
                            {{ $groupChar }}. {{ $kelompok }}</td>
                    </tr>

                    @php $groupChar++; @endphp

                    @foreach ($mapels as $index => $item)
                        @if ($item->mapel->is_mulok)
                            @if (!$showMulokHeader)
                                {{-- Baris judul Muatan Lokal --}}
                                <tr>
                                    <td rowspan="3" style="text-align:center; vertical-align: top;">
                                        {{ $no++ }}</td>
                                    <td colspan="2" style="padding-left: 5px;"><strong>Muatan Lokal</strong></td>
                                </tr>
                                @php
                                    $showMulokHeader = true;
                                    $abjad = 'a';
                                @endphp
                            @endif

                            <tr>

                                <td style="padding-left: 5px;">{{ $abjad++ }}.
                                    {{ $item->mapel->nama_mata_pelajaran }}</td>
                                <td style="text-align:center;">{{ $item->nilai }}</td>
                            </tr>
                        @else
                            <tr>
                                <td style="text-align:center;">{{ $no++ }}</td>
                                <td style="padding-left: 5px;">{{ $item->mapel->nama_mata_pelajaran }}</td>
                                <td style="text-align:center;">{{ $item->nilai }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach

                <tr class="rata-rata">
                    <td colspan="2" style="text-align: center;">Rata-rata</td>
                    <td style="text-align:center;">
                        {{ round($siswa->transkripNilai->avg('nilai'), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>


        {{-- Tanda Tangan --}}
        <div class="ttd">
            <p>{{ $schoolConfig->kota ?? '-' }},
                {{ \Carbon\Carbon::parse($transcriptConfig->tanggal_transkrip)->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 70px;">Kepala Sekolah</p>
            <p style="font-weight: bold; text-decoration: underline;">{{ $schoolConfig->nama_kepala_sekolah ?? '-' }}
            </p>
            <p>NIP. {{ $schoolConfig->nip_kepala_sekolah ?? '-' }}</p>
        </div>
    </div>
</body>

</html>
