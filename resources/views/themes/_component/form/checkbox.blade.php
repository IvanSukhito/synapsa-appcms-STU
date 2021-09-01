<?php
$attribute = $addAttribute;
$attribute['id'] = $fieldName;
$attribute['class'] = 'form-check-input';
if ($errors->has($fieldName)) {
    $attribute['class'] .= ' is-invalid';
}
$attribute['placeholder'] = __($fieldLang);
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
?>
<div class="form-group form-check">
    <label for="{{$fieldName}}" class="form-check-label">
        {{ Form::checkbox($fieldName, 1, old($fieldName, $fieldValue), $attribute) }}
        {{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}
    </label>
    @if(isset($fieldMessage)) <span class="small">{!! $fieldMessage !!}</span> @endif
    @if($errors->has($fieldName)) <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
