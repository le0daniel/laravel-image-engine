<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 14:49
 */

namespace le0daniel\Laravel\ImageEngine\Commands;


use Illuminate\Console\Command;
use le0daniel\Laravel\ImageEngine\Utility\Directories;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class FilesystemClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filesystem:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears tmp files older than 1 hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return absolute path of directories to clear with a file max age
     *
     * @return array
     */
    protected function dirsToClean(): array
    {
        return config('image-engine.dirs_to_clear', []);
    }

    /**
     * @param int $days
     * @return int
     */
    protected function days(int $days): int
    {
        return $days * 3600 * 24;
    }

    /**
     * @param int $hours
     * @return int
     */
    protected function hours(int $hours): int
    {
        return $hours * 3600;
    }

    /**
     * @param int $months
     * @return int
     */
    protected function months(int $months): int
    {
        return $months * 24 * 3600 * 24;
    }

    /**
     * Enforce some directories to always be there
     *
     * @return array
     */
    protected function recreateDirectories(): array
    {
        $dirs = collect(array_keys($this->dirsToClean()));
        return $dirs->map(function (string $dir) {
            return array_filter(
                explode('/', $dir),
                function (string $element) {
                    return $element !== '*';
                });
        })->map(function (array $parts): string {
            return implode('/', $parts);
        })->toArray();
    }

    protected function extractAgeFromConfig(array $config): int
    {
        if (isset($config['seconds'])) {
            return $config['seconds'];
        }

        if (isset($config['days'])) {
            return $this->days($config['days']);
        }

        if (isset($config['hours'])) {
            return $this->hours($config['hours']);
        }

        if (isset($config['months'])) {
            return $this->months($config['months']);
        }

        throw new \Exception('Invalid config. Seconds, hours, days or months must be given');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->dirsToClean() as $absoluteDir => $config) {
            try {
                $maxAge = $this->extractAgeFromConfig($config);

                $finder = (new Finder())
                    ->ignoreUnreadableDirs()
                    ->files()
                    ->in($absoluteDir);

                if (isset($config['name'])) {
                    $finder->name($config['name']);
                }
            }
            catch (DirectoryNotFoundException $exception){
                $this->line("Directory <info>{$absoluteDir}</info> empty or not found");
                continue;
            }
            catch (\Exception $error) {
                $class = get_class($error);
                $this->error("[{$class}]: {$error->getMessage()}");
                continue;
            }

            $this->line('Clear files in <info>' . $absoluteDir . '</info>');
            $this->cleanFinder($finder, $maxAge);
        }

        foreach ($this->recreateDirectories() as $directory) {
            if (!file_exists($directory) && !is_dir($directory)) {
                Directories::makeRecursive($directory);
                $this->line('Made directory: <info>' . $directory . '</info>');
            }
        }
    }

    /**
     * @param string $dir
     */
    protected function clearDirRecursively(string $dir)
    {
        while ($this->isEmptyDir($dir)) {
            rmdir($dir);
            $dir = dirname($dir);
        }
    }

    /**
     * @param string $dir
     * @return bool
     */
    protected function isEmptyDir(string $dir): bool
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        return !(new \FilesystemIterator($dir))->valid();
    }

    /**
     * @param Finder $finder
     * @param int $maxAgeInSeconds
     */
    protected function cleanFinder(Finder $finder, int $maxAgeInSeconds)
    {
        $index = 0;
        // Loop through files
        foreach ($finder as $splFileInfo) {
            if (($splFileInfo->getMTime() + $maxAgeInSeconds) < time()) {
                $index++;
                $path = $splFileInfo->getRealPath();

                if (file_exists($path)) {
                    $this->line($path);
                    unlink($path);
                } else {
                    $this->error("> [{$index}] could not delete {$path}, file does not exist");
                    continue;
                }

                $this->clearDirRecursively(dirname($path));
                $this->line("> [{$index}] deleted <info>{$path}</info>");
            }
        }
    }
}
