@if($question['isAnswered'] || $showUnanswered)
<div class="sjs-render-question sjs-render-question--{{ $question['type'] }}">
    @if(! in_array($question['type'], ['html', 'image', 'panel', 'paneldynamic']) && ($question['titleLocation'] ?? 'default') !== 'hidden')
        <div class="sjs-render-label">
            @if($showQuestionNumbers && ($question['showNumber'] ?? true) && $question['number'])
                <span class="sjs-render-number">{{ $question['number'] }}.</span>
            @endif
            {{ $question['title'] }}
            @if($question['description'])
                <span class="sjs-render-question-description">{{ $question['description'] }}</span>
            @endif
        </div>
    @endif
    <div class="sjs-render-answer">
        @if($question['isAnswered'])
            @include($question['view'], ['question' => $question])
        @else
            <span class="sjs-render-unanswered">{{ $unansweredText }}</span>
        @endif
    </div>
</div>
@endif
