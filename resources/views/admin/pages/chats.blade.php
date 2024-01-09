@extends('admin.layout.template')

@section('content')


<section class="panel">
    <header class="panel-heading">
        <b>Chat</b>
    </header>
    <div class="card-body">
        <main class="content">
            <div class="container p-0">
        
                <h1 class="h3 mb-3">Messages</h1>
        
                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-lg-5 col-xl-3 border-right chat-box">
        
                            <div class="px-4 d-none d-md-block">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        {{-- <input type="text" class="form-control my-3" placeholder="Search..."> --}}
                                    </div>
                                </div>
                            </div>
        
                            <div id="list-chat">
                                @foreach ($lists as $list)
                                    @php
                                        $unread = DB::table('chats')->where('chat_id', $list['chat_id'])->where('status', null)->where('to_id', auth()->id())->count();
                                    @endphp
    
                                    <div class="chat-list">
                                        <form id="form-message" action="#" method="POST">
                                            @csrf
                                            <meta name="csrf-token" content="{{ csrf_token() }}">
                                            <input type="hidden" name="chat_id" value="{{ $list['chat_id'] }}">
                                            <input type="hidden" class="input-name" name="name" value="{{ $list['name'] }}">
                                            <a 
                                                href="#" 
                                                class="list-group-item list-group-item-action border-0 list-message"
                                                data-chat-id="{{ $list['chat_id'] }}"
                                                data-name="{{ $list['name'] }}"
                                            >
                                                @if ($unread != 0)
                                                    <div class="badge bg-success float-right unread"></div>
                                                @endif
                                                <div class="d-flex align-items-start">
                                                    <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Vanessa Tucker" width="40" height="40">
                                                    <div class="flex-grow-1 ml-3">
                                                        {{ $list['name'] }}
                                                    </div>
                                                </div>
                                            </a>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
        
                            <hr class="d-block d-lg-none mt-1 mb-0">
                        </div>

                        <div class="col-12 col-lg-7 col-xl-9 start">
                        </div>

                        <div class="col-12 col-lg-7 col-xl-9 show-chat">
                            <div class="py-2 px-4 border-bottom d-none d-lg-block">
                                <div class="d-flex align-items-center py-1">
                                    <div class="position-relative">
                                        <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
                                    </div>
                                    <div class="flex-grow-1 pl-3">
                                        <strong id="chat-name"></strong>
                                        {{-- <div class="text-muted small"><em>Typing...</em></div> --}}
                                    </div>
                                </div>
                            </div>
        
                            <div class="position-relative">
                                <div class="chat-messages p-4" id="list-message">
        
                                    {{-- <div class="chat-message-right pb-4">
                                        <div>
                                            <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                                            <div class="text-muted small text-nowrap mt-2">2:33 am</div>
                                        </div>
                                        <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                            <div class="font-weight-bold mb-1">You</div>
                                            Lorem ipsum dolor sit amet, vis erat denique in, dicunt prodesset te vix.
                                        </div>
                                    </div>
        
                                    <div class="chat-message-left pb-4">
                                        <div>
                                            <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
                                            <div class="text-muted small text-nowrap mt-2">2:34 am</div>
                                        </div>
                                        <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                            <div class="font-weight-bold mb-1">Sharon Lessman</div>
                                            Sit meis deleniti eu, pri vidit meliore docendi ut, an eum erat animal commodo.
                                        </div>
                                    </div> --}}
                                    
                                </div>
                            </div>
        
                            @php
                                $userCheck = DB::table('users')->where('id', auth()->id())->first();
                            @endphp

                            @if ($userCheck->account_role == 'manager' || $userCheck->account_role == 'superadmin' || $userCheck->account_role == 'admin' || $userCheck->account_role == 'distributor')
                                <div class="flex-grow-0 py-3 px-4 border-top">
                                    <form id="send-message-form-admin" action="" method="POST">
                                        @csrf
                                        <meta name="csrf-token" content="{{ csrf_token() }}">
                                        <div class="input-group">
                                            <input type="text" name="message" class="form-control input-message" placeholder="Type your message">
                                            {{-- <input type="hidden" id="user_id" name="user_id"> --}}
                                            {{-- <button type="button" class="btn btn-primary">Send</button> --}}
                                        </div>
                                    </form>
                                </div>
                            @else 
                                <div class="flex-grow-0 py-3 px-4 border-top">
                                    <form id="send-message-form-member" action="" method="POST">
                                        @csrf
                                        <meta name="csrf-token" content="{{ csrf_token() }}">
                                        <div class="input-group">
                                            <input type="text" name="message" class="form-control input-message" placeholder="Type your message">
                                            {{-- <button type="button" class="btn btn-primary">Send</button> --}}
                                        </div>
                                    </form>
                                </div>
                            @endif
        
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</section>

<script>
    $( document ).ready(function() {
        if (!localStorage.getItem('chatId')) {
            $('.start').show()
            $('.show-chat').hide()
        } else {
            $('.start').hide()
            $('.show-chat').show()
        }
        $('.chat-messages').scrollTop($('.chat-messages').height());

        // showChat(localStorage.getItem('name'))
        showMessage(localStorage.getItem('chatId'), localStorage.getItem('name'))
    });

    $(document).on('click', '.list-message', function() {
        console.log('klik')
        $('.start').hide()
        $('.show-chat').show()

        var chatId = $(this).attr('data-chat-id')
        var name = $(this).attr('data-name')

        localStorage.setItem('chatId', chatId)
        localStorage.setItem('name', name)

        showMessage(chatId, name)
    })

    function showChat(name) {
        $.ajax({
            type:"GET",
            url: "{{url('admin/chats/list')}}",
            data: null,
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(response)
            {
                setTimeout( function() {
                    $('.chat-list').remove()
                    if(response.status == 1){
                        var chats = ""

                        $.each( response.data, function( key, value ) {
                        console.log('value', value)
                            chats += `
                                <div class="chat-list">
                                    <form id="form-message" action="#" method="POST">
                                        @csrf
                                        <meta name="csrf-token" content="{{ csrf_token() }}">
                                        <input type="hidden" name="chat_id" value="${ value.chat_id }">
                                        <input type="hidden" class="input-name" name="name" value="${ value.name }">
                                        <a 
                                            href="#" 
                                            class="list-group-item list-group-item-action border-0 list-message"
                                            data-chat-id="${ value.chat_id }"
                                            data-name="${ value.name }"
                                        >
                                            ${ value.status == null ? `<div class="badge bg-success float-right unread"> </div>` : `<div></div>`}
                                            <div class="d-flex align-items-start">
                                                <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Vanessa Tucker" width="40" height="40">
                                                <div class="flex-grow-1 ml-3">
                                                    ${ value.name }
                                                </div>
                                            </div>
                                        </a>
                                    </form>
                                </div>
                            `;
                        })

                        $('#chat-name').html(name)
                        $('#list-chat').append(chats)
                    }
                }, 1000);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    }

    function showMessage(chatId, name){
        $.ajax({
            type:"POST",
            url: "{{url('admin/chats')}}" + "/" + chatId,
            data: {
                chatId: chatId
            },
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(response)
            {
                setTimeout( function() {
                    $('.chat-message-list').remove()
                    // $('.unread').hide()
                    
                    if(response.status == 1){
                        var chats = ""

                        $.each( response.data, function( key, value ) {
                            let sendedAt = new Date(value.sended_at)
                            let years = sendedAt.getFullYear()
                            let monthsName = new Array(12);
                                monthsName[0] = "Jan";
                                monthsName[1] = "Feb";
                                monthsName[2] = "Mar";
                                monthsName[3] = "Apr";
                                monthsName[4] = "Mei";
                                monthsName[5] = "Jun";
                                monthsName[6] = "Jul";
                                monthsName[7] = "Agt";
                                monthsName[8] = "Sep";
                                monthsName[9] = "Okt";
                                monthsName[10] = "Nov";
                                monthsName[11] = "Des";
                            let months = monthsName[sendedAt.getMonth()]
                            let dates = sendedAt.getDate()
                            let fulldate = dates + ' ' + months + ' ' + years
                            let hours   = String(sendedAt.getHours()).padStart(2, "0")
                            let minutes = String(sendedAt.getMinutes()).padStart(2, "0")
                            var weekdays = new Array(7);
                                weekdays[0] = "Minggu";
                                weekdays[1] = "Senin";
                                weekdays[2] = "Selasa";
                                weekdays[3] = "Rabu";
                                weekdays[4] = "Kamis";
                                weekdays[5] = "Jumat";
                                weekdays[6] = "Sabtu";
                            let day    = String(sendedAt.getDay());
                            let days   = weekdays[day];

                            chats += `
                                <div 
                                    class="${ value.from_id == {{ auth()->id() }} ? 'chat-message-right' : 'chat-message-left' } pb-4 chat-message-list">
                                    <div>
                                        <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                                        <div class="text-muted small text-nowrap mt-2">${ fulldate }</div>
                                        <div class="text-muted small text-nowrap">${ days }, ${ hours }:${ minutes }</div>
                                    </div>
                                    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                        <div class="font-weight-bold mb-1">${ value.from_id == {{ auth()->id() }} ? 'You' : `${ value.name }`  }</div>
                                        ${ value.message }
                                    </div>
                                </div>
                            `;
                        })


                        $('#chat-name').html(name)
                        $('#list-message').append(chats)
                        $('.chat-messages').scrollTop($('.chat-messages').height());
                    }
                }, 1000);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    }

    $('#send-message-form-admin').keypress((e) => {
        if (e.which === 13) {
            e.preventDefault()
            
            var chatId = localStorage.getItem('chatId')
            var message = $('.input-message').val()

            $.ajax({
                type:"POST",
                url: "{{url('admin/chat')}}" + "/" + chatId,
                data: {
                    chatId: chatId,
                    message: message
                },
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(response)
                {
                    $('.input-message').val('')

                    setTimeout( function() {
                        if(response.status == 1){

                            var chats = ""
                            var value = response.data

                            let sendedAt = new Date(value.created_at)
                            let hours = String(sendedAt.getHours()).padStart(2, "0")
                            let minutes = String(sendedAt.getMinutes()).padStart(2, "0")

                            // chats += `
                            //     <div class="chat-message-right pb-4 chat-message-list">
                            //         <div>
                            //             <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                            //             <div class="text-muted small text-nowrap mt-2">${ hours }:${ minutes }</div>
                            //         </div>
                            //         <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                            //             <div class="font-weight-bold mb-1">${ value.from_id == {{ auth()->id() }} ? 'You' : `${ value.name }`  }</div>
                            //             ${ value.message }
                            //         </div>
                            //     </div>
                            // `;

                            // $('#chat-name').html(name)
                            // $('#list-message').append(chats)

                            // ajax broadcast
                            // $.ajax({
                            //     type:"POST",
                            //     url: "{{url('admin/broadcast')}}",
                            //     data: {
                            //         sender_name: "{{ auth()->user()->name }}",
                            //         message: value.message
                            //     },
                            //     headers: {
                            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            //     },
                            //     success:function(response)
                            //     {
                            //         setTimeout( function() {
                            //             if(response.status == 1){
                            //                 console.log("broadcast message nih bro")
                            //             }
                            //         }, 1000);
                            //     },
                            //     error: function(XMLHttpRequest, textStatus, errorThrown) {
                            //         alert("Error: " + errorThrown);
                            //     }
                            // });
                        }
                    }, 1000);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    })

    $('#send-message-form-member').keypress((e) => {
        if (e.which === 13) {
            e.preventDefault()
            
            var message = $('.input-message').val()

            $.ajax({
                type:"POST",
                url: "{{url('admin/chat')}}",
                data: {
                    message: message
                },
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(response)
                {
                    setTimeout( function() {
                        if(response.status == 1){
                            $('.input-message').val('')

                            var chats = ""
                            var value = response.data

                            let sendedAt = new Date(value.created_at)
                            let hours = String(sendedAt.getHours()).padStart(2, "0")
                            let minutes = String(sendedAt.getMinutes()).padStart(2, "0")

                            // chats += `
                            //     <div class="chat-message-right pb-4 chat-message-list">
                            //         <div>
                            //             <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                            //             <div class="text-muted small text-nowrap mt-2">${ hours }:${ minutes }</div>
                            //         </div>
                            //         <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                            //             <div class="font-weight-bold mb-1">${ value.from_id == {{ auth()->id() }} ? 'You' : `${ value.name }`  }</div>
                            //             ${ value.message }
                            //         </div>
                            //     </div>
                            // `;

                            $('#chat-name').html(name)
                            $('#list-message').append(chats)


                            // ajax broadcast
                            // $.ajax({
                            //     type:"POST",
                            //     url: "{{url('admin/broadcast')}}",
                            //     data: {
                            //         sender_name: "{{ auth()->user()->name }}",
                            //         message: value.message
                            //     },
                            //     headers: {
                            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            //     },
                            //     success:function(response)
                            //     {
                            //         setTimeout( function() {
                            //             if(response.status == 1){
                            //                 console.log("broadcast message nih bro")
                            //             }
                            //         }, 1000);
                            //     },
                            //     error: function(XMLHttpRequest, textStatus, errorThrown) {
                            //         alert("Error: " + errorThrown);
                            //     }
                            // });
                        }
                    }, 1000);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    })
</script>

<script>
    // Retrieve Firebase Messaging object.
    const messaging = firebase.messaging();
    // Add the public key generated from the console here.
    messaging.usePublicVapidKey("BGOyiLcz8NcWypLf9487jRjQVZV_E99bznlBdxEKCF8jXC73uBpHzzHw1ZVzdyD7eTl-8iw4yM-MZrYd7_PmhuM"); //vapid key lama
    // messaging.usePublicVapidKey("BG6DfB9iNk-4UbM7E48rYsokiNvcjeezPcK7lvESMeZPJgfJ70eppH4mdt-su5xE8_td4iEPhh1xv3XE9oPdbDU");

    function sendTokenToServer(fcm_token) {
        const user_id = '{{auth()->user()->id}}';
        //console.log($user_id);
        axios.post('/api/save-token', {
            fcm_token, user_id
        })
            .then(res => {
                console.log(res);
            })

    }

    function retreiveToken(){
        messaging.getToken().then((currentToken) => {
            if (currentToken) {
                sendTokenToServer(currentToken);
                // updateUIForPushEnabled(currentToken);
            } else {
                // Show permission request.
                //console.log('No Instance ID token available. Request permission to generate one.');
                // Show permission UI.
                //updateUIForPushPermissionRequired();
                //etTokenSentToServer(false);
                alert('You should allow notification!');
            }
        }).catch((err) => {
            console.log(err.message);
            // showToken('Error retrieving Instance ID token. ', err);
            // setTokenSentToServer(false);
        });
    }
    retreiveToken();
    messaging.onTokenRefresh(()=>{
        retreiveToken();
    });

    messaging.onMessage((response)=>{
        console.log('Message received');
        console.log(response);

        showChat(localStorage.getItem('name'))

        $(".chat-messages").animate({
            scrollTop: $(".chat-messages").get(0).scrollHeight
        }, 500);

        // location.reload();

        var chatsRight = ""
        var chatsLeft = ""

        var value = response.data

        let sendedAt = new Date(value.created_at)
        let hours   = String(sendedAt.getHours()).padStart(2, "0")
        let minutes = String(sendedAt.getMinutes()).padStart(2, "0")
        var weekdays = new Array(7);
                                weekdays[0] = "Minggu";
                                weekdays[1] = "Senin";
                                weekdays[2] = "Selasa";
                                weekdays[3] = "Rabu";
                                weekdays[4] = "Kamis";
                                weekdays[5] = "Jumat";
                                weekdays[6] = "Sabtu";
        let day    = String(sendedAt.getDay());
        let days   = weekdays[day];

        chatsRight += `
            <div class="chat-message-right pb-4 chat-message-list">
                <div>
                    <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                    <div class="text-muted small text-nowrap mt-2">${ days }, ${ hours }:${ minutes }</div>
                </div>
                <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                    <div class="font-weight-bold mb-1">${ value.from_id == {{ auth()->id() }} ? 'You' : `${ value.name }`  }</div>
                    ${ value.message }
                </div>
            </div>
        `;

        chatsLeft += `
            <div class="chat-message-left pb-4 chat-message-list">
                <div>
                    <img src="{{ asset('images/core/icon-user-one.svg') }}" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40">
                    <div class="text-muted small text-nowrap mt-2">${ hours }:${ minutes }</div>
                </div>
                <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                    <div class="font-weight-bold mb-1">${ value.from_id == {{ auth()->id() }} ? 'You' : `${ value.name }`  }</div>
                    ${ value.message }
                </div>
            </div>
        `;
        
        if (value.from_id == {{auth()->user()->id}} && value.to_id != {{auth()->user()->id}}) {
            $('#chat-name').html(name)
            $('#list-message').append(chatsRight)
        } else {
            $('#chat-name').html(name)
            $('#list-message').append(chatsLeft)
        }
    });
</script>

<style>
body{margin-top:20px;}

.chat-online {
    color: #34ce57
}

.chat-offline {
    color: #e4606d
}

.chat-box {
    display: flex;
    flex-direction: column;
    max-height: 380px;
    overflow-y: scroll;
}

.chat-messages {
    display: flex;
    flex-direction: column;
    max-height: 325px;
    overflow-y: scroll
}

.chat-message-left,
.chat-message-right {
    display: flex;
    flex-shrink: 0
}

.chat-message-left {
    margin-right: auto
}

.chat-message-right {
    flex-direction: row-reverse;
    margin-left: auto
}
.py-3 {
    padding-top: 1rem!important;
    padding-bottom: 1rem!important;
}
.px-4 {
    padding-right: 1.5rem!important;
    padding-left: 1.5rem!important;
}
.flex-grow-0 {
    flex-grow: 0!important;
}
.border-top {
    border-top: 1px solid #dee2e6!important;
}
</style>

@stop
