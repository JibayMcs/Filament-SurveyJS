@if($question['signatureData'])
    <img class="sjs-render-signature"
         src="{{ $question['signatureData'] }}"
         width="{{ $question['width'] }}"
         height="{{ $question['height'] }}"
         alt="Signature" />
@endif
