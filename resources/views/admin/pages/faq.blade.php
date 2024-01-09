@extends('admin.layout.template')
@section('content')

<section class="panel">
  <header class="panel-heading">
    Faqs
  </header>

  <div class="card-body">
    <div class="panel">
      <a href="#" data-toggle="modal" data-target="#newFaq" class="btn btn-blue"><span class="fa fa-plus"></span> New Faq</a><br><br>
      <table class="table default-table dataTable">
        <thead class="text-center">
          <tr>
            <th>No</th>
            <!-- <th>Key</th> -->
            <th>Question</th>
            <th>Answer</th>
            <th>Menu</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($faq as $key => $row)
          <tr>
            <td class="text-center">{{ $key+1 }}</td>

            <!-- <td>{{$row->slug}}</td> -->
            <td>
              {{$row->question}}<br>
              {{$row->question_en}}

            </td>
            <td>

              @if($row->icon)
              <div style="width: 100%; text-align: center">
              <img src="{{asset($row->icon)}}" class="img-fluid" style="max-width: 100px; max-height: 100px; object-fit: cover; margin: auto">
              </div>
              <br>
            @endif

            <div style="max-width: 400px; max-height: 150px; overflow:auto">
              {{$row->answer}}<br>
              {{$row->answer_en}}
            </div>
            </td>
            <td>{{$row->position}}</td>
            <td>

              <div class="btn-group">
                <a href="javascript:void(0)" class="btn btn-green btn-sm button-edit-faqs" data-id="{{ $row->id }}" data-type="{{ $row->type }}" data-slug="{{ $row->slug }}" data-question="{{ $row->question }}" data-question_en="{{ $row->question_en }}" data-answer="{{ $row->answer }}" data-answer_en="{{ $row->answer_en }}" data-editable="{{ $row->editable }}" data-icon="{{ $row->icon }}" data-status="{{ $row->status }}" data-update="{{ url('admin/faqs',$row->id) }}" data-position="{{ $row->position }}" data-toggle="modal" data-target="#editFaq" style="display: inline-block;">
                  <i class="fa fa-edit"></i>
                </a>

                @if($row->status == 0)
                <form action="{{url('admin/faqs/'.$row->id)}}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                  @method('delete')
                  @csrf
                  <button type="submit" class="btn btn-red btn-sm" value="Delete" onclick="return confirm('Delete Data?')"><i class="fa fa-trash"></i></button>
                </form>
                @endif
              </div>
    </div>

    </td>
    </tr>
    @endforeach
    </tbody>
    </table>
  </div>
  </div>
</section>
<!-- Modal New -->
<div class="modal fade" id="newFaq" tabindex="-1" role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Add Faq</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{url('admin/faqs')}}" method="post" enctype="multipart/form-data">
          @csrf

          <!-- <div class="form-group row">
            <label class="col-sm-12 col-form-label">Key</label>
            <div class="col-sm-12">
              <input type="text" name="slug" id="" class="form-control">
            </div>
          </div> -->
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Image </label>
            <div class="col-sm-12">
              <input type="file" class="file" name="icon" value="">
            </div>
          </div>
          <!-- <div class="form-group row">
            <label class="col-sm-12 col-form-label">Page</label>
            <div class="col-sm-12">
              <select class="form-control" name="position">
                <option value="none">- Select Page -</option>
                <option value="general">General</option>
                @foreach($menu as $row)
                <option value="{{$row->slug}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div> -->

          <div class="row">
            <div class="col-md-6">
            <div class="form-group row">
            <label class="col-sm-12 col-form-label">Question</label>
            <div class="col-sm-12">
              <input type="text" name="question" id="" class="form-control" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Answer</label>
            <div class="col-sm-12">
              <textarea name="answer" id="" class="form-control"></textarea>
            </div>
          </div>
            </div>
            <div class="col-md-6">
            <div class="form-group row">
            <label class="col-sm-12 col-form-label">Question En</label>
            <div class="col-sm-12">
              <input type="text" name="question_en" id="" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Answer En</label>
            <div class="col-sm-12">
              <textarea name="answer_en" id="" class="form-control"></textarea>
            </div>
          </div>
            </div>
          </div>
          
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- EndModalNew -->
<!-- Modal Edit -->
<div class="modal fade" id="editFaq" tabindex="-1" role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Edit Faq</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="updateFaq" action="#" method="post" enctype="multipart/form-data">
          @method('put')
          @csrf
          <input type="hidden" name="url" value="{{Request::url()}}" required>
          <input type="hidden" id="input-id-edit" name="id" value="">
          <!-- <div class="form-group row">
            <label class="col-sm-12 col-form-label">Key</label>
            <div class="col-sm-12">
              <input type="text" id="input-slug-edit" name="slug" class="form-control" readonly>
            </div>
          </div> -->
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Image </label>
            <div class="col-sm-12">
              <input type="file" class="file" name="icon" value="">
            </div>
          </div>
          <!-- <div class="form-group row">
            <label class="col-sm-12 col-form-label">Page</label>
            <div class="col-sm-12">
              <select id="edit-page" class="form-control" name="position">
                <option value="0">- Select Page -</option>
                <option value="general">General</option>
                @foreach($menu as $row)
                <option value="{{$row->slug}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div> -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Question</label>
                <div class="col-sm-12">
                  <input type="text" id="input-question-edit" name="question" class="form-control">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Answer</label>
                <div class="col-sm-12">
                  <textarea id="input-answer-edit" name="answer" class="form-control" style="height: 200px"></textarea>
                  <span style="font-size: 8pt">* Tambahkan tag {{"<br>"}} untuk new line paragraph</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
            <div class="form-group row">
              <label class="col-sm-12 col-form-label">Question En</label>
              <div class="col-sm-12">
                <input type="text" id="input-question-edit_en" name="question_en" class="form-control">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-12 col-form-label">Answer En</label>
              <div class="col-sm-12">
                <textarea id="input-answer-edit_en" name="answer_en" class="form-control" style="height: 200px"></textarea>
              </div>
            </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- EndModal Edit -->

<script type="text/javascript">
  $('.button-edit-faqs').on('click', function(e) {
    var url = $(this).data('update');
    var id = $(this).attr('data-id');
    var type = $(this).attr('data-type');
    var slug = $(this).attr('data-slug');
    var question = $(this).attr('data-question');
    var question_en = $(this).attr('data-question_en');
    var answer = $(this).attr('data-answer');
    var answer_en = $(this).attr('data-answer_en');
    var position = $(this).attr('data-position');
    var icon = $(this).attr('data-icon');
    var status = $(this).attr('data-status');

    $('#updateFaq').attr('action', url);
    $('#input-id-edit').val(id);
    $('#input-type-edit').val(type);
    $('#input-question-edit').val(question);
    $('#input-question-edit_en').val(question_en);
    $('#input-answer-edit').val(answer);
    $('#input-answer-edit_en').val(answer_en);
    // $('#input-slug-edit').val(slug);
    // $('#edit-page').val(position);
    $('#input-icon-edit').val(icon);
    $('#input-status-edit').val(status);
  });
  $('.file').bind('change', function() {
    //this.files[0].size gets the size of your file.
    if (this.files[0].size > 2000000) {
      alert("File size is too big, please select another file (max 2MB)");
      $(this).val('');
    }
  });
</script>
@stop