<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Console Controller.
 * Use this to take a look at your logs instead of having to get on the server.
 *
 * @package		Console
 * @author		Dave Widmer
 * @copyright	2009 (c) Dave Widmer
 */
class Console_Controller extends Kohana_Controller_Template
{
	// Sets the template variable
	public $template = 'console/template';

	// Log directory
	private $dir = 'logs';

	/**
	 * Checks for a media file
	 */
	public function before()
	{
		if( $this->request->action == 'media' )
		{
			$this->auto_render = FALSE;
		}
		else
		{
			parent::before();

			$this->dir = $this->request->param('dir');

			$view_data = array(
				'css' => array(
					'console/media/css/reset.css' => 'screen',
					'console/media/css/console.css' => 'screen',
				),
				'js' => array(
					'console/media/js/jquery-1.3.2.min.js',
					'console/media/js/console.js',
				),
			);

			$this->template->set($view_data);
		}

	}

	/**
	 * Main console page.
	 */
	public function action_index()
	{
		$file = $this->request->param('file');
		$this->template->title = 'Console';
		$this->template->headline = $this->headline( $file );
		$this->template->content = $this->log( $file );
		$this->template->right = $this->build_directory( $file );
	}

	/**
	 * Gets the log file.
	 *
	 * @param	string	Log file path
	 * @return	string
	 */
	protected function log( $file )
	{
		if( $file )
		{
			$path = pathinfo($file);
			$file = Kohana::find_file($this->dir, $path['dirname'] . DIRECTORY_SEPARATOR . $path['filename']);

			if( $file )
			{
				$content = file_get_contents( $file );
				return $this->parse( $content );
			}
			else
			{
				return Kohana::message( 'console', 'not_found' );
			}

		}
		else
		{
			return Kohana::message( 'console', 'directions');
		}

	}

	/**
	 * Parses the log file
	 *
	 * @param	string	Log Text
	 * @return	string
	 */
	protected function parse( $text )
	{
		// Parse out the times and then split by them.
		preg_match_all('/\d{4}-\d\d-\d\d\ (\d\d:\d\d:\d\d)\ ---\ /mx', $text, $times, PREG_PATTERN_ORDER);
		$times = $times[1];
		$lines = preg_split('/\d{4}-\d\d-\d\d\ \d\d:\d\d:\d\d\ ---\ /mx', $text);
		$lines = array_slice( $lines, 1 );
		$log = array();
		foreach($lines as $i => $line) {
			if (preg_match('/^(\w+): (.+)$/sm', $line, $s_parts)) {
				$header = $s_parts[1];
				$details = trim($s_parts[2]);
				if (preg_match('/^([a-z_\-]+) ?(?:\[ ?(\-?\d+) ?\])?: ?(.*)$/si', $details, $s_parts)) {
					$details = '<div class="class">' . $s_parts[1];
					$details .= empty($s_parts[2]) ? '</div>' : ' (' . $s_parts[2] . ')</div>';
					if (preg_match('/^(.*) \[ ?(\d+) ?\]$/s', $s_parts[3], $s_details)) {
						$details .= "\n<p>" . $s_details[1] . "</p>";
						$details .= "\n<p style=\"text-align:right;font-weight:bold;margin-bottom:0;\">(Line #" . $s_details[2] . ")</strong>";
					} else {
						$details .= "\n<p>" . $s_parts[3] . "</p>";
					}
				} else {
					if (preg_match('/^(.*) \[ ?(\d+) ?\]$/s', $details, $s_parts)) {
						$details .= "\n<p>" . $s_parts[1] . "</p>";
						$details .= "\n<p style=\"text-align:right;font-weight:bold;\">(Line #" . $s_parts[2] . ")</strong>";
					}
				}
				array_push($log, array(
					// Set the time.
					'time' => date("g:i:s A", strtotime($times[$i])),
					// Then the header.
					'header' => $header,
					// And finally, the details.
					'details' => $details,
				));
			}
		}

		return  View::factory('console/entry')->set('log', $log)->render();
	}

	/**
	 * Builds the Log Directory tree
	 *
	 * @param	string	Active file
	 * @return	string
	 */
	protected function build_directory( $file )
	{
		$logs = Kohana::list_files($this->dir);

		if( $logs )
		{
			// Create directory array
			$dir = array();

			// Get the active file info
			$active = ($file) ? pathinfo( $file ) : NULL;
			krsort($logs);

			foreach( $logs as $years => $months )
			{
				krsort( $months );

				foreach( $months as $path => $files )
				{
					krsort($files);

					foreach( $files as $file => $path )
					{
						list( $logs, $year, $month, $fn ) = explode( DIRECTORY_SEPARATOR, $file );
						
						$day = explode('.', $fn);
						$dir[$year][$month][$day[0]] = str_replace($this->dir . DIRECTORY_SEPARATOR, '', $file);
						$dir[$year][$month][$day[0]] = str_replace(DIRECTORY_SEPARATOR, '/', $dir[$year][$month][$day[0]]);
						$dir[$year][$month][$day[0]] = str_replace('.php', '', $dir[$year][$month][$day[0]]);
					}

				}

			}

			return View::factory('console/directory')->set('dir', $dir)->set('active', $active)->set('base', Kohana::$base_url)->render();
		}

	}

	/**
	 * Gets the headline (Usually just formatting the log date)
	 *
	 * @param	string	Filename
	 * @return	string	Formatted Date
	 */
	protected function headline( $file )
	{
		if( ! $file )
		{
			return 'Welcome to Console!';
		}
		else
		{
			$path = pathinfo($file);
			list( $year, $month ) = explode( '/', $path['dirname'] );
			$day = $path['filename'];

			return sprintf( '%s %s, %s', Console::get_month($month), $day, $year );
		}
	}

	/**
	 * Displays media files
	 */
	public function action_media()
	{
		// Get the file path from the request
		$file = $this->request->param('file');

		// Find the file extension
		$path = pathinfo($file);
		// Array ( [dirname] => css [basename] => reset.css [extension] => css [filename] => reset )
		$file = Kohana::find_file('media', $path['dirname'] . DIRECTORY_SEPARATOR . $path['filename'], $path['extension']);

		if ($file)
		{
			// Send the file content as the response
			$this->request->response = file_get_contents($file);
		}
		else
		{
			// Return a 404 status
			$this->request->status = 404;
		}

		// Set the content type for this extension
		$this->request->headers['Content-Type'] = File::mime_by_ext($path['extension']);
	}

}
