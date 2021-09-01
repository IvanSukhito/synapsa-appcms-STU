@foreach($passing as $fieldName => $fieldData)
    <?php
        $fieldValue = isset($data->$fieldName) ? $data->$fieldName : null;
        $listPassing = [
            'fieldName' => $fieldName,
            'fieldLang' => __($fieldData['lang']),
            'fieldRequired' => isset($fieldData['validation'][$viewType]) && in_array('required', explode('|', $fieldData['validation'][$viewType])) ? 1 : 0,
            'fieldValue' => $fieldValue,
            'fieldMessage'=>$fieldData['message'],
            'path'=>$fieldData['path'],
            'addAttribute'=>$addAttribute,
            'fieldExtra' => isset($fieldData['extra'][$viewType]) ? $fieldData['extra'][$viewType] : [],
            'viewType' => $viewType
        ];

        $arrayPassing = [];
        if (in_array($fieldData['type'], ['select', 'select2', 'tagging'])) {
            $arrayPassing = isset($listSet[$fieldName]) ? $listSet[$fieldName] : [];
        }
        $listPassing['listFieldName'] = $arrayPassing;
    ?>
    @component(env('ADMIN_TEMPLATE').'._component.form.'.$fieldData['type'], $listPassing)
    @endcomponent
@endforeach