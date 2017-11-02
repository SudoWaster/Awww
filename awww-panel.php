<?php
require_once 'awww_includes/session.php';

include 'awww_includes/content/header.php';
?>
    <div class="container-fluid">
      <div class="row">
        <?php include 'awww_includes/content/toolbar.php'; ?>
        
        <main id="main" role="main" class="col-12 col-sm-9 ml-sm-auto col-md-10 pt-3">
        
          <?php
          $page = 'overview';
          $restricted = array("/", "\\", ".");
          
          if(isset($_GET['a'])) {
            $page = str_replace($restricted, "", $_GET['a']);
          }
          
          include 'awww_includes/content/pages/' . $page . '.php';
          ?>
          
        </main>
      </div>
    </div>
<?php
include 'awww_includes/content/footer.php';
?>