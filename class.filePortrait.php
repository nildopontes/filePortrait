<?php
   /**
   * PHP class.filePortrait
   *
   * Converte qualquer arquivo para uma imagem PNG. Permite decodificar a imagem gerada e obter o arquivo inicial
   *
   * @author      Nildo Pontes <nildo.pontes@bol.com.br>
   * @version     1.0
   * @license     GNU General Public License v3.0
   * @param       String $file
   * @param       String $name
   * @param       Array $hex
   * @param       String $hexSting
   * @param       Array $dec
   * @param       Object $img
   * @param       Object $imgDecode
   * @param       String $mime
   *
   */
   class filePortrait{
      var $file;
      var $name;
      var $hex;
      var $hexString;
      var $dec;
      var $img;
      var $imgDecode;
      var $mime;
      var $debug = false;
      public function __construct($file, $name = '', $mime = ''){
         $this->file = $file;
         $this->name = $name;
         $this->mime = $mime;
      }
      /**
      * Exibe uma série de infomações sobre o processamento caso $this->debug == true
      * @access public
      * @param String $description
      * @param String $value
      */ 
      public function addLog($description, $value){
         if($this->debug){
            header('Content-type: text/html; charset=UTF-8');
            echo '<b>'.$description.'</b> : '.$value.'<br>';
         }
      }
      /**
      * Altera o valor de $this->debug para exibir ou não o log do processamento
      * @access public
      * @param Boolean $status
      */ 
      public function debugMode($status){
         $this->debug = $status;
      }
      /**
      * Converte o arquivo especificado no método construtor em hexadecimal, salvando em $this->hexString
      * @access public
      */ 
      public function getHex(){
         $handle = fopen($this->file, 'r') or die('Permission?');
         while(!feof($handle)){
            foreach(unpack('C*', fgets($handle)) as $dec){
               $tmp = dechex($dec);
               $this->hex[] .= strtoupper(str_repeat('0', 2 - strlen($tmp)).$tmp);
            }
         }
         fclose($handle);
         $this->hexString = join($this->hex);
         $this->addLog('Arquivo convertido em hexadecimal', $this->hexString);
      }
      /**
      * Converte um cadeia de caracteres hexadecimais em um aquivo cujo nome e mimetype são especificados nos parâmetros
      * @access public
      * @param String $hexCode
      * @param String $fileName
      * @param String $mimeType
      */ 
      public function writeHex($hexCode, $fileName, $mimeType){
         $tmp = null;
         foreach(str_split($hexCode, 2) as $hex){
            $tmp .= pack('C*', hexdec($hex));
         }
         if(!$this->debug){
            header('Content-Type: '.$mimeType);
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            echo $tmp;
         }
      }
      /**
      * Converte o array $this->hexString de hexadecimal para decimal, salvando em $this->dec
      * @access public
      */ 
      public function convertToDec(){
         $this->dec = array();
         $len = strlen($this->hexString);
         if($len == 0){
            $this->getHex();
         }else{
            foreach(str_split($this->hexString, 2) as $hex){
               $this->dec[] = hexdec($hex);
            }
         }
      }
      /**
      * Gera um array com um valor rgb aleatório
      * @access public
      * @return Array
      */ 
      public function randomRGB(){
         $rgb[] = rand(0, 255);
         $rgb[] = rand(0, 255);
         $rgb[] = rand(0, 255);
         return $rgb;
      }
      /**
      * Recebe uma string qualquer e converte para hexadecimal
      * @access public
      * @param String $string
      * @return String
      */ 
      function strToHex($string){
         $hex = '';
         $len = strlen($string);
         for($i = 0; $i < $len; $i++){
            $hex .= dechex(ord($string[$i]));
         }
         return $hex;
      }
      /**
      * Recebe uma string em hexadecimal e converte para ASCII
      * @access public
      * @param String $hex
      * @return String
      */ 
      function hexToStr($hex){
         $string = '';
         $len = strlen($hex) - 1;
         for($i = 0; $i < $len; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
         }
         return $string;
      }
      /**
      * Converte um array com valores decimais compreendidos entre 0 e 255 para uma string ASCII
      * @access public
      * @param Array $arr
      * @return String
      */ 
      public function decToStr($arr){
         $str = '';
         $len = count($arr);
         for($i = 0; $i < $len; $i++){
            $str .= chr($arr[$i]);
         }
         return $str;
      } 
      /**
      * Recebe uma string ASCII e converte para um array de valores decimais
      * @access public
      * @param String $string
      * @return Array
      */ 
      public function strToDec($string){
         $dec = null;
         $len = strlen($string);
         for($i = 0; $i < $len; $i++){
            $dec[] = ord($string[$i]);
         }
         return $dec;
      }
      /**
      * Recebe uma array de valores inteiros por referência e adiciona mais 1 ou 2 valores decimais aleatóris para tornar a quantidade total de elementos múltiplo de 3
      * @access public
      * @param Array $arr
      * @param Integer $min
      * @param Integer $max
      */ 
      public function perfect(&$arr, $min, $max){
         $rest = (count($arr) % 3);
         switch($rest){
            case 1:
               $arr[] = rand($min, $max);
               $arr[] = rand($min, $max);
               break;
            case 2:
               $arr[] = rand($min, $max);
               break;
         }
      }
      /**
      * Gera uma imagem PNG que conterá os dados do arquivo 'criptografados'. A imagem é escrita diretamente no navegador e deve ser salva com a extensão '.png'
      * @access public
      */ 
      public function generateImage(){
         $this->perfect($this->dec, 0, 255);
         $sizeFile = filesize($this->file);
         $fileInfo = $this->name.'*'.$sizeFile.'*'.$this->mime;
         $this->addLog('Informações armazenadas no Cabeçalho', $fileInfo);
         $fileInfo = $this->strToDec($fileInfo);
         $this->perfect($fileInfo, 0, 0);
         $end = false;
         $side = ($sizeFile / 3);
         $side = ceil(sqrt($side + 60)); // 60 pixels iniciais reservados para informações do arquivo ocultado, equivale a 180 bytes. Por convenção, será chamado de 'Cabeçalho'.
         $this->addLog('Dimensões da imagem criptografada em px', $side.' x '.$side);
         $count = 0;
         //-- Posiciona o 'cursor' depois dos Cabeçalho da imagem.
         if($side > 60){
            $xInitPosition = 60;
            $yInitPosition = 0;
         }else{
            if($side == 60){
               $xInitPosition = 0;
               $yInitPosition = 1;
            }else{
               $xInitPosition = (60 % $side);
               $yInitPosition = ((60 - $xInitPosition) / $side);
            }
         }
         //-----------
         $this->img = imagecreatetruecolor($side, $side);
         $color = imagecolorallocate($this->img, 0, 0, 0);
         imagesetpixel($this->img, 0, 0, $color);
         for($y = $yInitPosition; $y < $side; $y++){
            if($end){
               break;
            }
            for($x = $xInitPosition; $x < $side; $x++){
               $xInitPosition = 0;
               $count += 3;
               $color = imagecolorallocate($this->img, $this->dec[$count - 3], $this->dec[$count - 2], $this->dec[$count - 1]);
               imagesetpixel($this->img, $x, $y, $color);
               if($count >= $sizeFile){
                  $end = true;
                  break;
               }
            }
         }
         $count = 0;
         $lenInfo = count($fileInfo);
         $end = false;
         for($y = 0; $y < $side; $y++){
            if($end){
               break;
            }
            for($x = 0; $x < $side; $x++){
               $count += 3;
               $color = imagecolorallocate($this->img, $fileInfo[$count - 3], $fileInfo[$count - 2], $fileInfo[$count - 1]);
               imagesetpixel($this->img, $x, $y, $color);
               if($count >= $lenInfo){
                  $end = true;
                  break;
               }
            }
         }
         if(!$this->debug){
            header('Content-Type: image/png');
            imagepng($this->img);
            imagedestroy($this->img);
         }
      }
      /**
      * Descriptografa uma imagem gerada pelo método generateImage() e escreve os arquivo oculto diretamente na tela.
      * @access public
      */ 
      public function decodeImage(){
         $count = 0;
         $end = false;
         $this->imgDecode = imagecreatefrompng($this->file);
         for($y = 0; $y < imagesy($this->imgDecode); $y++){
            if($end){
               break;
            }
            for($x = 0; $x < imagesx($this->imgDecode); $x++){
               $rgb = imagecolorat($this->imgDecode, $x, $y);
               $r = ($rgb >> 16) & 0xFF;
               $g = ($rgb >> 8) & 0xFF;
               $b = $rgb & 0xFF;
               if($r != 0){
                  $this->dec[] = $r;
               }else{
                  $end = true;
                  break;
               }
               if($g != 0){
                  $this->dec[] = $g;
               }else{
                  $end = true;
                  break;
               }
               if($b != 0){
                  $this->dec[] = $b;
               }else{
                  $end = true;
                  break;
               }
               if($count >= 60){
                  $end = true;
               }
            }
         }
         $info = explode('*', $this->decToStr($this->dec));
         $info[1] = intval($info[1]);
         $this->addLog('Nome do arquivo contido, obtido no Cabeçalho', $info[0]);
         $this->addLog('Mimetype do arquivo contido, obtido no Cabeçalho', $info[2]);
         $this->addLog('Tamanho do arquivo contido, obtido no Cabeçalho', $info[1].'bytes');
         $count = 0;
         $end = false;
         //----- Posiciona o cursor para depois do Cabeçalho da imagem
         $side = imagesy($this->imgDecode);
         if($side > 60){
            $xInitPosition = 60;
            $yInitPosition = 0;
         }else{
            if($side == 60){
               $xInitPosition = 0;
               $yInitPosition = 1;
            }else{
               $xInitPosition = (60 % $side);
               $yInitPosition = ((60 - $xInitPosition) / $side);
            }
         }
         //-----------
         for($y = $yInitPosition; $y < imagesy($this->imgDecode); $y++){
            if($end){
               break;
            }
            for($x = $xInitPosition; $x < imagesx($this->imgDecode); $x++){
               $xInitPosition = 0;
               $rgb = imagecolorat($this->imgDecode, $x, $y);
               $r = ($rgb >> 16) & 0xFF;
               $g = ($rgb >> 8) & 0xFF;
               $b = $rgb & 0xFF;
               $r = strtoupper(dechex($r));
               $g = strtoupper(dechex($g));
               $b = strtoupper(dechex($b));
               if(strlen($r) < 2){
                  $r = '0'.$r;
               }
               if(strlen($g) < 2){
                  $g = '0'.$g;
               }
               if(strlen($b) < 2){
                  $b = '0'.$b;
               }
               if($count < $info[1]){
                  $this->hex[] = $r;
                  $count++;
               }else{
                  $end = true;
                  break;
               }
               if($count < $info[1]){
                  $this->hex[] = $g;
                  $count++;
               }else{
                  $end = true;
                  break;
               }
               if($count < $info[1]){
                  $this->hex[] = $b;
                  $count++;
               }else{
                  $end = true;
                  break;
               }
            }
         }
         $this->writeHex(join($this->hex), $info[0], $info[2]);
         $this->addLog('Operação concluída com sucesso', 'OK!');
         $this->addLog('Informação hexadecimal do arquivo contido', join($this->hex));
      }
   }
?>