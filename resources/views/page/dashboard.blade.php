@extends('lay.lay')

@section('title', 'DASHBOARD')

@section('body')

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex d-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-xl-6 col-md-12 mb-2">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp. <span id="textSaldo"><i class="fas fa-sync fa-spin fa-sm"></i></span>,-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Laporan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span>{{$jumlahLaporan}}</span> Biji</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Anggota (aktif)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span>{{$jumlahOrang}}</span> Orang</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Content Row -->
</div>

<div class="row mb-3">
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Garafik Perubahan Saldo</h6>
            </div>
            <div class="card-body">
                <canvas id="myChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Garafik Perubahan Pemasukan dan Pengeluaran</h6>
            </div>
            <div class="card-body">
                <canvas id="myChart2" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
<!-- Scroll to Top Button-->


@endsection

@section('anggota')
<style>
    .bulat{
        border-radius: 50%;
        height: 100px;
        width: 100px;
        color: whitesmoke;
    }
</style>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <h3 class="text-center">{{strtoupper(Session::get('userLogin')->org)}}</h3>
            </div>
            <div class="col-12 d-flex justify-content-center">
                <h4 style="font-weight: bold">LAPORAN KEUANGAN</h4>
            </div>
        </div>

        <div class="row mt-5">
            @for ($i = 0; $i < 6; $i++)

            <div class="col-md-6 col-xs-12 col-xl-4 p-1">
                <div class="card">
                    <div class="card-body row">
                        <div class="col-md-4 col-sm-12 d-flex flex-column justify-content-center align-items-center">
                            <div class="bulat d-flex flex-column justify-content-center align-items-center bg-primary">
                                <h4 class="m-0 p-0">10</h4>
                                <p class="p-0 m-0">BULAN</p>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-12 d-sm-flex d-flex d-md-block justify-content-center align-items-center mt-3 mt-md-0">
                            Laporan
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-item-center">
                        <a>Lihat</a>
                        <span class="fas fa-angle-right d-flex justify-content-center"></span>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
@endsection
