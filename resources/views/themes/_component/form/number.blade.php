<?php
$attribute = $addAttribute;
foreach ($fieldExtra as $extraKey => $extraVal) {
    $attribute[$extraKey] = $extraVal;
}
$attribute['id'] = $fieldName;
$attribute['class'] = 'form-control';
if ($errors->has($fieldName)) {
    $attribute['class'] .= ' is-invalid';
}
$attribute['placeholder'] = __($fieldLang);
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
?>
<div class="form-group">
    <label for="{{$fieldName}}">{{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}</label>
    {{ Form::number($fieldName, old($fieldName, $fieldValue), $attribute) }}
    @if(isset($fieldMessage)) <span class="small">{!! $fieldMessage !!}</span> @endif
    @if($errors->has($fieldName)) <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
