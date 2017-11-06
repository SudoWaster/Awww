<?php
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
$user         = Session::getUser();

?>

<h1>Ustawienia konta <?php echo $user->getFullName(); ?></h1>

<form id="account-form">
  <div class="row">
    <div class="col-4 col-md-2 item-name">
      Imię
    </div>

    <div class="col-8 col-md-10">
      <input type="text" id="inputName" class="form-control" value="<?php echo $user->getName(); ?>" required />
    </div>
  </div>

  <div class="row">
    <div class="col-4 col-md-2 item-name">
      Nazwisko
    </div>

    <div class="col-8 col-md-10">
      <input type="text" id="inputLastName" class="form-control" value="<?php echo $user->getLastName(); ?>" required />
    </div>
  </div>

  <div class="row">
    <div class="col-4 col-md-2 item-name">
      E-mail
    </div>

    <div class="col-8 col-md-10">
      <input type="text" id="inputMail" class="form-control" value="<?php echo $user->getMail(); ?>" required />
    </div>
  </div>

  <div class="row">
    <div class="col-4 col-md-2">
      Nowe hasło
    </div>

    <div class="col-8 col-md-10">
      <input type="password" id="inputNewPassword" class="form-control" />
    </div>
  </div>

  <div class="row">
    <div class="col-4 col-md-2 item-name">
      Stare hasło
    </div>

    <div class="col-8 col-md-10">
      <input type="password" id="inputPassword" class="form-control" required />
    </div>
  </div>
  <div class="row">
    <div class="col-4 col-md-2 helper-text">
      <strong>Pogrubione</strong> = wymagane
    </div>

    <div class="col-8 col-md-10">
      <input type="submit" class="form-control btn btn-outline-primary" value="Zapisz" />
    </div>
  </div>
  
  
 
</form>
