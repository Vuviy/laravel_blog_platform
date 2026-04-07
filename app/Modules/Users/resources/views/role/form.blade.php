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
        @if(isset($role))
            <form method="POST" action="{{route('admin.roles.update', ['role' => $role->id])}}">
            @method('PUT')
        @else
            <form method="POST" action="{{route('admin.roles.store')}}">
        @endif
            @csrf
            <div class="card-body">

                <div class="form-group">
                    <label>Permissions</label>
                    <select name="permissions[]" id="permissions-select" class="form-control" multiple>
                        @foreach($permissions as $permission)
                            <option value="{{ $permission->id }}"
                                    @if(isset($role) && in_array($permission->id->getValue(), $selectedPermissionIds ?? []))
                                        selected
                                @endif
                            >
                                {{ $permission->key }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Можна обрати декілька тегів</small>
                </div>


                <div class="form-group">
                    <label>{{ __('common.title') }}</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ isset($role) ? $role->name->getValue() : '' }}">
                </div>
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
