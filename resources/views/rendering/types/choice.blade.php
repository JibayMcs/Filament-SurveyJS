<ul class="sjs-render-choices">
    @foreach($question['choices'] as $choice)
        <li class="sjs-render-choice {{ $choice['selected'] ? 'sjs-render-choice--selected' : '' }}">
            <span class="sjs-render-check">{{ $choice['selected'] ? '✓' : '' }}</span>
            <span>{{ $choice['text'] }}</span>
        </li>
    @endforeach
</ul>
