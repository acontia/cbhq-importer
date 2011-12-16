<?php 

/**
 * Returns a select list field to select project
 */
function get_select_active_project_field($projects) {
  
  $projects_array = array();
  foreach($projects as $project) {
   if($project["status"] == "active") {
     $projects_array[$project["permalink"]] = $project["name"];
   }
  }  
  asort($projects_array);
  
  $select_project = '<select id="project" name="project" >';
  
  $select_project .= '<option value="">-- Select project --</option>';
  foreach($projects_array as $permalink => $name) {
    $select_project .= '<option value="' . $permalink . '">' . $name . '</option>';
  }
  
  $select_project .= '</select>';
  
  return $select_project;
}


/**
 * Returns a select list field to select user
 */
function get_select_user_field($users, $id = "users", $default = NULL) {
  $default_value = NULL;//stores the default
  
  $users_array = array();
  if(!empty($users)) {
    foreach($users as $user) {
      $first_last = implode(' ', array($user['first-name'], $user['last-name']));
      $users_array[$user["id"]] = $first_last . ' (' . $user["username"] . ')';
      
      if($default == $first_last) {
        $default_value = $users_array[$user["id"]];
      }
      
    }  
  }
  
  asort($users_array);
  
  $select = '<select class="medium" id="' . $id . '" name="' . $id . '" >';
  
  $select .= '<option value="">-- Select user --</option>';
  foreach($users_array as $user_id => $name) {
    if($name == $default_value) {
      $default_txt = ' selected="selected"';
    }
    else{
      $default_txt = '';
    }
    
    $select .= '<option value="' . $user_id . '"' . $default_txt . '>' . $name . '</option>';
    
  }
  
  $select .= '</select>';
  
  return $select;
}


/**
 * Generic function that returns a select list field to select a element
 */
function get_select_element_field($elements, $id = "elements", $default = NULL) {
  
  $default_value = NULL;//stores the default
  
  $elements_array = array();
  if(!empty($elements)) {
    foreach($elements as $element) {
      $elements_array[$element["id"]] = $element["name"];
      
      if($default == $element["name"]) {
        $default_value = $elements_array[$element["id"]];
      }
      
    }
  }
  
  asort($elements_array);
  
  $select = '<select class="small" id="' . $id . '" name="' . $id . '" >';
  
  $select .= '<option value="">-- Select --</option>';
  foreach($elements_array as $element_id => $element_name) {
    if($element_name == $default_value) {
      $default_txt = ' selected="selected"';
    }
    else{
      $default_txt = '';
    }
    
    $select .= '<option value="' . $element_id . '"' . $default_txt . '>' . $element_name . '</option>';
    
  }
  
  $select .= '</select>';
  
  return $select;
}


function get_login_form() {
  $code = '
    <form method="post" action="index.php">
      <fieldset>
      
        <legend>Login</legend>
        
        <input name="action" size="30" type="hidden" value="login">
        
        <div class="clearfix">            
          <label for="domain">Domain</label>
          <div class="input">
            <div class="input-append">
              <input id="domain" name="domain" type="text">
              <label class="add-on">.codebasehq.com</label>
            </div>
          </div> 
        </div><!-- /clearfix -->
        
        <div class="clearfix">            
          <label for="username">Username</label>
          <div class="input">
            <input id="username" name="username" size="30" type="text">
          </div> 
        </div><!-- /clearfix -->
        
        <div class="clearfix">            
          <label for="api_key">API Key</label>
          <div class="input">
            <input class="xlarge" id="api_key" name="api_key" size="30" type="password">
            <span class="help-block">something like "y39q6y6y9c0s722nx5tp5v7dxz3xyywywtg60lx0"</span>
          </div> 
        </div><!-- /clearfix -->
        
        <div class="actions">
          <input type="submit" class="btn primary" value="Log in">&nbsp;<button type="reset" class="btn">Cancel</button>
        </div>
        
      </fieldset>        
        
    </form>
  ';
  
  return $code;
}


function get_logout_form() {
  $code = '
    <form method="post" action="index.php">        
        <input name="action" size="30" type="hidden" value="logout">        
        <input type="submit" value="Logout">
    </form>
  ';
  
  return $code;
}


//Converts a csv file to an array, using the first row titles as keys of the array
function csv_to_array($filename, $separator = ",") {

  $handle = fopen($filename, "r");
  $first_row = @fgetcsv($handle, 1000000, $separator);

  $array = array();

  $row = 1;
  if (($handle = fopen($filename, "r")) !== FALSE) {
    $i = -1;
    while (($data = fgetcsv($handle, 1000000, $separator)) !== FALSE) {
      if ($i != -1) {
        $num = count($data);
        $row++;
        for ($c = 0; $c < $num; $c++) {
          if (!empty($first_row[$c])) {
            $array[$i][trim($first_row[$c])] = trim($data[$c]);
          }
          else {
            $array[$i][] = trim($data[$c]);
          }
        }
      }
      $i++;
    }
    fclose($handle);
  }

  return $array;
}


function set_message($message, $type = "warning") {
  session_start();
  $_SESSION["messages"][] = array("message" => $message, "type" => $type);
}