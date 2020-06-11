<?php

if (! function_exists('e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param string $value
     * @param bool $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('encode_or_null')) {
    /**
     * Encode HTML special characters in a string, but preserve a null value if the passed input equals null.
     *
     * @param string $value
     * @param bool $doubleEncode
     * @return string
     */
    function encode_or_null($value, $doubleEncode = true)
    {
        return is_null($value) ? null : e($value, $doubleEncode);
    }
}

if (! function_exists('bb_asset')) {
    /**
     * Return the public path of a Bapubuilder asset.
     *
     * @param string $path
     * @return string
     */
    function bb_asset($path)
    {
        return bb_full_url(config('general.assets_url') . '/' . $path);
    }
}

if (! function_exists('bb_theme_asset')) {
    /**
     * Return the public path of an asset of the current theme.
     *
     * @param string $path
     * @return string
     */
    function bb_theme_asset($path)
    {
        $themeFolder = config('theme.folder_url') . '/' . config('theme.active_theme');
        return $themeFolder . '/' . $path;
    }
}

if (! function_exists('bb_flash')) {
    /**
     * Return the flash data with the given key (as dot-separated multidimensional array selector) or false if not set.
     *
     * @param $key
     * @param bool $encode
     * @return bool|mixed
     */
    function bb_flash($key, $encode = true)
    {
        global $bb_flash;

        // if no dot notation is used, return first dimension value or empty string
        if (strpos($key, '.') === false) {
            if (! isset($bb_flash[$key])) {
                return false;
            }
            return $encode ? e($bb_flash[$key]) : $bb_flash[$key];
        }

        // if dot notation is used, traverse config string
        $segments = explode('.', $key);
        $subArray = $bb_flash;
        foreach ($segments as $segment) {
            if (isset($subArray[$segment])) {
                $subArray = &$subArray[$segment];
            } else {
                return false;
            }
        }

        // if the remaining sub array is a string, return this piece of flash data
        if (is_string($subArray)) {
            if ($encode) {
                return e($subArray);
            }
            return $subArray;
        }
        return false;
    }
}

if (! function_exists('config')) {
    /**
     * Return the configuration with the given key (as dot-separated multidimensional array selector).
     *
     * @param string $key
     * @return mixed
     */
    function config($key)
    {
        global $config;

        // if no dot notation is used, return first dimension value or empty string
        if (strpos($key, '.') === false) {
            return $config[$key] ?? '';
        }

        // if dot notation is used, traverse config string
        $segments = explode('.', $key);
        $subArray = $config;
        foreach ($segments as $segment) {
            if (isset($subArray[$segment])) {
                $subArray = &$subArray[$segment];
            } else {
                return '';
            }
        }

        return $subArray;
    }
}

if (! function_exists('bb_trans')) {
    /**
     * Return the translation of the given key (as dot-separated multidimensional array selector).
     *
     * @param $key
     * @return string
     */
    function bb_trans($key)
    {
        global $bb_translations;

        // if no dot notation is used, return first dimension value or empty string
        if (strpos($key, '.') === false) {
            return $bb_translations[$key] ?? '';
        }

        // if dot notation is used, traverse translations string
        $segments = explode('.', $key);
        $subArray = $bb_translations;
        foreach ($segments as $segment) {
            if (isset($subArray[$segment])) {
                $subArray = &$subArray[$segment];
            } else {
                return '';
            }
        }

        // if the remaining sub array is a non-empty string/array, return this translation or translations structure
        if (! empty($subArray)) {
            return $subArray;
        }
        return '';
    }
}

if (! function_exists('bb_full_url')) {
    /**
     * Give the full URL of a given URL which is relative to the base URL.
     *
     * @param string $urlRelativeToBaseUrl
     * @return string
     */
    function bb_full_url($urlRelativeToBaseUrl)
    {
        // if the URL is already a full URL, do not alter the URL
        if (strpos($urlRelativeToBaseUrl, 'http://') === 0 || strpos($urlRelativeToBaseUrl, 'https://') === 0) {
            return $urlRelativeToBaseUrl;
        }

        $baseUrl = config('general.base_url');
        return rtrim($baseUrl, '/') . $urlRelativeToBaseUrl;
    }
}

if (! function_exists('bb_url')) {
    /**
     * Give the full URL of a given public path.
     *
     * @param string $module
     * @param array $parameters
     * @param bool $fullUrl
     * @return string
     */
    function bb_url($module, array $parameters = [], $fullUrl = true)
    {
        $url = $fullUrl ? bb_full_url('') : '';
        $url .= config($module . '.url');

        if (! empty($parameters)) {
            $url .= '?';
            $pairs = [];
            foreach ($parameters as $key => $value) {
                $pairs[] = e($key) . '=' . e($value);
            }
            $url .= implode('&', $pairs);
        }

        return $url;
    }
}

if (! function_exists('bb_current_full_url')) {
    /**
     * Give the current full URL.
     *
     * @return string
     */
    function bb_current_full_url()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $port = '';
        if (! in_array($_SERVER['SERVER_PORT'], [80, 443])) {
            $port = ":" . $_SERVER['SERVER_PORT'];
        }
        $currentFullUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . urldecode($_SERVER['REQUEST_URI']);
        $currentFullUrl = rtrim($currentFullUrl, '/' . DIRECTORY_SEPARATOR);
        return $currentFullUrl;
    }
}

if (! function_exists('bb_current_relative_url')) {
    /**
     * Give the current URL relative to the base directory (the website's index.php entry point).
     * This omits any parent directories from the URL in which the project is installed.
     *
     * @return string
     */
    function bb_current_relative_url()
    {
        $baseUrl = config('general.base_url');
        $baseUrl = rtrim($baseUrl, '/'. DIRECTORY_SEPARATOR);

        $currentFullUrl = bb_current_full_url();
        $relativeUrl = substr($currentFullUrl, strlen($baseUrl));
        $relativeUrl = ltrim($relativeUrl, '/'. DIRECTORY_SEPARATOR);
        return '/' . $relativeUrl;
    }
}

if (! function_exists('bb_current_language')) {
    /**
     * Give the current language based on the current URL.
     *
     * @return string
     */
    function bb_current_language()
    {
        $urlComponents = explode('/', bb_current_relative_url());
        // remove empty values and reset array key numbering
        $urlComponents = array_values(array_filter($urlComponents));
        if (! empty($urlComponents)) {
            foreach (bb_active_languages() as $languageCode => $languageTranslation) {
                if ($urlComponents[0] === $languageCode) {
                    return $languageCode;
                }
            }
        }
        return config('general.language');
    }
}

if (! function_exists('bb_in_module')) {
    /**
     * Return whether we are currently accessing the given module.
     *
     * @param string $module
     * @return bool
     */
    function bb_in_module($module)
    {
        $url = bb_url($module, [], false);
        $currentUrl = explode('?', bb_current_relative_url(), 2)[0];
        return $currentUrl === $url;
    }
}

if (! function_exists('bb_on_url')) {
    /**
     * Return whether we are currently on the given URL.
     *
     * @param string $module
     * @param array $parameters
     * @return bool
     */
    function bb_on_url($module, array $parameters = [])
    {
        $url = bb_url($module, $parameters, false);
        return bb_current_relative_url() === $url;
    }
}

if (! function_exists('bb_set_in_editmode')) {
    /**
     * Set whether the current page is being load in edit mode (i.e. inside the page builder).
     *
     * @param bool $inEditMode
     */
    function bb_set_in_editmode($inEditMode = true)
    {
        global $bb_in_editmode;

        $bb_in_editmode = $inEditMode;
    }
}

if (! function_exists('bb_in_editmode')) {
    /**
     * Return whether the current page is load in edit mode (i.e. inside the page builder).
     *
     * @return bool
     */
    function bb_in_editmode()
    {
        global $bb_in_editmode;

        return $bb_in_editmode ?? false;
    }
}

if (! function_exists('bb_redirect')) {
    /**
     * Redirect to the given URL with optional session flash data.
     *
     * @param string $url
     * @param array $flashData
     */
    function bb_redirect($url, $flashData = [])
    {
        if (! empty($flashData)) {
            $_SESSION["bb_flash"] = $flashData;
        }

        header('Location: ' . $url);
        exit();
    }
}

if (! function_exists('bb_route_parameters')) {
    /**
     * Return the named route parameters resolved from the current URL.
     *
     * @return array|null
     */
    function bb_route_parameters()
    {
        global $bb_route_parameters;

        return $bb_route_parameters ?? [];
    }
}

if (! function_exists('bb_route_parameter')) {
    /**
     * Return the value of the given named route parameter resolved from the current URL.
     *
     * @param string $parameter
     * @return string|null
     */
    function bb_route_parameter($parameter)
    {
        global $bb_route_parameters;

        return $bb_route_parameters[$parameter] ?? null;
    }
}

if (! function_exists('bb_field_value')) {
    /**
     * Return the posted value or the attribute value of the given instance, or null if no value was found.
     *
     * @param $attribute
     * @param object $instance
     * @return string|null
     */
    function bb_field_value($attribute, $instance = null)
    {
        if (isset($_POST[$attribute])) {
            return encode_or_null($_POST[$attribute]);
        }
        if (isset($instance)) {
            if (method_exists($instance, 'get')) {
                return encode_or_null($instance->get($attribute));
            } else {
                return encode_or_null($instance->$attribute);
            }
        }
        return null;
    }
}

if (! function_exists('bb_active_languages')) {
    /**
     * Return the list of all active languages.
     *
     * @return array
     */
    function bb_active_languages()
    {
        $configLanguageCode = config('general.language');
        $languages = bb_instance('setting')::get('languages') ?? [$configLanguageCode];

        // if the array has numeric indices (which is the default), create a languageCode => languageTranslation structure
        if (array_values($languages) === $languages) {
            $newLanguagesStructure = [];
            foreach ($languages as $languageCode) {
                $newLanguagesStructure[$languageCode] = bb_trans('languages')[$languageCode];
            }
            $languages = $newLanguagesStructure;
        }

        if (! isset($languages[$configLanguageCode])) {
            return $languages;
        }

        // sort languages, starting by the configured language
        $languagesSorted[$configLanguageCode] = $languages[$configLanguageCode];
        foreach ($languages as $languageCode => $languageTranslation) {
            if ($languageCode !== $configLanguageCode) {
                $languagesSorted[$languageCode] = $languageTranslation;
            }
        }
        return $languagesSorted;
    }
}

if (! function_exists('bb_instance')) {
    /**
     * Return an instance of the given class as defined in config, or with the given namespace (which is potentially overridden and mapped to an alternative namespace).
     *
     * @param string $name          the name of the config main section in which the class path is defined
     * @param array $params
     * @return object|null
     */
    function bb_instance(string $name, $params = [])
    {
        if (config($name . '.class')) {
            $className = config($name . '.class');
            return new $className(...$params);
        }
        if (class_exists($name)) {
            if (config('class_replacements.' . $name)) {
                $replacement = config('class_replacements.' . $name);
                return new $replacement(...$params);
            }
            return new $name(...$params);
        }
        return null;
    }
}

if (! function_exists('bb_autoload')) {
    /**
     * Autoload classes from the Bapubuilder package.
     *
     * @param  string $className
     */
    function bb_autoload($className)
    {
        // PSR-0 autoloader
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        // remove leading Bapubuilder/ from the class path
        $fileName = str_replace('Bapubuilder' . DIRECTORY_SEPARATOR, '', $fileName);

        // include class files starting in the src directory
        require __DIR__ . '/../' . $fileName;
    }
}
