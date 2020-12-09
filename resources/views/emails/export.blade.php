@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('Your :filename export is ready', [
    'filename' => $export->file->original_name,
]) }}.

{{ __('The generated file has :entries entries', [
    'entries' => $export->entries
]) }}.

@component('mail::button', ['url' => $export->file->temporaryLink()])
@lang('Download file')
@endcomponent

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent