<?php

namespace Thecodebunny\Bapubuilder\Modules\GrapesJS;

use Thecodebunny\Bapubuilder\Contracts\PageBuilderContract;
use Thecodebunny\Bapubuilder\Contracts\PageContract;
use Thecodebunny\Bapubuilder\Contracts\ThemeContract;
use Thecodebunny\Bapubuilder\Modules\GrapesJS\Block\BlockAdapter;
use Thecodebunny\Bapubuilder\Modules\GrapesJS\Thumb\ThumbGenerator;
use Thecodebunny\Bapubuilder\Modules\GrapesJS\Upload\Uploader;
use Thecodebunny\Bapubuilder\Repositories\PageRepository;
use Thecodebunny\Bapubuilder\Repositories\UploadRepository;
use Exception;

class ProductBuilder implements PageBuilderContract
{
    /**
     * @var ThemeContract $theme
     */
    protected $theme;

    /**
     * @var array $scripts
     */
    protected $scripts = [];

    /**
     * @var string $css
     */
    protected $css;

    /**
     * ProductBuilder constructor.
     */
    public function __construct()
    {
        $this->theme = bb_instance('theme', [config('theme'), config('theme.active_theme')]);
    }

    /**
     * Set the theme used while rendering pages in the page builder.
     *
     * @param ThemeContract $theme
     */
    public function setTheme(ThemeContract $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Process the current GET or POST request and redirect or render the requested page.
     *
     * @param $route
     * @param $action
     * @param PageContract|null $page
     * @return bool
     * @throws Exception
     */
    public function handleRequest($route, $action, PageContract $page = null)
    {
        bb_set_in_editmode();

        if ($route === 'thumb_generator') {
            $thumbGenerator = new ThumbGenerator($this->theme);
            return $thumbGenerator->handleThumbRequest($action);
        }

        if (is_null($page)) {
            $pageId = $_GET['page'] ?? null;
            $pageRepository = new PageRepository;
            $page = $pageRepository->findWithId($pageId);
        }
        if (! ($page instanceof PageContract)) {
            return false;
        }

        switch ($action) {
            case null:
            case 'edit':
                $this->renderPageBuilder($page);
                exit();
                break;
            case 'store':
                if (isset($_POST) && isset($_POST['data'])) {
                    $data = json_decode($_POST['data'], true);
                    $this->updatePage($page, $data);
                    exit();
                }
                break;
            case 'upload':
                if (isset($_FILES)) {
                    $this->handleFileUpload();
                }
                break;
            case 'upload_delete':
                if (isset($_POST['id'])) {
                    $this->handleFileDelete();
                }
                break;
            case 'renderBlock':
                if (isset($_POST['language']) && isset($_POST['data']) && isset(bb_active_languages()[$_POST['language']])) {
                    $this->renderPageBuilderBlock($page, $_POST['language'], json_decode($_POST['data'], true));
                    exit();
                }
                break;
            case 'renderLanguageVariant':
                if (isset($_POST['language']) && isset($_POST['data']) && isset(bb_active_languages()[$_POST['language']])) {
                    $this->renderLanguageVariant($page, $_POST['language'], json_decode($_POST['data'], true));
                    exit();
                }
                break;
        }

        return false;
    }

    /**
     * Handle uploading of the posted file.
     *
     * @throws Exception
     */
    public function handleFileUpload()
    {
        $uploader = new Uploader('files');
        $uploader
            ->file_name(true)
            ->upload_to(config('storage.uploads_folder') . '/')
            ->run();

        if (! $uploader->was_uploaded) {
            die("Upload error: {$uploader->error}");
        } else {
            $originalFile = $uploader->file_src_name;
            $originalMime = $uploader->file_src_mime;
            $serverFile = $uploader->final_file_name;
            $publicId = explode('.', $serverFile)[0];

            $uploadRepository = new UploadRepository;
            $uploadedFile = $uploadRepository->create([
                'public_id' => $publicId,
                'original_file' => $originalFile,
                'mime_type' => $originalMime,
                'server_file' => $serverFile
            ]);

            echo json_encode([
                'data' => [
                    'src' => $uploadedFile->getUrl(),
                    'type' => 'image'
                ]
            ]);
            exit();
        }
    }

    /**
     * Handle deleting of the posted previously uploaded file.
     */
    public function handleFileDelete()
    {
        $uploadRepository = new UploadRepository;
        $uploadedFileResult = $uploadRepository->findWhere('public_id', $_POST['id']);
        if (empty($uploadedFileResult)) {
            echo json_encode([
                'success' => false,
                'message' => 'File not found'
            ]);
            exit();
        }

        $uploadedFile = $uploadedFileResult[0];
        $uploadRepository->destroy($uploadedFile->id);

        $serverFilePath = realpath(config('storage.uploads_folder') . '/' . basename($uploadedFile->server_file));
        if (! $serverFilePath) {
            echo json_encode([
                'success' => false,
                'message' => 'File not found'
            ]);
            exit();
        }
        unlink($serverFilePath);

        echo json_encode([
            'success' => true
        ]);
        exit();
    }

    /**
     * Render the ProductBuilder for the given page.
     *
     * @param PageContract $page
     * @throws Exception
     */
    public function renderPageBuilder(PageContract $page)
    {
        bb_set_in_editmode();

        // init variables that should be accessible in the view
        $pageBuilder = $this;
        $pageRenderer = bb_instance(PageRenderer::class, [$this->theme, $page, true]);

        // create an array of theme blocks and theme block settings for in the page builder sidebar
        $blocks = [];
        $blockSettings = [];
        foreach ($this->theme->getThemeBlocks() as $themeBlock) {
            $slug = e($themeBlock->getSlug());
            $adapter = new BlockAdapter($pageRenderer, $themeBlock);
            $blockSettings[$slug] = $adapter->getBlockSettingsArray();

            if ($themeBlock->get('hidden') !== true) {
                $blocks[$slug] = $adapter->getBlockManagerArray();
            }
        }

        // create an array of all uploaded assets
        $assets = [];
        foreach ((new UploadRepository)->getAll() as $file) {
            $assets[] = [
                'src' => $file->getUrl(),
                'public_id' => $file->public_id
            ];
        }

        require __DIR__ . '/resources/views/layout.php';
    }

    /**
     * Render the given page.
     *
     * @param PageContract $page
     * @param null $language
     * @throws Exception
     */
    public function renderPage(PageContract $page, $language = null)
    {
        $renderer = bb_instance(PageRenderer::class, [$this->theme, $page]);
        if (! is_null($language)) {
            $renderer->setLanguage($language);
        }
        echo $renderer->render();
    }

    /**
     * Render in context of the given page, the given block with the passed settings, for updating the page builder.
     *
     * @param PageContract $page
     * @param string $language
     * @param array $blockData
     * @throws Exception
     */
    public function renderPageBuilderBlock(PageContract $page, string $language, $blockData = [])
    {
        bb_set_in_editmode();

        $blockData = is_array($blockData) ? $blockData : [];
        $page->setData(['data' => $blockData], false);

        $renderer = bb_instance(PageRenderer::class, [$this->theme, $page, true]);
        $renderer->setLanguage($language);
        echo $renderer->parseShortcodes($blockData['html'], $blockData['blocks']);
    }

    /**
     * Render the given page in the given language using the given block data.
     *
     * @param PageContract $page
     * @param string $language
     * @param array $blockData
     * @throws Exception
     */
    public function renderLanguageVariant(PageContract $page, string $language, $blockData = [])
    {
        bb_set_in_editmode();

        $blockData = is_array($blockData) ? $blockData : [];
        $page->setData(['data' => $blockData], false);

        $renderer = bb_instance(PageRenderer::class, [$this->theme, $page, true]);
        $renderer->setLanguage($language);
        echo json_encode([
            'dynamicBlocks' => $renderer->getPageBlocksData()[$language]
        ]);
    }

    /**
     * Update the given page with the given data (an array of html blocks)
     *
     * @param PageContract $page
     * @param $data
     * @return bool|object|null
     */
    public function updatePage(PageContract $page, $data)
    {
        $pageRepository = new PageRepository;
        return $pageRepository->updatePageData($page, $data);
    }

    /**
     * Return the list of all pages, used in CKEditor link editor.
     *
     * @return array
     */
    public function getPages()
    {
        $pages = [];

        $pageRepository = new PageRepository;
        foreach ($pageRepository->getAll() as $page) {
            $pages[] = [
                e($page->getName()),
                e($page->getId())
            ];
        }

        return $pages;
    }

    /**
     * Return this page's components in the format passed to GrapesJS.
     *
     * @param PageContract $page
     * @return array
     */
    public function getPageComponents(PageContract $page)
    {
        $data = $page->getBuilderData();
        if (isset($data['components'])) {
            return $data['components'];
        }
        return [];
    }

    /**
     * Return this page's style in the format passed to GrapesJS.
     *
     * @param PageContract $page
     * @return array
     */
    public function getPageStyleComponents(PageContract $page)
    {
        $data = $page->getBuilderData();
        if (isset($data['style'])) {
            return $data['style'];
        }
        return [];
    }

    /**
     * Get or set custom css for customizing layout of the page builder.
     *
     * @param string|null $css
     * @return string
     */
    public function customStyle(string $css = null)
    {
        if (! is_null($css)) {
            $this->css = $css;
        }
        return $this->css;
    }

    /**
     * Get or set custom scripts for customizing behaviour of the page builder.
     *
     * @param string $location              head|body
     * @param string|null $scripts
     * @return string
     */
    public function customScripts(string $location, string $scripts = null)
    {
        if (! is_null($scripts)) {
            $this->scripts[$location] = $scripts;
        }
        return $this->scripts[$location] ?? '';
    }
}
