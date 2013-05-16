<?php

	//some config stuff
	$db['host'] = 'localhost';
	$db['name'] = 'mazetree';
	$db['user'] = 'root';
	$db['pass'] = 'root';
	$db['cset'] = 'utf8';	


	//actual code
	$db = new PDO('mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$db['cset'], $db['user'], $db['pass']);

	if($_GET['t']){
		echo generateJSON($_GET['t'], intval($_GET['max']));
		exit;
	}

	if($_FILES["audio"]["tmp_name"]){		
		if ($_FILES["audio"]["error"] > 0) {
			echo "Error: " . $_FILES["audio"]["error"] . "<br>";
		}else{
			$sound = 'sounds/'.uniqid().'.mp3';
			if( move_uploaded_file($_FILES["audio"]["tmp_name"], $sound) ){
				$stmt = $db->prepare("INSERT INTO trees VALUES(NULL, :sound)");
				$stmt->execute( array(':sound' => $sound) );
				Header( "Location: tree/index.php?t=" . $db->lastInsertId() ); exit;
			}else{
				return json_encode( array('error' => 'upload failed') );
			}
		}
	}

	function generateJSON($tree_id, $max){
		global $db;
		if($max == 0) $max = 30;
		$max--;
 		$trees = $db->prepare("SELECT id, sound FROM trees WHERE id=:id");
		$trees->execute( array(':id' => $tree_id) );
		if($trees->rowCount()>0){
			$tree = $trees->fetch(PDO::FETCH_ASSOC);
			$json['id'] = $tree['id'];
			$json['sounds'][] = $tree;
			$sounds = $db->prepare("SELECT id, sound FROM trees WHERE id<>:treeid ORDER BY RAND() LIMIT $max");
			$sounds->execute( array(':treeid' => $tree['id']) );
			while($sound = $sounds->fetch(PDO::FETCH_ASSOC)) {
				$json['sounds'][] = $sound;
  			}
			return json_encode($json);
		}
		return json_encode( array('error' => 'invalid tree id') );
	}		
			
	
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Audio upload</title>
	</head>
	<body>
		
		<h1>Upload a new audio file:</h1>
		<form action="index.php" method="post" enctype="multipart/form-data">
			<label for="audio">Audio file:</label>
			<input type="file" name="audio" id="audio"><br>
			<input type="submit" name="submit" value="Upload">
			<p id="report"> The soundfile should be:<br>1. Smaller than 512k in size <br> 2. In MP3 format </p>
		</form>  		

 	<script>
		document.forms[0].addEventListener('submit', function( evt ) {
		console.log("hallo");
    	var file = document.getElementById('audio').files[0];
    	if (file) {
    		console.log("there is a file");
    		console.log("it's size = " + file.size);
    		console.log("");
    	}
    	if(file.size < (1024 * 512))
    		//&& (file.type == 'audio/mpeg') || file.type == 'audio/m4a' || file.type == 'audio/wav' ) { // 10 MB (this size is in bytes)
       		console.log(file.size + "this is file.size");
       		console.log("it's type is:"+file.type);      
		} else {
	       		//Prevent default and display error
	       		console.log(file.size + "file size is");
	       		document.getElementById('report').innerHTML = "please make sure that the file"
      		evt.preventDefault();
   		}
	
		}, false);
	
		</script>


	</body>
</html>

