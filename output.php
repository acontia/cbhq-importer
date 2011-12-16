<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

  <head>
    <title>CBHQ importer</title>
    <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
  </head>
  
  <body>
  
    <div class="container-fluid" style="margin-bottom:80px">
  
      <div class="topbar">
        <div class="topbar-inner">
          <div class="container-fluid">
            <h3><a href="http://www.cameronandwilding.com">CBHQ importer</a></h3>
            <ul class="nav">
              <li><a href="index.php">Home</a></li>
              <li><a href="index.php?page=import-tickets">Import tickets</a></li>
              <li><a href="index.php?page=time-tracking">Time tracking</a></li>
            </ul>
            <ul class="nav secondary-nav">
              <li><?php
                if(!empty($_SESSION["user"])) {
                  print (get_logout_form());
                }
              ?></li>
            </ul>
          </div>
        </div><!-- /topbar-inner -->
      </div>
      
    </div>
    
  
    <div class="container-fluid">    
    
      <?php print ($content); ?>
      
      <div>
      
        <?php
        /*
        <p><b>$_POST:</b></p>
        <?php krumo($_POST); ?>
        
        <p><b>$_FILES:</b></p>
        <?php krumo($_FILES); ?>
        
        <p><b>$_SESSION:</b></p>
        <?php krumo($_SESSION); ?>
        */
        ?>
        
        
      </div>

      
    </div>
  
  </body>
  
</html>