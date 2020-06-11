<?php

namespace Thecodebunny\Bapubuilder\Modules\GrapesJS\Block;

use Thecodebunny\Bapubuilder\ThemeBlock;

class BaseModel
{
    /**
     * @var ThemeBlock $block
     */
    protected $block;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var bool $forPageBuilder
     */
    protected $forPageBuilder;

    /**
     * Construct a new model instance.
     *
     * @param ThemeBlock $block
     * @param array $data
     * @param bool $forPageBuilder
     */
    public function __construct(ThemeBlock $block, $data = [], $forPageBuilder = false)
    {
        $this->block = $block;
        $this->data = is_array($data) ? $data : [];
        $this->forPageBuilder = $forPageBuilder;

        if (bb_in_editmode() && method_exists($this, 'initEdit')) {
            $this->initEdit();
        } else {
            $this->init();
        }
    }

    /**
     * Initialize the model.
     */
    protected function init()
    {
    }

    /**
     * Return the given setting stored for this block instance using the page builder.
     *
     * @param $setting
     * @param bool $allowHtml
     * @return string
     */
    public function setting($setting, $allowHtml = false)
    {
        $value = $this->block->get('settings.' . $setting . '.value');

        if (isset($this->data['settings']['attributes'][$setting])) {
            $value = $this->data['settings']['attributes'][$setting];
        }

        return $allowHtml ? $value : e($value);
    }

    /**
     * Return data of this block, passed as argument by a parent block.
     *
     * @param $key
     * @return string
     */
    public function data($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Return data of the child block with the given relative ID.
     *
     * @param $childBlockId
     * @return string
     */
    public function childData($childBlockId)
    {
        return $this->data['blocks'][$childBlockId] ?? null;
    }

}
