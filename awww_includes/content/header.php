<?php 
require_once __DIR__ . '/../../awww_engine/Config.class.php';
require_once __DIR__ . '/../../awww_engine/Session.class.php';

Session::startSession();
$config = Config::Instance();
?>

<!doctype html>
<html lang="pl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.png">

    <title>Panel AWWW</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <link href="css/main.css" rel="stylesheet">
  </head>
  
  <body>
    <header>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        
        <a id="brand" class="navbar-brand" href="<?php echo $config->get()['PANEL']['PAGE']; ?>"><?php echo $config->get()['PANEL']['TITLE']; ?></a>
        
        <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link no-refresh" data-ref="overview" href="apanel">Panel</a>
            </li>
            <li class="nav-item">
              <a class="nav-link no-refresh" data-ref="settings" href="#!settings">Ustawienia</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="alogin?logout">Wyloguj</a>
            </li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item pull-right">
              <a class="nav-link no-refresh" data-ref="user?id=<?php echo Session::getUser()->getID(); ?>" href="#">Witaj, <?php echo Session::getUser()->getName(); ?></a>
            </li>
          </ul>
        </div>
      </nav>
    </header>