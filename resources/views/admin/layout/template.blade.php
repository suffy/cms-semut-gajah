<!doctype html>

<html>

<head>


    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <link rel="shortcut icon" href="{{asset('images')}}/icon-semut-gajah.png">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600" rel="stylesheet">

     <!-- Bootstrap 4 core CSS -->
     <link href="{{asset('admin-themes')}}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
     <link href="{{asset('admin-themes')}}/css/font-awesome.min.css" rel="stylesheet">
     <link href="{{asset('admin-themes')}}/bootstrap/css/bootstrap-datepicker.min.css" rel="stylesheet">

     <link rel="stylesheet" href="{{asset('')}}plugin/lightbox/css/lightbox.css">
     <link rel="stylesheet" type="text/css" href="{{asset('')}}plugin/DataTables/datatables.min.css"/>

     <!-- Custom styles for this template -->
     <link rel="stylesheet" type="text/css" href="{{asset('admin-themes/css/cyber-themes.css')}}">

    <link rel="shortcut icon" type="image/png" href="{{asset('img/favicon.png')}}">

    <link href="{{asset('assets')}}/css/select2/select2.min.css" rel="stylesheet">
   
    <!-- Bootstrap core JavaScript -->
    <script src="{{asset('admin-themes/')}}/jquery/jquery.min.js"></script>
    <script src="{{asset('admin-themes/')}}/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('plugin/Chartjs/chart.min.js')}}"></script>
    <script src="{{asset('plugin/Chartjs/utils.js')}}"></script>
    {{-- <script src="{{asset('firebase-messaging-sw.js')}}"></script> --}}
    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>

    <!-- TODO: Add SDKs for Firebase products that you want to use
        https://firebase.google.com/docs/web/setup#available-libraries -->
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-analytics.js"></script>

    <script src="https://www.gstatic.com/firebasejs/7.23.0/firebase-messaging.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>

    <script>
        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        var firebaseConfig = {
        apiKey: "AIzaSyAClo3NSMegblWg5Ule6Ly3n_abwbAeT6k",
        authDomain: "antangin-mpm.firebaseapp.com",
        projectId: "antangin-mpm",
        storageBucket: "antangin-mpm.appspot.com",
        messagingSenderId: "796841059848",
        appId: "1:796841059848:web:7fcff42be82f328d6313bd",
        measurementId: "G-4PJD9R8VTC"
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        firebase.analytics();
    </script>

    <title>Semut Gajah</title>

    <link rel="shortcut icon" type="image/png" href="{{asset('img/favicon.png')}}"/>


    <script>
        //Format ke ISO Standard
        function formatDateToISO(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        // Indonesia Format
        function formatDate(d) {

            var date = new Date(d);

            if (isNaN(date.getTime())) {
                return d;
            } else {

                var weekday = new Array(7);
                weekday[0] = "Minggu";
                weekday[1] = "Senin";
                weekday[2] = "Selasa";
                weekday[3] = "Rabu";
                weekday[4] = "Kamis";
                weekday[5] = "Jumat";
                weekday[6] = "Sabtu";

                var month = new Array();
                month[0] = "Januari";
                month[1] = "Februari";
                month[2] = "Maret";
                month[3] = "April";
                month[4] = "Mei";
                month[5] = "Juni";
                month[6] = "Juli";
                month[7] = "Agustus";
                month[8] = "September";
                month[9] = "October";
                month[10] = "November";
                month[11] = "Desember";

                day = date.getDate();

                if (day < 10) {
                    day = "0" + day;
                }

                var hour;
                var minutes;
                var second;

                if (date.getHours() == 0) {
                    hour = ""
                } else {
                    hour = " | " + date.getHours() + ":";
                }

                if (date.getMinutes() == 0) {
                    minutes = ""
                } else {
                    minutes = date.getMinutes() + ":";
                }

                if (date.getSeconds() == 0) {
                    second = ""
                } else {
                    second = date.getSeconds();
                }

                // return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;
                return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear();

            }

        }

        function formatDateTime(d) {

            var date = new Date(d);

            if (isNaN(date.getTime())) {
                return d;
            } else {

                var weekday = new Array(7);
                weekday[0] = "Minggu";
                weekday[1] = "Senin";
                weekday[2] = "Selasa";
                weekday[3] = "Rabu";
                weekday[4] = "Kamis";
                weekday[5] = "Jumat";
                weekday[6] = "Sabtu";

                var month = new Array();
                month[0] = "Januari";
                month[1] = "Februari";
                month[2] = "Maret";
                month[3] = "April";
                month[4] = "Mei";
                month[5] = "Juni";
                month[6] = "Juli";
                month[7] = "Agustus";
                month[8] = "September";
                month[9] = "October";
                month[10] = "November";
                month[11] = "Desember";

                day = date.getDate();

                if (day < 10) {
                    day = "0" + day;
                }

                var hour;
                var minutes;
                var second;

                if (date.getHours() == 0) {
                    hour = ""
                } else {
                    hour = " | " + date.getHours() + ":";
                }

                if (date.getMinutes() == 0) {
                    minutes = ""
                } else {
                    minutes = date.getMinutes() + ":";
                }

                if (date.getSeconds() == 0) {
                    second = ""
                } else {
                    second = date.getSeconds();
                }

                return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;

            }

        }

        function nominalToCurrency(number)
        {
            number = number.toFixed(2) + '';
            x = number.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            return x1 + x2;
        }
    </script>

        <style>
            table th,
            table td{
                font-size: 0.9em;
            }
        </style>
</head>

<body>

<style>
        #topbar-notification {
            display: none;
            z-index: 99999;
            background: #ffffff;
            color: #05ab08;
            width: 300px;
            margin: auto;
            margin-top: 10%;
            overflow: auto;
            position: fixed;
            left: 0; right: 0;
            box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
            border-top: 5px solid #05ab08;
            text-align: center;
            border-radius: 30px;
            padding: 30px;
            animation-name: fadeIn;
        }

        #topbar-notification i{
            font-size: 44pt;
            margin-bottom: 15px;
        }

        #alert-notification {
            display: none;
            z-index: 99999;
            background: #ffffff;
            color: #e3342f;
            width: 300px;
            margin: auto;
            margin-top: 10%;
            overflow: auto;
            position: fixed;
            left: 0; right: 0;
            box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
            border-top: 5px solid #e3342f;
            text-align: center;
            border-radius: 30px;
            padding: 30px;
            animation-name: fadeIn;
        }

        #alert-notification i{
            font-size: 44pt;
            margin-bottom: 15px;
        }
        
    </style>

<div id="topbar-notification">

    <div class="container">
        <i class="fa fa-check-circle-o"></i>
        <div id="text-notif">
            My awesome top bar
        </div>
    </div>

</div>

<div id="alert-notification">

    <div class="container">
    <i class="fa fa-times-circle-o"></i>
        <div id="alert-notif">
            My awesome top bar
        </div>
    </div>

</div>



    <script type="text/javascript" src="{{asset('admin-themes/js/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin-themes/js/daterangepicker.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin-themes/js/selectize.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin-themes/bootstrap/js/bootstrap-datepicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('plugin/tinymce/tinymce.min.js')}}"></script>
    
    <script type="text/javascript" src="{{asset('assets/js/select2/select2.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('')}}plugin/lightbox/js/lightbox.js"></script>
    <script type="text/javascript" src="{{asset('')}}plugin/DataTables/datatables.min.js"></script>
  
    @yield('script')

<script>
    function showNotif(text) {

        $('#text-notif').html(text);
        $('#topbar-notification').fadeIn();

        setTimeout(function () {
            $('#topbar-notification').fadeOut();
        }, 2000)
    }

    function showAlert(text) {

        $('#alert-notif').html(text);
        $('#alert-notification').fadeIn();

        setTimeout(function () {
            $('#alert-notification').fadeOut();
        }, 2000)
    }
</script>


<div class="d-flex" id="wrapper">

    <!-- Sidebar -->
    @include('admin.includes.sidebar')
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">

      @include('admin.includes.header')

      <div class="container-fluid">
        @yield('content')
      </div>
    </div>
    <!-- /#page-content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
  </script>

<script>

    tinymce.init({
        selector: "textarea.editor",
        theme: "modern",
        plugins: [
        "advlist autolink link image lists charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons paste textcolor code"
        ],
        toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        toolbar2: "| link unlink anchor | image media | forecolor backcolor  | print preview code | fontsizeselect",
        image_advtab: true,
        fontsize_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 18pt',
        content_style: "div, p { font-size: 14px; }",
        height: "200",
        relative_urls: false,
        remove_script_host: false,
    });

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });

    $('.dataTable').DataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": false,
        "bAutoWidth": false,
        "searching": false,
        "order": [[ 0, "asc" ]],
    });
    
</script>

<script>
    $("img").on("error", function () {
        $(this).attr("src", "{{asset('/')}}no-images.png");
    });
</script>

<script>
    function formatAmountNoDecimals( number ) {
      var rgx = /(\d+)(\d{3})/;
      while( rgx.test( number ) ) {
          number = number.replace( rgx, '$1' + '.' + '$2' );
      }
      return number;
  }

  function formatAmount( number ) {

      // remove all the characters except the numeric values
      number = number.replace( /[^0-9]/g, '' );
      number.substring( number.length - 2, number.length );

      // set the precision
      number = new Number( number );
      number = number.toFixed( 2 );    // only works with the "."

      // change the splitter to ","
      number = number.replace( /\./g, ',' );

      // format the amount
      x = number.split( ',' );
      x1 = x[0];
      x2 = x.length > 1 ? ',' + x[1] : '';

      return formatAmountNoDecimals( x1 );
  }


  $(function() {

      $( '.input-amount' ).keyup( function() {
          $( this ).val( formatAmount( $( this ).val() ) );
      });

      $('.file').bind('change', function() {
            //this.files[0].size gets the size of your file.
            if(this.files[0].size>2000000){
                alert("File size is too big, please select another file (max 2MB)");
                $(this).val('');
            }
        });

  });
</script>

@if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
@endif

@if(!empty(Session::get('status')) && Session::get('status') == 2)
    <script>
        showAlert("{{Session::get('message')}}");
    </script>
@endif


</body>

</html>
