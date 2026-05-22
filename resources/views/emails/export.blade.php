@component('mail::message')
@component('mail::title')
{{ __('Data export ready') }}
@endcomponent

{{ __('Hi :name', ['name' => $name]) }},

{{ __('The :filename file is ready', [
    'filename' => $export->file->original_name,
]) }}.

{{ __('The generated document has :entries entries', [
    'entries' => $export->entries
]) }}.

@component('mail::file', [
    'meta' => __(':entries entries', ['entries' => $export->entries]),
    'url' => $export->file->temporaryLink(),
])
{{ $export->file->original_name }}
@endcomponent

@component('mail::signature')
@endcomponent
@endcomponent
