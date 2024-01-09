@extends('admin.layout.template')

@section('content')


<section class="panel">
    <header class="panel-heading">
        <b>Filter</b>
    </header>
    <div class="card-body">

    <table class="table table-sm default-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Rating</th>
                <th>Tanggal</th>
                <th>#</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rating as $row)
            <tr>
                <td>{{$loop->iteration-1+$rating->firstItem() }}</td>
                <td><a href="{{url('admin/order-detail/'.$row->order_id)}}" target="_blank">#{{$row->order_id}}</a></td>
                <td>{{App\User::find($row->user_id)->name ?? ""}}</td>
                <td>{{App\Product::find($row->product_id)->name ?? ""}}</td>
                <td>
                    <div class="review-block-rate">
                        <button type="button" class="btn @if($row->star_review>=1 && $row->star_review!=0) btn-warning @endif btn-xs" aria-label="Left Align">
                        <span class="fa fa-star" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn @if($row->star_review>=2 && $row->star_review!=0) btn-warning @endif btn-xs" aria-label="Left Align">
                        <span class="fa fa-star" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn @if($row->star_review>=3 && $row->star_review!=0) btn-warning @endif btn-xs" aria-label="Left Align">
                        <span class="fa fa-star" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn @if($row->star_review>=4 && $row->star_review!=0) btn-warning @endif btn-xs" aria-label="Left Align">
                        <span class="fa fa-star" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn @if($row->star_review>=5 && $row->star_review!=0) btn-warning @endif btn-xs" aria-label="Left Align">
                        <span class="fa fa-star" aria-hidden="true"></span>
                        </button>
                    </div>
                    <hr>
                    {{$row->detail_review}}
                </td>
                <td>{{date('d/m/Y H:i:s', strtotime($row->created_at))}}</td>
                <td><a href="{{url('admin/order-detail/'.$row->order_id)}}" class="btn btn-sm btn-success">Detail</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

        

            {{$rating->links()}}
        </div>

    </div>
</section>

@stop
