<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <link rel="shortcut icon" href="{{asset('images')}}/icon-semut-gajah.png">
    <!-- Bootstrap 4 core CSS -->
    <link href="{{asset('admin-themes')}}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('admin-themes')}}/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('')}}plugin/lightbox/css/lightbox.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}plugin/DataTables/datatables.min.css"/>
    <!-- Bootstrap core JavaScript -->
    <script src="{{asset('admin-themes/')}}/jquery/jquery.min.js"></script>
    <script src="{{asset('admin-themes/')}}/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="{{asset('admin-themes/css/cyber-themes.css')}}">

    <link rel="shortcut icon" type="image/png" href="{{asset('img/favicon.png')}}">

    <title>Semut Gajah</title>

    <link rel="shortcut icon" type="image/png" href="{{asset('img/favicon.png')}}"/>
</head>
<body>
    <div class="col-md-12 d-flex justify-content-center">
        <div class="col-md-6 mt-5 text-center">
            <h3 class="mt-5"><b>{{$topSpender->title}}</b></h3>
            <table class="table table-striped table-bordered my-5" id="table-top-spender">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Customer Code</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data_list as $row)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            @php
                                $name = explode(" ", ucwords($row['name']));
                                foreach($name as $key => $value) {
                                    $len = strlen($value);
                                    $name[$key] = substr($value, 0, 1) . str_repeat('*', (int) abs((int)$len - 1));
                                }
                                $name = implode(" ", $name);
                            @endphp
                            <td>{{$name}}</td>
                            <td>{{$row['customer_code']}}</td>
                            <td>Rp{{number_format($row['total'], 0, '.', ',')}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($data_user_index != null)
                @if ($data_user_index < $topSpender->limit)
                    <h3 class="mt-3">Anda berada di Top {{$topSpender->limit}}</h3>
                @else
                    <h3 class="mt-3">Anda berada di peringkat <b>{{$data_user_index + 1}}</b> dengan total <b>Rp{{number_format($data_user['total'], 0, ',', '.')}}</b></h3>
                @endif
            @else
                <h3 class="mt-3">Anda belum masuk dalam peringkat</h3>
            @endif
        </div>
    </div>
</body>