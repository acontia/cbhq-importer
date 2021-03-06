<?php

// Report all errors except E_NOTICE and E_DEPRECATED
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

include("functions.php");
include("CBHQ.class.php");
include("krumo/class.krumo.php");

//add here the contents to be shown
$content = '';


$cb = new CBHQ();
session_start();

//process login form
if($_POST["action"] == "login") {
  unset($_SESSION["user"]);
  
  $domain = $_POST["domain"];
  $username = $_POST["username"];
  $api_key = $_POST["api_key"];
  
  $user = $cb->login($domain, $username, $api_key);
  if($user) {
    $_SESSION["user"] = $user;
  }
  else {
    set_message("Login failed. Try again", "error");
  }

}


//Check if user is logged in
if(!isset($_SESSION["user"]) || (empty($_SESSION["user"])) ) {
  $content .= get_login_form();
}
else {

  $user = $_SESSION["user"];
  $cb->user = $user;
  
  //Edit csv screen
  if($_POST["action"] == "import_tickets") {
  
    //Select user field
    $project_id = $_POST["project"]; 
       
    $project_users = $cb->get_project_users($project_id);    
    $project_milestones = $cb->get_project_milestones($project_id);
    
    //filter milestones. We only want active ones
    if(!empty($project_milestones)) {
      foreach($project_milestones as $i => $milestone) {
        if($milestone["status"] != "active") {
          unset($project_milestones[$i]);
        }
      }
    }
    
    $project_statuses = $cb->get_project_statuses($project_id);
    
    $project_priorities = $cb->get_project_priorities($project_id);
    
    $ticket_types = $cb->get_ticket_types();
    
    $csv_array = csv_to_array($_FILES["file"]["tmp_name"]);
    
    $form = '
      <form method="post" action="index.php">
        <fieldset>
        
          <legend>Import tickets - Step 2</legend>
          
          <input name="action" size="30" type="hidden" value="import_tickets_2">
          <input name="project" size="30" type="hidden" value="' . $project_id . '">
          
          <table>
            <tr>            
              <th>Summary</th>
              <th>Status</th>              
              <th>Reporter</th>
              <th>Assignee</th>
              <th>Priority</th>
              <th>Ticket type</th>
              <th>Milestone</th>
              <th>Estimated time</th>              
              <th>Description</th>              
            </tr>
    ';

    if(!empty($csv_array)) {
      $i = 0;
      foreach($csv_array as $ticket) {
        
        $form .= '
          <tr>
            
            <td>
              <input class="xlarge" id="summary_' . $i . '" name="summary_' . $i . '" size="30" type="text" value="' . $csv_array[$i]["summary"] . '">
            </td>
            
            <td>
              ' . get_select_element_field($project_statuses, "status-id_" . $i, $csv_array[$i]["status"]) . '
            </td>
            
            <td>
              ' . get_select_user_field($project_users, "reporter-id_" . $i, $csv_array[$i]["reporter"]) . '
            </td>
            
            <td>
              ' . get_select_user_field($project_users, "assignee-id_" . $i, $csv_array[$i]["assignee"]) . '
            </td>
            
            <td>
              ' . get_select_element_field($project_priorities, "priority-id_" . $i, $csv_array[$i]["priority"]) . '
            </td>
            
            <td>
              ' . get_select_element_field($ticket_types, "ticket-type_" . $i, $csv_array[$i]["ticket-type"]) . '
            </td>
            
            <td>
              ' . get_select_element_field($project_milestones, "milestone-id_" . $i, $csv_array[$i]["milestone"]) . '
            </td>
            
            <td>
              <input class="mini" id="estimated-time_' . $i . '" name="estimated-time_' . $i . '" size="30" type="text" value="' . $csv_array[$i]["estimated-time"] . '">
            </td>  

            <td>
              <textarea class="xxlarge" id="body_' . $i . '" name="body_' . $i . '" size="30" type="text">' . $csv_array[$i]["body"] . '</textarea>
            </td>
                   
          </tr>
        ';
        
        $i++;
      }
    }
    
    
    $form .='  
          </table>        
          <div class="actions">
            <input type="submit" class="btn primary" value="Import">&nbsp;<button type="reset" class="btn">Reset</button>
          </div>          
          
      </form>
    ';
  
    $content .= $form; 
  
  }
  
//Submit tickets to CBHQ
  else if($_POST["action"] == "import_tickets_2") {
    $i = 0;
    
    while(isset($_POST['summary_' . $i])) {
    
      $fields = array("summary", "ticket-type", "reporter-id", "assignee-id", "priority-id", "status-id", "milestone-id", "estimated-time", "body");
      
      $new_ticket = array();
      foreach($fields as $field) {
        $new_ticket[$field] = $_POST[$field . '_' . $i]; 
      }
      
      //@todo Check security!! using $_POST without filter
      $response = $cb->create_ticket($_POST["project"], $new_ticket);
      $ticket_number = $response["ticket"][0];
      if($ticket_number > 0) {
        set_message('<p>Created ticket: <strong><a href="https://cwoss.codebasehq.com/projects/'.$_POST["project"].'/tickets/'.$ticket_number.'">#'.$ticket_number.' - ' . $_POST['summary_' . $i] . '</a></strong>.</p>', 'success');
      }
      else {
        set_message('<p>Error creating ticket: <strong>' . $_POST['summary_' . $i] . '</strong>.</p>', 'error');
      }
    
      $i++;
    }
    

  }
  
  
  //Logout
  else if($_POST["action"] == "logout") {    
    unset($_SESSION["user"]);
    header('Location: index.php');
    exit();
  }
  
  else if ($_GET["page"] == "import-tickets") {
      
    //Select project screen
  
    $projects = $cb->get_projects();
    $select_project_field = get_select_active_project_field($projects); 
  
    
    $form = '
      <form method="post" action="index.php" enctype="multipart/form-data">
        <fieldset>
        
          <legend>Import tickets</legend>
          
          <input name="action" size="30" type="hidden" value="import_tickets">
          
          <div class="clearfix">            
            <label for="project">Project</label>
            <div class="input">
              ' . $select_project_field . '
            </div> 
          </div><!-- /clearfix -->
          
          <div class="clearfix">            
            <label for="file">CSV file</label>
            <div class="input">
              <input class="input-file" id="file" name="file" type="file">
            </div> 
          </div><!-- /clearfix -->
          
          <div class="actions">
            <input type="submit" class="btn primary" value="Import">&nbsp;<button type="reset" class="btn">Reset</button>
          </div>
          
          
      </form>
    ';
    
    $content .= $form;
  
  }
  
  //Time tracking
  else if ($_GET["page"] == "time-tracking") {
      
    $form = 'time tracking form';
    
    $content .= $form;
  
  }
  
  //Welcome
  else {
    $username = $user["username"];
    $welcome = '
      <h3>Welcome ' . $username . '!</h3>
    ';
    
    $content .= $welcome;
  
  }//else

}


//show messages
$messages = $_SESSION["messages"];
$messages_text = '';
if(!empty($messages)) {
  foreach($messages as $message) {
    $messages_text .= '<div class="alert-message data-alert ' . $message["type"] . '">' . $message["message"] . '</div>';
  }
}
unset($_SESSION["messages"]);

$content = $messages_text . $content;



//HTML output
include ("output.php");


