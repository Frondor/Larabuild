<?php

namespace Frondor\Larabuild\Drivers;

use ZipArchive;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use Symfony\Component\Process\Process;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Frondor\Larabuild\Commands\Build as BuildCommand;

class SharedWebhostingDriver
{
	private $build_path;
	private $manager;
	private $config;

	public function __construct(BuildCommand $command)
	{
        $this->cmd = $command;
		$this->config = config('larabuild');
    	$this->build_path = $this->createBuildFile();
		$this->manager = $this->createManager($this->build_path);
	}

    public function run()
    {
        set_time_limit(255);
        ini_set('memory_limit', '256M');
        if ($this->cmd->option('optimize')) {
            $this->optimize();
        }
        $this->runPreCommands();
		$this->copyFiles();
		$this->editFiles();
        $this->runPostCommands();

		dd('done');
    }

    protected function runPreCommands()
    {
        foreach ($this->config['artisan'] as $command) {
            $this->cmd->call($command);
        }
    }

    protected function runPostCommands(){}

    protected function optimize()
    {
        try {
            $this->manager->deleteDir('local://.temp');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        $this->manager->createDir('local://.temp/vendor');
        $this->manager->createDir('local://.temp/database'); // because of actual composer.json
        $this->manager->copy('local://composer.json', 'local://.temp/composer.json');

        $process = new Process('composer install --no-dev --no-scripts --no-suggest --prefer-dist --ignore-platform-reqs -d=.temp --no-interaction -o');
        $process->setTimeout(500);
        $process->setIdleTimeout(255);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->cmd->info($buffer);
            } else {
                $this->cmd->error($buffer);
            }
        });

        if ($process->isSuccessful()) {
            $process->stop(3);
            foreach ($this->manager->listContents('local://.temp', true) as $path) {
                $path['path'] = str_replace('.temp/', '', $path['path']);
                if ($path['type'] == 'dir' && $path['basename'] != 'database') { // because of permissions problems in windows
                    $this->manager->createDir('zip://'.$this->config['app_name'].DIRECTORY_SEPARATOR.$path['path']);
                }
                else if ($path['type'] == 'file') {
                    $this->manager->copy('local://'.$path['path'], 'zip://'.$this->config['app_name'].DIRECTORY_SEPARATOR.$path['path']);
                }
            }
        }
    }

    protected function editFiles()
    {
    	foreach ($this->config['files_to_edit'] as $path => $modifications) {
    		if ($this->manager->has('zip://'.$path)) {
				$contents = $this->manager->read('zip://'.$path);

	    		foreach ($modifications as $search => $replace) {
	    			$contents = str_replace($search, $replace, $contents);
	    		}

				$contents = $this->manager->update('zip://'.$path, $contents);
    		} else {
    			dd('Couldn\'t find the file to edit.');
    		}
    	}
    }

    protected function copyFiles()
    {
    	$this->manager->getFilesystem('local')->addPlugin(new ListPaths());
    	$paths = array_intersect( $this->manager->getFilesystem('local')->listPaths(), $this->config['files'] );
    	$files = [];

        // glob() can be implemented to match black/white lists

    	foreach ($paths as $path) {
    		if (is_file(base_path($path))) {
    			$files[] = $path;
    		}
    		else {
    			foreach ($this->manager->listContents('local://'.$path, true) as $path) {
    				$files[] = $path['path'];
    			}
    		}
    	}

    	foreach ($files as $file) {
    		$output = strncmp($file, 'public', strlen('public')) === 0 
    		? str_replace('public', $this->config['public_folder'], $file)
    		: $this->config['app_name'].DIRECTORY_SEPARATOR.$file;

    		if (is_dir(base_path($file))) { // because of permissions problems in windows
    			$this->manager->createDir('zip://'.$output);
    			continue;
    		}
    		$this->manager->copy('local://'.$file, 'zip://'.$output);
    	}

        $this->manager->delete('zip://tmp');
    }

    protected function createManager(string $build_path)
    {
    	//$ftp = new League\Flysystem\Filesystem($ftpAdapter);
		$zip = new Filesystem(new ZipArchiveAdapter($build_path));
		$local = new Filesystem(new Local(base_path(), LOCK_EX, Local::SKIP_LINKS, $this->config['file_permissions']));

		// Add them in the constructor
		return $manager = new MountManager([
		    //'ftp' => $ftp,
		    'zip' => $zip,
		    'local' => $local,
		]);
    }

    protected function createBuildFile()
    {
		$zip = new ZipArchive();
		$zip_path = base_path('build.zip');

    	if (file_exists($zip_path)) {
    		unlink($zip_path);
    	}

		if ($zip->open($zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
    		dd("An error occurred creating the ZIP file.");
  		}

		$zip->addFromString('tmp', '');
		if ($zip->close()) {
			return $zip_path;
		}
		else {
			dd('Couldn\'t create build.zip file');
		}
    }
}
