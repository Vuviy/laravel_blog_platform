@extends('layout')

@section('content')

    @foreach ($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <body class="register-page bg-body-secondary">
    <div class="register-box">


        <div class="card">
            <div class="card-body register-card-body">
                <p class="register-box-msg">Register a new membership</p>

                <form action="{{route('register')}}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Full Name" />
                        <div class="input-group-text">
                            <span class="bi bi-person"></span>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email"  name="email" class="form-control" placeholder="Email" />
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" />
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>

                        <div class="col-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </body>
@endsection
