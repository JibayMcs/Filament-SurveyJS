@php
    $range = $question['max'] - $question['min'];
    $percent = $range > 0 ? (($question['value'] - $question['min']) / $range) * 100 : 0;
@endphp
<div class="sjs-render-slider">
    <div class="sjs-render-slider-bar">
        <div class="sjs-render-slider-fill" style="width: {{ $percent }}%"></div>
    </div>
    <span class="sjs-render-slider-value">{{ $question['displayValue'] }}</span>
    <span class="sjs-render-slider-range">{{ $question['min'] }} — {{ $question['max'] }}</span>
</div>
