<?php
require_once __DIR__ . '/../../awww_engine/UserData.class.php';
require_once __DIR__ . '/../../awww_engine/Session.class.php';

Session::startSession();
$userdata = UserData::Instance();
$userID = Session::getUser()->getID();
$groups = $userdata->getUserGroups($userID);
?>

          <nav class="col-12 col-sm-3 col-md-2 d-sm-block bg-light sidebar">
            <ul class="nav nav-pills flex-column">
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="overview" href="#!overview">Przegląd</a>
              </li>
            </ul>
            
            <ul class="nav nav-pills flex-column">
              
              <li class="nav-item">
                <h5>Grupy</h5>
              </li>
              
              <?php 
              if (Session::getUser()->isPrivileged()) { ?>
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="addgroup" href="#!addgroup">Stwórz</a>
              </li>
              <?php } ?>
              
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="groupsign" href="#!groupsign">Zapisy</a>
              </li>

              <?php

              // display user groups
              foreach ($groups as $group) {
              ?>

              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="group?id=<?php echo $group['group_id']; ?>" href="#"><?php echo $group['group_name']; ?></a>
              </li>

              <?php
              }
              ?>
              
            </ul>
            
            <ul class="nav nav-pills flex-column">
              
              <li class="nav-item">
                <h5>Profil</h5>
              </li>
              
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="user?id=<?php echo Session::getUser()->getID(); ?>" href="#">Podgląd</a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="settings" href="#!settings">Ustawienia</a>
              </li>
              
            </ul>
            
            <?php
            if (Session::getUser()->isAdmin()) { ?>
            
            <ul class="nav nav-pills flex-column">
              
              <li class="nav-item">
                <h5>System</h5>
              </li>
              
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="manageusers" href="#!manageusers">Użytkownicy</a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link no-refresh" data-ref="manageawww" href="#!manageawww">Ustawienia systemu</a>
              </li>
            </ul>
            
            <?php } ?>
          </nav>