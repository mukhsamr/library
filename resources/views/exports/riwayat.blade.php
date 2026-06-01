<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIK Buku</th>
            <th>Judul</th>
            <th>NIK/NIS Peminjam</th>
            <th>Nama Peminjam</th>
            <th>Dari</th>
            <th>Sampai</th>
            <th>Catatan</th>
            <th>Waktu Dipinjam</th>
            <th>Waktu Dikembalikan</th>
            <th>Status</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($riwayat as $item)
        <tr>
            <th>{{ $loop->iteration }}</th>
            <td>{{ $item->tanggal }}</td>
            <td>{{ $item->book->nik }}</td>
            <td>{{ $item->book->judul }}</td>
            <td>{{ $item->loanable->nis ?? $item->loanable->nik }}</td>
            <td>{{ $item->loanable->nama }}</td>
            <td>{{ $item->dipinjam->format('d-m-Y') }}</td>
            <td>{{ $item->dikembalikan->format('d-m-Y')}}</td>
            <td>{{ $item->catatan }}</td>
            <td>{{ $item->created_at->format('d-m-Y H:i:s') }}</td>
            <td>{{ $item->returned_at ? $item->returned_at->format('d-m-Y H:i:s') :'-' }}</td>

            @if ($item->returned_at)
            <td>Selesai</td>
            @else
            <td>Sedang dipinjam</td>
            @endif

            <td>{{ $item->status['keterangan'] ? $item->status['keterangan']['pesan'] : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>