<div class="sjs-render-panel">
    @if($question['panelTitle'])
        <div class="sjs-render-panel-title">{{ $question['panelTitle'] }}</div>
    @endif
    <div class="sjs-render-panel-content">
        @foreach($question['rows'] ?? [] as $row)
            @if(count($row) > 1)
                <div class="sjs-render-row">
                    @foreach($row as $element)
                        <div class="sjs-render-row-item">
                            @include('survey-js::rendering.question', [
                                'question' => $element,
                                'showQuestionNumbers' => $showQuestionNumbers ?? true,
                                'showUnanswered' => $showUnanswered ?? true,
                                'unansweredText' => $unansweredText ?? '—',
                            ])
                        </div>
                    @endforeach
                </div>
            @else
                @include('survey-js::rendering.question', [
                    'question' => $row[0],
                    'showQuestionNumbers' => $showQuestionNumbers ?? true,
                    'showUnanswered' => $showUnanswered ?? true,
                    'unansweredText' => $unansweredText ?? '—',
                ])
            @endif
        @endforeach
    </div>
</div>
