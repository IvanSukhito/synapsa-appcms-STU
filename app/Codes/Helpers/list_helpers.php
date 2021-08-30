<?php
if ( ! function_exists('get_list_active_inactive')) {
    function get_list_active_inactive()
    {
        return [
            1 => __('general.active'),
            2 => __('general.inactive')
        ];
    }
}

if ( ! function_exists('get_list_lang')) {
    function get_list_lang()
    {
        return [
            'en' => __('en'),
            'id' => __('id')
        ];
    }
}

if ( ! function_exists('get_list_gender')) {
    function get_list_gender()
    {
        return [
            'Pria' => __('Pria'),
            'Wanita' => __('Wanita')
        ];
    }
}

if ( ! function_exists('get_list_condition')) {
    function get_list_condition()
    {
        return [
            1 => __('general.occupied'),
            2 => __('general.rent'),
            3 => __('general.empty')
        ];
    }
}

if ( ! function_exists('get_list_privilege')) {
    function get_list_privilege()
    {
        return [
            0 => __('general.pending'),
            1 => __('general.active')
        ];
    }
}

if ( ! function_exists('get_list_billing_status')) {
    function get_list_billing_status()
    {
        return [
            1 => __('general.not_paid'),
            2 => __('general.waiting'),
            3 => __('general.need_update'),
            4 => __('general.due_date'),
            5 => __('general.confirm'),
            9 => __('general.void')
        ];
    }
}

if ( ! function_exists('get_list_read')) {
    function get_list_read()
    {
        return [
            1 => __('general.unread'),
            2 => __('general.read')
        ];
    }
}

if ( ! function_exists('get_list_notification')) {
    function get_list_notification()
    {
        return [
            1 => __('general.asking_pending'),
            2 => __('general.house_monthly_fees'),
            3 => __('general.house_inbox'),
            4 => __('general.house_report'),
            5 => __('general.security_reply_report'),
            6 => __('general.security_report'),
            7 => __('general.house_emergency_report'),
            8 => __('general.security_emergency_report'),
            9 => __('general.organization_emergency_report'),
            10 => __('general.organization_billing'),
            11 => __('general.organization_confirm_monthly_fees')
        ];
    }
}

if ( ! function_exists('get_list_month')) {
    function get_list_month()
    {
        return [
            1 => __('general.january'),
            2 => __('general.february'),
            3 => __('general.march'),
            4 => __('general.april'),
            5 => __('general.mei'),
            6 => __('general.june'),
            7 => __('general.juli'),
            8 => __('general.augustus'),
            9 => __('general.september'),
            10 => __('general.october'),
            11 => __('general.november'),
            12 => __('general.december')
        ];
    }
}

if ( ! function_exists('get_list_month_data')) {
    function get_list_month_data($month)
    {
        $list_month = get_list_month();
        $month = intval($month);
        return isset($list_month[$month]) ? $list_month[$month] : '';
    }
}

if ( ! function_exists('get_list_data')) {
    function get_list_data($getList)
    {
        $result = [];
        foreach ($getList as $key => $val) {
            $result[] = [
                'id' => $key,
                'name' => $val
            ];
        }
        return $result;
    }
}

if ( ! function_exists('get_list_year')) {
    function get_list_year()
    {
        return [
            date('Y', strtotime('-2 year')) => date('Y', strtotime('-2 year')),
            date('Y', strtotime('-1 year')) => date('Y', strtotime('-1 year')),
            date('Y', strtotime("now")) => date('Y', strtotime("now")),
            date('Y', strtotime('+1 year')) => date('Y', strtotime('+1 year'))
        ];
    }
}




if ( ! function_exists('get_list_show_hide')) {
    function get_list_show_hide()
    {
        return [
            1 => __('general.hide'),
            2 => __('general.show')
        ];
    }
}

if ( ! function_exists('get_list_position')) {
    function get_list_position()
    {
        return [
            1 => __('general.resident'),
            2 => __('general.organization')
        ];
    }
}

if ( ! function_exists('get_list_householder')) {
    function get_list_householder()
    {
        return [
            0 => __('general.resident'),
            1 => __('general.householder'),
        ];
    }
}

if ( ! function_exists('get_list_house_condition')) {
    function get_list_house_condition()
    {
        return [
            1 => __('general.ok'),
            2 => __('general.rent'),
            3 => __('general.empty'),
            9 => __('general.problem')
        ];
    }
}

if ( ! function_exists('get_list_house_status')) {
    function get_list_house_status()
    {
        return [
            1 => __('general.active'),
            2 => __('general.inactive'),
        ];
    }
}

if ( ! function_exists('get_list_type_report')) {
    function get_list_type_report()
    {
        return [
            1 => __('general.report'),
            2 => __('general.emergency')
        ];
    }
}

if ( ! function_exists('get_list_report_status')) {
    function get_list_report_status()
    {
        return [
            1 => __('general.waiting'),
            2 => __('general.process'),
            3 => __('general.waiting_approve'),
            5 => __('general.done'),
            9 => __('general.cancel'),
        ];
    }
}

if ( ! function_exists('get_list_report_status2')) {
    function get_list_report_status2()
    {
        return [
            2 => __('general.process'),
            3 => __('general.waiting_approve'),
        ];
    }
}

if ( ! function_exists('get_list_report_status_user_edit')) {
    function get_list_report_status_user_edit()
    {
        return [
            2 => __('general.process'),
            5 => __('general.done')
        ];
    }
}

if ( ! function_exists('get_list_shift_data_status')) {
    function get_list_shift_data_status()
    {
        return [
            1 => __('general.normal'),
            2 => __('general.full_day'),
            3 => __('general.day_off')
        ];
    }
}
