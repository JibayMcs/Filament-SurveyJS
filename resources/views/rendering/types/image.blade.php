@if($question['imageLink'] ?? null)
    <img class="sjs-render-image" src="{{ $question['imageLink'] }}" alt="{{ $question['title'] }}" />
@endif
