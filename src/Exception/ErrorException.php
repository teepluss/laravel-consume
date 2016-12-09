<?php

namespace Teepluss\Consume\Exception;

use Exception;

class ErrorException extends Exception
{
    /**
     * Exception content
     */
    protected $content;

    /**
     * Constructor.
     *
     * @param string  $message
     * @param integer $code
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);

        return $this;
    }

    /**
     * Set content to exception.
     *
     * @param mixed $content string\json
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content from exception.
     *
     * @return mixed string\json
     */
    public function getContent()
    {
        return $this->content;
    }
}
