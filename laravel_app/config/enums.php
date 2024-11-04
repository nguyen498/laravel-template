<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020-08-18
 * Time: 13:13
 */
return [
    'code_length' => [
        'user' => 10,
        'employee' => 10,
    ],
    'notification' => [
        'prefix' => 'NOTI',
        'retry' => 3,
        'status' => [
            0 => 'Nháp',
            1 => 'Chuẩn bị gửi',
            2 => 'Đã gửi'
        ],
        'type' => [
            1 => 'Gửi tất cả',
            2 => 'Gửi người dùng'
        ],
        'send_type' => [
            1 => 'Gửi một lần',
            2 => 'Gửi theo ngày',
            3 => 'Gửi theo tuần',
            4 => 'Gửi theo tháng'
        ],
    ],
    'user_inbox' => [
        'type' => [
            1 => 'Xác nhận hành trình',
            2 => 'Tài xế đã đến',
            3 => 'Kết thúc hành trình',
            4 => 'Bảo hiểm hành trình',
            10 => 'Kích hoạt tài khoản',
            11 => 'Hủy tài khoản',
            12 => 'Nạp tiền vào tài khoản',
            13 => 'Hồ sơ không hợp lệ',
            14 => 'Đã tắt chức năng nhận cuốc',
            15 => 'Phí dịch vụ',
            20 => 'Thông báo hệ thống',
            100 => 'Room chat hành trình',
            101 => 'Chat trip',
            102 => 'Chat support',
        ],
        'read_status' => [
            1 => 'Chưa đọc',
            2 => 'Đã đọc',
        ]
    ]
];
