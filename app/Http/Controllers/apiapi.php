<?php

namespace App\Http\Controllers;

use App\tanggota;
use Illuminate\Http\Request;
use App\tsaldo;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Contracts\DataTable;
use App\tlaporan;
use Yajra\DataTables\DataTables;
use App\tpoin;
use App\tsaran;
use App\tuser;
use Carbon\Traits\Timestamp;
use Illuminate\Support\Facades\DB;
use RangeException;

function writeSaldo()
{

    $saldoAwal = tsaldo::where([
        'idUser' => Session::get('userLogin')->id,
        'idLaporan' => 0
    ])->first();

    $saldo = $saldoAwal->jumlah;

    $poinPoin = tpoin::where('idUser', Session::get('userLogin')->id)->get();

    $pemasukan = 0;
    $pengeluaran = 0;

    foreach ($poinPoin as $p) {
        switch ($p->jenis) {
            case '+':
                $pemasukan = $pemasukan + $p->jumlah;
                break;
            case '-':
                $pengeluaran = $pengeluaran + $p->jumlah;
                break;
        }
    }

    $pemasukan = $saldo + $pemasukan;
    $saldo = $pemasukan - $pengeluaran;

    $ambilSaldo = tsaldo::where('created_at', 'like', '%' . date('Y-m-d') . '%')->where([
        'idUser' => Session::get('userLogin')->id,
        'idLaporan' => 0
    ])->get();

    if (count($ambilSaldo) > 0) {
        return false;
    } else {
        tsaldo::create([
            'jumlah' => $saldo,
            'idUser' => Session::get('userLogin')->id
        ]);
    }
}

class apiapi extends Controller
{
    //
    public function getSaldo()
    {
        $a = tsaldo::where('idUser', Session::get('userLogin')->id)->orderBy('updated_at', 'desc')->first();

        if ($a == null) {
            return 'x';
        } else {
            return response()->json($a);
        }
    }

    public function postSaldo(Request $a)
    {
        tsaldo::create([
            'jumlah' => $a->saldo,
            'idUser' => Session::get('userLogin')->id,
            'idLaporan' => 0
        ]);

        return 'y';
    }
    /*
    |
    | LAPORAN
    |
    */

    public function getDataLaporan()
    {
        return DataTables::of(
            tlaporan::where('idUser', Session::get('userLogin')->id)->orderBy('tanggal', 'desc')->get()
        )->make(true);
    }

    public function deleteDataLaporan(Request $a)
    {
        // LANJUT HAPUS LAPORAN SERENTAk
        $getLaporan = tlaporan::where([
            'idUser' => Session::get('userLogin')->id,
        ])->where('tanggal', '>=', $a->tanggal)->get();

        $daftarLaporan = [];

        foreach ($getLaporan as $laporan) {
            array_push($daftarLaporan, $laporan->id);
            tsaldo::where('idLaporan', $laporan->id)->delete();
        }

        tlaporan::destroy($daftarLaporan);

        writeSaldo();
    }

    public function addDataLaporan(Request $a)
    {
        $cek = tlaporan::where([
            'tanggal' => $a->tanggal,
            'idUser' => Session::get('userLogin')->id
        ])->get();


        if (count($cek) == 0) {
            $cek = tlaporan::where('tanggal', '>', $a->tanggal)->where('idUser', Session::get('userLogin')->id)->count();

            if ($cek == 0) {
                $cek = tlaporan::where([
                    'terbit' => '0',
                    'idUser' => Session::get('userLogin')->id
                ])->count();

                if ($cek == 0) {
                    tlaporan::create([
                        'tanggal' => $a->tanggal,
                        'idUser' => Session::get('userLogin')->id,
                        'terbit' => 0
                    ]);

                    return response()->json([
                        'type' => 'y',
                        'msg' => 'Berhasil menambah laporan.'
                    ]);
                } else {
                    return response()->json([
                        'type' => 'x',
                        'msg' => 'Ada laporan yang masih belom anda terbitkan, terbitkan terlebih dahulu !'
                    ]);
                }
            } else {
                return response()->json([
                    'type' => 'x',
                    'msg' => 'Tidak bisa membuat laporan dengan tanggal yang mundur !'
                ]);
            }
        } else {
            return response()->json([
                'type' => 'x',
                'msg' => 'Laporan dengan tanggal tersebut sudah ada !'
            ]);
        }
    }

    public function prepareEditLaporan(tlaporan $a)
    {
        return $a;
    }

    public function editDataLaporan(Request $a)
    {
        $cek = tlaporan::where('tanggal', $a->tanggal)->count();

        if ($cek > 0) {
            return 'x';
        } else {
            tlaporan::where([
                'id' => $a->id,
                'idUser' => Session::get('userLogin')->id
            ])->update([
                'tanggal' => $a->tanggal
            ]);

            return 'y';
        }
    }

    public function loadDataLaporan()
    {
        $res = collect();
        // $query = DB::select('select tlaporans.*, tpoins.id as idPoin from tlaporans left join tpoins on tlaporans.id = tpoins.idLaporan where tlaporans.idUser = ' . Session::get('userLogin')->id);
        $laporan = tlaporan::where('idUser', Session::get('userLogin')->id)->orderBy('tanggal', 'desc')->get();
        foreach ($laporan as $lapo) {
            $poin = tpoin::where([
                'idUser' => Session::get('userLogin')->id,
                'idLaporan' => $lapo->id
            ])->count();

            if ($poin > 0) {
                $poin = true;
            } else {
                $poin = false;
            }

            // dd($lapo);
            $res->push([
                'id' => $lapo->id,
                'tanggal' => $lapo->tanggal,
                'idUser' => $lapo->idUser,
                'terbit' => $lapo->terbit,
                'created_at' => $lapo->created_at,
                'updated_at' => $lapo->updated_at,
                'deleted_at' => $lapo->deleted_at,
                'poin' => $poin
            ]);
        }
        return $res;
        // return tlaporan::where('idUser', Session::get('userLogin')->id)->orderBy('tanggal', 'asc')->get();
    }

    public function terbit(Request $a)
    {
        $cek = tpoin::where('idLaporan', $a->id)->get();

        if (count($cek) > 0) {
            tlaporan::where('id', $a->id)->update([
                'terbit' => $a->status
            ]);

            return $a->status;
        } else {
            return 'x';
        }
    }

    /*
    \
    \ PEMASPENGE
    \
    */

    public function loadDetailLaporan($id)
    {

        return tpoin::where([
            'idLaporan' => $id,
            'idUser' => Session::get('userLogin')->id
        ])->orderBy('jenis', 'asc')->get();
    }

    public function poinLaporan(Request $a)
    {
        return tpoin::where([
            'idUser' => Session::get('userLogin')->id,
            'idLaporan' => $a->id
        ])->select('id', 'jenis', 'nama', 'banyak', 'harga', 'jumlah')->get();
    }

    public function deletePoinLaporan(Request $a)
    {
        tpoin::destroy($a->id);

        writeSaldo();

        return tpoin::where([
            'idUser' => Session::get('userLogin')->id,
            'idLaporan' => $a->idLaporan
        ])->select('id', 'jenis', 'nama', 'banyak', 'harga', 'jumlah')->get();
    }

    public function savePoinLaporan(Request $a)
    {
        // LANJUT garap maneh
        $saldoAwal = tsaldo::where([
            'idUser' => Session::get('userLogin')->id,
            'idLaporan' => 0
        ])->first();

        $saldo = $saldoAwal->jumlah;

        foreach ($a->poin as $poin) {
            tpoin::updateOrCreate([
                'id' => $poin['id'],
                'idLaporan' => $a->idLaporan,
                'idUser' => Session::get('userLogin')->id
            ], [
                'jenis' => $poin['jenis'],
                'nama' => $poin['nama'],
                'banyak' => $poin['banyak'],
                'harga' => $poin['harga'],
                'jumlah' => $poin['jumlah'],
            ]);
        }

        $poinPoin = tpoin::where('idUser', Session::get('userLogin')->id)->get();

        $pemasukan = 0;
        $pengeluaran = 0;

        foreach ($poinPoin as $p) {
            switch ($p->jenis) {
                case '+':
                    $pemasukan = $pemasukan + $p->jumlah;
                    break;
                case '-':
                    $pengeluaran = $pengeluaran + $p->jumlah;
                    break;
            }
        }

        $pemasukan = $saldo + $pemasukan;
        $saldo = $pemasukan - $pengeluaran;

        $ambilSaldo = tsaldo::where('created_at', 'like', '%' . date('Y-m-d') . '%')->where([
            'idUser' => Session::get('userLogin')->id,
            'idLaporan' => null
        ])->get();

        if (count($ambilSaldo) > 0) {
            tsaldo::where('created_at', 'like', '%' . date('Y-m-d') . '%')->where([
                'idUser' => Session::get('userLogin')->id,
                'idLaporan' => $a->idLaporan,
            ])->update([
                'jumlah' => $saldo
            ]);
        } else {
            tsaldo::create([
                'jumlah' => $saldo,
                'idUser' => Session::get('userLogin')->id,
                'idLaporan' => $a->idLaporan
            ]);
        }
    }

    // ANGGOTA

    public function getDataAnggota()
    {
        return DataTables::of(
            tanggota::where('idUser', Session::get('userLogin')->id)
                ->join('tlevels', 'tanggotas.idLevel', '=', 'tlevels.id')
                ->select('tanggotas.nama', 'tanggotas.email', 'tlevels.nama as level', 'tanggotas.status', 'tanggotas.kode', 'tanggotas.id')
                ->orderBy('idLevel', 'asc')
                // ->orderBy('status', 'asc')
                ->withTrashed()
                ->get()
        )->make(true);
    }

    public function addDataAnggota(Request $a)
    {
        $cekEmailTUser = tuser::where('email', $a->email)->count();

        if ($cekEmailTUser > 0) {
            return 'x';
        } else {
            $cekEmailTAnggota = tanggota::where('email', $a->email)->count();

            if ($cekEmailTAnggota > 0) {
                return 'x';
            } else {
                tanggota::create([
                    'nama' => $a->nama,
                    'email' => $a->email,
                    'idLevel' => $a->level,
                    'status' => 'pending',
                    'kode' => rand(10000, 99999),
                    'idUser' => Session::get('userLogin')->id
                ]);

                return 'y';
            }
        }
    }

    public function deleteDataAnggota(Request $a)
    {
        tanggota::destroy($a->id);
    }

    public function prepareEditAnggota(tanggota $a)
    {
        return $a;
    }

    public function kodeKeamanan(Request $a)
    {
        return tanggota::where('id', $a->id)->select('nama', 'kode')->first();
    }

    public function editDataAnggota(Request $a)
    {

        if ($a->type == 'pending') {
            if ($a->change == 1) {
                $cek = tanggota::where('email', $a->email)->count();
                $cekk = tuser::where('email', $a->email)->count();

                if ($cek == 0 && $cekk == 0) {
                    tanggota::where('id', $a->id)->update([
                        'nama' => $a->nama,
                        'email' => $a->email,
                        'idLevel' => $a->level
                    ]);
                } else {
                    return 'x';
                }
            } else {
                tanggota::where('id', $a->id)->update([
                    'nama' => $a->nama,
                    'idLevel' => $a->level
                ]);
            }
        } else {
            tanggota::where('id', $a->id)->update([
                'idLevel' => $a->level
            ]);
        }
        return 'y';
    }

    public function getDataLaporanForAnggota()
    {
        $get = tlaporan::where([
            'idUser' => Session::get('userLogin')->id,
            'terbit' => 1
        ])->select('tanggal', 'id')->orderBy('tanggal', 'asc')->get();

        return response()->json($get);
    }

    public function getDetailLaporanForAnggota(tlaporan $id)
    {
        $laporan = tlaporan::where([
            'id' => $id->id,
            'idUser' => Session::get('userLogin')->id
        ])->first();

        if ($laporan == null) {
            return abort(404);
        }

        $poin = tpoin::where('idLaporan', $id->id)->get();

        $laporanSebelumnya = tlaporan::where('idUser', Session::get('userLogin')->id)->where('tanggal', '<', $laporan->tanggal)->orderBy('tanggal', 'desc')->first();

        if ($laporanSebelumnya == null) {
            $laporanSebelumnya = 'x';
            $saldoSebelumnya = tsaldo::where([
                'idUser' => Session::get('userLogin')->id,
                'idLaporan' => 0
            ])->first()->jumlah;
        } else {
            // $saldoSebelumnya = tsaldo::whereDate('created_at', $laporanSebelumnya->created_at)->where('idUser', Session::get('userLogin')->id)->orderBy('created_at', 'asc')->first()->jumlah;
            $saldoSebelumnya = tsaldo::where('idUser', Session::get('userLogin')->id)->where('idLaporan', '<', $id->id)->orderBy('idLaporan', 'desc')->first()->jumlah;
            $laporanSebelumnya = $laporanSebelumnya->tanggal;
        }

        // $saldoSaatIni = tsaldo::whereDate('created_at', $laporan->tanggal)->where('idUser', Session::get('userLogin')->id)->orderBy('created_at', 'desc')->first();
        $saldoSaatIni = tsaldo::where(['idUser' => Session::get('userLogin')->id, 'idLaporan' => $id->id])->first();

        $pemasukan = 0;
        $pengeluaran = 0;

        foreach ($poin as $p) {
            switch ($p->jenis) {
                case '+':
                    $pemasukan = $pemasukan + $p->jumlah;
                    break;
                case '-':
                    $pengeluaran = $pengeluaran + $p->jumlah;
                    break;
            }
        }

        return response()->json([$id, $poin, [$pemasukan, $pengeluaran], [$laporanSebelumnya, $saldoSebelumnya, $saldoSaatIni->jumlah]]);
    }

    public function saranBug(Request $a)
    {
        tsaran::create($a->all());
    }

    public function saranBugshow($key)
    {
        if ($key  == 'untillness') {
            return tsaran::orderBy('created_at', 'desc')->get();
        }
        return abort(404);
    }
}
