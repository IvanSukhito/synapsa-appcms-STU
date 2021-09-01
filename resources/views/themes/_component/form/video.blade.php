<?php
$attribute = $addAttribute;
foreach ($fieldExtra as $extraKey => $extraVal) {
    $attribute[$extraKey] = $extraVal;
}
$attribute['id'] = $fieldName;
$attribute['accept'] = 'video/*';
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
?>
<div class="form-group">
    <label for="{{$fieldName}}">{{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}</label>
    @if($fieldValue)
        <?php
        $getExt = explode('.', $fieldValue);
        $ext = end($getExt);
        ?>
        <br/>
        <video width="320" height="240" controls>
            <source src="{{ asset($path.$fieldValue) }}" type="video/{{ $ext }}">
            Your browser does not support the video tag.
        </video>
        <br/>
    @endif
    @if(!in_array($viewType, ['show']))
        <br/>
        {{ Form::file($fieldName, $attribute) }}
        <br/>
    @endif
    @if(isset($fieldMessage)) <span class="small">{{ $fieldMessage }}</span> @endif
    @if($errors->has($fieldName)) <div class="form-control is-invalid" style="display: none;"></div><div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
