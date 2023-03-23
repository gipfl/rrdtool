<?php

namespace gipfl\RrdTool\Rendering;

use Exception;

class ErrorImage
{
    /** @var Exception|string */
    protected $error;
    protected bool $showStackTrace = false;

    /**
     * @param Exception|string $error
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    public function showStackTrace(bool $show = true): void
    {
        $this->showStackTrace = (bool) $show;
    }

    protected function shortenFileInTrace(string $file): string
    {
        return preg_replace(
            '_^(#\d+)\s+/.+?/(?:vendor|application|library)/_m',
            '\1:',
            $file
        );
    }

    protected function shortenFile(string $file): string
    {
        return preg_replace(
            '_^/.+?/(?:vendor|application|library)/_m',
            '',
            $file
        );
    }

    public function render($width, $height): string
    {
        $error = $this->error;
        if ($error instanceof Exception) {
            $message = $error->getMessage();
            if ($this->showStackTrace) {
                $message .= sprintf(
                    "\nin %s(%s)\n%s\n",
                    $this->shortenFile($error->getFile()),
                    $error->getLine(),
                    $this->shortenFileInTrace($error->getTraceAsString())
                );
            }
        } else {
            $message = (string) $error;
        }
        $img = \imagecreate($width, $height);
        $bgColor = \imagecolorallocatealpha($img, 255, 255, 255, 127);
        $textColor = \imagecolorallocatealpha($img, 200, 100, 100, 0);
        imagecolortransparent($img, $bgColor);

        $fonts = [4, 2, 1];
        $lines = null;

        foreach ($fonts as $idx => $font) {
            $charWidth = \imagefontwidth($font);
            $charHeight = \imagefontheight($font);
            $maxChars = floor($width / $charWidth) - 1;
            $maxLines = floor($height / $charHeight) - 1;
            $newLines = preg_split("/\n/", \wordwrap($message, $maxChars, "\n", true));
            if (count($newLines) > $maxLines) {
                if (! isset($fonts[$idx + 1])) {
                    // There is no smaller font.
                    if ($lines === null) {
                        // We got no fitting size? Keep font.
                        $lines = $newLines;
                    } else {
                        // Otherwise use previous lines and font
                        $font = $fonts[$idx - 1];
                    }
                }
            } else {
                $lines = $newLines;
                break;
            }
        }

        foreach ($lines as $nr => $line) {
            \imagestring(
                $img,
                $font,
                floor($charWidth / 2),
                $nr * $charHeight + floor($charHeight / 2),
                $line,
                $textColor
            );
        }

        \ob_start();
        \imagepng($img);
        $image = \ob_get_contents();
        \ob_end_clean();

        return $image;
    }

    public function renderToJson($width, $height): object
    {
        return (object) [
            'graph' => [],
            'image' => ['width' => $width, 'height' => $height],
            'value' => [],
            'raw' =>  "data:image/png;base64,"
                . \base64_encode($this->render($width, $height)),
            'content-type' => 'image/png',
            // 'error'
        ];
    }
}
