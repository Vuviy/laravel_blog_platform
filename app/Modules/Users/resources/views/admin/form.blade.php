@extends('admin.layout')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{__('common.create_update') }}</h3>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(isset($user))
            <form method="POST" action="{{route('admin.users.update', ['user' => $user->id])}}">
            @method('PUT')
        @else
            <form method="POST" action="{{route('admin.users.store')}}">
        @endif
            @csrf
            <div class="card-body">

                <div class="form-group">
                    <label>{{__('common.roles')}}</label>
                    <select name="roles[]" id="roles-select" class="form-control" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                    @if(isset($role) && in_array($role->id->getValue(), $selectedRoleIds ?? []))
                                        selected
                                @endif
                            >
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Можна обрати декілька ролів</small>
                </div>


                <div class="form-group">
                    <label for="username">username</label>
                    <input type="text"
                           name="username"
                           class="form-control"
                           value="{{ isset($user) ? $user->username->getValue() : '' }}">
                </div>

                <div class="form-group">
                    <label for="email">email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ isset($user) ? $user->email->getValue() : '' }}">
                </div>
                @if(!isset($user))


                <div class="form-group">
                    <label for="password">password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           value="{{ isset($user) ? $user->password->getValue() : '' }}">
                </div>
                @endif
            </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#permissions-select').select2({
                placeholder: 'Оберіть permissions...',
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endpush
