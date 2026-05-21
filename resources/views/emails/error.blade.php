@component('mail::message')
@component('mail::title')
{{ __('Data export failed') }}
@endcomponent

@component('mail::alert', ['variant' => 'danger'])
{{ $message }}
@endcomponent

@component('mail::box', ['variant' => 'light'])
{{ __('Export') }}: {{ $export->name }}
@endcomponent

@component('mail::signature')
@endcomponent
@endcomponent
