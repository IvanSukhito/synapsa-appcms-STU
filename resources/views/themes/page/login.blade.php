@extends(env('ADMIN_TEMPLATE').'._base.login')

@section('title', __('general.login'))

@section('content')
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">@lang('general.welcome_login')</p>

            {{ Form::open(['route' => 'admin.login.post', 'id'=>'form', 'novalidate'=>'novalidate'])  }}
                <div class="input-group mb-3">
                    {{ Form::text('username', old('username'), ['id'=>'username',
                                'class'=> $errors->has('username') ? 'form-control is-invalid' : 'form-control',
                                'placeholder'=>__('general.username'), 'required'=>'required']) }}
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fa fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    {{ Form::password('password', ['id'=>'password',
                    'class'=> $errors->has('password') ? 'form-control is-invalid' : 'form-control',
                    'placeholder'=>__('general.password'), 'required'=>'required']) }}
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fa fa-lock"></span>
                        </div>
                    </div>
                </div>
                @if($errors->any())
                    @foreach ($errors->all() as $error)
                        <p><code>{{ $error }}</code></p>
                    @endforeach
                @endif
                <div class="row">
                    <div class="col-8">&nbsp;</div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">@lang('general.sign_in')</button>
                    </div>
                    <!-- /.col -->
                </div>
            {{ Form::close() }}
        </div>
        <!-- /.login-card-body -->
    </div>
@stop
