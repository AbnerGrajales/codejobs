<?php
/**
 * Access from index.php:
 */
if(!defined("_access")) {
	die("Error: You don't have permission to access here...");
}

class CPanel_Controller extends ZP_Controller {
	
	private $vars = array();
	
	public function __construct() {		
		$this->app("cpanel");
		
		$this->application = whichApplication();
		
		$this->CPanel = $this->classes("cpanel", "CPanel", NULL, "cpanel");
		
		$this->isAdmin = $this->CPanel->load();
		
		$this->vars = $this->CPanel->notifications();
		
		$this->CPanel_Model = $this->model("CPanel_Model");
		
		$this->Templates = $this->core("Templates");
		
		$this->Templates->theme("cpanel");
		
		$this->Model = ucfirst($this->application) ."_Model";
		
		$this->{"$this->Model"} = $this->model($this->Model);		
	}
	
	public function index() {
		if($this->isAdmin) {
			redirect("cpanel");
		} else {
			$this->login();
		}
	}

	public function check() {
		if(POST("trash") and is_array(POST("records"))) { 
			foreach(POST("records") as $record) {
				$this->trash($record, TRUE); 
			}

			redirect("$this->application/cpanel/results");
		} elseif(POST("restore") and is_array(POST("records"))) {
			foreach(POST("records") as $record) {
				$this->restore($record, TRUE); 
			}

			redirect("$this->application/cpanel/results");
		} elseif(POST("delete") and is_array(POST("records"))) {
			foreach(POST("records") as $record) {
				$this->delete($record, TRUE); 
			}

			redirect("$this->application/cpanel/results");
		}

		return FALSE;
	}

	public function delete($ID = 0, $return = FALSE) {
		if(!$this->isAdmin) {
			$this->login();
		}
		
		if($this->CPanel_Model->delete($ID)) {
			if($return) {
				return TRUE;
			}

			redirect("$this->application/cpanel/results/trash");
		} else {
			if($return) {
				return FALSE;
			}

			redirect("$this->application/cpanel/results");
		}	
	}

	public function restore($ID = 0, $return = FALSE) { 
		if(!$this->isAdmin) {
			$this->login();
		}
		
		if($this->CPanel_Model->restore($ID)) {
			if($return) {
				return TRUE;
			}

			redirect("$this->application/cpanel/results/trash");
		} else {
			if($return) {
				return FALSE;
			}

			redirect("$this->application/cpanel/results");
		}
	}

	public function trash($ID = 0, $return = FALSE) {
		if(!$this->isAdmin) {
			$this->login();
		}
		
		if($this->CPanel_Model->trash($ID)) {		
			if($return) {
				return TRUE;
			}	

			redirect("$this->application/cpanel/results");
		} else {
			if($return) {
				return FALSE;
			}

			redirect("$this->application/cpanel/add");
		}
	}
	
	public function add() { 
		if(!$this->isAdmin) {
			$this->login();
		}

		$this->helper("forms");
		
		$this->title("Add");
		
		$this->CSS("forms", "cpanel");
		
		$this->js("redactorjs");
		$this->js("insert-html");
		$this->js("show-element");

		$this->CSS("www/lib/scripts/js/upload/client/fileuploader.css");
		$this->js("www/lib/scripts/js/upload/client/fileuploader.js");
		
		if(POST("save")) {
			
			$save = $this->{"$this->Model"}->cpanel("save");
			
			$this->vars["alert"] = $save;

		} elseif(POST("cancel")) {
			redirect("cpanel");
		}
		
		$this->vars["application"] = $this->CPanel->getApplicationID();

		$this->js("actions", $this->application);
		$this->js("tagsinput.min", "cpanel");
		$this->js("jquery-ui.min", "cpanel");
		$this->js("tags", "cpanel");
		
		$this->CSS("tagsinput", "cpanel");
		
		$this->vars["view"] = $this->view("add", TRUE, $this->application);
		
		$this->render("content", $this->vars);
	}
	
	
	
	public function login() {
		$this->title("Login");
		$this->CSS("login", "users");
		
		if(POST("connect")) {	
			$this->Users_Controller = $this->controller("Users_Controller");
			
			$this->Users_Controller->login("cpanel");
		} else {
			$this->vars["URL"]  = getURL();
			$this->vars["view"] = $this->view("login", TRUE, "cpanel");
		}
		
		$this->render("include", $this->vars);
		$this->rendering("header", "footer");
		
		exit;
	}
	
	public function results() {
		if(!$this->isAdmin) {
			$this->login();
		}

		$this->check();

		
		$this->title("Manage ". ucfirst($this->application));

		$this->CSS("results", "cpanel");
		$this->CSS("pagination");
		
		$this->js("checkbox");
			
		$trash = (segment(3, isLang()) === "trash") ? TRUE : FALSE;

				
		$this->vars["total"] 	  = $this->CPanel_Model->total($trash);
		$this->vars["tFoot"] 	  = $this->CPanel_Model->records($trash);
		$this->vars["message"]    = (!$this->vars["tFoot"]) ? "Error" : NULL;
		$this->vars["pagination"] = $this->CPanel_Model->getPagination($trash);
		$this->vars["trash"]  	  = $trash;	
		$this->vars["search"] 	  = getSearch(); 			
		$this->vars["view"]       = $this->view("results", TRUE, $this->application);
		

		$this->render("content", $this->vars);
	}
	
	public function edit($ID = 0) {
		if(!$this->isAdmin) {
			$this->login();
		}
		
		if((int) $ID === 0) { 
			redirect("$this->application/cpanel/results");
		}

		$this->title("Edit");
		
		$this->helper("forms");
		
		$this->title("Add");
		
		$this->CSS("forms", "cpanel");
		
		$this->js("redactorjs");
		$this->js("insert-html");
		$this->js("show-element");

		$this->CSS("www/lib/scripts/js/upload/client/fileuploader.css");
		$this->js("www/lib/scripts/js/upload/client/fileuploader.js");
		
		if(POST("edit")) {
			$this->vars["alert"] = $this->{"$this->Model"}->cpanel("edit");
		} elseif(POST("cancel")) {
			redirect("cpanel");
		} 
		
		$data = $this->{"$this->Model"}->getByID($ID);
		
		if($data) {
			//$this->Library 	  = $this->classes("Library", "cpanel");
			//$this->Categories = $this->classes("Categories", "categories");		
			
			$this->vars["data"]				= $data;
			//$this->vars["muralImage"] 		= $this->{"$this->Model"}->getMuralByID(segment(3, isLang()));
			//$this->vars["muralDeleteURL"] 	= ($this->vars["muralImage"]) ? path("$this->application/cpanel/delete-mural/$ID")  : NULL;
			$this->vars["application"]		= $this->CPanel->getApplicationID($this->application);
			
			$this->js("www/lib/scripts/ajax/password.js", TRUE);
			$this->js("jquery-ui.min", "cpanel");
					
			$this->vars["view"] = $this->view("add", TRUE, $this->application);
			
			$this->render("content", $this->vars);
		} else {
			redirect("$this->application/cpanel/results");
		}
	}
}