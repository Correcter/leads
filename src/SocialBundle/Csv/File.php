<?php

namespace SocialBundle\Csv;

/**
 * @author Vitaly Dergunov
 */
class File
{
    protected $handle;

    protected $path;

    protected $delimiter = "\t";

    protected static $chmod = 0777;

    /**
     * File constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (file_exists($path)) {
            chmod($path, self::$chmod);
        }
        $this->path = $path;
        $this->handle = fopen($path, 'w');
    }

    /**
     * @param $char
     */
    public function setDelimiter($char)
    {
        $this->delimiter = $char;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param array $row
     *
     * @return $this
     */
    public function writeRow(array $row)
    {
        fputcsv($this->handle, $row, $this->delimiter);

        return $this;
    }

    public function close()
    {
        fclose($this->handle);
        $this->handle = null;
    }

    /**
     * Delete path.
     */
    public function remove()
    {
        unlink($this->path);
    }

    /**
     * Get path.
     */
    public function getPath()
    {
        return $this->path;
    }
}
