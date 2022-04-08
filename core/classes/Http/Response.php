<?php

class Response {

    public static function make(Request $request): Response {
        return new Response($request);
    }

    public function send(): void {
        extract(Container::get()->asArray(), EXTR_OVERWRITE);

        if (!isset($GLOBALS['config']['core']) && is_file(ROOT_PATH . '/install.php')) {
            Redirect::to('install.php');
        }

        // Get page to load from URL
        if (!isset($_GET['route']) || $_GET['route'] == '/') {
            if (((!isset($_GET['route']) || ($_GET['route'] != '/')) && count($directories) > 1)) {
                require(ROOT_PATH . '/404.php');
            } else {
                // Homepage
                $pages->setActivePage($pages->getPageByURL('/'));
                require(ROOT_PATH . '/modules/Core/pages/index.php');
            }
            die();
        }

        $route = rtrim(strtok($_GET['route'], '?'), '/');

        $all_pages = $pages->returnPages();

        if (array_key_exists($route, $all_pages)) {
            $pages->setActivePage($all_pages[$route]);
            if (isset($all_pages[$route]['custom'])) {
                require(implode(DIRECTORY_SEPARATOR, [ROOT_PATH, 'modules', 'Core', 'pages', 'custom.php']));
                die();
            }

            $path = implode(DIRECTORY_SEPARATOR, [ROOT_PATH, 'modules', $all_pages[$route]['module'], $all_pages[$route]['file']]);

            if (file_exists($path)) {
                require($path);
                die();
            }
        } else {
            // Use recursion to check - might have URL parameters in path
            $path_array = explode('/', $route);

            for ($i = count($path_array) - 2; $i > 0; $i--) {

                $new_path = '/';
                for ($n = 1; $n <= $i; $n++) {
                    $new_path .= $path_array[$n] . '/';
                }

                $new_path = rtrim($new_path, '/');

                if (array_key_exists($new_path, $all_pages)) {
                    $path = implode(DIRECTORY_SEPARATOR, [ROOT_PATH, 'modules', $all_pages[$new_path]['module'], $all_pages[$new_path]['file']]);

                    if (file_exists($path)) {
                        $pages->setActivePage($all_pages[$new_path]);
                        require($path);
                        die();
                    }
                }
            }
        }

        require(ROOT_PATH . '/404.php');
    }

}
