<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "3.251.28.226";
	$dbUser = "dashboardUser"; //Only SELECTS
	$dbPwd = "9@@2Zmaf(v2!2sJ-";
	$fmUser = "formUser"; //Only INSERTS
	$fmPwd = "pJ[m.VP)DwvPN]Br";
	$dbname = "enigma_db";



	$dbConn = new mysqli($servername, $dbUser, $dbPwd, $dbname); //Only SELECTS

	if ($dbConn->connect_error) {
		die("Connection failed: " . $dbConn->connect_error);
	}

	$db_User = $_POST['user-name'];
	$db_Pwd = $_POST['passwd'];
	$statement = $dbConn->prepare('SELECT * FROM users WHERE email = ? AND password_hash = PASSWORD(?)');
	$statement->bind_param('ss', $db_User, $db_Pwd);
	$statement->execute();
	$result = $statement->get_result();

	if ($result->num_rows == 1) {
		$row = $result->fetch_assoc();
		if ($row['is_root'] == 1) {
			$_SESSION['username'] = "IMS NDY SAS";
			header("Location: ../mainPages/adminDashboard.php" );
		} else {
			$_SESSION['username'] = "COMMON UNAME";
			header("Location: ../errorPages/404ErrorPage.html");
		}
		$dbConn->close();
	}else {
			//CLOSE PREV CONNECTION
			$dbConn->close();
			//NEW CONNECTION
			$dbConn2 = new mysqli($servername, $fmUser, $fmPwd, $dbname); //Only SELECTS

			if ($dbConn2->connect_error) {
				die("Connection failed: " . $dbConn->connect_error);
			}
			//PREPARE
			$intruder_message = "Invalid username: " . $db_User;
			$statement2 = $dbConn2->prepare('INSERT INTO access_errors (int_logs, int_date) VALUES (?, NOW())');
			$statement2->bind_param('s', $intruder_message);
			$statement2->execute();
			$result2 = $statement2->affected_rows;
			if ($result2 == 1) {
				echo "<script>console.log('Data Saved');</script>";
			}
			$dbConn2->close();
			header("Location: ../index.html" );

	}
}

