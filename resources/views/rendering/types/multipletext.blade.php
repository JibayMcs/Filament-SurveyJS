<div class="sjs-render-multipletext">
    @foreach($question['items'] as $item)
        <div class="sjs-render-multipletext-row">
            <span class="sjs-render-multipletext-label">{{ $item['title'] }}</span>
            <span class="sjs-render-multipletext-value">{{ $item['value'] ?? '—' }}</span>
        </div>
    @endforeach
</div>
