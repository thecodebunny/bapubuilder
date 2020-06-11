<div id="phpb-loading">
    <div class="circle">
        <div class="loader">
            <div class="loader">
                <div class="loader">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
        <div class="text">
            <?= bb_trans('pagebuilder.loading-text') ?>
        </div>
    </div>
</div>

<div id="gjs"></div>

<script type="text/javascript" src="https://cdn.ckeditor.com/4.11.4/full-all/ckeditor.js"></script>
<script type="text/javascript" src="<?= bb_asset('pagebuilder/grapesjs-plugin-ckeditor-v0.0.9.min.js') ?>"></script>
<script type="text/javascript" src="<?= bb_asset('pagebuilder/grapesjs-touch-v0.1.1.min.js') ?>"></script>
<script type="text/javascript">
CKEDITOR.dtd.$editable.a = 1;
CKEDITOR.dtd.$editable.b = 1;
CKEDITOR.dtd.$editable.em = 1;
CKEDITOR.dtd.$editable.button = 1;
CKEDITOR.dtd.$editable.strong = 1;
CKEDITOR.dtd.$editable.small = 1;
CKEDITOR.dtd.$editable.span = 1;
CKEDITOR.dtd.$editable.ol = 1;
CKEDITOR.dtd.$editable.ul = 1;
CKEDITOR.dtd.$editable.table = 1;

window.currentLanguage = <?= json_encode(config('general.language')) ?>;
window.languages = <?= json_encode(bb_active_languages()) ?>;
window.translations = <?= json_encode(bb_trans('pagebuilder')) ?>;
window.pageComponents = <?= json_encode($pageBuilder->getPageComponents($page)) ?>;
window.themeBlocks = <?= json_encode($blocks) ?>;
window.blockSettings = <?= json_encode($blockSettings) ?>;
window.pageBlocks = <?= json_encode($pageRenderer->getPageBlocksData()) ?>;
window.pages = <?= json_encode($pageBuilder->getPages()) ?>;
window.renderBlockUrl = '<?= bb_url('pagebuilder', ['action' => 'renderBlock', 'page' => $page->getId()]) ?>';
window.injectionScriptUrl = '<?= bb_asset('pagebuilder/page-injection.js') ?>';
window.renderLanguageVariantUrl = '<?= bb_url('pagebuilder', ['action' => 'renderLanguageVariant', 'page' => $page->getId()]) ?>';

<?php
$config = require __DIR__ . '/grapesjs/config.php';
?>
let config = <?= json_encode($config) ?>;
if (window.customConfig !== undefined) {
    config = $.extend(true, {}, window.customConfig, config);
}

window.initialComponents = <?= json_encode($pageRenderer->render()) ?>;
window.initialStyle = <?= json_encode($pageBuilder->getPageStyleComponents($page)) ?>;
window.grapesJSTranslations = {
    <?= config('general.language') ?>: {
        styleManager: {
            empty: '<?= bb_trans('pagebuilder.style-no-element-selected') ?>'
        },
        traitManager: {
            empty: '<?= bb_trans('pagebuilder.trait-no-element-selected') ?>',
            label: '<?= bb_trans('pagebuilder.trait-settings') ?>',
            traits: {
                options: {
                    target: {
                        false: '<?= bb_trans('pagebuilder.no') ?>',
                        _blank: '<?= bb_trans('pagebuilder.yes') ?>'
                    }
                }
            }
        },
        assetManager: {
            addButton: '<?= bb_trans('pagebuilder.asset-manager.add-image') ?>',
            inputPlh: 'http://path/to/the/image.jpg',
            modalTitle: '<?= bb_trans('pagebuilder.asset-manager.modal-title') ?>',
            uploadTitle: '<?= bb_trans('pagebuilder.asset-manager.drop-files') ?>'
        }
    }
};

window.grapesJSLoaded = false;
window.editor = window.grapesjs.init(config);
window.editor.on('load', function(editor) {
    window.grapesJSLoaded = true;
});
window.editor.I18n.addMessages(window.grapesJSTranslations);

// load the default or earlier saved page css components
editor.setStyle(window.initialStyle);
</script>

<?php
require __DIR__ . '/grapesjs/asset-manager.php';
require __DIR__ . '/grapesjs/component-type-manager.php';
require __DIR__ . '/grapesjs/style-manager.php';
require __DIR__ . '/grapesjs/trait-manager.php';
?>

<button id="toggle-sidebar" class="btn">
    <i class="fa fa-bars"></i>
</button>
<div id="sidebar-header">
    <?php
    if (sizeof(bb_active_languages()) > 1):
    ?>
    <div id="language-selector">
        <select class="selectpicker" data-width="fit">
            <?php
            foreach (bb_active_languages() as $languageCode => $languageTranslation):
            ?>
            <option value="<?= e($languageCode) ?>" <?= config('general.language') === $languageCode ? 'selected' : '' ?>><?= e($languageTranslation) ?></option>
            <?php
            endforeach;
            ?>
        </select>
    </div>
    <?php
    endif;
    ?>
</div>

<div id="sidebar-bottom-buttons">
    <button id="save-page" class="btn" data-url="<?= bb_url('pagebuilder', ['action' => 'store', 'page' => $page->getId()]) ?>">
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        <i class="fa fa-save"></i>
        <?= bb_trans('pagebuilder.save-page') ?>
    </button>

    <a id="view-page" href="<?= e(bb_full_url($page->getRoute())) ?>" target="_blank" class="btn">
        <i class="fa fa-external-link"></i>
        <?= bb_trans('pagebuilder.view-page') ?>
    </a>

    <a id="go-back" href="<?= e(bb_full_url(config('pagebuilder.actions.back'))) ?>" class="btn">
        <i class="fa fa-arrow-circle-left"></i>
        <?= bb_trans('pagebuilder.go-back') ?>
    </a>
</div>

<div id="block-search">
    <i class="fa fa-search"></i>
    <input type="text" class="form-control" placeholder="Filter">
</div>
