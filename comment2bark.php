<?php
/*
Plugin Name: WordPress Bark通知
Plugin URI: https://github.com/xqa/comment2bark.git
Description: 评论bark通知配置
Version:  1.0
Author: minge
 * $bark_api        bark api地址
 * $bark_key        bark token
 * $bark_icon       bark 推送图标
 * $bark_group      bark 群组
 * $bark_sound      bark 推送声音
 * $bark_archive    bark 自动保存   
 * $filter_author   过滤作者自己的评论
*/

if (!defined('ABSPATH')) exit;


function bark_settings_init()
{
    // 为评论页面注册新设置
    register_setting('discussion', 'bark_settings');

    // 在评论页面上注册新分节
    add_settings_section(
        'bark_settings_section',
        'Bark通知设置',
        function () {
            echo "<p>通过简单配置即可实现有新评论时自动向手机发送通知，配置详见Bark文档</p>";
        },
        'discussion'
    );

    // bark api地址,未设置默认为https://api.day.app
    add_settings_field(
        'bark_settings_api',
        'Bark API地址',
        'bark_settings_api_cb',
        'discussion',
        'bark_settings_section'
    );

    // bark key,必填
    add_settings_field(
        'bark_settings_key',
        'Bark Key',
        'bark_settings_key_cb',
        'discussion',
        'bark_settings_section'
    );

    // bark icon
    add_settings_field(
        'bark_settings_icon',
        'Bark Icon',
        'bark_settings_icon_cb',
        'discussion',
        'bark_settings_section'
    );

    // bark group
    add_settings_field(
        'bark_settings_group',
        'Bark Group',
        'bark_settings_group_cb',
        'discussion',
        'bark_settings_section'
    );

    // bark sound
    add_settings_field(
        'bark_settings_sound',
        'Bark Sound',
        'bark_settings_sound_cb',
        'discussion',
        'bark_settings_section'
    );

    // bark archive
    add_settings_field(
        'bark_settings_archive',
        'Bark Archive',
        'bark_settings_archive_cb',
        'discussion',
        'bark_settings_section'
    );

    // filter author
    add_settings_field(
        'bark_settings_filter_author',
        '过滤作者自己的评论',
        'bark_settings_filter_author_cb',
        'discussion',
        'bark_settings_section'
    );

    // 开关
    add_settings_field(
        'bark_settings_active',
        '开启Bark通知',
        'bark_settings_active_cb',
        'discussion',
        'bark_settings_section'
    );
}


add_action('admin_init', 'bark_settings_init');

function bark_settings_api_cb()
{
    $options = get_option('bark_settings');
    $bark_api = $options['bark_api'] ?? 'https://api.day.app';
    echo "<input id='bark_settings_api' name='bark_settings[bark_api]' type='text' value='{$bark_api}' />";
}

function bark_settings_key_cb()
{
    $options = get_option('bark_settings');
    $bark_key = $options['bark_key'];
    echo "<input id='bark_settings_key' name='bark_settings[bark_key]' type='text' value='{$bark_key}' />";
}

function bark_settings_icon_cb()
{
    $options = get_option('bark_settings');
    $bark_icon = $options['bark_icon'];
    echo "<input id='bark_settings_icon' name='bark_settings[bark_icon]' type='text' value='{$bark_icon}' />";
}

function bark_settings_group_cb()
{
    $options = get_option('bark_settings');
    $bark_group = $options['bark_group'] ?? 'Blog';
    echo "<input id='bark_settings_group' name='bark_settings[bark_group]' type='text' value='{$bark_group}' />";
}

function bark_settings_sound_cb()
{
    $options = get_option('bark_settings');
    $bark_sound = $options['bark_sound'];
    echo "<input id='bark_settings_sound' name='bark_settings[bark_sound]' type='text' value='{$bark_sound}' />";
}

function bark_settings_active_cb()
{
    $options = get_option('bark_settings');
    $bark_active = $options['bark_active'] ?? '';
    echo "<input id='bark_settings_active' name='bark_settings[bark_active]' type='checkbox' value='1' " . checked(1, $bark_active, false) . " />";
}

function bark_settings_archive_cb()
{
    $options = get_option('bark_settings');
    $bark_archive = $options['bark_archive'] ?? '';
    echo "<input id='bark_settings_archive' name='bark_settings[bark_archive]' type='checkbox' value='1' " . checked(1, $bark_archive, false) . " />";
}

function bark_settings_filter_author_cb()
{
    $options = get_option('bark_settings');
    $bark_filter_author = $options['bark_filter_author'] ?? '';
    echo "<input id='bark_settings_filter_author' name='bark_settings[bark_filter_author]' type='checkbox' value='1' " . checked(1, $bark_filter_author, false) . " />";
}


function bark_comment_send($comment_id)
{
    $setting = get_option('bark_settings');
    $comment = get_comment($comment_id);

    if ($setting['bark_active'] != 1) {
        return;
    }

    if (empty($setting['bark_key'])) {
        return;
    }

    // 过滤作者自己的评论
    if (!empty($setting['bark_filter_author'])) {
        if ($setting['bark_filter_author'] == 1 && $comment->user_id == get_current_user_id()) {
            return;
        }
    }


    $url = $setting['bark_api'] ?? 'https://api.day.app';
    $url .= '/push';
    $postData = [
        'device_key' => $setting['bark_key'],
        'title' => '博客有新评论啦！',
        'body' => $comment->comment_content,
        'url' => get_comment_link($comment_id)
    ];
    !empty($setting['bark_icon']) && $postData['icon'] = $setting['bark_icon'];
    !empty($setting['bark_sound']) && $postData['sound'] = $setting['bark_sound'];
    !empty($setting['bark_group']) && $postData['group'] = $setting['bark_group'];
    !empty($setting['bark_archive']) ? $postData['isArchive'] = $setting['bark_archive'] : $postData['isArchive'] = 0;

    $body = json_encode($postData);

    return $response = wp_remote_post($url, array(
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => $body
    ));
}
add_action('comment_post', 'bark_comment_send', 15);
