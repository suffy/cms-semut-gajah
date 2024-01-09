@extends('admin.layout.template')

@section('content')
    <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/product-offers')}}@else{{url('manager/product-offers')}}@endif" class="btn btn-blue"><span class="fa fa-arrow-left"></span> Kembali</a>
    <br><br>

    <section class="panel" >
        <header class="panel-heading">
            <h3>Detail Penawaran</h3>
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-4">
                  <img src="{{asset($penawaran->icon)}}" class="img-fluid" style="max-width: 400px">
                </div>  

                <div class="col-md-4 col-sm-4 col-xs-4">
                    <h4>Informasi Penawaran</h4>
                    <br>
                    <p><b>Judul</b> : {{$penawaran->title}}</p>
                    <p><b>Deskripsi</b> : {!! substr($penawaran->description, 0, 200) !!}</p>
                    <p><b>Status</b> : @php
                        if($penawaran->status==1){
                            echo "Active";
                        }else if($penawaran->status==2){
                             echo "Non Active";
                        }
                        @endphp

                    <p><b>Mulai Penawaran</b> : {{$penawaran->day_start}}</p>
                    <p><b>Berakhir</b> : {{$penawaran->day_end}}</p>
                    <br><br>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-4">
                     
                        <hr>  
                        <br><br><br>            
                </div>

         <div class="col-md-8 col-sm-12 col-xs-12">
            <h4>Item Penawaran</h4>
            <div style="border: 1px solid #f1f1f1; padding: 15px">
                <input type="text" id="search-item" placeholder="Cari Item" class="form-control">
                <br>
                
                <div id="list-item-search">

                </div>

                <div class="card" id="list-item">

                </div>
            </div>
            <br>

            </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        
        var offer_id = '{{$penawaran->id}}';
        listItem();

        $('#search-item').on('keyup', function(){
        id = $(this).val();
        searchItem(id)
        });

        function searchItem(id){
            if (id.length > 0) {
                $.ajax({
                    type: "GET",
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/ajax-search-product?keyword=')}}"+id 
                         @else 
                            "{{url('superadmin/ajax-search-product?keyword=')}}"+id  
                         @endif,
                    mimeType: "multipart/form-data",
                    beforeSend: function () {

                    },
                    success: function (data) {
                       
                    var resp = JSON.parse(data).data;

                    var html = "";
                    var name ="";
                    var views ="";
                    var sold ="";
                    var stock ="";

                    if(JSON.parse(data).status=="1"){
                        for(var i=0; i<resp.length; i++){
                            html = html+"<div class='status status-other' style='margin-bottom: 3px'> <a href='javascript:void(0)' class='btn btn-xs btn-success btn-add-to' data-id='"+resp[i].id+"'>+</a> "+resp[i].id+" - "+resp[i].name+"</div>"

                            name = resp[i].name;
                            views = resp[i].views;
                            sold = resp[i].sold;
                            stock = resp[i].stock;
                        }
                    }

                    $('#list-item-search').html(html);

                    $('.btn-add-to').on('click', function(){

                        var uid = $(this).attr('data-id');
                        
                            var formData = {
                                "_token": "{{ csrf_token() }}",
                                "product_id": uid, 
                                "views": views,
                                "sold": sold,
                                "stock": stock,
                                "description":'{{$penawaran->description}}',
                                "offer_id": offer_id,
                            };
                            storeItem(formData);
                    })
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
                });
            }else{
                $('#list-item-search').html("");
            }
        }

    function storeItem(formData){
        var data = formData;  

        $.ajax({
            type: "POST",
            url: @if(auth()->user()->account_role == 'manager')
                    "{{url('manager/offers-store-product')}}"
                @else
                    "{{url('superadmin/offers-store-product')}}"
                 @endif,
            data: data,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            
            beforeSend: function () {

            },
            success: function (response) {

                showNotif("Item Penawaran Sudah Ditambahkan");
                listItem();

            },
            error: function (xhr, status, error) {
                setTimeout(function () {

                    console.log(xhr.responseText)

                }, 2000);
            }
        });
    }

    function listItem(){
   
        $.ajax({
                type: "GET",
                url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/offers-product-item')}}"+"/"+offer_id
                     @else
                        "{{url('superadmin/offers-product-item')}}"+"/"+offer_id
                     @endif,
                mimeType: "multipart/form-data",
                beforeSend: function () {

                },
                success: function (response) {
                    var resp = JSON.parse(response).data;
                    var html = "";

                    if(JSON.parse(response).status=="1"){
                        for(var i=0; i<resp.length; i++){
                            html = html+"<div class='status status-info' style='margin-bottom: 3px'> <a href='javascript:void(0)' class='btn btn-xs btn-danger btn-remove-to' data-id='"+resp[i].id+"'>x</a> "+resp[i].product.name+"</div>"
                        }
                    }

                    $('#list-item').html(html);

                    $('.btn-remove-to').on('click', function(){
                        var uid = $(this).attr('data-id');
                        var formData = {
                            "_token": "{{ csrf_token() }}",
                            "id": uid
                        };

                        removeOffersItem(formData);
                    })
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });
    }

    function removeOffersItem(formData){

        var data = formData;

        $.ajax({
                type: "POST",
                url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/offers-item-remove')}}"
                     @else
                        "{{url('superadmin/offers-item-remove')}}"
                     @endif,
                data: data,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                
                beforeSend: function () {
                },
                success: function (response) {

                    console.log(response);
                    showAlert("Item Penawaran Dihapus");
                    listItem();

                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });
        }
    </script>
@stop