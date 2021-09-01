<?php
$attribute = $addAttribute;
foreach ($fieldExtra as $extraKey => $extraVal) {
    $attribute[$extraKey] = $extraVal;
}
$attribute['id'] = $fieldName;
$attribute['multiple'] = 'true';
$attribute['class'] = 'form-control tagging';
if ($errors->has($fieldName)) {
    $attribute['class'] .= ' is-invalid';
}
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
if (is_string($fieldValue) && strlen($fieldValue) > 0) {
    $fieldValue = explode(',', $fieldValue);
}
?>
<div class="form-group">
    <label for="{{$fieldName}}">{{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}</label>
    {{ Form::select($fieldName.'[]', $listFieldName, old($fieldName, $fieldValue), $attribute) }}
    @if(isset($fieldMessage)) <span class="small">{!! $fieldMessage !!}</span> @endif
    @if($errors->has($fieldName)) <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
