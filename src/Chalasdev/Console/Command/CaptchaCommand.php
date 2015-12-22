<?php

namespace Chalasdev\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class CaptchaCommand extends Command
{
  protected function configure()
  {
    $this
    ->setName('chalasdev:captcha')
    ->setDescription('Scrap la centrale')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $log = new Logger('captcha');
    $log->pushHandler(new StreamHandler('app/logs/captcha.log', Logger::WARNING));
    $log->addWarning('Start scrap');
    $proxy = '107.168.141.98';
    for ($i=0; $i < 1000 ; $i++) {
      $this->parseCaptcha();
    }
  }

  //function for step2
  public function parseCaptcha()
  {
    //$link =strtok($link,'?');
    $curl = curl_init('https://chalas_r:3c417b18-1505-4b35-9903-e0684fe26690@0f40c82c-5142-4313-9eaf-4ef54b0f3009.levels.pathwar.net/captcha.php');
    //set options
    curl_setopt($curl, CURLOPT_USERAGENT,
    'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    
    // curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID=b07e0d00563f25ed5ce807ce55ddda93');
    session_write_close();
    $result = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($result, 0, $header_size);
    $body = substr($result, $header_size);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
    $cookies = array();
    foreach ($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookies = array_merge($cookies, $cookie);
    }
    $session = $cookies['PHPSESSID'];
    printf('Session found %s', $session);

    curl_close($curl);
    $saveto = '/Users/Robin/Sites/epitech/thot/captcha/catcha.png';
    $saveto2 = '/Users/Robin/Sites/epitech/thot/captcha/catcha_clear_gray.png';
    if (file_exists($saveto)) {
      unlink($saveto);
    }
    $fp = fopen($saveto, 'x');
    fwrite($fp, $body);
    fclose($fp);

    $img = $this->LoadPNG($saveto);
    $imageWidth = imagesx($img);
    $imageHeight = imagesy($img);
    $pixel_colors = array();
    for ($i = 0; $i < $imageWidth; $i++) {
      for ($j = 0; $j < $imageHeight; $j++) {
        $pixel_colors[] = imagecolorat($img, $i, $j);
        $rgb = imagecolorat($img, $i, $j);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        /*if ($r == 138)
        echo 'Gray is'.$rgb;
        var_dump($r, $g, $b);
        echo '===';*/
        //remove grey from image (9079434) $rgb =138 138 138
        //echo $rgb.'----';
        if ($rgb == 9079434) {
          $white = imagecolorallocate($img, 255, 255, 255);
          $index_pixel = imagecolorat($img, $i, $j);
          //echo $index_pixel;
          //echo $index_pixel;
          imagefill($img, $i, $j, $white);
        }
      }
    }
    imagepng($img, $saveto2);

    imagedestroy($img);
    /* Get the most recurent color
    $c = array_count_values($pixel_colors);
    $rgb = array_search(max($c), $c);
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;

    var_dump($r, $g, $b);*/
    $captcha_result = exec(sprintf('gocr %s', $saveto2));

    printf('Found password: %s', $captcha_result);
    return $this->submitCaptcha($captcha_result, $session);
  }

  public function submitCaptcha($captcha, $session)
  {
    $curl = curl_init('https://chalas_r:3c417b18-1505-4b35-9903-e0684fe26690@0f40c82c-5142-4313-9eaf-4ef54b0f3009.levels.pathwar.net/');
    $post_items = array('password' => $captcha);
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'password='. $captcha);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID=' . $session);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    session_write_close();
    $result = curl_exec($curl);

    curl_close($curl);

    if (strpos($result, 'You win')) {
        return print 'You win !';
    }

    return print 'You lose';
  }

  public function LoadPNG($imgname)
  {
    /* Attempt to open */
    $im = @imagecreatefrompng($imgname);

    /* See if it failed */
    if (!$im) {
      /* Create a blank image */
      $im = imagecreatetruecolor(150, 30);
      $bgc = imagecolorallocate($im, 255, 255, 255);
      $tc = imagecolorallocate($im, 0, 0, 0);

      imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

      /* Output an error message */
      imagestring($im, 1, 5, 5, 'Error loading '.$imgname, $tc);
    }

    return $im;
  }

}
