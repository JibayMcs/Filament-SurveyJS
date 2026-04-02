<div class="sjs-render-imagepicker">
    @foreach($question['images'] ?? [] as $img)
        @if($img['selected'])
            <div class="sjs-render-imagepicker-item sjs-render-imagepicker-item--selected">
                @if($img['imageLink'])
                    <img src="{{ $img['imageLink'] }}" alt="{{ $img['text'] }}" />
                @endif
                <span>{{ $img['text'] }}</span>
            </div>
        @endif
    @endforeach
</div>
