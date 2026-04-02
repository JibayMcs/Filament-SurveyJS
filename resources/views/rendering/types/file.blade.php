@foreach($question['files'] as $file)
    <div class="sjs-render-file">
        @if($file['isImage'] && $file['src'])
            <img class="sjs-render-image" src="{{ $file['src'] }}" alt="{{ $file['name'] }}" />
        @else
            <span class="sjs-render-file-icon">📎</span>
            <span class="sjs-render-file-name">{{ $file['name'] }}</span>
        @endif
    </div>
@endforeach
