<div class="sjs-render-paneldynamic">
    @foreach($question['panels'] ?? [] as $panel)
        <div class="sjs-render-paneldynamic-entry">
            @if($panel['title'])
                <div class="sjs-render-paneldynamic-title">{{ $panel['title'] }} #{{ $panel['index'] + 1 }}</div>
            @endif
            @foreach($panel['rows'] ?? [] as $row)
                @if(count($row) > 1)
                    <div class="sjs-render-row">
                        @foreach($row as $element)
                            <div class="sjs-render-row-item">
                                @include('survey-js::rendering.question', [
                                    'question' => $element,
                                    'showQuestionNumbers' => false,
                                    'showUnanswered' => $showUnanswered ?? true,
                                    'unansweredText' => $unansweredText ?? '—',
                                ])
                            </div>
                        @endforeach
                    </div>
                @else
                    @include('survey-js::rendering.question', [
                        'question' => $row[0],
                        'showQuestionNumbers' => false,
                        'showUnanswered' => $showUnanswered ?? true,
                        'unansweredText' => $unansweredText ?? '—',
                    ])
                @endif
            @endforeach
        </div>
    @endforeach
</div>
