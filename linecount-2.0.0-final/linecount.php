<?php
	# Line Counter version 2.0
	# Developed by Timothy McClatchey at LinkTech Engineering
	# Email: timothy@mcclatchey.us
	# Website: http://timothy.mcclatchey.us/
	# Company website: http://www.linktechengineering.net/
	#
	# Feel free to copy and use any part or all of this script as use see fit. We hope everyone
	# who needs a script as this can find value in this one here. Our only request is to be sure
	# you let us maintain credit, unless you rewrite it of course.
	# If you have any comments or questions, let us know!
	# support@linktechengineering.net
	#
	# command line usage: php lc.php
	
	define('INCLUDE_EXTENSIONS', 'php|js|html|htm|css');
	define('EXCLUDE_FILES', 'linecount.php');
	
	class LineCounter extends ArrayObject {
		private $extensions, $exclusions, $root;
		
		private $totalLines, $totalFiles, $totalSize;
		
		public function __construct($extensions, $exclusions) {
			$this->totalLines = 0;
			$this->totalFiles = 0;
			$this->totalSize = 0;
			$this->extensions = explode('|', $extensions);
			$this->exclusions = explode('|', $exclusions);
			$this->files = array();
			$this->root = dirname(__file__);
			if (substr($this->root, 0 - strlen(DIRECTORY_SEPARATOR), strlen(DIRECTORY_SEPARATOR)) != DIRECTORY_SEPARATOR) {
				$this->root.= DIRECTORY_SEPARATOR;
			}			
		}
		
		public function __get($variableName) {
			$variableName = strtolower($variableName);
			switch ($variableName) {
				case 'lines': return number_format($this->totalLines);
				case 'files': return number_format($this->totalFiles);
				case 'size': return format_filesize($this->totalSize);
				case 'root': return $this->root;
			}
		}
		
		public function Process() {
			$this->processDirectory($this->root, '.'.DIRECTORY_SEPARATOR);
		}
		private function processDirectory($directory, $displayAs) {
			$list = glob($directory.'*/', GLOB_ONLYDIR);
			foreach ($list as $item) {
				$base = basename($item);
				if (!in_array($base, $this->exclusions)) {
					$this->processDirectory($item, $displayAs.$base.'/');
				}
			}
			$list = glob($directory.'*.{'.implode(',', $this->extensions).'}', GLOB_BRACE);
			foreach ($list as $item) {
				$base = basename($item);
				if (!in_array($base, $this->exclusions)) {
					$count = 0;
					$handle = @fopen($item, 'r');
					if ($handle !== false) {
						$this->totalFiles++;
						$this->totalSize += filesize($item);
						while (!feof($handle)) {
							$buffer = fgets($handle, 4096);
							$count += substr_count($buffer, PHP_EOL);
						}
						$this->totalLines += $count;
						$this[$displayAs.$base] = format_filesize($count);
						fclose($handle);
					}
				}
			}
		}
	}
	
	function format_filesize($size)
	{
		$sizes = array(
			'Bytes', 'Kilobytes', 'Megabytes', 'Gigabytes', 'Terabytes', 
			// Just a few more for fun
			'Petabytes', 'Exabytes', 'Zettabyte', 'Yottabyte', 'Brontobyte',
			'Geopbyte'
		);
		for ($i = 0; $i < count($sizes); $i++) {
			if ($size < pow(1024, $i+1) || $i == count($sizes) - 1) {
				if ($i == 0) {
					$size = number_format($size);
				} else {
					$size = number_format($size / pow(1024, $i), 2);
				}
				return $size.' '.$sizes[$i];
			}
		}
		return $size;
	}
	
	$counter = new Linecounter(INCLUDE_EXTENSIONS, EXCLUDE_FILES);
	$counter->Process();
	
	if (array_key_exists("HTTP_HOST", $_SERVER)) {
		echo '<html><head><title>Line Counter 2.0 ('.$counter->Root.')</title><meta name="author" content="LinkTech Engineering">';
		echo '<style>';
		echo 'body { font-size: 12pt; }';
		echo 'h1 { font-size: 18pt; margin: 0px 0px 4px 0px; }';
		echo 'h2 { font-size: 14pt; margin: 0px 0px 4px 0px; }';
		echo 'dl { display: table;  width: 100%; margin: 0 auto; border-width: 1px; border-style: solid; border-color: #c0c0c0; border-radius: 4px; }';
		echo 'dt { display: table-cell: width: 1px; white-space: nowrap; padding: 4px 4px 4px 12px; text-align: right; font-size: 10pt; font-weight: bold; border-width: 0px 1px 0px 0px; border-style: solid; border-color: #c0c0c0; }';
		echo 'dd { display: table-cell; width: 100%; padding: 2px 4px; }';
		echo 'dd.break { display: table-row; }';
		echo 'hr { border-width: 0px; height: 8px; }';
		echo 'table { width: 100%; border-collapse: collapse; border-width: 1px; border-style: solid; border-color: #c0c0c0; }';
		echo 'tr {  }';
		echo 'tr:nth-child(odd) { background-color: #fafafa; }';
		echo 'tr:nth-child(even) { background-color: #e0e0e0; }';
		echo 'td {  }';
		echo 'td.filename { width: 100%; }';
		echo 'td.count { font-size: 10pt; text-align: right; white-space: nowrap; }';
		echo '</style>';
		echo '</head><body>';
		echo '<h1>Line Count</h1>';
		echo '<dl>';
		echo '<dt>Total Files:</dt><dd>'.$counter->Files.'</dd><dd class="break"></dd>';
		echo '<dt>Total Lines:</dt><dd>'.$counter->Lines.'</dd><dd class="break"></dd>';
		echo '<dt>Total Size:</dt><dd>'.$counter->Size.'</dd><dd class="break"></dd>';
		echo '</dl>';
		echo '<hr><h2>Included Files</h2>';
		echo '<table>';
		define("NEWLINE", "<br>");
		define('LINE_BEFORE', '<tr><td class="filename">');
		define('LINE_MIDDLE', '</td><td class="count">');
		define('LINE_AFTER', '</td></tr>');
	} else {
		passthru('clear');
		$cols = @exec('tput cols'); if (!is_numeric($cols)) { $cols = 80; }
		echo 'Line count searching in: '.$counter->Root."\n";
		echo str_repeat('-', $cols);
		echo '--> Total Files : '.$counter->Files."\n";
		echo '--> Total Lines : '.$counter->Lines."\n";
		echo '--> Total Size  : '.$counter->Size."\n";
		echo str_repeat('-', $cols);
		$handle = @fopen('php://stdin', 'r');
		if ($handle == false) {
			$c = 'y';
		} else {
			echo 'Would you like to review the included files? [y/N] ';
			$c = fgetc($handle);
			fclose($handle);
			if (strtolower($c) != 'y') { $c = 'n'; }
		}
		if ($c == 'n') { return; }
		define("NEWLINE", "\n");
		define('LINE_BEFORE', '');
		define('LINE_MIDDLE', "\t");
		define('LINE_AFTER', "\n");
	}
	
	foreach ($counter as $file => $count) {
		echo LINE_BEFORE.$file.LINE_MIDDLE.$count.LINE_AFTER;
	}
	
	if (array_key_exists("HTTP_HOST", $_SERVER)) { echo '</table></body></html>'; }
?>