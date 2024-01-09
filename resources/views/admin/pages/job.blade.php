@extends('admin.layout.template')

@section('content')
    <section class="panel">
        <div class="card-body">
            <div class="heading-section">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Jobs</h4>
                    </div>
                    <div class="col-md-8">
                        <div class="panel-menu-content">
                            {{-- <form class="custom-form-search">
                                <input class="form-control" name="search" placeholder="search...">
                                <button type="submit" class="btn"><span class="fa fa-search"></span></button>
                            </form> --}}

                        </div>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <div class="row mt-5" id="print">
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Subscribe : 
                        <button class="btn btn-primary float-right" id="subscribe-btn">Run Job</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Site : 
                        <button class="btn btn-primary float-right" id="master-site-btn">Run Job</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Salesman : 
                        <button class="btn btn-primary float-right" id="master-salesman-btn">Run Job</button>
                    </div>
                </div>
            </div>

            {{-- <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Custom Customer : 
                        <input id="master-customer-input" placeholder="Kode"></input>
                        <button class="btn btn-primary float-right" id="master-custom-btn">Run Job</button>
                    </div>
                </div>
            </div> --}}

            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Remind Update Apps : 
                        <input id="version-input" placeholder="Versi"></input>
                        <button class="btn btn-primary float-right" id="remind-update-btn">Run Job</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Product Site
                        <button class="btn btn-primary float-right" id="master-product-site-btn">Run Job</button>
                    </div>
                </div>
            </div>
            {{-- <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Customer Binaan
                        <button class="btn btn-primary float-right" id="master-customer-binaan-btn">Run Job</button>
                    </div>
                </div>
            </div> --}}
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Product
                        <button class="btn btn-primary float-right" id="master-product-btn">Run Job</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Master Stock : 
                        <button class="btn btn-primary float-right" id="master-stock-btn">Run Job</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Remind Checkout
                        <button class="btn btn-primary float-right" id="master-remind-checkout-btn">Run Job</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Fix Empty Image
                        <button class="btn btn-primary float-right" id="fix-image">Run Job</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Custom Convert Json : 
                        <input id="custom-convert-input" placeholder="Site Code"></input>
                        <button class="btn btn-primary float-right" id="master-convert-custom-btn">Run Job</button>
                    </div>
                    <div class="card-body">
                        Convert Json : 
                        <button class="btn btn-primary float-right" id="master-convert-btn">Run Job</button>
                    </div>
                    <div class="card-body">
                        Master Daily Customer : 
                        <button class="btn btn-primary float-right" id="master-daily-btn">Run Job</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Register approval
                        <button class="btn btn-primary float-right" id="master-approval">Run Job</button>
                    </div>
                </div>
            </div>

            {{-- <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Send Notification if verified : 
                        <button class="btn btn-primary float-right" id="notification-verification-btn">Run Job</button>
                    </div>
                </div>
            </div> --}}

            {{-- <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        Complete Complaint : 
                        <button class="btn btn-primary float-right" id="complete-complaint-btn">Run Job</button>
                    </div>
                </div>
            </div> --}}

            {{-- <div class="col-md-4">
                <div class="box-border">
                    <div class="card-body">
                        COD : 
                        <button class="btn btn-primary float-right" id="cod-btn">Run Job</button>
                    </div>
                </div>
            </div> --}}
        </div>
    </section>

    <script>
        $("#subscribe-btn").click(function(){
            $('#subscribe-btn').html('running ...')
            $.get("{{ url('subscribe-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#subscribe-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#subscribe-btn').html('Run Job')
                }
            })
        })

        $("#master-site-btn").click(function(){
            $('#master-site-btn').html('running ...')
            $.get("{{ url('site-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-site-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-site-btn').html('Run Job')
                }
            })
        })

        $("#master-salesman-btn").click(function(){
            $('#master-salesman-btn').html('running ...')
            $.get("{{ url('salesman-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-salesman-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-salsesman-btn').html('Run Job')
                }
            })
        })

        $("#master-custom-btn").click(function(){
            if($("#master-customer-input").val()!==""){
                $('#master-custom-btn').html('running ...')
                $.get("{{ url('custom-customer') }}/" + $("#master-customer-input").val(), function(response){
                    if (response.status == 'success') {
                        alert('running job successfully')
                        $('#master-custom-btn').html('Run Job')
                    } else {
                        alert('running job failed')
                        $('#master-custom-btn').html('Run Job')
                    }
                })
            }else{
                alert('code must be filled')
            }
        })

        $("#remind-update-btn").click(function(){
            if($("#version-input").val()!==""){
                $('#remind-update-btn').html('running ...')
                $.get("{{ url('reminder-update') }}/" + $("#version-input").val(), function(response){
                    if (response.status == 'success') {
                        alert('running job successfully')
                        $('#remind-update-btn').html('Run Job')
                    } else {
                        alert('running job failed')
                        $('#remind-update-btn').html('Run Job')
                    }
                })
            }else{
                alert('code must be filled')
            }
        })

        $("#master-daily-btn").click(function(){
            $('#master-daily-btn').html('running ...')
            $.get("{{ url('customer-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-daily-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-daily-btn').html('Run Job')
                }
            })
        })

        $("#master-convert-btn").click(function(){
            $('#master-convert-btn').html('running ...')
            $.get("{{ url('convert-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-convert-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-convert-btn').html('Run Job')
                }
            })
        })

        $("#master-convert-custom-btn").click(function(){
            $('#master-convert-custom-btn').html('running ...')
            $.get("{{ url('custom-convert')}}/" + $("#custom-convert-input").val(), function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-convert-custom-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-convert-custom-btn').html('Run Job')
                }
            })
        })

        $("#master-remind-checkout-btn").click(function(){
            $('#master-remind-checkout-btn').html('running ...')
            $.get("{{ url('remind-checkout')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-remind-checkout-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-remind-checkout-btn').html('Run Job')
                }
            })
        })

        $("#fix-image").click(function(){
            $('#fix-image').html('running ...')
            $.get("{{ url('image-check')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#fix-image').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#fix-image').html('Run Job')
                }
            })
        })
        
        $("#master-approval").click(function(){
            $('#master-approval').html('running ...')
            $.get("{{ url('master-approval')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-approval').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-approval').html('Run Job')
                }
            })
        })

        // $("#notification-verification-btn").click(function(){
        //     $('#notification-verification-btn').html('running ...')
        //     $.get("{{ url('notification-verification')}}/", function(response){
        //         if (response.status == 'success') {
        //             alert('running job successfully')
        //             $('#notification-verification-btn').html('Run Job')
        //         } else {
        //             alert('running job failed')
        //             $('#notification-verification-btn').html('Run Job')
        //         }
        //     })
        // })
        
        // $("#master-customer-binaan-btn").click(function(){
        //     $('#master-customer-binaan-btn').html('running ...')
        //     $.get("{{ url('customer-binaan-daily')}}/", function(response){
        //         if (response.status == 'success') {
        //             alert('running job successfully')
        //             $('#master-customer-binaan-btn').html('Run Job')
        //         } else {
        //             alert('running job failed')
        //             $('#master-customer-btn').html('Run Job')
        //         }
        //     })
        // })

        $("#master-product-btn").click(function(){
            $('#master-product-btn').html('running ...')
            $.get("{{ url('product-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-product-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-product-btn').html('Run Job')
                }
            })
        })

        $("#master-product-site-btn").click(function(){
            $('#master-product-site-btn').html('running ...')
            $.get("{{ url('product-site-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-product-site-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-product-site-btn').html('Run Job')
                }
            })
        })

        $("#master-stock-btn").click(function(){
            $('#master-stock-btn').html('running ...')
            $.get("{{ url('stock-daily')}}/", function(response){
                if (response.status == 'success') {
                    alert('running job successfully')
                    $('#master-stock-btn').html('Run Job')
                } else {
                    alert('running job failed')
                    $('#master-stock-btn').html('Run Job')
                }
            })
        })

        // $("#complete-complaint-btn").click(function(){
        //     $('#complete-complaint-btn').html('running ...')
        //     $.get("{{ url('complete-complaint-daily')}}/", function(response){
        //         if (response.status == 'success') {
        //             alert('running job successfully')
        //             $('#complete-complaint-btn').html('Run Job')
        //         } else {
        //             alert('running job failed')
        //             $('#complete-complaint-btn').html('Run Job')
        //         }
        //     })
        // })

        // $("#cod-btn").click(function(){
        //     $('#cod-btn').html('running ...')
        //     $.get("{{ url('cod-daily')}}/", function(response){
        //         if (response.status == 'success') {
        //             alert('running job successfully')
        //             $('#cod-btn').html('Run Job')
        //         } else {
        //             alert('running job failed')
        //             $('#cod-btn').html('Run Job')
        //         }
        //     })
        // })
    </script>
@endsection
