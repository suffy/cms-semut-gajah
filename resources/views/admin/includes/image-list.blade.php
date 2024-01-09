<section class="panel">
    <div class="content-body" style="padding: 15px;">
    <div class="row">
        @foreach($images as $gallery)
        <div class="col-md-4 col-sm-4 ">
            <div class="box-border" style="background-image: url({{asset($gallery->path)}}); background-size: cover; background-position: center center; height: 100px;">
                <div class="action-button">
                    <a href="javascript:void(0)" class="btn add-image-to-text" data-value="{{asset($gallery->path)}}"><i class="fa fa-plus"></i></a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
</section>

{{$images->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}

<style>

    .box-border{
        position: relative;
    }

    .action-button{
        width: 100%;
        position: absolute;
        bottom: 0;
        margin: auto;
        border-radius: 0px;
        background: rgba(0,0,0,0.4);
    }

    .action-button a{
        display: flex;
        color: #ffffff;
        margin: auto;
    }
</style>

<script>
    $(".add-image-to-text").click(function(){
        var dataLink = $(this).attr('data-value');
        tinymce.get('content').execCommand('mceInsertContent', false, "<img class='img-responsive' style='max-width: 750px; margin-left: auto; margin-right: auto;' src='"+dataLink+"'/>");
        tinymce.get('edit-content').execCommand('mceInsertContent', false, "<img class='img-responsive' style='max-width: 750px; margin-left: auto; margin-right: auto;' src='"+dataLink+"'/>");

    });

    $('.pagination a').on('click', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var asset = "{{asset('/')}}";
        $.ajax({
            url: url,
            method: "GET",
            success: function(response){

                $('#image-list').html(response);
            }

        });
    });

</script>
