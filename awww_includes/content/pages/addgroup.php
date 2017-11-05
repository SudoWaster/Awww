<?php
$PRIVILEGED = true;
require_once __DIR__ . '/../../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../session.php';

$userdata     = UserData::Instance();
?>

<link rel="stylesheet" type="text/css" href="css/groups.css" />

<h1>Tworzenie nowej grupy</h1>

<form id="group-add-form">

  <div class="form-group row">
    <label for="gname" class="col-4 col-form-label">Nazwa:</label>
    <div class="col-8">
      <input type="text" class="form-control" id="gname" value="" required />
    </div>
  </div>

  <div class="form-group row">
    <label for="gvac" class="col-4 col-form-label">Miejsca:</label>
    <div class="col-8">
      <input type="number" class="form-control-inline" id="gvac" value="0" required />
    </div>
  </div>

  <div class="form-group row">
    <div class="col-12">
      <textarea class="form-control" id="gdesc" required></textarea>
    </div>
  </div>

  <div>
    <div class="form-group col-12">
      <input type="submit" role="button" class="btn btn-outline-primary save-group" value="Zapisz" />
    </div>
  </div>
</form>

<script src="js/tinymce/tinymce.min.js"></script>
<script src="js/groups.jquery.js"></script>