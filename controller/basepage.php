<?php

namespace Controller {

    use Api;
    use Lib;
    use stdClass;

    class BasePage implements Page {

        protected static $renderKeys = [];
        protected static $enabledSources;
        protected static $tests;

        public static function render() {

            self::$tests = new stdClass;

            $user = Api\User::getCurrentUser();
            Lib\Display::addKey('user', $user);

            if ($user instanceof Api\User) {
                Lib\TestBucket::initialize($user->id);
                Lib\Display::addKey('csrfToken', $user->csrfToken);
            }

            Lib\Display::addKey('phpSessionUpload', ini_get("session.upload_progress.name"));

            // Get sources
            $sources = QueryOption::getSources();
            $enabledSources = [];

            // nsfw display flag
            Lib\Display::addKey('showNsfw', Lib\Url::GetBool('showNsfw', $_COOKIE));

            // If there were sources passed on the query string, use those for image fetchery. Fall back on cookies
            $qsSources = Lib\Url::Get('sources', null);
            if ($qsSources) {
                $enabledSources = explode(',', $qsSources);
            } else {
                foreach ($sources as $source) {
                    if ($source->checked) {
                        $enabledSources[] = $source->value;
                    }
                }
            }
            self::$enabledSources = $enabledSources;

            self::addTestToOutput('enableMobile');
            self::addTestToOutput('showRedditControls');

            Lib\Display::addKey('sources', json_encode($sources));
            Lib\Display::addKey('tests', self::$tests);

        }

        protected static function setOgData($title, $image) {
            $ogData = new stdClass;
            $ogData->title = $title;
            $ogData->image = $image;
            $ogData->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            Lib\Display::addKey('ogData', $ogData);
        }

        protected static function addTestToOutput($key) {
            self::$tests->$key = Lib\TestBucket::get($key);
        }

    }

}