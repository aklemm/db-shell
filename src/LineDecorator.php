<?php

namespace JamesClark32\DbTinker;

class LineDecorator
{
    public function getDecoratedLine(string $text, string $color): string
    {
        if (substr($color, 0, 4) === 'bold') {
            return $this->getFormatStringOpen(substr($color, 4), true) . $text . $this->getFormatStringClose();
        }

        return $this->getFormatStringOpen($color) . $text . $this->getFormatStringClose();
    }

    protected function getFormatStringOpen(string $color, $isBold = false): string
    {
        if ($isBold) {
            return '<fg=' . $color . ';options=bold>';
        }
        return '<fg=' . $color . '>';
    }

    protected function getFormatStringClose(): string
    {
        return '</>';
    }
}
