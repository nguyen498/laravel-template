<?php
use \App\Models\UserInbox;

return [
    'title' => [
        UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT => [
            'en' => '',
            'vi' => '%s đã trả lời bình luận của bạn'
        ],
        UserInbox::TYPE_COMMENT_TO_POST_OWNER => [
            'en' => '',
            'vi' => '%s đã bình luận bài viết của bạn'
        ]
    ],
    'content' => [
        UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT => [
            'en' => '',
            'vi' => '%s đã trả lời: "%s"'
        ],
        UserInbox::TYPE_COMMENT_TO_POST_OWNER => [
            'en' => '',
            'vi' => '%s đã bình luận: "%s"'
        ]
    ],
];
