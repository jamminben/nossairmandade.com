<?php
namespace App\Classes;

class Stanza
{
    private $text;
    private $lines;

    public function __construct($text)
    {
        $this->text = $text;
        $this->lines = explode("\n", $text);
        if (end($this->lines) === '') {
            array_pop($this->lines);
        }

    }

    public function getText()
    {
        return $this->text;
    }

    public function getLineCount()
    {
        return count($this->lines);
    }

    public function getLines()
    {
        return $this->lines;
    }

    public function getLine($index)
    {
        return $this->lines[$index];
    }
}
