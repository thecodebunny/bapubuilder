<?php

return [
    'container' => '#gjs',
    'noticeOnUnload' => false,
    'avoidInlineStyle' => true,
    'allowScripts' => true,
    'storageManager' => [
        'type' => 'remote',
        'autoload' => false,
        'autosave' => false
    ],
    'canvasCss' => 'body {height: auto;}',  // prevent scrollbar jump on pasting in CKEditor
    'assetManager' => [
        'upload' => bb_url('pagebuilder', ['action' => 'upload', 'page' => $page->getId()]),
        'uploadName' => 'files',
        'multiUpload' => false,
        'assets' => $assets
    ],
    'styleManager' => [
        'sectors' => [[
            'id' => 'position',
            'name' => bb_trans('pagebuilder.style-manager.sectors.position'),
            'open' => true,
            'buildProps' => ['width', 'height', 'min-width', 'min-height', 'max-width', 'max-height', 'padding', 'margin']
        ], [
            'id' => 'background',
            'name' => bb_trans('pagebuilder.style-manager.sectors.background'),
            'open' => false,
            'buildProps' => ['background-color', 'background']
        ]]
    ],
    'selectorManager' => [
        'label' => bb_trans('pagebuilder.selector-manager.label'),
        'statesLabel' => bb_trans('pagebuilder.selector-manager.states-label'),
        'selectedLabel' => bb_trans('pagebuilder.selector-manager.selected-label'),
        'states' => [
            ['name' => 'hover', 'label' => bb_trans('pagebuilder.selector-manager.state-hover')],
            ['name' => 'active', 'label' => bb_trans('pagebuilder.selector-manager.state-active')],
            ['name' => 'nth-of-type(2n)', 'label' => bb_trans('pagebuilder.selector-manager.state-nth')]
        ],
    ],
    'traitManager' => [
        'labelPlhText' => '',
        'labelPlhHref' => 'https://website.com'
    ],
    'panels' => [
        'defaults' => [
            [
                'id' => 'views',
                'buttons' => [
                    [
                        'id' => 'open-blocks-button',
                        'className' => 'fa fa-th-large',
                        'command' => 'open-blocks',
                        'togglable' => 0,
                        'attributes' => ['title' => bb_trans('pagebuilder.view-blocks')],
                        'active' => true,
                    ],
                    [
                        'id' => 'open-settings-button',
                        'className' => 'fa fa-cog',
                        'command' => 'open-tm',
                        'togglable' => 0,
                        'attributes' => ['title' => bb_trans('pagebuilder.view-settings')],
                    ],
                    [
                        'id' => 'open-style-button',
                        'className' => 'fa fa-paint-brush',
                        'command' => 'open-sm',
                        'togglable' => 0,
                        'attributes' => ['title' => bb_trans('pagebuilder.view-style-manager')],
                    ]
                ]
            ],
        ]
    ],
    'canvas' => [
        'styles' => [
            bb_asset('pagebuilder/page-injection.css'),
        ],
    ],
    'plugins' => ['grapesjs-touch', 'gjs-plugin-ckeditor'],
    'pluginsOpts' => [
        'gjs-plugin-ckeditor' => [
            'position' => 'left',
            'options' => [
                'startupFocus' => true,
                'extraAllowedContent' => '*(*);*[*]', // allows classes and inline styles
                'enterMode' => 'CKEDITOR.ENTER_BR',
                'extraPlugins' => 'sharedspace, justify, colorbutton, panelbutton',
                'toolbar' => [
                    ['name' => 'styles', 'items' => ['FontSize']],
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['name' => 'links', 'items' => ['Link', 'Unlink']],
                    ['name' => 'colors', 'items' => ['TextColor', 'BGColor']],
                ],
            ]
        ]
    ]
];
