<div class="sjs-render-rating">
    <div class="sjs-render-stars">
        @foreach($question['stars'] as $star)
            <span class="sjs-render-star {{ $star['filled'] ? 'sjs-render-star--filled' : '' }}">
                {{ $star['filled'] ? '★' : '☆' }}
            </span>
        @endforeach
    </div>
    <span class="sjs-render-rating-value">{{ $question['displayValue'] }}</span>
</div>
