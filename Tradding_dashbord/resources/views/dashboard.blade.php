@extends('layouts.user_type.auth')

@section('content')
    <div class="row d-flex justify-content-center">
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Nombre d'utilisateurs</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $userCount }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape  shadow text-center border-radius-md" style="background: #0a8897">
                                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold"> Nombre d'équipes</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $teamCount }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape  shadow text-center border-radius-md" style="background: #0a8897">
                                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Nombre des ligues</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $leagueCount }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape  shadow text-center border-radius-md" style="background: #0a8897">
                                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-5 mb-lg-0 mb-4 d-none">
            <div class=" z-index-2">
                <div class=" p-3">
                    <div class=" py-3 pe-1 mb-3">
                        <div class="">
                            <canvas id="chart-bars" class="" height="0"></canvas>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="col-lg-11 col-sm-12 mx-auto ">
            <div class="card z-index-2">
                <div class="card-header pb-0">
                    <h6>Sales overview</h6>
                    <p class="text-sm">
                        <i class="fa fa-arrow-up text-success"></i>
                        <span class="font-weight-bold">4% more</span> in 2021
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-4">

        <div class="col-lg-11 col-sm-12 mx-auto col-md-6 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Ligues</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-black text-center">Id</th>
                                    <th class=" text-black text-center">Nom de la Ligue</th>
                                    <th class="text-black text-center">Nombre d'équipes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaguedashbord as $league)
                                    <tr>
                                        <td>
                                            <div class="text-center px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $league->id }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $league->league_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" text-center flex-column justify-content-center">
                                            <span class="text-xs font-weight-bold">{{ $league->number_of_teams }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('dashboard')
        <script>
            window.onload = function() {
                var ctx = document.getElementById("chart-bars").getContext("2d");

                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "Sales",
                            tension: 0.4,
                            borderWidth: 0,
                            borderRadius: 4,
                            borderSkipped: false,
                            backgroundColor: "#fff",
                            data: [450, 200, 100, 220, 500, 100, 400, 230, 500],
                            maxBarThickness: 6
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                grid: {
                                    drawBorder: false,
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false,
                                },
                                ticks: {
                                    suggestedMin: 0,
                                    suggestedMax: 500,
                                    beginAtZero: true,
                                    padding: 15,
                                    font: {
                                        size: 14,
                                        family: "Open Sans",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                    color: "#fff"
                                },
                            },
                            x: {
                                grid: {
                                    drawBorder: false,
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false
                                },
                                ticks: {
                                    display: false
                                },
                            },
                        },
                    },
                });


                var ctx2 = document.getElementById("chart-line").getContext("2d");

                var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);

                gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
                gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
                gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors

                var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);

                gradientStroke2.addColorStop(1, 'rgba(20,23,39,0.2)');
                gradientStroke2.addColorStop(0.2, 'rgba(72,72,176,0.0)');
                gradientStroke2.addColorStop(0, 'rgba(20,23,39,0)'); //purple colors

                new Chart(ctx2, {
                    type: "line",
                    data: {
                        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                                label: "Mobile apps",
                                tension: 0.4,
                                borderWidth: 0,
                                pointRadius: 0,
                                borderColor: "#cb0c9f",
                                borderWidth: 3,
                                backgroundColor: gradientStroke1,
                                fill: true,
                                data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                                maxBarThickness: 6

                            },
                            {
                                label: "Websites",
                                tension: 0.4,
                                borderWidth: 0,
                                pointRadius: 0,
                                borderColor: "#3A416F",
                                borderWidth: 3,
                                backgroundColor: gradientStroke2,
                                fill: true,
                                data: [30, 90, 40, 140, 290, 290, 340, 230, 400],
                                maxBarThickness: 6
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                grid: {
                                    drawBorder: false,
                                    display: true,
                                    drawOnChartArea: true,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    display: true,
                                    padding: 10,
                                    color: '#b2b9bf',
                                    font: {
                                        size: 11,
                                        family: "Open Sans",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                }
                            },
                            x: {
                                grid: {
                                    drawBorder: false,
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    display: true,
                                    color: '#b2b9bf',
                                    padding: 20,
                                    font: {
                                        size: 11,
                                        family: "Open Sans",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                }
                            },
                        },
                    },
                });
            }
        </script>
    @endpush
