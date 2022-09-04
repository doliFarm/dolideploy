<html>
<head>
<title>DoliDeploy</title>
</head>
<body>
<h1>DoliDeploy v0.1</h1>
<?php
// ToDo:
//  - check PHP write permission
//  - better error checking 

$conf = parse_ini_file('./deploy.ini',1);
//print_r($conf);

foreach ($conf as $cliente => $data) {
	if($data["enabled"] != 1) {
		echo "<h2>Skipping ". $cliente. "</h2><br>";
		continue;
	}
	
	echo "<h2>Sito " . $cliente . "</h2>";

	$branch = $data["branch"];
	$location = $data["location"];
	$backuplocation = $data["backuplocation"];


	$modules = explode(",", $data["modules"]);
	foreach($modules as $module) {
		echo "<h4> - Deploying module " . $module . ".</h4>";
		
		$url = "https://github.com/doliFarm/" . $module . "/archive/refs/heads/" . $branch . ".zip";
		$zipfile = $module . ".zip";
		
		//download from github
		echo "Downloading " . $url . "<br>";
		exec("wget " . $url . " -O " . $zipfile . " > /dev/null 2>&1");
		// ToDo check wget error
		echo "Download complete, I hope (no error checking).<br>";
		
		// extract files and remove zip file
		echo "Extracting files.<br>";
		$zip = new ZipArchive;
		$res = $zip->open($zipfile);
		if ($res === TRUE) {
			$zip->extractTo("./");
			$zip->close();
			
			unlink($zipfile);
			// ToDo check unlink error
			
			echo 'Extraction complete.<br>';
		} else {
			echo '<b>Failed unzipping!</b><br>';
		}
		
		// creating backup of old module
		$backupfile = $backuplocation . "/" . $module . date('_Y-m-d_His') ;
		echo "Creating backup of old module in " . $backupfile . ".<br>";
		exec("mv " .  $location . "/" . $module . " " . $backupfile);
		//ToDo check exec error
		
		// moving files to location
		echo "Moving module to " . $location . "/" . $module . ".<br>";
		exec("mv ./" . $module . "-" . $branch . " " .  $location . "/" . $module);
	}
}
?>
</body>
</html>