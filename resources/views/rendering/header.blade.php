@if($survey['title'] || $survey['description'])
<div class="sjs-render-header">
    @if($survey['title'])
        <h1 class="sjs-render-title">{{ $survey['title'] }}</h1>
    @endif
    @if($survey['description'])
        <p class="sjs-render-description">{{ $survey['description'] }}</p>
    @endif
</div>
@endif
