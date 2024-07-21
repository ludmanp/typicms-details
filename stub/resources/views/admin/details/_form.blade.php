@push('js')
    <script type="module" src="{{ asset('components/ckeditor4/ckeditor.js') }}"></script>
    <script type="module" src="{{ asset('components/ckeditor4/config-full.js') }}"></script>
@endpush

<div class="header">
    @include('core::admin._button-back', ['url' => $object->editUrl(), 'title' => __('Object ":title"', ['title' => $object->title])])
    @include('core::admin._title', ['default' => __('New object ":title" detail', ['title' => $object->title])])
    @component('core::admin._buttons-form', ['model' => $model])
    @endcomponent
</div>

<div class="content">
    @include('core::admin._form-errors')

    {!! BootForm::hidden('object_id')->value($object->id) !!}

    <file-manager related-table="{{ $model->getTable() }}" :related-id="{{ $model->id ?? 0 }}"></file-manager>
    <file-field type="image" field="image_id" :init-file="{{ $model->image ?? 'null' }}"></file-field>
    <files-field :init-files="{{ $model->files }}"></files-field>
    @include('core::form._title-and-slug')
    <div class="mb-3">
        {!! BootForm::hidden('status')->value(0) !!}
        {!! BootForm::checkbox(__('Published'), 'status') !!}
    </div>
    {!! TranslatableBootForm::textarea(__('Summary'), 'summary')->rows(4) !!}
    {!! TranslatableBootForm::textarea(__('Body'), 'body')->addClass('ckeditor-full') !!}
</div>
