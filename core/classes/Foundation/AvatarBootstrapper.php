<?php

class AvatarBootstrapper extends Bootstrapper {

    public function run(): void {
        $cache = Container::get()->make(Cache::class);

        // Avatars
        $cache->setCache('avatar_settings_cache');
        if ($cache->isCached('custom_avatars') && $cache->retrieve('custom_avatars') == 1) {
            define('CUSTOM_AVATARS', true);
        }

        if ($cache->isCached('default_avatar_type')) {
            define('DEFAULT_AVATAR_TYPE', $cache->retrieve('default_avatar_type'));
            if (DEFAULT_AVATAR_TYPE == 'custom' && $cache->isCached('default_avatar_image')) {
                define('DEFAULT_AVATAR_IMAGE', $cache->retrieve('default_avatar_image'));
            } else {
                define('DEFAULT_AVATAR_IMAGE', '');
            }
        } else {
            define('DEFAULT_AVATAR_TYPE', 'minecraft');
        }

        if ($cache->isCached('avatar_source')) {
            define('DEFAULT_AVATAR_SOURCE', $cache->retrieve('avatar_source'));
        } else {
            define('DEFAULT_AVATAR_SOURCE', 'cravatar');
        }

        if ($cache->isCached('avatar_perspective')) {
            define('DEFAULT_AVATAR_PERSPECTIVE', $cache->retrieve('avatar_perspective'));
        } else {
            define('DEFAULT_AVATAR_PERSPECTIVE', 'face');
        }
    }
}
