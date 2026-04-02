<div class="sjs-render-page" @if($showPageBreaks && $pageIndex > 0) style="page-break-before: always;" @endif>
    @if($showPageTitles && $page['title'])
        <h2 class="sjs-render-page-title">{{ $page['title'] }}</h2>
    @endif
    @if($showPageTitles && $page['description'])
        <p class="sjs-render-page-description">{{ $page['description'] }}</p>
    @endif

    @foreach($page['rows'] as $row)
        @if(count($row) > 1)
            <div class="sjs-render-row">
                @foreach($row as $question)
                    <div class="sjs-render-row-item">
                        @include('survey-js::rendering.question', [
                            'question' => $question,
                            'showQuestionNumbers' => $showQuestionNumbers,
                            'showUnanswered' => $showUnanswered,
                            'unansweredText' => $unansweredText,
                        ])
                    </div>
                @endforeach
            </div>
        @else
            @include('survey-js::rendering.question', [
                'question' => $row[0],
                'showQuestionNumbers' => $showQuestionNumbers,
                'showUnanswered' => $showUnanswered,
                'unansweredText' => $unansweredText,
            ])
        @endif
    @endforeach
</div>
