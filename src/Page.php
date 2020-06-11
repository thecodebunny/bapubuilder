<?php

namespace Thecodebunny\Bapubuilder;

use Thecodebunny\Bapubuilder\Contracts\PageContract;
use Thecodebunny\Bapubuilder\Repositories\PageTranslationRepository;

class Page implements PageContract
{
    /**
     * @var array|null $attributes
     */
    protected $attributes;

    /**
     * @var array|null $translations
     */
    protected $translations;

    /**
     * Set the data stored for this page.
     *
     * @param array|null $data
     * @param bool $fullOverwrite       whether to fully overwrite or extend existing data
     */
    public function setData($data, $fullOverwrite = true)
    {
        // if page builder data is set, try to decode json
        if (isset($data['data']) && is_string($data['data'])) {
            $data['data'] = json_decode($data['data'], true);
        }
        if ($fullOverwrite) {
            $this->attributes = $data;
        }  elseif (is_array($data)) {
            $this->attributes = is_null($this->attributes) ? [] : $this->attributes;
            foreach ($data as $key => $value) {
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * Return all data stored for this page (page builder data and other data set via setData).
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->attributes;
    }

    /**
     * Return the page builder data stored for this page.
     *
     * @return array|null
     */
    public function getBuilderData()
    {
        return $this->attributes['data'] ?? [];
    }

    /**
     * Return the id of this page.
     *
     * @return string
     */
    public function getId()
    {
        return $this->get('id');
    }

    /**
     * Return the name of this page.
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Return the layout (file name) of this page.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->get('layout');
    }

    /**
     * Return the translated settings of this page.
     *
     * @return array
     */
    public function getTranslations()
    {
        if (is_null($this->translations)) {
            $records = (new PageTranslationRepository)->findWhere('page_id', $this->getId());
            $translations = [];
            foreach ($records as $record) {
                $translations[$record->locale] = (array) $record;
            }
            $this->translations = $translations;
        }
        return $this->translations;
    }

    /**
     * Return the given language dependant setting for this page, in the current or in the given language.
     *
     * @param string $setting
     * @param string|null $locale
     * @return mixed|string|null
     */
    public function getTranslation(string $setting, $locale = null)
    {
        $translations = $this->getTranslations();
        $locale = $locale ?? config('general.language');
        return $translations[$locale][$setting] ?? null;
    }

    /**
     * Return the route of this page.
     *
     * @param string|null $locale
     * @return mixed|string|null
     */
    public function getRoute($locale = null)
    {
        return $this->getTranslation('route', $locale);
    }

    /**
     * Get the value of the given property of this Page.
     *
     * @param $property
     * @return mixed|null
     */
    public function get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        if ($this->attributes && is_array($this->attributes)) {
            return $this->attributes[$property] ?? null;
        }

        return null;
    }
}
