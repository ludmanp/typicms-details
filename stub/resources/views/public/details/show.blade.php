@extends('core::public.master')

@section('title', $model->title.' – '.__('Object Details').' – '.$websiteTitle)
@section('ogTitle', $model->title)
@section('description', $model->summary)
@section('ogImage', $model->present()->image(1200, 630))
@section('bodyClass', 'body-objects body-object-'.$model->id.' body-page body-page-'.$page->id)

@section('content')

<article class="object-detail">
    <header class="object-detail-header">
        <div class="object-detail-header-container">
            <div class="object-detail-header-navigator">
                @include('core::public._items-navigator', ['module' => 'Object Details', 'model' => $model])
            </div>
            <h1 class="object-detail-title">{{ $model->title }}</h1>
        </div>
    </header>
    <div class="object-detail-body">
        @include('objects::public.details._json-ld', ['detail' => $model])
        @empty(!$model->summary)
        <p class="object-detail-summary">{!! nl2br($model->summary) !!}</p>
        @endempty
        @empty(!$model->image)
        <picture class="object-detail-picture">
            <img class="object-detail-picture-image" src="{{ $model->present()->image(2000, 1000) }}" width="{{ $model->image->width }}" height="{{ $model->image->height }}" alt="">
            @empty(!$model->image->description)
            <legend class="object-detail-picture-legend">{{ $model->image->description }}</legend>
            @endempty
        </picture>
        @endempty
        @empty(!$model->body)
        <div class="rich-content">{!! $model->present()->body !!}</div>
        @endempty
        @include('files::public._documents')
        @include('files::public._images')
    </div>
</article>

@endsection
