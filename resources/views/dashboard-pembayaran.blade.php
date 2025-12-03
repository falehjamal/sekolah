@extends('layouts.app')

@section('title', 'Dashboard Pembayaran | ' . config('app.name', 'Sekolah'))

@section('content')
    <div class="row gy-4">
        <!-- Total Tagihan -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-label-primary p-3 rounded-circle">
                                <i class="bx bx-receipt text-primary fs-5"></i>
                            </span>
                            <div class="text-start">
                                <span class="d-block text-muted">Total Tagihan</span>
                                <h3 class="card-title text-nowrap mb-0">
                                    {{ number_format($stats['total_tagihan']['jumlah'] ?? 0, 0, ',', '.') }} tagihan
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 fw-bold">Rp {{ number_format($stats['total_tagihan']['nominal'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPP Lunas -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-label-success p-3 rounded-circle">
                                <i class="bx bx-check-circle text-success fs-5"></i>
                            </span>
                            <div class="text-start">
                                <span class="d-block text-muted">SPP Lunas</span>
                                <h3 class="card-title text-nowrap mb-0">
                                    {{ number_format($stats['spp_lunas']['jumlah'] ?? 0, 0, ',', '.') }} tagihan
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 fw-bold text-success">Rp {{ number_format($stats['spp_lunas']['nominal'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPP Belum Lunas -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-label-warning p-3 rounded-circle">
                                <i class="bx bx-time-five text-warning fs-5"></i>
                            </span>
                            <div class="text-start">
                                <span class="d-block text-muted">SPP Belum Lunas</span>
                                <h3 class="card-title text-nowrap mb-0">
                                    {{ number_format($stats['spp_belum_lunas']['jumlah'] ?? 0, 0, ',', '.') }} tagihan
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 fw-bold text-warning">Rp {{ number_format($stats['spp_belum_lunas']['nominal'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Deposit -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-label-info p-3 rounded-circle">
                                <i class="bx bx-wallet text-info fs-5"></i>
                            </span>
                            <div class="text-start">
                                <span class="d-block text-muted">Total Deposit</span>
                                <h3 class="card-title text-nowrap mb-0">Deposit</h3>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 fw-bold text-info">Rp {{ number_format($stats['total_deposit'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pemasukan & Pengeluaran -->
        <div class="col-12">
            <div class="row g-4">
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-success p-3 rounded-circle">
                                        <i class="bx bx-trending-up text-success fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Pemasukan SPP Bulan Ini</span>
                                    </div>
                                </div>
                            </div>
                            <h3 class="card-title text-nowrap mb-0 fw-bold text-success">
                                Rp {{ number_format($stats['pemasukan_spp_bulan_ini'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-success p-3 rounded-circle">
                                        <i class="bx bx-calendar-check text-success fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Pemasukan SPP Hari Ini</span>
                                    </div>
                                </div>
                            </div>
                            <h3 class="card-title text-nowrap mb-0 fw-bold text-success">
                                Rp {{ number_format($stats['pemasukan_spp_hari_ini'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-danger p-3 rounded-circle">
                                        <i class="bx bx-trending-down text-danger fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Pengeluaran Bulan Ini</span>
                                    </div>
                                </div>
                            </div>
                            <h3 class="card-title text-nowrap mb-0 fw-bold text-danger">
                                Rp {{ number_format($stats['pengeluaran_bulan_ini'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-label-primary p-3 rounded-circle">
                                        <i class="bx bx-money text-primary fs-5"></i>
                                    </span>
                                    <div class="text-start">
                                        <span class="d-block text-muted">Balance Bulan Ini</span>
                                    </div>
                                </div>
                            </div>
                            <h3 class="card-title text-nowrap mb-0 fw-bold text-primary">
                                Rp {{ number_format($stats['balance_bulan_ini'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Pemasukan Setahun -->
        <div class="col-12">
            <div class="card">
                <div class="row row-bordered g-0">
                    <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Pemasukan SPP Setahun</h5>
                        <div id="pemasukanChart" class="px-2"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                        type="button"
                                        id="tahunReportId"
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                    >
                                        {{ date('Y') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="tahunReportId">
                                        <a class="dropdown-item" href="javascript:void(0);">{{ date('Y') - 1 }}</a>
                                        <a class="dropdown-item" href="javascript:void(0);">{{ date('Y') - 2 }}</a>
                                        <a class="dropdown-item" href="javascript:void(0);">{{ date('Y') - 3 }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="growthChart"></div>
                        <div class="text-center fw-semibold pt-3 mb-2">Pertumbuhan</div>
                        <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
                            <div class="d-flex">
                                <div class="me-2">
                                    <span class="badge bg-label-primary p-2"><i class="bx bx-dollar text-primary"></i></span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>{{ date('Y') }}</small>
                                    <h6 class="mb-0">Rp {{ number_format(array_sum($chartData['data']), 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
'use strict';

(function () {
    let cardColor, headingColor, axisColor, shadeColor, borderColor;

    cardColor = config.colors.white;
    headingColor = config.colors.headingColor;
    axisColor = config.colors.axisColor;
    borderColor = config.colors.borderColor;

    // Pemasukan SPP Chart - Bar Chart
    // --------------------------------------------------------------------
    const pemasukanChartEl = document.querySelector('#pemasukanChart');

    if (pemasukanChartEl) {
        const pemasukanChartOptions = {
            series: [
                {
                    name: 'Pemasukan SPP',
                    data: @json($chartData['data'])
                }
            ],
            chart: {
                height: 300,
                type: 'bar',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 12,
                    startingShape: 'rounded',
                    endingShape: 'rounded'
                }
            },
            colors: [config.colors.primary],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 6,
                lineCap: 'round',
                colors: [cardColor]
            },
            legend: {
                show: true,
                horizontalAlign: 'left',
                position: 'top',
                markers: {
                    height: 8,
                    width: 8,
                    radius: 12,
                    offsetX: -3
                },
                labels: {
                    colors: axisColor
                },
                itemMargin: {
                    horizontal: 10
                }
            },
            grid: {
                borderColor: borderColor,
                padding: {
                    top: 0,
                    bottom: -8,
                    left: 20,
                    right: 20
                }
            },
            xaxis: {
                categories: @json($chartData['categories']),
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: axisColor
                    }
                },
                axisTicks: {
                    show: false
                },
                axisBorder: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        colors: axisColor
                    },
                    formatter: function (val) {
                        return 'Rp ' + (val / 1000000).toFixed(0) + 'Jt';
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 1700,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '32%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 1580,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '35%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 1440,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '42%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 1300,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '48%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 1200,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '40%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 1040,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 11,
                                columnWidth: '48%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 991,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '30%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 840,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '35%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 768,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '28%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 640,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '32%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 480,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 10,
                                columnWidth: '50%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 420,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 8,
                                columnWidth: '55%'
                            }
                        }
                    }
                },
                {
                    breakpoint: 380,
                    options: {
                        plotOptions: {
                            bar: {
                                borderRadius: 8,
                                columnWidth: '60%'
                            }
                        }
                    }
                }
            ],
            states: {
                hover: {
                    filter: {
                        type: 'none'
                    }
                },
                active: {
                    filter: {
                        type: 'none'
                    }
                }
            }
        };

        const pemasukanChart = new ApexCharts(pemasukanChartEl, pemasukanChartOptions);
        pemasukanChart.render();
    }

    // Growth Chart - Radial Chart
    // --------------------------------------------------------------------
    const growthChartEl = document.querySelector('#growthChart');

    if (growthChartEl) {
        const growthChartOptions = {
            series: [62],
            labels: ['Growth'],
            chart: {
                height: 240,
                type: 'radialBar'
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '60%'
                    },
                    track: {
                        background: borderColor,
                        strokeWidth: '100%'
                    },
                    dataLabels: {
                        name: {
                            offsetY: -10,
                            color: headingColor,
                            fontSize: '13px'
                        },
                        value: {
                            color: headingColor,
                            fontSize: '30px',
                            fontWeight: 600,
                            offsetY: 16
                        }
                    }
                }
            },
            colors: [config.colors.primary],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.5,
                    gradientToColors: [config.colors.primary],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 0.6,
                    stops: [30, 70, 100]
                }
            },
            stroke: {
                dashArray: 5
            },
            grid: {
                padding: {
                    top: -35,
                    bottom: -10
                }
            },
            states: {
                hover: {
                    filter: {
                        type: 'none'
                    }
                },
                active: {
                    filter: {
                        type: 'none'
                    }
                }
            }
        };

        const growthChart = new ApexCharts(growthChartEl, growthChartOptions);
        growthChart.render();
    }
})();
</script>
@endpush

