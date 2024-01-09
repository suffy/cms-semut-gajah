    <div class="messaging">
        <div class="row">
            <div class="col-md-8">
                <div class="active" id="messages-1" style="border: 1px solid #f1f1f1; padding: 15px">

                    <div class="msg_history" id="messg">

                        @foreach ($message as $messages)



                        @if($messages->sender_id==$user->id)

                        @php
                            $msg = "";
                            $m_id = "";

                            $pecah = explode("***", $messages->content);
                            if(count($pecah)>1){
                                $m_id = $pecah[1];
                                $question_media = App\Models\Media::find($m_id);
                            }
                        @endphp


                        @if(count($pecah)>1)
                            <div class='panel panel-body' style='border: 1px solid #f1f1f1; padding: 10px; font-size:9pt; background-color: #e1f1fd; margin-bottom: -25px'>
                            Nama : {{$question_media->name}}<br>
                            Alamat : {{$question_media->address}}<br>
                            Harga : Rp {{number_format($question_media->price)}}<br>
                            <a href='{{url('products/'.$question_media->id.'/'.str_replace(" ", "-", $question_media->name))}}' target='_blank'>Go to Product >></a>
                            </div>
                        @endif

                        <div class="outgoing_msg">
                            <div class="sent_msg">
                                @if(count($pecah)>1)
                                    <p>{{$pecah[0]}}</p>
                                @else
                                    <p>{{ $messages->content }}</p>
                                @endif
                                <span class="time_date">{{ $messages->created_at }}</span>
                            </div>
                        </div>

                        @else
                        <div class="incoming_msg">
                            <div class="received_msg">
                                <div class="received_withd_msg">

                                    <p>{{ $messages->content }}</p>
                                    <span class="time_date mb-4">{{ $messages->created_at }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        @endforeach



                    </div>

                    <div class="type_msg">
                        <div class="input_msg_write">
                            <div class="loading-place" id="send-loading"  style="height: 70px; position: absolute; width: 100%; text-align:center; display: none">
                                <div style="margin: auto">
                                    <div class="text-center">
                                        <div class="loading-outer">
                                            <img src="{{asset('images/loading.gif')}}" height="25px"><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form id="form-message-1" method="POST" action="#">
                                @csrf
                                <input type="hidden" name="sender_id" value="{{$user->id}}" />
                                <input type="hidden" name="receiver_id" value="{{$user->id}}" />
                                <input type="text" name="content" class="write_msg" placeholder="Type a message" style="padding: 15px"/>
                                <button class="msg_send_btn" type="submit" style="margin: -4px 10px 5px 10px"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></button>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <script>


            var objDiv = document.getElementById("messg");
            objDiv.scrollTop = objDiv.scrollHeight;


        function openChat(id) {
            $('.chat_list').removeClass('active_chat');
            $("#chat-" + id).addClass('active_chat');

            $('.mesgs').removeClass('active');
            $("#messages-" + id).addClass('active');
        }

        $("#form-message-1").submit(function(e){
            e.preventDefault();
            sendData(1);
        });

        function sendData(id) {

            var data = new FormData($('#form-message-1')[0]);

            $.ajax({
                type: "POST",
                url: "{{url('member/messages')}}",
                data: data,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $('#send-loading').show();
                },
                success: function (rsp) {
                    response = JSON.parse(rsp);
                    console.log(rsp);
                    $('#send-loading').hide();
                    reloadMessages();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#send-loading').hide();
                    alert(textStatus)
                }
            });

        };

        var url = "{{url('member/messages/get/'.$user->id)}}";

        function reloadMessages() {

            console.log(url);
            $.ajax({
                type: "GET",
                url: url,
                beforeSend: function () {
                    $('#send-loading').show();
                },
                success: function (response) {

                    setTimeout(function () {
                        $('#message').html("");
                        $('#message').html(response);
                        $('#send-loading').hide();

                        var objDiv = document.getElementById("messg");
                        objDiv.scrollTop = objDiv.scrollHeight;

                    }, 1000)

                }
            })
        }
    </script>
