<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$servername = "3.251.28.226";
	$dbUser = "formUser";
	$dbPwd = "pJ[m.VP)DwvPN]Br";
	$dbname = "enigma_db";



	$dbConn = new mysqli($servername, $dbUser, $dbPwd, $dbname);

	if ($dbConn->connect_error) {
		die("Connection failed: " . $dbConn->connect_error);
	}

	$name = $_POST['fname'];
    $name_parts = explode(" ", $name);
    $pname = $name_parts[0];
    $ftsname = $name_parts[1];
    $snsname = $name_parts[2];
	$mail = $_POST['mail'];
    $group_name = $_POST['g_name'];
    $group_size = $_POST['num'];
    $escape_room = $_POST['book-room'];
    $book_time = $_POST['book-time'];

	$statement = $dbConn->prepare('INSERT INTO booking (book_id, name, ft_surname, sn_suname, email, phone, group_name, group_size, book_date, group_data, escape_name) VALUES 
                                                                                                                                            (NULL, ? , ? , ? , ? , NULL, ? , ? , ? , NULL, ? )');
	$statement->bind_param('sssssiss', $pname, $ftsname, $snsname, $mail, $group_name, $group_size, $book_time, $escape_room );
     $success = $statement->execute();
     $dbConn->close();
     if ($success) {
         header("Location: ../index.html?success=true" );
     } else {
         header("Location: ../index.html?success=false" );
     }
}

