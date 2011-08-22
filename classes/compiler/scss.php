<?php defined('SYSPATH') or die('No direct script access.');

class Compiler_SCSS {

	public static function compile(array $filepaths, array $options)
	{
		foreach ($filepaths as $relative_path => $absolute_path)
		{
			$destination = $options['tmp_dir'].'/'.$relative_path;
			Compiler_SCSS::put_file($absolute_path, $destination);
		}

		// Create the compass project
		$config_dir = $options['tmp_dir'].'/config';
		if ( ! is_dir($config_dir))
		{
			mkdir($config_dir, 0777, TRUE);
		}

		$view = View::factory('minion/tasks/assets/compass')
			->set('options', $options);
		file_put_contents($config_dir.'/compass.rb', $view->render());

		// Compile the project!
		exec('cd '.escapeshellarg($options['tmp_dir']).' && compass compile');

		// Copy the compiled files to it's final home :)
		$css_dir = $options['tmp_dir'].'/'.$options['css_dir'];

		$compiled_files = Kohana::list_files($options['css_dir'], array($options['tmp_dir'].'/'));

		foreach ($compiled_files as $relative_path => $absolute_path)
		{
			$destination = $options['save_dir'].'/'.$relative_path;
			Compiler_SCSS::put_file($absolute_path, $destination);
		}

		// Remove the tmp directory
		exec('rm -R '.escapeshellarg($options['tmp_dir']));
	}

	public static function put_file($source, $destination)
	{
		$directory = pathinfo($destination, PATHINFO_DIRNAME);
		if ( ! is_dir($directory))
		{
			// Make any missing directory
			mkdir($directory, 0777, TRUE);
		}

		copy($source, $destination);
	}
}