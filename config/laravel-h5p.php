<?php

/*
 *
 * @Project        laravel-h5p
 * @Copyright      leechanrin
 * @Created        2017-03-20 오후 5:00:58
 * @Filename       h5p.php
 * @Description
 *
 */

return [
    'H5P_DEV'                           => FALSE,
    'language'                          => 'en',
    'domain'                            => 'http://localhost',
    'h5p_public_path'                   => '/vendor',
    'slug'                              => 'laravel-h5p',
    'views'                             => 'h5p', // h5p view path
    'layout'                            => 'h5p.layouts.h5p', // layoute path
    'use_router'                        => 'ALL', // ALL,EXPORT,EDITOR

    'H5P_DISABLE_AGGREGATION'           => FALSE,

    // 컨텐츠 화면설정
    'h5p_show_display_option'           => TRUE,
    'h5p_frame'                         => TRUE,
    'h5p_export'                        => TRUE,
    'h5p_embed'                         => TRUE,
    'h5p_copyright'                     => FALSE,
    'h5p_icon'                          => TRUE,
    'h5p_track_user'                    => FALSE,
    'h5p_ext_communication'             => TRUE,
    'h5p_save_content_state'            => FALSE,
    'h5p_save_content_frequency'        => 30,
    'h5p_site_key' => [
            'h5p_h5p_site_uuid'         => FALSE
    ],
    'h5p_content_type_cache_updated_at' => 0,
    'h5p_check_h5p_requirements'        => FALSE,
    'h5p_hub_is_enabled'                => FALSE,
    'h5p_version'                       => '1.8.2',
];
