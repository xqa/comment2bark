<?php

if ( !class_exists('Comments2bark_Setting' ) ):
class Comments2bark_Setting {

    private $settings_api;

    function __construct() {
        $this->settings_api = new Comments2bark_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action('comment_post', array($this,'bark_comment_send'), 15);
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_options_page( 'Bark通知', 'Bark通知', 'manage_options', 'comments2bark', array($this, 'plugin_page') );
    }


    function bark_comment_send($comment_id){
        $setting = get_option('bark_settings');
        $comment = get_comment($comment_id);


        if ($setting['bark_active'] != 'on') {
            return;
        }

        if (empty($setting['bark_key'])) {
            return;
        }

        // 过滤作者自己的评论
        if ($setting['bark_filter_author'] == 'on' && $comment->user_id == get_current_user_id()) {
            return;
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
        $setting['bark_sound'] != 'default' && $postData['sound'] = $setting['bark_sound'];
        !empty($setting['bark_group']) && $postData['group'] = $setting['bark_group'];
        $setting['bark_archive'] == 'on' && $postData['isArchive'] = 1;


        $body = json_encode($postData);

        return $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => $body
        ));
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'bark_settings',
                'title' => 'Bark通知设置',
            ),
        );
        return $sections;
    }


    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'bark_settings' => array(
                array(
                    'name'              => 'bark_api',
                    'label'             => 'Bark API地址',
                    'type'              => 'text',
                    'default'           => 'https://api.day.app',
                ),
                array(
                    'name'              => 'bark_key',
                    'label'             => 'Bark Key',
                    'type'              => 'text',
                ),
                array(
                    'name'        => 'bark_icon',
                    'label'       => '自定义推送图标',
                    'desc'        => '需IOS15及以上版本支持',
                    'type'        => 'text'
                ),
                array(
                    'name'        => 'bark_group',
                    'label'       => '推送消息分组',
                    'type'        => 'text'
                ),
                array(
                    'name'        => 'bark_sound',
                    'label'       => '推送铃声',
                    'type'    => 'select',
                    'default' => 'default',
                    'options' => array(
                        'default' => '默认',
                        'alarm' => '闹钟',
                        'anticipate' => '预期',
                        'bell' => '铃声',
                        'birdsong' => '鸟鸣',
                        'bloom' => '开花',
                        'calypso' => '加利福尼亚',
                        'chime' => '钟声',
                        'choo' => '火车',
                        'descent' => '下降',
                        'electronic' => '电子',
                        'fanfare' => '庆典',
                        'glass' => '玻璃',
                        'gotosleep' => '睡觉',
                        'healthnotification' => '健康通知',
                        'horn' => '喇叭',
                        'ladder' => '梯子',
                        'mailsent' => '邮件发送',
                        'minuet' => '小步舞曲',
                        'multiwayinvitation' => '多方邀请',
                        'newmail' => '新邮件',
                        'newsflash' => '快讯',
                        'noir' => '黑暗',
                        'paymentsuccess' => '支付成功',
                        'shake' => '摇晃',
                        'sherwoodforest' => '谢尔伍德森林',
                        'silence' => '沉默',
                        'spell' => '拼写',
                        'suspense' => '悬念',
                        'telegraph' => '电报',
                        'tiptoes' => '脚尖',
                        'typewriters' => '打字机',
                        'update' => '更新',
                    )
                ),
                array(
                    'name'        => 'bark_archive',
                    'label'       => '自动保存推送消息',
                    'type'        => 'checkbox'
                ),
                array(
                    'name'        => 'bark_filter_author',
                    'label'       => '过滤作者自己的评论',
                    'type'        => 'checkbox'
                ),
                array(
                    'name'        => 'bark_active',
                    'label'       => '开启Bark通知',
                    'type'        => 'checkbox'
                ),

            ),

        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;
