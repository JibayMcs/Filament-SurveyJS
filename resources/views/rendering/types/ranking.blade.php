<ol class="sjs-render-ranking">
    @foreach($question['choices'] as $choice)
        <li class="sjs-render-ranking-item">{{ $choice['text'] }}</li>
    @endforeach
</ol>
