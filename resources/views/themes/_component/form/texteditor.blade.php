<?php
$attribute = $addAttribute;
$attribute['id'] = $fieldName;
$attribute['class'] = 'form-control texteditor';
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
    {{ Form::textarea($fieldName, old($fieldName, $fieldValue), $attribute) }}
    @if(isset($fieldMessage)) <span class="small">{!! $fieldMessage !!}</span> @endif
    @if($errors->has($fieldName)) <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
