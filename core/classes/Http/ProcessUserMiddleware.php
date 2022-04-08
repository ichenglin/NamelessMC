<?php

class ProcessUserMiddleware extends Middleware {

    public function handle(Request $request): void {
        $user = $request->user();
        $language = Container::get()->make(Language::class);
        $queries = Container::get()->make(Queries::class);
        $cache = Container::get()->make(Cache::class);

        $user->isLoggedIn()
            ? $this->handleLoggedInUser($user, $language, $queries)
            : $this->handleGuest($queries);

        // Dark mode
        $cache->setCache('template_settings');
        $darkMode = $cache->isCached('darkMode') ? $cache->retrieve('darkMode') : '0';
        if ($user->isLoggedIn()) {
            $darkMode = $user->data()->night_mode ?? $darkMode;
        } else if (Cookie::exists('night_mode')) {
            $darkMode = Cookie::get('night_mode');
        }

        define('DARK_MODE', $darkMode);
    }

    private function handleLoggedInUser(User $user, Language $language, Queries $queries): void {
        // Ensure a user is not banned
        if ($user->data()->isbanned == 1) {
            $user->logout();
            Session::flash('home_error', $language->get('user', 'you_have_been_banned'));
            Redirect::to(URL::build('/'));
        }

        // Get IP
        $ip = $user->getIP();

        // Is the IP address banned?
        $ip_bans = $queries->getWhere('ip_bans', ['ip', '=', $ip]);
        if (count($ip_bans)) {
            $user->logout();
            Session::flash('home_error', $language->get('user', 'you_have_been_banned'));
            Redirect::to(URL::build('/'));
        }

        // Update user last IP and last online
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $user->update([
                'last_online' => date('U'),
                'lastip' => $ip
            ]);
        } else {
            $user->update([
                'last_online' => date('U')
            ]);
        }

        // Insert it into the logs
        $user_ip_logged = $queries->getWhere('users_ips', ['ip', '=', $ip]);
        if (!count($user_ip_logged)) {
            // Create the entry now
            $queries->create('users_ips', [
                'user_id' => $user->data()->id,
                'ip' => $ip
            ]);
        } else if (count($user_ip_logged) > 1) {
            foreach ($user_ip_logged as $user_ip) {
                // Check to see if it's been logged by the current user
                if ($user_ip->user_id == $user->data()->id) {
                    // Already logged for this user
                    $already_logged = true;
                    break;
                }
            }

            if (!isset($already_logged)) {
                // Not yet logged, do so now
                $queries->create('users_ips', [
                    'user_id' => $user->data()->id,
                    'ip' => $ip
                ]);
            }
        } else if ($user_ip_logged[0]->user_id != $user->data()->id) {
            $queries->create('users_ips', [
                'user_id' => $user->data()->id,
                'ip' => $ip
            ]);
        }

        // Does their group have TFA forced?
        $forced = false;
        foreach ($user->getGroups() as $group) {
            if ($group->force_tfa) {
                $forced = true;
                break;
            }
        }

        if ($forced) {
            // Do they have TFA configured?
            if (!$user->data()->tfa_enabled && rtrim($_GET['route'], '/') != '/logout') {
                if (!str_contains($_SERVER['REQUEST_URI'], 'do=enable_tfa')) {
                    Session::put('force_tfa_alert', $language->get('admin', 'force_tfa_alert'));
                    Redirect::to(URL::build('/user/settings', 'do=enable_tfa'));
                }
            }
        }

        $smarty = Container::get()->make(Smarty::class);
        // Basic user variables
        $smarty->assign('LOGGED_IN_USER', [
            'username' => $user->getDisplayname(true),
            'nickname' => $user->getDisplayname(),
            'profile' => $user->getProfileURL(),
            'panel_profile' => URL::build('/panel/user/' . urlencode($user->data()->id) . '-' . urlencode($user->data()->username)),
            'username_style' => $user->getGroupClass(),
            'user_title' => Output::getClean($user->data()->user_title),
            'avatar' => $user->getAvatar(),
            'uuid' => Output::getClean($user->data()->uuid)
        ]);

        // Panel access?
        if ($user->canViewStaffCP()) {
            $smarty->assign([
                'PANEL_LINK' => URL::build('/panel'),
                'PANEL' => $language->get('moderator', 'staff_cp')
            ]);
        }
    }

    private function handleGuest(Queries $queries): void {
        // Perform tasks for guests
        if (!$_SESSION['checked'] || ($_SESSION['checked'] <= strtotime('-5 minutes'))) {
            $already_online = $queries->getWhere('online_guests', ['ip', '=', $ip]);

            $date = date('U');

            if (count($already_online)) {
                $queries->update('online_guests', $already_online[0]->id, ['last_seen' => $date]);
            } else {
                $queries->create('online_guests', ['ip' => $ip, 'last_seen' => $date]);
            }

            $_SESSION['checked'] = $date;
        }
    }
}
