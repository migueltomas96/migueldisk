<?php

class DocxImages
{
    private $file;
    private $indexes = [];

    private $savepath = 'docimages'; /* pasta local onde as imagens serao guardadas */

    public function __construct($filePath)
    {
        $this->file = $filePath;
        $this->extractImages();
    }

    function extractImages()
    {
        $ZipArchive = new ZipArchive;
        if (true === $ZipArchive->open($this->file)) {
            for ($i = 0; $i < $ZipArchive->numFiles; $i++) {
                $zip_element = $ZipArchive->statIndex($i);
                if (preg_match("([^\s]+(\.(?i)(jpg|jpeg|png|gif|bmp))$)", $zip_element['name'])) {
                    $imagename = explode('/', $zip_element['name']);
                    $imagename = end($imagename);
                    $this->indexes[$imagename] = $i;
                }
            }
        }
    }

    function saveAllImages()
    {
        if (count($this->indexes) == 0) {
            echo 'No images found';
        }
        foreach ($this->indexes as $key => $index) {
            $zip = new ZipArchive;
            if (true === $zip->open($this->file)) {
                file_put_contents(dirname(__FILE__) . '/' . $this->savepath . '/' . $key, $zip->getFromIndex($index));
            }
            $zip->close();
        }
    }

    function displayImages()
    {
        $this->saveAllImages();
        if (count($this->indexes) == 0) {
            return 'No images found';
        }
        $images = '';
        foreach ($this->indexes as $key => $index) {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->savepath . '/' . $key;
            $images .= '<img src="' . $path . '" alt="' . $key . '"/> <br>';
        }
        echo $images;
    }
}

$DocxImages = new DocxImages("Imagens.docx");//nome do documento word onde estao as imagens
/** It will save and display images*/
$DocxImages->displayImages();
/** It will only save images to local server */
#$DocxImages->saveAllImages();

