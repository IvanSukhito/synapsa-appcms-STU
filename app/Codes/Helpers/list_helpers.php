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

if ( ! function_exists('get_list_status_article')) {
    function get_list_status_article()
    {
        return [
            1 => __('general.draft'),
            80 => __('general.publish')
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
            5 => __('Lab Telemed'),
            6 => __('Lab HomeCare'),
            7 => __('Lab Visit'),
        ];
    }
}

if ( ! function_exists('get_list_transaction')) {
    function get_list_transaction()
    {
        return [
            1 => __('general.pending_payment'),
            80 => __('general.complete'),
            90 => __('general.void'),
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

if ( ! function_exists('get_list_type_service')) {
    function get_list_type_service()
    {
        return [
            1 => __('general.no_address'),
            2 => __('general.need_address')
        ];
    }
}

if ( ! function_exists('get_list_service_payment')) {
    function get_list_service_payment()
    {
        return [
            'xendit' => __('Xendit')
        ];
    }
}

if ( ! function_exists('get_list_type_payment')) {
    function get_list_type_payment()
    {
        return [
            'va_bca' => __('Virtual Account BCA'),
            'va_bni' => __('Virtual Account BNI'),
            'ew_ovo' => __('E-Wallet OVO'),
            'ew_dana' => __('E-Wallet DANA'),
            'ew_linkaja' => __('E-Wallet LINKAJA'),
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

