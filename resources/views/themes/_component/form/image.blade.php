<?php
$attribute = $addAttribute;
$attribute['id'] = $fieldName;
$attribute['accept'] = 'image/*';
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
?>
<div class="form-group">
    <label for="{{$fieldName}}">{{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}</label>
    @if($fieldValue)
        <br/>
        <a href="{{ asset($path.$fieldValue) }}" target="_blank" title="{{$fieldName}}" data-fancybox>
            <img src="{{ asset($path.$fieldValue) }}" class="img-responsive max-image-preview" alt="{{$fieldName}}"/>
        </a>
        <br/>
    @endif
    @if(!in_array($viewType, ['show']))
        <br/>
        {{ Form::file($fieldName, $attribute) }}
        <br/>
    @endif
    @if(isset($fieldMessage)) <br/><span class="small">{{ $fieldMessage }}</span> @endif
    @if($errors->has($fieldName)) <div class="form-control is-invalid" style="display: none;"></div><div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
