<?php
	# line counter version 1.1
	if (array_key_exists("HTTP_HOST", $_SERVER))
	{
		define("NEWLINE", "<br>");
		define("TAB", "&nbsp;&nbsp;&nbsp;&nbsp;");
	}
	else
	{
		define("NEWLINE", "\n");
		define("TAB", "\t");
	}
		//define("TAB", "----");
	
	$extensions = array("php", "js", "html", "htm", "css");
	$exclusions = array("jquery-1.10.2.min.js", "lc.php", "jquery-ui-1.10.3", "includes");
	$excluded_files = array();
	
	$filecount = 0;
	$totalsize = 0;
	
	function traverse($path, $root = null, $tabs = "")
	{
		global $extensions, $exclusions, $filecount, $excluded_files, $totalsize;
		$first = false;
		$prefix = "";
		if (strlen($tabs) > 0) { $prefix = $tabs."- "; }
		if ($root == null) { $root = $path; $first = true; }
		if (strpos($path, -1, 1) != "/") { $path.= "/"; }
		$count = 0;
		echo $prefix.substr($path, strlen($root)).NEWLINE;
		$files = glob($path."*");
		foreach ($files as $file)
		{
			$base = basename($file);
			if (in_array($base, $exclusions)) { }
			else if (is_dir($file))
			{
				$children = traverse($file, $root, $tabs.TAB);
				$count += $children;
				//echo $file.NEWLINE;
			}
			else
			{
				$totalsize += filesize($file);
				$ext = substr($base, strrpos($base, ".") + 1);
				if (in_array($ext, $extensions))
				{
					$lines = count(file($file));
					echo TAB.$prefix.$base.": ".number_format($lines)." Lines".NEWLINE;
					$count += $lines;
					$filecount++;
				}
				else { $excluded_files[] = $file; }
			}
		}
		if (!$first)
		{
			echo $prefix."Total Line Count: ".number_format($count).NEWLINE;
		}
		//echo $prefix."Total 
		return $count;
		//var_dump($_SERVER["HTTP_HOST"]);
	}
	
	function format_filesize($size)
	{
		if ($size < 1024)
		{
			return (number_format($size))." Bytes";
		}
		if ($size < 1024 * 1024)
		{
			return (number_format($size / 1024, 2))." Kilobytes";
		}
		if ($size < 1024 * 1024 * 1024)
		{
			return (number_format($size / 1024 / 1024, 2))." Megabytes";
		}
		else
		{
			return (number_format($size / 1024 / 1024 / 1024, 2))." Gigabytes";
		}
	}
	$path = str_replace("\\", "/", dirname(__file__));
	echo "Counting lines of all files in: ".$path.NEWLINE;
	echo str_repeat("#", 80).NEWLINE;
	$count = traverse($path);
	echo str_repeat("#", 80).NEWLINE;
	echo "Found ".number_format($count)." lines in ".number_format($filecount)." files using ".format_filesize($totalsize);
?>