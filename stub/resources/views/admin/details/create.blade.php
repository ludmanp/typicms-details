@extends('core::admin.master')

@section('title', __('New object detail'))

@section('content')
    {!! BootForm::open()->action(route('admin::store-object_detail', $object))->multipart()->role('form') !!}
    @include('objects::admin.details._form')
    {!! BootForm::close() !!}
@endsection
