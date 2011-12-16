<?php

class CBHQ {
  
  /**
   * The format of the responses. At the moment other than "array" will return a XML string
   */
  public $output = "array";
  
  /**
   * Array
   */
  public $user = NULL;
  
  public function login($domain, $username, $api_key) {
  
    $user = array(
      "domain" => $domain,
      "username" => $username,
      "api_key" => $api_key,
    );
    
    //doesn't exist a function to check login details, so use this one
    $result = $this->request($user, "projects", "GET");
    if($result == FALSE) {
      return FALSE;
    }
    else {
      $this->user = $user;
      return $user;
    }

  }
  
  
  public function get_projects() {
    $user = $this->user;    
    $result = $this->request($user, "projects", "GET");        
    return $result;
  }
  
  public function get_project_users($project_id) {
    $user = $this->user;
    $result = $this->request($user, $project_id . '/assignments', 'GET');
    return $result;
  }
  
  public function get_project_milestones($project_id) {
    $user = $this->user;
    $result = $this->request($user, $project_id . '/milestones', 'GET');
    return $result;
  }
  
  public function get_project_statuses($project_id) {
    $user = $this->user;
    $result = $this->request($user, $project_id . '/tickets/statuses', 'GET');
    return $result;
  }
  
  public function get_project_priorities($project_id) {
    $user = $this->user;
    $result = $this->request($user, $project_id . '/tickets/priorities', 'GET');
    return $result;
  }
  
  public function get_ticket_types() {
    $ticket_types[0]['id'] = 'bug';
    $ticket_types[0]['name'] = 'Bug';    
    $ticket_types[1]['id'] = 'enhancement';
    $ticket_types[1]['name'] = 'Enhancement';    
    $ticket_types[2]['id'] = 'task';
    $ticket_types[2]['name'] = 'Task';
    
    return $ticket_types;
  }
  
  /**
   * Inserts a new ticket
   * 
   * $project_id The project id
   * $ticket an array of properties for the ticket. See http://docs.atechmedia.com/codebase/developer-api/tickets-and-milestones
   */
  public function create_ticket($project_id, $ticket) {
  
      $ticket_body = $ticket["body"];  
      $user = $this->user;
        
      //To create a ticket we need to create the ticket itself and the add a note (body)
      
      //Create ticket
      unset($ticket["body"]);    
      $ticket_xml = '<ticket>';
      foreach($ticket as $field => $value) {
        $ticket_xml .= '<'.$field.'>' . $value . '</'.$field.'>'; 
      }      
      $ticket_xml .= '</ticket>';
    
      $result["ticket"] = $this->request($user, $project_id . '/tickets', 'POST', $ticket_xml);
      
      $ticket_id = $result["ticket"][0];
      
      //TODO: Separate to another funcion ->insert_ticket_note
      
      //Create note
      $note_xml = '<ticket-note>';
        //@todo: Change name of field from "body" to "content"
        $note_xml .= '<content>' . $ticket_body . '</content>';
        $note_xml .= '<changes></changes>';
      $note_xml .= '</ticket-note>';
      
      $result["note"] = $this->request($user, $project_id . '/tickets/' . $ticket_id . '/notes', 'POST', $note_xml);    
      
      return $result;
  }
  
  
  
  /**
   * Makes a query to the API and returns the response
   */
  private function request($user, $path, $type = "POST", $fields = NULL) {

    $domain = $user["domain"];
    $username = $user["username"];
    $api_key = $user["api_key"];
  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, $domain . '/' . $username . ":" . $api_key); 
    curl_setopt($ch, CURLOPT_URL, 'http://api3.codebasehq.com/' . $path);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Accept', 'application/xml'));              
    curl_setopt($ch, CURLOPT_HEADER, 1); 
    
    if($type == "POST") {
      curl_setopt($ch, CURLOPT_POST, 1);
    }
    
    if(!empty($fields)) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }                                                                             
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);                     
    curl_close($ch);
    
    $parsed_response = $this->parse_request_response($response);    
    return $parsed_response;    
  }
  
  /**
   * Parses a response separating headers from XML
   */
  private function parse_request_response($response) {
    $xml_start_pos = strpos($response, '<?xml');
    
    if($xml_start_pos !== FALSE) {
      
      $headers = substr($response, 0, $xml_start_pos);
      
      if( (strpos($headers, '200 OK') !== FALSE) || (strpos($headers, '201 Created') !== FALSE) ) {
        $xml = substr($response, $xml_start_pos);
        
        
        //Instead of returning this array, we should return an array like
        // array(
        //  "headers",
        //  "values",
        // );
        // and process the result on each function. The response is not always on the same
        // format so we can't make it general
        
        if($this->output == "array") {
          //unset($array["@attributes"]);
          $values = array_values($this->xmlstr_to_array($xml));
          if(is_array($values[0])) {
            $values_0 = array_values($values[0]);
          }
          
          if(is_array($values_0[0])) {
            return $values[0];
          }
          else{
            return array($values[0]);
          }

        }
        else{
          return $xml;
        }

      }
    }
    
    return FALSE;
  }
  
  
  private function xmlstr_to_array($xmlstr) {
    $doc = new DOMDocument();
    $doc->loadXML($xmlstr);
    return $this->domnode_to_array($doc->documentElement);
  }
  
  private function domnode_to_array($node) {
    $output = array();
    switch ($node->nodeType) {
     case XML_CDATA_SECTION_NODE:
     case XML_TEXT_NODE:
      $output = trim($node->textContent);
     break;
     case XML_ELEMENT_NODE:
      for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
       $child = $node->childNodes->item($i);
       $v = $this->domnode_to_array($child);
       if(isset($child->tagName)) {
         $t = $child->tagName;
         if(!isset($output[$t])) {
          $output[$t] = array();
         }
         $output[$t][] = $v;
       }
       elseif($v) {
        $output = (string) $v;
       }
      }
      if(is_array($output)) {
       if($node->attributes->length) {
        $a = array();
        foreach($node->attributes as $attrName => $attrNode) {
         $a[$attrName] = (string) $attrNode->value;
        }
        $output['@attributes'] = $a;
       }
       foreach ($output as $t => $v) {
        if(is_array($v) && count($v)==1 && $t!='@attributes') {
         $output[$t] = $v[0];
        }
       }
      }
     break;
    }
    return $output;
  }

}//class