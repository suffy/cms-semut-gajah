
    <section id="sakura-cart">
        <div class="">
            <div class="section-title">
                <h5 class="text-upper mb-3">Items on cart</h5>
            </div>
           
            @foreach($cart as $row)
            <form id="cart-{{$row->id}}" method="post" action="{{url('member/product-update-to-cart')}}">
                @csrf
                <div class="box-border">
                  
                        <div class="cart-title">{{$row->data_product->name ?? "Produk ini sudah tidak tersedia"}} <a href="javascript:void(0)" class="btn btn-danger btn-sm removeFromCart pull-right" data-id="{{$row->product_id}}"><span class="fa fa-trash"></span></a></div>
                        <div class="cart-content">Harga : Rp. {{number_format($row->price)}}</div>
                        <div class="cart-content">
                            Kuantiti <input type="number" value="{{$row->qty}}" class="form-control change-qty" data-id="{{$row->id}}" data-prod-id="{{$row->product_id}}" style="width: 70px; padding: 0px;
                            text-align: center;">
                        </div>
                        <div class="cart-content">Total : Rp. {{number_format($row->total_price)}}</div>
                       

                </div> 
            </form>
            @endforeach

            

            <div class="cart-total">
                <div class="row">
                    <div class="offset-6 col-6">
                        <div class="pull-right">
                            <a href="{{ url('checkout') }}" class="btn button-blue text-upper">proceed to checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .box-border{
            border: 1px solid #f1f1f1;
            padding: 10px;
            margin-bottom: 10px;
        }
        .cart-title{
            font-weight: 600;
            font-size: 12pt;
        }

        .cart-content{
            font-size: 10pt;
        }
    </style>

    <script>
        $('.change-qty').on('keyup change', function(){

                    var id = $(this).attr('data-id');
                    var prod_id = $(this).attr('data-prod-id');
                    var qty = $(this).val();

                        var form_data = new FormData($('#cart-'+id)[0]);
                        var url = "{{url('/member/product-update-to-cart')}}"
                        var formData = {
                            "_token": "{{ csrf_token() }}",
                            "cart_id": id, //for 
                            "product_id": prod_id, //for 
                            "qty": qty, //for 
                        };

                    var data = formData;
                        
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},

                    beforeSend: function () {
                        if (window.location.href == '{{url("cart")}}') {
                            $('#page-cart-display-loading').show();
                        }
                    },
                    
                    success: function(data){
                        console.log(data)
                        setTimeout(function () {
                            if (window.location.href == '{{url("cart")}}') {
                                $('#page-cart-display-loading').hide();
                            }
                            getShoppingCart();
                            getShoppingCartCount();
                        }, 1000);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        showAlert(thrownError);
                    }
                });
                
        })

        $('.removeFromCart').click(function(){

        var prod_id = $(this).attr('data-id');
        var formData = {
                "_token": "{{ csrf_token() }}",
                "product_id": prod_id, //for get email 
                "qty": "0", //for get email 
            };

            var data = formData;
            
        $.ajax({
        type: "POST",
        url: "{{url('member/product-remove-from-cart')}}",
        data: data,
        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},

        beforeSend: function () {

        },
        success: function (response) {

            if(response.status=="0"){
                $("#cartList").modal('show');
            }else if(response.status=="200"){
                console.log(response)
                showAlert("Cart berhasil dihapus");

                setTimeout(function () {

                    getShoppingCart();
                    getShoppingCartCount();
                    $("#cartList").modal('show');

                }, 1000);
            }

            $('.removeFromCart').click(function(){
                
            })


        },
        error: function (xhr, status, error) {
            setTimeout(function () {

                console.log(xhr.responseText)

            }, 2000);
        }
        });

        })
    </script>