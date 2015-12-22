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
        ->setDescription('Pathwar - Captcha level')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = new Logger('captcha');
        $log->pushHandler(new StreamHandler('app/logs/captcha.log', Logger::WARNING));
        $log->addWarning('Start scrap');
        for ($i=0; $i < 1000 ; $i++) {
            $this->parseCaptcha();
        }
    }

    public function parseCaptcha()
    {
        $session = 'a8c55488ef84b6368621a1a10cc26d3e';
        $link = 'https://chalas_r:3c417b18-1505-4b35-9903-e0684fe26690@0f40c82c-5142-4313-9eaf-4ef54b0f3009.levels.pathwar.net/';
        $curl = curl_init($link.'captcha.php');
        curl_setopt($curl, CURLOPT_USERAGENT,
        'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID='.$session);

        session_write_close();
        $result = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($curl);

        $saveto = '/Users/Robin/Sites/epitech/thot/captcha/catcha.png';
        $saveto2 = '/Users/Robin/Sites/epitech/thot/captcha/catcha_clear_gray.png';
        if (file_exists($saveto)) {
            unlink($saveto);
        }
        $fp = fopen($saveto, 'x');
        fwrite($fp, $body);
        fclose($fp);

        $img = $this->loadPNG($saveto);
        $imageWidth = imagesx($img);
        $imageHeight = imagesy($img);
        $pixelColors = array();
        for ($i = 0; $i < $imageWidth; $i++) {
            for ($j = 0; $j < $imageHeight; $j++) {
                $pixelColors[] = imagecolorat($img, $i, $j);
                $rgb = imagecolorat($img, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if ($rgb == 9079434) {
                    $white = imagecolorallocate($img, 255, 255, 255);
                    $indexPixel = imagecolorat($img, $i, $j);
                    imagefill($img, $i, $j, $white);
                }
            }
        }
        imagepng($img, $saveto2);
        imagedestroy($img);

        $captcha = exec(sprintf('gocr %s', $saveto2));

        return $this->submitCaptcha($captcha, $session, $link);
    }

    public function submitCaptcha($captcha, $session, $link)
    {
        $curl = curl_init($link);
        $post_items = array('password' => $captcha);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'password='. $captcha);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID=' . $session);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        session_write_close();
        $result = curl_exec($curl);

        curl_close($curl);

        $findScore = strpos($result, 'Score');
        if (false !== $findScore) {
                $score = substr($result, $findScore, 13);
        }

        return print $score.PHP_EOL;
    }

    public function loadPNG($imgname)
    {
        $im = @imagecreatefrompng($imgname);

        if (!$im) {
            $im = imagecreatetruecolor(150, 30);
            $bgc = imagecolorallocate($im, 255, 255, 255);
            $tc = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
            imagestring($im, 1, 5, 5, 'Error loading '.$imgname, $tc);
        }

        return $im;
    }

}
