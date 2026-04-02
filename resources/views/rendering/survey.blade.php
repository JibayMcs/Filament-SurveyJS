<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $survey['title'] ?? 'Survey' }}</title>
    <style>{!! $css !!}</style>
</head>
<body class="sjs-render sjs-render--{{ $theme }}">
    @if($showHeader)
        @include($headerView ?? 'survey-js::rendering.header', ['survey' => $survey])
    @endif

    @foreach($survey['pages'] as $pageIndex => $page)
        @include('survey-js::rendering.page', [
            'page' => $page,
            'pageIndex' => $pageIndex,
            'showPageTitles' => $showPageTitles,
            'showPageBreaks' => $showPageBreaks,
            'showQuestionNumbers' => $showQuestionNumbers,
            'showUnanswered' => $showUnanswered,
            'unansweredText' => $unansweredText,
        ])
    @endforeach

    @if($showFooter)
        @include($footerView ?? 'survey-js::rendering.footer', ['survey' => $survey])
    @endif
</body>
</html>
