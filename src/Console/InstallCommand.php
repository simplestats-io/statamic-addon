<?php

namespace SimpleStatsIo\StatamicAddon\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'simplestats-statamic:install';

    protected $description = 'Install the SimpleStats addon';

    public function handle(): int
    {
        $this->info('Publishing config file...');

        $this->callSilently('vendor:publish', ['--tag' => 'simplestats-config']);

        if ($this->confirm('Would you like to star our repo on GitHub?')) {
            $this->openInBrowser('https://github.com/simplestats-io/statamic-addon');
        }

        $this->info('SimpleStats has been installed!');

        return self::SUCCESS;
    }

    protected function openInBrowser(string $url): void
    {
        match (PHP_OS_FAMILY) {
            'Darwin' => exec("open {$url}"),
            'Windows' => exec("start {$url}"),
            'Linux' => exec("xdg-open {$url}"),
            default => null,
        };
    }
}
