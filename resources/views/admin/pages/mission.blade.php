@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        Mission Statistik
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <canvas id="mission-chart"></canvas>
            </div>
            <div class="col-xs-12 col-md-6">
                <canvas id="mission-complete-chart"></canvas>
            </div>
        </div>
    </div>
</section>

<a href="
    @if($account_role == 'manager')
        {{url('manager/missions/create')}}
    @endif
" class="btn btn-blue" onclick="return togglePage()">Tambah Mission</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Mission Task
    </header>

    <div class="table-responsive">
        <div class="scroll-table-outer">
            <div class="scroll-table-inner card-body">
                <table class="table default-table dataTable">
                    <thead>
                        <tr>
                            <th width="15%">Nama</th>
                            <th width="25%">Deskripsi</th>
                            <th>Reward</th>
                            {{-- <th>Status</th> --}}
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mission as $row )
                        <tr>
                            <td>{{$row->name}}</td>
                            <td>{{$row->description}}</td>
                            <td>{{$row->reward}}</td>
                            {{-- <td>{{$row->status}}</td> --}}
                            <td>{{$row->start_date}}</td>
                            <td>{{$row->end_date}}</td>
                            <td class="align-middle" width="40px">
                                        <label class="switch">
                                            <input data-id="{{$row->id}}" data-user_role={{$account_role}} class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $row->status ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                            <td class="text-center">
                                <button class="btn btn-info mt-2" onclick="detail({{$row->id}})">Statistik</button>
                                <a href=" @if($account_role == 'manager')
                                    {{url('manager/missions/edit/'.$row->id)}}
                                @endif" class="btn btn-blue mt-2">Edit</a>
                                <a href="javascript:void(0)" class="btn btn-red mt-2" onclick="hapus('{{$row->id}}')">Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- modal statistik --}}
<div id="statistikModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Statistik</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12 mb-md-4">
                        <canvas id="statistik-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function hapus(id) {
        if (confirm('Yakin Akan Menghapus Data ?')) {
            var formData = {
                _token: "{{ csrf_token() }}",
                id: id,
                url: "{{ Request::url() }}",
            };

            var id = id;

            $.ajax({
                type: 'POST',
                url: @if(auth()->user()->account_role == 'manager')
                        '/manager/missions/delete'
                        @elseif(auth()->user()->account_role == 'superadmin')
                            '/superadmin/top-spender/delete'
                        @elseif(auth()->user()->account_role == 'admin')
                            '/admin/top-spender/delete'
                        @endif,
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function(data) {
                location.reload();
                showNotif("Data Berhasil Dihapus!");
            });
        } else {
            return false;
        }
    }
    $(function(){
        $('.toggle-class').change(function(){
            var status = $(this).prop('checked') == true ? 1 : 0;
            var misi_id = $(this).data('id');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: '/manager/misi-status/' + misi_id ,
                data: {'status': status, 'id': misi_id},
                success: function(data){
                    console.log(data)
                    showNotif("Perubahan status sukses")
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
                }
            });
        })
    })

    function detail(id) {
        let partisipasi = 320;
        let complete    = 215;
        // $.ajax({
        //     type: "GET",
        //     url: @if(auth()->user()->account_role == 'manager')
        //                 "{{url('manager/missions/statistik?id=')}}"+id 
        //             @elseif(auth()->user()->account_role == 'superadmin') 
        //                 "{{url('superadmin/missions/statistik?id=')}}"+id
        //         @endif,
        //     beforeSend: function () {

        //     },
        //     success: function (response) {
        //         partisipasi = response.partisipasi;
        //         complete    = response.complete;
        //     },
        //     error: function (xhr, status, error) {
        //         setTimeout(function () {
        //             console.log(xhr.responseText)
        //         }, 2000);
        //     }
        // });

        // Statistik chart
        const statistik_labels = ['Partisipasi', 'Selesai'];
        const statistik_labels_data = [partisipasi, complete];
        const statistik_labels_color = ['rgb(255, 205, 86, 0.5)', 'rgb(255, 159, 64, 0.5)'];
        const statistik_data = {
            labels: statistik_labels,
            datasets: [
                {
                    label: 'Statistik',
                    data: statistik_labels_data,
                    borderColor: statistik_labels_color,
                    backgroundColor: statistik_labels_color,
                }
            ]
        }
        const statistik_config = {
            type: 'pie',
            data: statistik_data,
            options: {
                responsive: true,
                title: {
                    display: true,
                    position: "top",
                    text: "Statistik",
                    fontSize: 18,
                    fontColor: "#111"
                },
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                    fontColor: "#333",
                    fontSize: 16
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.round((currentValue/total) * 100)+"%";
                            return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                        }
                    }
                }
            }
        }

        var statistikChart = new Chart($("#statistik-chart"), statistik_config);
        $('#statistikModal').modal('show');
    }

    $(document).ready(function() {
        // Mission chart
        const mission_labels = ['User', 'Partisipasi', 'Selesai'];
        const mission_labels_data = [1000, 320, 215];
        const mission_labels_color = ['rgb(54, 162, 235, 0.5)', 'rgb(255, 205, 86, 0.5)', 'rgb(255, 159, 64, 0.5)'];
        const mission_data = {
            labels: mission_labels,
            datasets: [
                {
                    label: 'Mission Statistik',
                    data: mission_labels_data,
                    borderColor: mission_labels_color,
                    backgroundColor: mission_labels_color,
                }
            ]
        }
        const mission_config = {
            type: 'pie',
            data: mission_data,
            options: {
                responsive: true,
                title: {
                    display: true,
                    position: "top",
                    text: "Mission Statistik",
                    fontSize: 18,
                    fontColor: "#111"
                },
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                    fontColor: "#333",
                    fontSize: 16
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.round((currentValue/total) * 100)+"%";
                            return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                        }
                    }
                }
            }
        }

        var missionChart = new Chart($("#mission-chart"), mission_config);
        

        // Mission complete chart
        const mission_complete_labels = ['Partisipasi', 'Selesai'];
        const mission_complete_labels_data = [320, 215];
        const mission_complete_labels_color = ['rgb(255, 205, 86, 0.5)', 'rgb(255, 159, 64, 0.5)'];
        const mission_complete_data = {
            labels: mission_complete_labels,
            datasets: [
                {
                    label: 'Partisipasi Statistik',
                    data: mission_complete_labels_data,
                    borderColor: mission_complete_labels_color,
                    backgroundColor: mission_complete_labels_color,
                }
            ]
        }
        const mission_complete_config = {
            type: 'pie',
            data: mission_complete_data,
            options: {
                responsive: true,
                title: {
                    display: true,
                    position: "top",
                    text: "Partisipasi Statistik",
                    fontSize: 18,
                    fontColor: "#111"
                },
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                    fontColor: "#333",
                    fontSize: 16
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.round((currentValue/total) * 100)+"%";
                            return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                        }
                    }
                }
            }
        }

        var missionCompleteChart = new Chart($("#mission-complete-chart"), mission_complete_config);
    });
</script>
@endsection