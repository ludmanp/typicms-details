@extends('core::admin.master')

@section('title', __('New detail'))

@section('content')

    <div class="header">
        @include('core::admin._button-back', ['url' => route('admin::edit-object', $object)])
        <h1 class="header-title">@lang('New object')</h1>
    </div>

    {!! BootForm::open()->action(route('admin::store-object_detail', $object->id))->multipart()->role('form') !!}
    @include('objects::admin.items._form')
    {!! BootForm::close() !!}

@endsection
