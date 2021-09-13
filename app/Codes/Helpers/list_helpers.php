<?php
if ( ! function_exists('get_list_active_inactive')) {
    function get_list_active_inactive()
    {
        return [
            80 => __('general.active'),
            99 => __('general.inactive')
        ];
    }
}

if ( ! function_exists('get_list_stock_flag')) {
    function get_list_stock_flag()
    {
        return [
            1 => __('general.unlimited'),
            2 => __('general.limited')
        ];
    }
}


if ( ! function_exists('get_list_gender')) {
    function get_list_gender()
    {
        return [
            1 => __('general.pria'),
            2 => __('general.wanita')
        ];
    }
}

if ( ! function_exists('get_list_book')) {
    function get_list_book()
    {
        return [
            80 => __('Available'),
            99 => __('Book')
        ];
    }
}


if ( ! function_exists('get_list_type_transaction')) {
    function get_list_type_transaction()
    {
        return [
            1 => __('Product'),
            2 => __('Doctor Telemed'),
            3 => __('Doctor HomeCare'),
            4 => __('Doctor Visit'),
        ];
    }
}

if ( ! function_exists('get_list_transaction')) {
    function get_list_transaction()
    {
        return [
            1 => __('general.pending'),
            80 => __('general.success'),
            99 => __('general.cancel'),
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

if ( ! function_exists('get_list_recommended_for')) {
    function get_list_recommended_for()
    {
        return [
            'Pria' => __('Pria'),
            'Wanita' => __('Wanita'),
            'Lansia' => __('Lansia'),
            'Anak-anak' => __('Anak-anak'),
        ];
    }
}

if ( ! function_exists('get_list_gender')) {
    function get_list_gender()
    {
        return [
            1 => __('Pria'),
            2 => __('Wanita')
        ];
    }
}

if ( ! function_exists('get_list_order_status')) {
    function get_list_order_status()
    {
        return [
            1 => __('general.not_complete'),
            2 => __('general.waiting'),
            3 => __('general.driver_assign'),
            4 => __('general.driver_pickup'),
            5 => __('general.lab_receive'),
            80 => __('general.complete'),
            81 => __('general.billing'),
            99 => __('general.void')
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

if ( ! function_exists('get_list_availabe')) {
    function get_list_availabe()
    {
        return [
            80 => __('general.available'),
            99 => __('general.unavailable')
        ];
    }
}

if ( ! function_exists('get_list_day')) {
    function get_list_day()
    {
        return [
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
            7 => __('Sunday'),
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

if ( ! function_exists('get_list_show_hide')) {
    function get_list_show_hide()
    {
        return [
            1 => __('general.hide'),
            2 => __('general.show')
        ];
    }
}

