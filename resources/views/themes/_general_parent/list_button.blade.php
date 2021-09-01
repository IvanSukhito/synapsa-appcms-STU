@if ($permission['show'])
    <a href="{{ route('admin.' . $thisRoute . '.show', ['parent_id' => $parentId, $thisRoute => $query->{$masterId}]) }}" class="mb-1 btn btn-info btn-sm"
       title="@lang('general.show')">
        <i class="fa fa-eye"></i>
        <span class="d-none d-md-inline"> @lang('general.show')</span>
    </a>
@endif
@if ($permission['edit'])
    <a href="{{ route('admin.' . $thisRoute . '.edit', ['parent_id' => $parentId, $thisRoute => $query->{$masterId}]) }}" class="mb-1 btn btn-primary btn-sm"
       title="@lang('general.edit')">
        <i class="fa fa-pencil"></i>
        <span class="d-none d-md-inline"> @lang('general.edit')</span>
    </a>
@endif
@if ($permission['destroy'])
    <a href="#" class="mb-1 btn btn-danger btn-sm" title="@lang('general.delete')"
       onclick="return actionData('{{ route('admin.' . $thisRoute . '.destroy', ['parent_id' => $parentId, $thisRoute => $query->{$masterId}]) }}', 'delete')">
        <i class="fa fa-trash"></i>
        <span class="d-none d-md-inline"> @lang('general.delete')</span>
    </a>
@endif
