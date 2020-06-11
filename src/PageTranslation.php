<?php

namespace Thecodebunny\Bapubuilder;

use Thecodebunny\Bapubuilder\Contracts\PageContract;
use Thecodebunny\Bapubuilder\Contracts\PageTranslationContract;
use Thecodebunny\Bapubuilder\Repositories\PageRepository;

class PageTranslation implements PageTranslationContract
{
    /**
     * Return the page this translation belongs to.
     *
     * @return PageContract
     */
    public function getPage()
    {
        return (new PageRepository)->findWithId($this->page_id);
    }
}
