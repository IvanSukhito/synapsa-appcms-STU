@if ($permission['show'])
    <a href="{{ route('admin.' . $thisRoute . '.show', $query->{$masterId}) }}" class="mb-1 btn btn-info btn-sm"
       title="@lang('general.show')">
        <i class="fa fa-eye"></i>
        <span class="d-none d-md-inline"> @lang('general.show')</span>
    </a>
@endif
        @if ($permission['edit'])
            <a href="{{ route('admin.' . $thisRoute . '.edit', $query->{$masterId}) }}"class="mb-1 btn btn-primary btn-sm"
               title="@lang('general.edit_status')">
                <i class="fa fa-pencil"></i>
                <span class="d-none d-md-inline"> @lang('general.edit_status')</span>
            </a>
        @endif
{{--@if ($permission['edit'] && in_array($query->status, [1, 2]))--}}
{{--    <a href="{{ route('admin.' . $thisRoute . '.approve', $query->{$masterId}) }}" class="mb-1 btn btn-success btn-sm"--}}
{{--       title="@lang('general.approve')">--}}
{{--        <i class="fa fa-check"></i>--}}
{{--        <span class="d-none d-md-inline"> @lang('general.approve')</span>--}}
{{--    </a>--}}
{{--    <a href="{{ route('admin.' . $thisRoute . '.reject', $query->{$masterId}) }}"  onclick="return askingReject(this)"  class="mb-1 btn btn-danger btn-sm"--}}
{{--       title="@lang('general.reject')">--}}
{{--        <i class="fa fa-ban"></i>--}}
{{--        <span class="d-none d-md-inline"> @lang('general.reject')</span>--}}
{{--    </a>--}}
{{--@endif--}}
@if(in_array($query->status,[1,80,99]))
@if ($permission['destroy'])
    <a href="#" class="mb-1 btn btn-danger btn-sm" title="@lang('general.void')"
       onclick="return actionData('{{ route('admin.' . $thisRoute . '.destroy', $query->{$masterId}) }}', 'delete')">
        <i class="fa fa-trash"></i>
        <span class="d-none d-md-inline"> @lang('general.void')</span>
    </a>
@endif
@endif
