<?php

namespace JibayMcs\SurveyJs\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallCreatorCommand extends Command
{
    protected $signature = 'surveyjs:install-creator';

    protected $description = 'Install and build the SurveyJS Creator assets (requires a commercial license)';

    public function handle(): int
    {
        $packagePath = realpath(__DIR__ . '/../../');

        $this->info('Installing SurveyJS Creator...');
        $this->newLine();

        // Check that we have the plugin's source files (package.json, bin/build.js)
        if (! file_exists($packagePath . '/package.json')) {
            $this->error('package.json not found in the plugin directory.');
            $this->newLine();
            $this->line('This command requires the plugin\'s source files.');
            $this->line('If installed via Composer, reinstall with:');
            $this->newLine();
            $this->line('  <comment>composer require jibaymcs/survey-js --prefer-source</comment>');
            $this->newLine();

            return self::FAILURE;
        }

        // Check npm
        if (! $this->commandExists('npm')) {
            $this->error('npm is not installed. Please install Node.js and npm first.');

            return self::FAILURE;
        }

        // Check node_modules
        if (! is_dir($packagePath . '/node_modules')) {
            $this->warn('node_modules not found. Running npm install first...');
            $this->newLine();

            if (! $this->runProcess(['npm', 'install', '--legacy-peer-deps'], $packagePath)) {
                return self::FAILURE;
            }
        }

        // Install Creator deps
        $this->info('Installing survey-creator-core and survey-creator-js...');

        if (! $this->runProcess(['npm', 'install', '--legacy-peer-deps', 'survey-creator-core', 'survey-creator-js'], $packagePath)) {
            return self::FAILURE;
        }

        // Build
        $this->info('Building assets...');
        $this->newLine();

        if (! $this->runProcess(['npm', 'run', 'build'], $packagePath)) {
            return self::FAILURE;
        }

        // Verify
        if (! file_exists($packagePath . '/resources/dist/survey-js-creator.js')) {
            $this->error('Build completed but survey-js-creator.js was not generated.');
            $this->line('Expected output at: <comment>' . $packagePath . '/resources/dist/survey-js-creator.js</comment>');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('SurveyJS Creator installed successfully!');
        $this->line('You can now use <comment>SurveyJSCreatorField::make()</comment> in your Filament forms.');
        $this->newLine();
        $this->warn('Reminder: The SurveyJS Creator requires a commercial license.');
        $this->line('Set <comment>SURVEYJS_LICENSE_KEY</comment> in your .env file.');

        return self::SUCCESS;
    }

    protected function runProcess(array $command, string $cwd): bool
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(120);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error('Command failed: ' . implode(' ', $command));

            return false;
        }

        return true;
    }

    protected function commandExists(string $command): bool
    {
        $process = new Process(PHP_OS_FAMILY === 'Windows' ? ['where', $command] : ['which', $command]);
        $process->run();

        return $process->isSuccessful();
    }
}
