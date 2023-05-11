<?php
session_start();
if ($_SESSION['username'] != null) {
    if ($_SESSION['username'] != "IMS NDY SAS"){
        http_response_code(403);
        die();
    }
}

$servername = "3.251.28.226";
$dbUser = "dashboardUser";
$dbPwd = "9@@2Zmaf(v2!2sJ-";
$dbname = "enigma_db";



$dbConn = new mysqli($servername, $dbUser, $dbPwd, $dbname); //Only SELECTS

if ($dbConn->connect_error) {
    die("Connection failed: " . $dbConn->connect_error);
}
// Create your database query statement
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enigma Dashboard</title>
    <meta name="title" content="Emigma">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/Favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/Favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/Favicons/favicon-16x16.png">
    <link rel="manifest" href="../images/Favicons/site.webmanifest">
    <link href="../css/adminStyles.css" rel="stylesheet">
	<script src="../js/jquery/3.4.1/jquery.min.js"></script>
    <script src="../js/disableOptions.js"></script>


</head>
<body>
    <div class="dashboard">
        <aside class="title">
            <header class="panels-title">
                <h1>Administrator Dashboard</h1>
            </header>
        </aside>
		<header class="side-menu">
			<figure class="admin">
				<div class="avatar">
					<img src="../images/GraphicElements/Admin-Profile-Vector-PNG-Clipart.png" alt="Admin">
				</div>
				<figcaption>
					Administrator
				</figcaption>
			</figure>
			<nav>
				<section class="tools">
					<h3>Tools</h3>
					<ul>
						<li>
							<a href="#">
								IoT Manager
							</a>
						</li>
						<li>
							<a href="http://3.251.28.226/phpmyadmin/">
								DB Manager
							</a>
						</li>
						<li>
							<a href="http://192.168.0.98">
								Services Manager
							</a>
						</li>
						<li>
							<a href="../index.html">
								Return to WebSite
							</a>
						</li>
					</ul>
				</section>
			</nav>
		</header>
		<main class="content-panels">
			<div class="content">
				<section class="reports">
					<div class="report">
						<div class="repIcon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24">
								<path d="M20 10H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V11a1 1 0 0 0-1-1zm-1 10H5v-8h14v8zM5 6h14v2H5zM7 2h10v2H7z"/>
							</svg>
						</div>
						
						<div class="reporting">
							<span class="repTitle"><?php $query=$dbConn->query( "SELECT COUNT(*) FROM users");
                                $result =$query->fetch_row(); echo $result[0]; ?></span>
							Registered Users
						</div>
					</div>
					
					<div class="report">
						<div class="repIcon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24">
								<path d="M20 10H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V11a1 1 0 0 0-1-1zm-1 10H5v-8h14v8zM5 6h14v2H5zM7 2h10v2H7z"/>
							</svg>
						</div>
						
						<div class="reporting">
							<span class="repTitle"><?php $query=$dbConn->query( "SELECT COUNT(*) FROM access_errors");
                                $result =$query->fetch_row(); echo $result[0]; ?></span>
							Number of failed access
						</div>
					</div>
				</section>
			
				<section class="connections">
					<div class="connection">
						<div class="space">
						</div>
						<div class="conn-content">
							<h2 class="connTitle">Database Server Terminal</h2>
						</div>
						<div class="box-actions">
							<button>
                                <a href="https://3.251.28.226:4200" >
								<svg xmlns="http://www.w3.org/2000/svg" style="cursor: pointer !important;" width="24" height="24" viewbox="0 0 24 24">
									<path d="M18 18H6V6h7V4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-8h-2v7z"/>
									<path d="M17.465 5.121l-6.172 6.172 1.414 1.414 6.172-6.172 2.12 2.121L21 3h-5.657z"/>
								</svg>
                                </a>
							</button>
							<button>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24">
									<path d="M12 3C6.486 3 2 6.364 2 10.5c0 2.742 1.982 5.354 5 6.678V21a.999.999 0 0 0 1.707.707l3.714-3.714C17.74 17.827 22 14.529 22 10.5 22 6.364 17.514 3 12 3zm0 13a.996.996 0 0 0-.707.293L9 18.586V16.5a1 1 0 0 0-.663-.941C5.743 14.629 4 12.596 4 10.5 4 7.468 7.589 5 12 5s8 2.468 8 5.5-3.589 5.5-8 5.5z"/>
								</svg>
							</button>
						</div>
					</div>
					
					<div class="connection">
						<div class="space">
						</div>
						<div class="conn-content">
							<h2 class="connTitle">WebServer Terminal</h2>
						</div>
						<div class="box-actions">
							<button>
                                <a href="https://apacheserver.enigma.who:4200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" style="cursor: pointer !important">
									<path d="M18 18H6V6h7V4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-8h-2v7z"/>
                                        <path d="M17.465 5.121l-6.172 6.172 1.414 1.414 6.172-6.172 2.12 2.121L21 3h-5.657z"/></svg></a>
							</button>
							<button>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24">
									<path d="M12 3C6.486 3 2 6.364 2 10.5c0 2.742 1.982 5.354 5 6.678V21a.999.999 0 0 0 1.707.707l3.714-3.714C17.74 17.827 22 14.529 22 10.5 22 6.364 17.514 3 12 3zm0 13a.996.996 0 0 0-.707.293L9 18.586V16.5a1 1 0 0 0-.663-.941C5.743 14.629 4 12.596 4 10.5 4 7.468 7.589 5 12 5s8 2.468 8 5.5-3.589 5.5-8 5.5z"/>
								</svg>
							</button>
						</div>
					</div>
				</section>
			</div>
		</main>
	</div>
<script>
</script>
</body>
</html>
