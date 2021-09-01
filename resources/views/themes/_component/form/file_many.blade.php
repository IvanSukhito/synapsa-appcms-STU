<?php
$attribute = $addAttribute;
$attribute['accept'] = 'application/pdf,application/vnd.ms-excel,application/msword,application/vnd.ms-powerpoint';
if ($fieldRequired == 1) {
    $attribute['required'] = 'true';
}
?>
<div class="form-group">
    <label for="{{$fieldName}}">{{ __($fieldLang) }} {{ $fieldRequired == 1 ? ' *' : '' }}</label>
    @if($fieldValue)
        <?php
        $getListValue = json_decode($fieldValue, TRUE);
        $listIndexValue = [];
        foreach($getListValue as $index => $listValue) {
            $listIndexValue[] = $index;
        }
        if (count($listIndexValue) > 0) {
            $listIndexValue = implode(',', $listIndexValue);
        }
        else {
            $listIndexValue = '';
        }
        ?>
        <input type="hidden" value="{!! $listIndexValue !!}" name="{!! $fieldName.'_storage' !!}" id="{!! $fieldName.'_storage' !!}">
        @foreach($getListValue as $index => $listValue)
            @if ($listValue['type'] == 'pdf')
                <div class="showing-image" data-id="{!! $index !!}">
                    <a href="{{ asset($listValue['path']) }}" class="link_image" target="_blank" title="{{$listValue['name']}}" data-fancybox>
                        <i class="fa fa-file-pdf-o"></i>
                    </a>
                    @if(in_array($viewType, ['edit']))
                        <a href="#" onclick="return remove_image(this)" data-storage="{!! $fieldName.'_storage' !!}" data-id="{!! $index !!}" class="btn btn-sm btn-danger"><i class="fa fa-close"></i></a>
                    @endif
                </div>
            @elseif (in_array($listValue['type'], ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx']))
                <div class="showing-image" data-id="{!! $index !!}">
                    <a href="{{ asset($listValue['path']) }}" class="link_image" target="_blank" title="{{$listValue['name']}}" data-fancybox>
                        <i class="fa fa-file"></i>
                    </a>
                    @if(in_array($viewType, ['edit']))
                        <a href="#" onclick="return remove_image(this)" data-storage="{!! $fieldName.'_storage' !!}" data-id="{!! $index !!}" class="btn btn-sm btn-danger"><i class="fa fa-close"></i></a>
                    @endif
                </div>
            @else
                <div class="showing-image" data-id="{!! $index !!}">
                    <a href="{{ asset($listValue['path']) }}" class="link_image" target="_blank" title="{{$listValue['name']}}" data-fancybox>
                        <img src="{{ asset($listValue['path']) }}" class="img-responsive max-image-preview" alt="{{$listValue['name']}}"/>
                    </a>
                    @if(in_array($viewType, ['edit']))
                        <a href="#" onclick="return remove_image(this)" data-storage="{!! $fieldName.'_storage' !!}" data-id="{!! $index !!}" class="btn btn-sm btn-danger"><i class="fa fa-close"></i></a>
                    @endif
                </div>
            @endif
        @endforeach
    @endif
    @if(!in_array($viewType, ['show']))
        <div class="upload-image">
            {{ Form::file($fieldName.'[]', $attribute) }}
        </div>
        <a href="#" onclick="return add_image(this)" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> @lang('general.add')</a>
    @endif
    @if(isset($fieldMessage)) <br/><span class="small">{{ $fieldMessage }}</span> @endif
    @if($errors->has($fieldName)) <div class="form-control is-invalid" style="display: none;"></div><div class="invalid-feedback">{{ $errors->first($fieldName) }}</div> @endif
</div>
