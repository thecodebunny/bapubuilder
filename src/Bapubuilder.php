<?php

namespace Thecodebunny\Bapubuilder;

use Thecodebunny\Bapubuilder\Contracts\PageContract;
use Thecodebunny\Bapubuilder\Contracts\PageTranslationContract;
use Thecodebunny\Bapubuilder\Contracts\PageBuilderContract;
use Thecodebunny\Bapubuilder\Contracts\ThemeContract;
use Thecodebunny\Bapubuilder\Repositories\UploadRepository;
use Thecodebunny\Bapubuilder\Core\DB;

class Bapubuilder
{

    /**
     * @var PageBuilderContract $pageBuilder
     */
    protected $pageBuilder;

    /**
     * @var ThemeContract $theme
     */
    protected $theme;

    /**
     * Bapubuilder constructor.
     *
     * @param array $config         configuration in the format defined in config/config.example.php
     */
    public function __construct(array $config)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // if flash session data is set, set global session flash data and remove data
        if (isset($_SESSION['bb_flash'])) {
            global $bb_flash;
            $bb_flash = $_SESSION['bb_flash'];
            unset($_SESSION['bb_flash']);
        }

        $this->setConfig($config);

        // create database connection, if enabled
        if (config('bapubuilder.storage.use_database')) {
            $this->setDatabaseConnection(config('bapubuilder.storage.database'));
        }

        // init the default page builder, active theme and page router
        $this->pageBuilder = bb_instance('pagebuilder');
        $this->theme = bb_instance('theme', [config('bapubuilder.theme'), config('bapubuilder.theme.active_theme')]);
        $this->router = bb_instance('router');

        // load translations of the configured language
        $this->loadTranslations(config('bapubuilder.general.language'));
    }

    /**
     * Load translations of the given language into a global variable.
     *
     * @param $language
     */
    public function loadTranslations($language)
    {
        if (! $language ) {$language = 'en';}
        global $bb_translations;
        $bb_translations = require dirname(__DIR__) . '/lang/' . $language . '.php';
    }


    /**
     * Set the Bapubuilder configuration to the given array.
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        global $config;
        $config = $config;
    }

    /**
     * Set the Bapubuilder database connection using the given array.
     *
     * @param array $config
     */
    public function setDatabaseConnection(array $config)
    {
        global $bb_db;
        $bb_db = new DB($config);
    }

    /**
     * Set a custom PageBuilder.
     *
     * @param PageBuilderContract $pageBuilder
     */
    public function setPageBuilder(PageBuilderContract $pageBuilder)
    {
        $this->pageBuilder = $pageBuilder;
    }

    /**
     * Set a custom theme.
     *
     * @param ThemeContract $theme
     */
    public function setTheme(ThemeContract $theme)
    {
        $this->theme = $theme;
        if (isset($this->pageBuilder)) {
            $this->pageBuilder->setTheme($theme);
        }
    }


    /**
     * Return the Auth instance of this Bapubuilder.
     *
     * @return AuthContract
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Return the PageBuilder instance of this Bapubuilder.
     *
     * @return PageBuilderContract
     */
    public function getPageBuilder()
    {
        return $this->pageBuilder;
    }

    /**
     * Return the Router instance of this Bapubuilder.
     *
     * @return RouterContract
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Return the Theme instance of this Bapubuilder.
     *
     * @return ThemeContract
     */
    public function getTheme()
    {
        return $this->theme;
    }


    /**
     * Process the current GET or POST request and redirect or render the requested page.
     *
     * @param string|null $action
     * @return bool
     */
    public function handleRequest($action = null)
    {
        $route = $route ?? $_GET['route'] ?? null;
        $action = $action ?? $_GET['action'] ?? null;

        // handle login and logout requests
        $this->auth->handleRequest($action);

        // handle page builder requests
        if (bb_in_module('pagebuilder')) {
            $this->auth->requireAuth();
            bb_set_in_editmode();
            $this->pageBuilder->handleRequest($route, $action);
            die('Page not found');
        }

        // handle all requests that do not need authentication
        if ($this->handlePublicRequest()) {
            return true;
        }

        die('Page not found');
    }

    /**
     * Handle public requests, allowed without any authentication.
     *
     * @return bool
     */
    public function handlePublicRequest()
    {
        // if we are on the URL of an upload, return uploaded file
        if (strpos(bb_current_relative_url(), config('bapubuilder.general.uploads_url') . '/') === 0) {
            $this->handleUploadedFileRequest();
            die('File not found');
        }
        // if we are on the URL of a Bapubuilder asset, return the asset
        if (strpos(bb_current_relative_url(), config('bapubuilder.general.assets_url') . '/') === 0) {
            $this->handlePageBuilderAssetRequest();
            die('Asset not found');
        }

        // let the page router resolve the current URL
        $pageTranslation = $this->router->resolve(bb_current_relative_url());
        if ($pageTranslation instanceof PageTranslationContract) {
            $page = $pageTranslation->getPage();
            $this->pageBuilder->renderPage($page, $pageTranslation->locale);
            return true;
        }
        return false;
    }

    /**
     * Handle authenticated requests, this method assumes you have checked that the user is currently logged in.
     *
     * @param string|null $route
     * @param string|null $action
     */
    public function handleAuthenticatedRequest($route = null, $action = null)
    {
        $route = $route ?? $_GET['route'] ?? null;
        $action = $action ?? $_GET['action'] ?? null;

        // handle page builder requests
        if (bb_in_module('pagebuilder')) {
            bb_set_in_editmode();
            $this->pageBuilder->handleRequest($route, $action);
            die('Page not found');
        }
    }

    /**
     * Handle uploaded file requests.
     */
    public function handleUploadedFileRequest()
    {
        // get the requested file by stripping the configured uploads_url prefix from the current request URI
        $file = substr(bb_current_relative_url(), strlen(config('bapubuilder.general.uploads_url')) + 1);
        // $file is in the format {file id}/{file name}.{file extension}, so get file id as the part before /
        $fileId = explode('/', $file)[0];
        if (empty($fileId)) die('File not found');

        $uploadRepository = new UploadRepository;
        $uploadedFile = $uploadRepository->findWhere('public_id', $fileId);
        if (! $uploadedFile) die('File not found');

        $uploadedFile = $uploadedFile[0];
        $serverFile = realpath(config('bapubuilder.storage.uploads_folder') . '/' . basename($uploadedFile->server_file));
        if (! $serverFile) die('File not found');

        header('Content-Type: ' . $uploadedFile->mime_type);
        header('Content-Disposition: inline; filename="' . basename($uploadedFile->original_file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . filesize($serverFile));

        readfile($serverFile);
        exit();
    }

    /**
     * Handle page builder asset requests.
     */
    public function handlePageBuilderAssetRequest()
    {
        // get asset file path by stripping the configured assets_url prefix from the current request URI
        $asset = substr(bb_current_relative_url(), strlen(config('bapubuilder.general.assets_url')) + 1);

        $distPath = realpath(__DIR__ . '/../dist/');
        $requestedFile = realpath($distPath . '/' . $asset);
        if (! $requestedFile) die('Asset not found');

        // prevent path traversal by ensuring the requested file is inside the dist folder
        if (strpos($requestedFile, $distPath) !== 0) die('Asset not found');

        // only allow specific extensions
        $ext = pathinfo($requestedFile, PATHINFO_EXTENSION);
        if (! in_array($ext, ['js', 'css', 'jpg', 'png'])) die('Asset not found');

        $contentTypes = [
            'js' => 'application/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg'
        ];
        header('Content-Type: ' . $contentTypes[$ext]);
        header('Content-Disposition: inline; filename="' . basename($requestedFile) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . filesize($requestedFile));

        readfile($requestedFile);
        exit();
    }


    /**
     * Render the PageBuilder.
     *
     * @param PageContract $page
     */
    public function renderPageBuilder(PageContract $page)
    {
        bb_set_in_editmode();
        $this->pageBuilder->renderPageBuilder($page);
    }
}
