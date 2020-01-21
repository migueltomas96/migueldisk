<?php
date_default_timezone_set("Europe/Lisbon");
define ("SLOT_DA_ULTIMA_PALAVRA", count($argv)-1);
define ("BD", "APONTAMENTOS.BD");
define ("FALHA_APONTAMENTO_VAZIO", -1);

$pasta = substr($argv[1], 0, -5);  // retorna o nome do ficheiro sem o ".docx"
$DocxImages = new DocxImages($argv[1],$argv);// arg[1]nome do documento word onde estao as imagens


class DocxImages
{
    private $argv;
    private $file;
    private $indexes = [];

    private $savepath = 'D://Escola/ACA/Trabalho_ACA';
    private $pathImages = '/Imagens';


    public function __construct($filePath, $argv)
    {
        $this->argv = $argv;
        $this->file = $filePath;
        $this->delTree('D://Escola/ACA/Trabalho_ACA/Imagens');
        $this->newFolder('D://Escola/ACA/Trabalho_ACA/', $argv);




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
                file_put_contents(dirname(__FILE__) . '/' . $this->pathImages . '/' . $key, $zip->getFromIndex($index));
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
    //   var_dump($this->indexes);
        foreach ($this->indexes as $key => $index) {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->pathImages . '/' . $key;
            $images .= '<img src="' . $path . '" alt="' . $key . '"/> <br>';
        }
        echo count($this->indexes) . " imagems encontradas";
        //echo $images;
    }

    function delTree($dir) {
        if(file_exists($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
            }
            rmdir($dir);
        }
    }

    function newFolder($pathname, $argv){
        if($pathname=='D://Escola/ACA/Trabalho_ACA/'){
        $this->argv = explode(".",$argv[1]);
        $pathname = $pathname .$this->argv[0];
        }
        if(file_exists($pathname) == false) {
            mkdir($pathname);
            $this->pathImages="/".$this->argv[0];
            $this->extractImages();
            $this->displayImages();


        }else{
            $this->delTree($pathname);
            $this->newFolder($pathname, $argv);
        }
    }

    function renameDir($pathname, $savepath, $argv){

        $this->argv = explode(".",$argv);

        $newName = $savepath . '/' .$this->argv[0];

        rename($pathname, $newName);
    }
}


function colherApontamento(){
    global $argv;

    // nome do decomento docx
    $strApontamento = $argv[1];

    return $strApontamento;
}//colherApontamento

function gravarApontamento($pApontamento) //grava a data de gravação de imagens e o decumento "docx" do qual estas tiverem origem
{
    $bHaApontamentoParaGravarNaBaseDeDados = !empty($pApontamento);

    if ($bHaApontamentoParaGravarNaBaseDeDados){

        $strDataHora = date("Y-m-d H:i:s"); //2019-10-09 15:31:25
        $strLinha = $strDataHora."\t".$pApontamento."\n";
        /* sprintf = string print formatted */
        $strLinha = sprintf(
            "%s\t%s".PHP_EOL,
            $strDataHora,
            $pApontamento
        );

        $iBytesEscritosOuFalseSeFalhar =
            file_put_contents(
                BD,
                $strLinha,
                FILE_APPEND
            );

        return $iBytesEscritosOuFalseSeFalhar;
    }//if
    return FALHA_APONTAMENTO_VAZIO;
}//gravarApontamento

$strApontamento = colherApontamento();
gravarApontamento($strApontamento);
