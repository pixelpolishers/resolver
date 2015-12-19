<?php

namespace PixelPolishers\Resolver\Source\Handler;

use Exception;
use PixelPolishers\Resolver\Application;
use PixelPolishers\Resolver\Package\PackageInterface;
use PixelPolishers\Resolver\Utils\FileSystem;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use ZipArchive;

class Zip implements HandlerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function download(OutputInterface $output, PackageInterface $package, $path)
    {
        $this->output = $output;

        $this->ensureZipArchivePresent();

        $downloadPath = 'C:\Users\Walter\AppData\Local\Temp\resA4B4.tmp';//tempnam(sys_get_temp_dir(), 'resolver-zip');
        //$this->downloadFromUrl($package->getSource()->getUrl(), $downloadPath);

        $this->extractContent($downloadPath, $path);
    }

    private function ensureZipArchivePresent()
    {
        if (class_exists('ZipArchive')) {
            return;
        }

        // php.ini path is added to the error message to help users find the correct file
        $iniPath = php_ini_loaded_file();

        if ($iniPath) {
            $iniMessage = 'The php.ini used by your command-line PHP is: ' . $iniPath;
        } else {
            $iniMessage = 'A php.ini file does not exist. You will have to create one.';
        }

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $error = "Could not decompress the archive, enable the PHP zip extension or install unzip.\n";
        } else {
            $error = "Could not decompress the archive, enable the PHP zip extension.\n" . $iniMessage;
        }

        $error .= $iniMessage . "\n";

        throw new RuntimeException($error);
    }

    private function extractContent($downloadPath, $path)
    {
        $extractDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('zip-extract');
        FileSystem::createDirectory($extractDirectory, 0777);
        FileSystem::emptyDirectory($path);

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->extractContentWindows($downloadPath, $extractDirectory);
        } else {
            $this->extractContentUnix($downloadPath, $extractDirectory);
        }

        // When there was only one directory in the zip, we extract the content out of it.
        $files = FileSystem::getDirectoryContent($extractDirectory);
        if (count($files) === 1 && is_dir(key($files))) {
            $files = FileSystem::getDirectoryContent(key($files));
        }

        foreach ($files as $file) {
            $file = (string)$file;

            FileSystem::rename($file, $path . '/' . basename($file));
        }

        FileSystem::removeDirectory($extractDirectory);
    }

    private function extractContentWindows($downloadPath, $path)
    {
        $zip = new ZipArchive();
        $error = $zip->open($downloadPath);

        // Depending on the PHP version, the output of ZipArchive::open is different.
        if ($error === ZipArchive::ER_OK || $error === true) {
            $zip->extractTo($path);
            $zip->close();
            return;
        }

        $messages = [
            ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported.',
            ZipArchive::ER_RENAME => 'Renaming temporary file failed.',
            ZipArchive::ER_CLOSE => 'Closing zip archive failed',
            ZipArchive::ER_SEEK => 'Seek error',
            ZipArchive::ER_READ => 'Read error',
            ZipArchive::ER_WRITE => 'Write error',
            ZipArchive::ER_CRC => 'CRC error',
            ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
            ZipArchive::ER_NOENT => 'No such file.',
            ZipArchive::ER_EXISTS => 'File already exists',
            ZipArchive::ER_OPEN => 'Can\'t open file',
            ZipArchive::ER_TMPOPEN => 'Failure to create temporary file.',
            ZipArchive::ER_ZLIB => 'Zlib error',
            ZipArchive::ER_MEMORY => 'Memory allocation failure',
            ZipArchive::ER_CHANGED => 'Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported.',
            ZipArchive::ER_EOF => 'Premature EOF',
            ZipArchive::ER_INVAL => 'Invalid argument',
            ZipArchive::ER_NOZIP => sprintf('%s is not a zip archive', $downloadPath),
            ZipArchive::ER_INTERNAL => 'Internal error',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent',
            ZipArchive::ER_REMOVE => 'Can\'t remove file',
            ZipArchive::ER_DELETED => 'Entry has been deleted',
        ];

        throw new RuntimeException($messages[$error], $error);
    }

    private function extractContentUnix($downloadPath, $path)
    {
        try {
            $command = sprintf(
                'unzip %s -d %s && chmod -R u+w %s',
                ProcessUtils::escapeArgument($downloadPath),
                ProcessUtils::escapeArgument($path),
                ProcessUtils::escapeArgument($path)
            );

            $process = new Process($command);

            if ($process->run() === 0) {
                return;
            }

            $processError = sprintf("Failed to execute %s\n\n%s", $command, $this->process->getErrorOutput());
        } catch (Exception $e) {
            $processError = sprintf("Failed to execute %s\n\n%s", $command, $e->getMessage());
        }

        throw new RuntimeException($processError);
    }

    private function downloadFromUrl($url, $path)
    {
        $fp = @fopen($path, 'wb');
        if (!$fp) {
            throw new RuntimeException('Failed to open ' . $path);
        }

        $ch = curl_init();
        if (!$ch) {
            throw new RuntimeException('Failed to initialize curl.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Resolver/' . Application::VERSION);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this, 'onCurlProgress'));
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);

        curl_exec($ch);
        curl_close($ch);

        fclose($fp);

        $this->output->writeln('');
    }

    private function onCurlProgress($resource, $size, $downloaded)
    {
        if ($size > 0) {
            $percentage = round($downloaded / $size * 100);
        } else {
            $percentage = 0;
        }

        $this->output->write("\x0D");
        $this->output->write(sprintf('<info>    Downloaded: %s%%</info>', $percentage));
    }
}
