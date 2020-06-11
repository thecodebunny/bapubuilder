<?php

namespace Thecodebunny\Bapubuilder;

class UploadedFile
{
    /**
     * Return the URL of this uploaded file.
     */
    public function getUrl()
    {
        return config('general.uploads_url') . '/' . $this->public_id . '/' . $this->original_file;
    }
}
