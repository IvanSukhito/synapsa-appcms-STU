@extends('mail.base')

@section('content')
    <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="header">
        <tr>
            <td colspan="2" align="center" class="header" bgcolor="#ffed7f" style="padding: 15px;">
                <font color="#e97e84">Halo, {{ $user->fullname }}</font>
            </td>
        </tr>
        <tr>
            <td colspan="2" bgcolor="#ffffff" style="padding-bottom: 75px">
                <table width="400" border="0" align="center" cellpadding="10" cellspacing="0">
                    <form action="{{ route('api.user.updateVerifEmail', ['id' => $user->id]) }}" method="POST">
                        @csrf
                    <tbody>
                    <tr>
                        <td align="center">
                            <p>
                                Kamu baru saja meminta Verifikasi Email, Klik disini untuk verifikasi account kamu :
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="100" align="center">
                            <button type="submit" class="mb-2 mr-2 btn btn-primary" title="@lang('general.verification_email')">
                            <i class="fa fa-save"></i><span class=""> @lang('general.verification_email')</span>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                    </form>
                </table>
            </td>
        </tr>
    </table>
@stop
