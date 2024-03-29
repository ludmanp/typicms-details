@extends('core::admin.master')

@section('title', $model->present()->title)

@section('content')
    {!! BootForm::open()->put()->action(route('admin::update-object_detail', [$object->id, $model->id]))
        ->multipart()->role('form') !!}
    {!! BootForm::bind($model) !!}
    @include('objects::admin.details._form')
    {!! BootForm::close() !!}
@endsection
