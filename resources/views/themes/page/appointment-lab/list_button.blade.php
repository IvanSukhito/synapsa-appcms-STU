@if ($permission['show'])
    <a href="{{ route('admin.' . $thisRoute . '.show', $query->{$masterId}) }}" class="mb-1 btn btn-info btn-sm"
       title="@lang('general.show')">
        <i class="fa fa-eye"></i>
        <span class="d-none d-md-inline"> @lang('general.show')</span>
    </a>
@endif
@if ($permission['edit'] && in_array($query->status, [1]))
    <a href="{{ route('admin.' . $thisRoute . '.approve', $query->{$masterId}) }}" class="mb-1 btn btn-success btn-sm"
       title="@lang('general.approve')">
        <i class="fa fa-check"></i>
        <span class="d-none d-md-inline"> @lang('general.approve')</span>
    </a>
    <a href="{{ route('admin.' . $thisRoute . '.reject', $query->{$masterId}) }}"  onclick="return askingReject(this)"  class="mb-1 btn btn-danger btn-sm"
       title="@lang('general.reject')">
        <i class="fa fa-ban"></i>
        <span class="d-none d-md-inline"> @lang('general.reject')</span>
    </a>
@endif
@if ($permission['edit'] && in_array($query->status, [4]))
    <a href="{{ route('admin.' . $thisRoute . '.uploadHasilLab', $query->{$masterId}) }}" class="mb-1 btn btn-primary btn-sm"
       title="@lang('general.upload_hasil_lab')">
        <i class="fa fa-file-pdf-o"></i>
        <span class="d-none d-md-inline"> @lang('general.upload_hasil_lab')</span>
    </a>
@endif
@if ($permission['destroy'])
    <a href="#" class="mb-1 btn btn-danger btn-sm" title="@lang('general.delete')"
       onclick="return actionData('{{ route('admin.' . $thisRoute . '.destroy', $query->{$masterId}) }}', 'delete')">
        <i class="fa fa-trash"></i>
        <span class="d-none d-md-inline"> @lang('general.delete')</span>
    </a>
@endif
