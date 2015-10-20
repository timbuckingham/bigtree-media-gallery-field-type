<?
	$options = $_POST["options"];
	$options["validation"] = "required";
	$type = $_POST["type"];

	// If we're using a preset, the prefix may be there
	if (!empty($options["preset"])) {
		if (!isset($bigtree["media_settings"])) {
			$bigtree["media_settings"] = $cms->getSetting("bigtree-internal-media-settings");
		}
		$preset = $bigtree["media_settings"]["presets"][$options["preset"]];
		if (!empty($preset["min_width"])) {
			$options["min_width"] = $preset["min_width"];
		}
		if (!empty($preset["min_height"])) {
			$options["min_height"] = $preset["min_height"];
		}
	}

	// We're going to fake post data here so it includes properly in the form
	if (isset($_POST["data"])) {
		$origin_data = json_decode(base64_decode($_POST["data"]),true);
		$type = $origin_data["type"];
		$data = $origin_data["info"];

		if ($type == "image") {
			$data["*photo"] = $origin_data["image"];
		} else {
			$data["*video"]= $origin_data["video"];
			$data["*video"]["image"] = $origin_data["image"];
		}

		// Do weird things to get the values set properly
		$weird_data = array();
		foreach ($data as $key => $val) {
			$weird_data["info][".$key] = $val;
		}

		$_POST["data"] = base64_encode(json_encode($weird_data));
	}	

	if ($type == "photo") {
		$field = array(
			"id" => "*photo",
			"type" => "upload",
			"title" => "Photo",
			"options" => $options
		);
		if ($options["min_width"] && $options["min_height"]) {
			$field["subtitle"] = "(min ".$options["min_width"]."x".$options["min_height"].")";
		}
	} else {
		$field = array(
			"id" => "*video",
			"type" => "com.fastspot.media-gallery-field-type*video",
			"title" => "Video URL",
			"subtitle" => "(include http://)",
			"options" => array("validation" => "link required")
		);
	}

	// Matrix expects this to be encoded
	$field["options"] = json_encode($field["options"]);
	
	// Ensure that the columns is an array
	if (!is_array($_POST["columns"])) {
		$_POST["columns"] = array();
	}

	// Add our media field
	array_unshift($_POST["columns"],$field);

	// Do a funky thing to change the ID of columns so we can retrieve data easier for processing
	foreach ($_POST["columns"] as &$column) {
		$column["id"] = "info][".$column["id"];
	}
	unset($column);

	include SERVER_ROOT."core/admin/ajax/matrix-field.php";
?>