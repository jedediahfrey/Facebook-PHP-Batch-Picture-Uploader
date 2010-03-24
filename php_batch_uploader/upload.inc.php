<?php


function batchPrep($images) {
}

# Wait for all thumbnail processing threads to finish.
function waitToProcess($procs) {
	do {
		# Set the count of running processes to 0.
		$running = 0;
		# For each process
		foreach($procs as $proc) {
			# Get process status
			$r = proc_get_status($proc);
			# Increment the number of running threads.
			if ($r["running"]) $running++;
		}
	}
	while ($running != 0); # While the number running process isn't 0, keep checking.
	
}

function uploadImages($images) {
}
# Upload the photo
function uploadImage($aids, $image) {
	global $fbo, $temp_file;
	$errors = 1;
	while (1) {
		try {
			# Make the thumbnail.
			# Get the album caption
			$caption = getCaption($image);
			# Upload the photo
			#$fbReturn = $fbo->api_client->photos_upload($temp_file, end($aids), $caption);
			# If the image was uploaded successfully
			disp("Uploaded: $image", 2);
			# Break the while loop
			break;
			# Catch exception
			
		}
		catch(Exception $e) {
			if ($e->getMessage() == "Album is full") {
				# If the album is full, get a new album name & return album ids
				$aids = getAlbumId(getAlbumBase($image));
				# Give the uploader 2 chances to generate thumbnail and upload picture
				
			} elseif ($errors >= 2) {
				# Display error and continue on
				disp("Unexpected Error #$errors: " . $e->getMessage() . ", skipping $image", 1);
			} else {
				disp("Unexpected Error #$errors: " . $e->getMessage(), 2);
				$errors++;
				# Occasionally happens when there are too many API requests, slow it down.
				sleep(2);
			}
		}
	}
	# Return album IDs
	return $aids;
}
# Get the album ID if the album exists, else create the album and return the ID.
function getAlbumIds($album_name, $description = "") {
	global $albums, $fbo, $uid;
	# Get a list of user albums
	$albums=getAlbums();
//	$album_name="Road Trip - May 2006";
	if ($idx[]=array_search($album_name,$albums["name"])) {
		disp("Found $album_name",6);
		for ($i=2;$idx_tmp=array_search("$album_name #$i",$albums["name"]);$i++) {
				$idx[]=$idx_tmp;
				disp("Found $album_name #$i",6);
		}
		print_r($albums);
		$albums=arrayMutate($albums);
		print_r($albums);
		die;
	} else {
		disp("$album_name not found. Creating.",2);
	}
	foreach ($idx as $i) {
		$albums2[]=$albums[$i];
	}
	print_r($albums2);
	die;
	return $idx;
	print_r($idx);
	print_r($albums);
	print_r(array_keys($albums));
	die;
	
	die;
	$i = 0;
	# Create Album IDs array
	$aids = array();
	# For each of the albums
	while ($i < count($albums)) {
		# If the album name is the same as the current increment album
		if ($albums[$i]['name'] == $albumName) {
			# Check if album is full.
			if ($albums[$i]['size'] >= 200) { # Limit of 200 photos per album.
				// If the album is full, generate a new name.
				disp("$albumName is full", 2);
				// Build $aid array of all aids associated with current Album Name.
				$aids[] = $albums[$i]['aid'];
				// Generate a new album name based on the current name
				$albumName = genAlbumName($albumName);
				// Reset search index, start searching from the beginning of album list with the new album
				$i = 0;
				continue;
			} else {
				// If it is not full, find out the aid.
				$aids[] = $albums[$i]['aid'];
				return $aids;
				break;
			}
		}
		$i++;
	}
	disp("Create Album: $albumName ($description)", 5);
	$aids[] = $album['aid'];
	return $aids;
}
# getAlbumBase - Get the base name of an album based on the mode.
# Input: $image - Image to get the album base for.
function getAlbumBase($image) {
	global $root_dir, $mode;
	if ($mode == 1) {
		# Mode 1: Album name = folder image is in
		$album_name = basename(dirname($image));
	} elseif ($mode == 2) {
		# Moded 2: Album name = root folder
		$album_name = basename($root_dir);
	} else {
		disp("Invalid Mode: $mode", 1);
	}
	return $album_name;
}

# genAlbumName - Generate a new album name.
# Input: $baseAlbumName - base name of album.
# Output: return the newName.
function genAlbumName($baseAlbumName) {
	// Determine if the album name 'My Album #2' etc is in use.
	if (preg_match('/([^#]+) #([\\d]+)/', $baseAlbumName, $regs)) {
		// If so, increment the number by 1.
		$newName = $regs[1] . " #" . (intval($regs[2]) + 1);
	} else {
		// Else, album name is #2.
		$newName = $baseAlbumName . " #2";
	}
	disp("Generated new album name $newName from $baseAlbumName", 6);
	// Return the new name
	return $newName;
}
# getCaption - Get the caption for the image based on the mode.
# Input: $image - Image file to generate caption for.
# Output: Caption of image file.
function getCaption($image) {
	global $root_dir, $mode;
	$root_dir = substr($root_dir, -1) == "/" ? $root_dir : $root_dir . "/";
	if ($mode == 1) {
		# In Mode 1 (where each (sub)directory gets its own album, just use the file name
		$caption = pathinfo($image, PATHINFO_FILENAME);
	} elseif ($mode == 2) {
		# Define the glue for the caption.
		$glue = " - ";
		# Replace the root directory with nothing.
		$dir_structure = explode(DIRECTORY_SEPARATOR, str_replace($root_dir, "", $image));
		# Generate a caption based on the folder's relative
		$caption = pathinfo(implode($glue, $dir_structure), PATHINFO_FILENAME);
	} else {
		disp("Invalid Mode", 1);
	}
	# Trim off excess white spaces.
	$caption = trim($caption);
	disp("Got Caption: $caption for $image", 6);
	return $caption;
}