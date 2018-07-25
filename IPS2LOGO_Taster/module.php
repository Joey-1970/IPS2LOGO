<?
    // Klassendefinition
    class IPS2LOGO_Taster extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		
	}
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("LOGO_ID", 0);
		$this->RegisterPropertyInteger("State_ID", 0);
		$this->RegisterPropertyInteger("Switch_ID", 0);
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Switch", 10);
		$this->EnableAction("State");
        }
       	
	public function GetConfigurationForm() { 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 200, "icon" => "error", "caption" => "Instanz ist fehlerhaft"); 
		
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
 		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "label" => "LOGO");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "LOGO_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Status");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "Status_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Schalter");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "Switch_ID", "caption" => "Variablen ID");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		// Registrierung für die Änderung am LOGO-Status
		If ($this->ReadPropertyInteger("LOGO_ID") > 0) {
			//$this->RegisterMessage($this->ReadPropertyInteger("LOGO_ID"), 10603);
		}
		
		// Registrierung für die Änderung des Status
		If ($this->ReadPropertyInteger("Status_ID") > 0) {
			$this->RegisterMessage($this->ReadPropertyInteger("Status_ID"), 10603);
		}
		
            
		If ($this->ReadPropertyBoolean("Open") == true) AND 
			($this->ReadPropertyInteger("LOGO_ID") > 0) AND 
			($this->ReadPropertyInteger("Status_ID") > 0) AND 
			($this->ReadPropertyInteger("Switch_ID") > 0) {
			
			$this->SetStatus(102);
		}
		else {
			$this->SetStatus(104);
		}
	}
	
	
	// Beginn der Funktionen
	
	    
	
	    
	
}
?>
