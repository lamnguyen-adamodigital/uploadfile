<div class="card-body album">
    @if($data)
        <h3 class="card-title text-center font-weight-bold">Start screen</h3>
        {{-- <p class="text-center">(Upload unlimited and can change the position)</p> --}}
        <div class="album--form">
        <form id="upload-form" method="POST" action="{{ route('admin.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="image"><i class="fas fa-upload"></i></label>
                <input type="file" name="album" id="image" multiple accept="image/*">
                <input type="submit">
            </div>
        </form>
        </div>
        @if(count($data) > 0)
            <div class="row el-element-overlay mt-5" id="list-album">
                @foreach($data as $value)
                <div class="col-lg-3 col-md-6 item" data-id="{{$value->id}}" data-display-position="{{ $value->position }}">
                    <div class="card">
                        <div class="el-card-item">
                            <div class="el-card-avatar el-overlay-1"> <img src="{{ asset('uploads/'.$value->image) }}" alt="Image">
                                <div class="el-overlay">
                                    <ul class="list-style-none el-info">
                                        <li class="el-item"><a class="btn btn-danger btn-outline el-link btn-delete" data-album-id = "{{ $value->id }}" href=""><i class="fas fa-trash-alt"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-info">Slide image not found!</p>
        @endif
    @else
        <p class="text-center text-dark">Image not found!</p>
    @endif
</div>
